<?php

declare(strict_types=1);

namespace Kobana;

use Kobana\Exceptions\ConfigurationException;
use Kobana\Resources\Charge\ChargeProxy;
use Kobana\Resources\Financial\FinancialProxy;

/**
 * Global singleton access to Kobana SDK.
 */
class Kobana
{
    private static ?Client $instance = null;

    /**
     * Prevent instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Configure the global Kobana instance.
     *
     * @param array<string, mixed> $options
     */
    public static function configure(array $options): void
    {
        self::$instance = new Client($options);
    }

    /**
     * Get the global client instance.
     *
     * @throws ConfigurationException
     */
    public static function getInstance(): Client
    {
        if (self::$instance === null) {
            throw ConfigurationException::notConfigured();
        }

        return self::$instance;
    }

    /**
     * Reset the global instance.
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /**
     * Check if the SDK is configured.
     */
    public static function isConfigured(): bool
    {
        return self::$instance !== null;
    }

    /**
     * Access charge resources.
     *
     * @throws ConfigurationException
     */
    public static function charge(): ChargeProxy
    {
        return self::getInstance()->charge();
    }

    /**
     * Access financial resources.
     *
     * @throws ConfigurationException
     */
    public static function financial(): FinancialProxy
    {
        return self::getInstance()->financial();
    }
}
