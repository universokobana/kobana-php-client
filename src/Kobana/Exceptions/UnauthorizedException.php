<?php

declare(strict_types=1);

namespace Kobana\Exceptions;

/**
 * Exception thrown when authentication fails or permissions are insufficient.
 */
class UnauthorizedException extends KobanaException
{
    public static function invalidToken(): self
    {
        return new self('Invalid or expired API token.');
    }

    public static function insufficientPermissions(): self
    {
        return new self('Insufficient permissions for this operation.');
    }
}
