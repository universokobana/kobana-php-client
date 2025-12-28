<?php

declare(strict_types=1);

namespace Kobana\Exceptions;

/**
 * Exception thrown when SDK configuration is invalid or missing.
 */
class ConfigurationException extends KobanaException
{
    public static function missingApiToken(): self
    {
        return new self(
            'API token is required. Set it via KOBANA_API_TOKEN environment variable or pass it to configure().'
        );
    }

    public static function invalidEnvironment(string $environment): self
    {
        return new self(
            "Invalid environment '{$environment}'. Valid options are: sandbox, production, development."
        );
    }

    public static function notConfigured(): self
    {
        return new self(
            'Kobana SDK is not configured. Call Kobana::configure() first or create a new Client instance.'
        );
    }
}
