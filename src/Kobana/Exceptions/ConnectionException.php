<?php

declare(strict_types=1);

namespace Kobana\Exceptions;

/**
 * Exception thrown when there are network or connection issues.
 */
class ConnectionException extends KobanaException
{
    public static function timeout(string $url): self
    {
        return new self("Request to {$url} timed out.");
    }

    public static function networkError(string $message): self
    {
        return new self("Network error: {$message}");
    }
}
