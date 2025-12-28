<?php

declare(strict_types=1);

namespace Kobana\Exceptions;

/**
 * Exception thrown when API validation fails.
 */
class ValidationException extends KobanaException
{
    /**
     * Validation errors from the API.
     *
     * @var array<string, array<string>>
     */
    protected array $errors;

    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(array $errors, string $message = 'Validation failed')
    {
        $this->errors = $errors;

        $formattedErrors = $this->formatErrors();
        $fullMessage = $message . ($formattedErrors ? ": {$formattedErrors}" : '');

        parent::__construct($fullMessage);
    }

    /**
     * Get the validation errors.
     *
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get errors for a specific field.
     *
     * @return array<string>
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Check if a specific field has errors.
     */
    public function hasFieldError(string $field): bool
    {
        return isset($this->errors[$field]) && count($this->errors[$field]) > 0;
    }

    /**
     * Format errors as a human-readable string.
     */
    protected function formatErrors(): string
    {
        $parts = [];

        foreach ($this->errors as $field => $messages) {
            if (is_array($messages)) {
                $parts[] = "{$field}: " . implode(', ', $messages);
            } else {
                $parts[] = "{$field}: {$messages}";
            }
        }

        return implode('; ', $parts);
    }
}
