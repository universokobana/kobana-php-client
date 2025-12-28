<?php

declare(strict_types=1);

namespace Kobana\Exceptions;

use Exception;

/**
 * Base exception for all Kobana SDK errors.
 */
class KobanaException extends Exception
{
    /**
     * The original error message.
     */
    protected string $originalMessage;

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->originalMessage = $message;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the original error message.
     */
    public function getOriginalMessage(): string
    {
        return $this->originalMessage;
    }
}
