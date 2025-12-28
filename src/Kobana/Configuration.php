<?php

declare(strict_types=1);

namespace Kobana;

use Kobana\Exceptions\ConfigurationException;

/**
 * SDK Configuration.
 */
class Configuration
{
    public const ENV_SANDBOX = 'sandbox';
    public const ENV_PRODUCTION = 'production';
    public const ENV_DEVELOPMENT = 'development';

    public const API_VERSION_V1 = 'v1';
    public const API_VERSION_V2 = 'v2';

    private const BASE_URLS = [
        self::ENV_SANDBOX => 'https://api-sandbox.kobana.com.br',
        self::ENV_PRODUCTION => 'https://api.kobana.com.br',
        self::ENV_DEVELOPMENT => 'http://localhost:5000/api',
    ];

    private const VALID_ENVIRONMENTS = [
        self::ENV_SANDBOX,
        self::ENV_PRODUCTION,
        self::ENV_DEVELOPMENT,
    ];

    private ?string $apiToken;
    private string $environment;
    private string $apiVersion;
    private array $customHeaders;
    private bool $debug;
    private int $timeout;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->apiToken = $options['apiToken'] ?? $_ENV['KOBANA_API_TOKEN'] ?? null;
        $this->environment = $options['environment'] ?? $_ENV['KOBANA_ENVIRONMENT'] ?? self::ENV_SANDBOX;
        $this->apiVersion = $options['apiVersion'] ?? self::API_VERSION_V1;
        $this->customHeaders = $options['customHeaders'] ?? [];
        $this->debug = $options['debug'] ?? (bool) ($_ENV['KOBANA_DEBUG'] ?? false);
        $this->timeout = $options['timeout'] ?? 30;
    }

    /**
     * Validate the configuration.
     *
     * @throws ConfigurationException
     */
    public function validate(): void
    {
        if (empty($this->apiToken)) {
            throw ConfigurationException::missingApiToken();
        }

        if (!in_array($this->environment, self::VALID_ENVIRONMENTS, true)) {
            throw ConfigurationException::invalidEnvironment($this->environment);
        }
    }

    /**
     * Get the API token.
     */
    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    /**
     * Set the API token.
     */
    public function setApiToken(string $token): self
    {
        $this->apiToken = $token;
        return $this;
    }

    /**
     * Get the environment.
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Set the environment.
     */
    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Get the API version.
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Set the API version.
     */
    public function setApiVersion(string $version): self
    {
        $this->apiVersion = $version;
        return $this;
    }

    /**
     * Get custom headers.
     *
     * @return array<string, string>
     */
    public function getCustomHeaders(): array
    {
        return $this->customHeaders;
    }

    /**
     * Set custom headers.
     *
     * @param array<string, string> $headers
     */
    public function setCustomHeaders(array $headers): self
    {
        $this->customHeaders = $headers;
        return $this;
    }

    /**
     * Add a custom header.
     */
    public function addCustomHeader(string $name, string $value): self
    {
        $this->customHeaders[$name] = $value;
        return $this;
    }

    /**
     * Check if debug mode is enabled.
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Enable or disable debug mode.
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Get the request timeout in seconds.
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set the request timeout in seconds.
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Get the base URL for the current environment.
     */
    public function getBaseUrl(): string
    {
        return self::BASE_URLS[$this->environment] ?? self::BASE_URLS[self::ENV_SANDBOX];
    }

    /**
     * Get the full base URL with API version.
     */
    public function getVersionedBaseUrl(?string $version = null): string
    {
        $version = $version ?? $this->apiVersion;
        return $this->getBaseUrl() . '/' . $version;
    }

    /**
     * Get the headers for API requests.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return array_merge([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'Kobana-PHP-Client/1.0.0',
        ], $this->customHeaders);
    }

    /**
     * Redact sensitive information for logging.
     */
    public function __toString(): string
    {
        return sprintf(
            'Configuration(environment=%s, apiVersion=%s, debug=%s, token=***)',
            $this->environment,
            $this->apiVersion,
            $this->debug ? 'true' : 'false'
        );
    }

    /**
     * Prevent token from being serialized.
     *
     * @return array<string>
     */
    public function __sleep(): array
    {
        return ['environment', 'apiVersion', 'customHeaders', 'debug', 'timeout'];
    }

    /**
     * Initialize token as null after unserialization.
     */
    public function __wakeup(): void
    {
        $this->apiToken = null;
    }
}
