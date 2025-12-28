<?php

declare(strict_types=1);

namespace Kobana;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Kobana\Exceptions\ApiException;
use Kobana\Exceptions\ConnectionException;
use Kobana\Exceptions\ResourceNotFoundException;
use Kobana\Exceptions\UnauthorizedException;
use Kobana\Exceptions\ValidationException;
use Kobana\Support\Str;

/**
 * HTTP connection handler.
 */
class Connection
{
    private Configuration $config;
    private ?HttpClient $httpClient = null;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->config->validate();
    }

    /**
     * Get the configuration.
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Get or create the HTTP client.
     */
    protected function getHttpClient(): HttpClient
    {
        if ($this->httpClient === null) {
            $this->httpClient = new HttpClient([
                'timeout' => $this->config->getTimeout(),
                'http_errors' => true,
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Set a custom HTTP client (useful for testing).
     */
    public function setHttpClient(HttpClient $client): void
    {
        $this->httpClient = $client;
    }

    /**
     * Make a GET request.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function get(string $path, array $params = [], ?string $apiVersion = null): array
    {
        return $this->request('GET', $path, [
            'query' => Str::keysToSnakeCase($params),
        ], $apiVersion);
    }

    /**
     * Make a POST request.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function post(string $path, array $data = [], array $options = [], ?string $apiVersion = null): array
    {
        $requestOptions = [
            'json' => Str::keysToSnakeCase($data),
        ];

        if (isset($options['idempotencyKey'])) {
            $requestOptions['headers'] = [
                'X-Idempotency-Key' => $options['idempotencyKey'],
            ];
        }

        return $this->request('POST', $path, $requestOptions, $apiVersion);
    }

    /**
     * Make a PUT request.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function put(string $path, array $data = [], ?string $apiVersion = null): array
    {
        return $this->request('PUT', $path, [
            'json' => Str::keysToSnakeCase($data),
        ], $apiVersion);
    }

    /**
     * Make a PATCH request.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function patch(string $path, array $data = [], ?string $apiVersion = null): array
    {
        return $this->request('PATCH', $path, [
            'json' => Str::keysToSnakeCase($data),
        ], $apiVersion);
    }

    /**
     * Make a DELETE request.
     */
    public function delete(string $path, ?string $apiVersion = null): bool
    {
        $this->request('DELETE', $path, [], $apiVersion);
        return true;
    }

    /**
     * Make an HTTP request.
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    protected function request(string $method, string $path, array $options = [], ?string $apiVersion = null): array
    {
        $url = $this->config->getVersionedBaseUrl($apiVersion) . $path;

        $options['headers'] = array_merge(
            $this->config->getHeaders(),
            $options['headers'] ?? []
        );

        if ($this->config->isDebug()) {
            $this->logRequest($method, $url, $options);
        }

        try {
            $response = $this->getHttpClient()->request($method, $url, $options);
            $body = $this->parseResponseBody($response->getBody()->getContents());

            if ($this->config->isDebug()) {
                $this->logResponse($response->getStatusCode(), $body);
            }

            return $body;
        } catch (ConnectException $e) {
            throw ConnectionException::networkError($e->getMessage());
        } catch (ClientException|ServerException $e) {
            $this->handleErrorResponse($e, $path);
        } catch (RequestException $e) {
            throw ConnectionException::networkError($e->getMessage());
        }

        return [];
    }

    /**
     * Parse response body to array.
     *
     * @return array<string, mixed>
     */
    protected function parseResponseBody(string $body): array
    {
        if (empty($body)) {
            return [];
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['raw' => $body];
        }

        return Str::keysToCamelCase($data);
    }

    /**
     * Handle error responses from the API.
     *
     * @throws ApiException
     * @throws UnauthorizedException
     * @throws ResourceNotFoundException
     * @throws ValidationException
     */
    protected function handleErrorResponse(ClientException|ServerException $e, string $path): never
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $this->parseResponseBody($response->getBody()->getContents());

        if ($this->config->isDebug()) {
            $this->logResponse($statusCode, $body);
        }

        match ($statusCode) {
            401 => throw UnauthorizedException::invalidToken(),
            403 => throw UnauthorizedException::insufficientPermissions(),
            404 => throw new ResourceNotFoundException($this->extractResourceType($path), $this->extractResourceId($path)),
            422 => throw new ValidationException($this->extractValidationErrors($body)),
            default => throw ApiException::fromResponse($statusCode, $body),
        };
    }

    /**
     * Extract validation errors from response body.
     *
     * @param array<string, mixed> $body
     * @return array<string, array<string>>
     */
    protected function extractValidationErrors(array $body): array
    {
        if (isset($body['errors']) && is_array($body['errors'])) {
            $errors = [];
            foreach ($body['errors'] as $error) {
                if (is_array($error) && isset($error['field'])) {
                    $field = Str::toCamelCase($error['field']);
                    $message = $error['message'] ?? $error['title'] ?? 'Invalid value';
                    $errors[$field][] = $message;
                } elseif (is_array($error) && isset($error['title'])) {
                    $errors['base'][] = $error['title'];
                } elseif (is_string($error)) {
                    $errors['base'][] = $error;
                }
            }
            return $errors;
        }

        if (isset($body['error'])) {
            return ['base' => [$body['error']]];
        }

        return ['base' => ['Validation failed']];
    }

    /**
     * Extract resource type from path.
     */
    protected function extractResourceType(string $path): string
    {
        $parts = explode('/', trim($path, '/'));
        return $parts[0] ?? 'Resource';
    }

    /**
     * Extract resource ID from path.
     */
    protected function extractResourceId(string $path): string
    {
        $parts = explode('/', trim($path, '/'));
        return $parts[1] ?? 'unknown';
    }

    /**
     * Log request details (for debugging).
     *
     * @param array<string, mixed> $options
     */
    protected function logRequest(string $method, string $url, array $options): void
    {
        $headers = $options['headers'] ?? [];
        $headers = $this->redactSensitiveHeaders($headers);

        error_log(sprintf(
            "[Kobana] Request: %s %s\nHeaders: %s\nBody: %s",
            $method,
            $url,
            json_encode($headers),
            json_encode($options['json'] ?? $options['query'] ?? [])
        ));
    }

    /**
     * Log response details (for debugging).
     *
     * @param array<string, mixed> $body
     */
    protected function logResponse(int $statusCode, array $body): void
    {
        error_log(sprintf(
            "[Kobana] Response: %d\nBody: %s",
            $statusCode,
            json_encode($body)
        ));
    }

    /**
     * Redact sensitive information from headers.
     *
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    protected function redactSensitiveHeaders(array $headers): array
    {
        if (isset($headers['Authorization'])) {
            $headers['Authorization'] = 'Bearer [REDACTED]';
        }

        return $headers;
    }
}
