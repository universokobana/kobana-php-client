<?php

declare(strict_types=1);

namespace Kobana\Exceptions;

/**
 * Exception thrown for general API errors.
 */
class ApiException extends KobanaException
{
    protected int $statusCode;
    protected mixed $responseBody;

    /**
     * @var array<mixed>|null
     */
    protected ?array $errors;

    public function __construct(
        string $message,
        int $statusCode,
        mixed $responseBody = null,
        ?array $errors = null,
        ?\Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
        $this->errors = $errors;

        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Get the HTTP status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the response body.
     */
    public function getResponseBody(): mixed
    {
        return $this->responseBody;
    }

    /**
     * Get the errors from the response.
     *
     * @return array<mixed>|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Create an API exception from an HTTP response.
     */
    public static function fromResponse(int $statusCode, mixed $body): self
    {
        $message = 'API request failed';
        $errors = null;

        if (is_array($body)) {
            if (isset($body['error'])) {
                $message = $body['error'];
            } elseif (isset($body['message'])) {
                $message = $body['message'];
            }

            if (isset($body['errors'])) {
                $errors = $body['errors'];
            }
        }

        return new self($message, $statusCode, $body, $errors);
    }
}
