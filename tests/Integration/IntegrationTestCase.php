<?php

declare(strict_types=1);

namespace Kobana\Tests\Integration;

use Kobana\Client;
use Kobana\Kobana;
use PHPUnit\Framework\TestCase;
use VCR\VCR;

/**
 * Base test case for integration tests with VCR recording.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected Client $client;
    protected static string $fixturesPath = __DIR__ . '/../fixtures';

    protected function setUp(): void
    {
        parent::setUp();

        // Load .env if not already loaded
        if (!isset($_ENV['KOBANA_API_TOKEN']) || $_ENV['KOBANA_API_TOKEN'] === 'test_token') {
            if (file_exists(__DIR__ . '/../../.env')) {
                $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
                $dotenv->safeLoad();
            }
        }

        // Ensure we have an API token
        $apiToken = $_ENV['KOBANA_API_TOKEN'] ?? null;

        if (empty($apiToken) || $apiToken === 'test_token') {
            $this->markTestSkipped('KOBANA_API_TOKEN environment variable is required for integration tests.');
        }

        // Configure the client
        Kobana::configure([
            'apiToken' => $apiToken,
            'environment' => $_ENV['KOBANA_ENVIRONMENT'] ?? 'sandbox',
            'debug' => false,
        ]);

        $this->client = Kobana::getInstance();
    }

    protected function tearDown(): void
    {
        Kobana::reset();
        parent::tearDown();
    }

    /**
     * Start VCR recording with a specific cassette.
     */
    protected function useCassette(string $name): void
    {
        VCR::turnOn();
        VCR::insertCassette($name . '.json');
    }

    /**
     * Stop VCR recording and sanitize the cassette.
     */
    protected function stopCassette(string $name): void
    {
        VCR::eject();
        VCR::turnOff();

        // Sanitize the cassette to remove sensitive data
        $cassettePath = self::$fixturesPath . '/' . $name . '.json';
        if (function_exists('sanitizeCassette')) {
            sanitizeCassette($cassettePath);
        }
    }

    /**
     * Generate a unique identifier for test data.
     */
    protected function uniqueId(): string
    {
        return 'test_' . time() . '_' . mt_rand(1000, 9999);
    }

    /**
     * Get a future date string.
     */
    protected function futureDate(int $daysAhead = 30): string
    {
        return date('Y-m-d', strtotime("+{$daysAhead} days"));
    }

    /**
     * Generate a valid CPF for testing.
     */
    protected function generateCpf(): string
    {
        // Generate a valid CPF (Brazilian individual taxpayer number)
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[$i] = mt_rand(0, 9);
        }

        // Calculate first verification digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += $n[$i] * (10 - $i);
        }
        $n[9] = ($sum * 10) % 11;
        if ($n[9] === 10) {
            $n[9] = 0;
        }

        // Calculate second verification digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += $n[$i] * (11 - $i);
        }
        $n[10] = ($sum * 10) % 11;
        if ($n[10] === 10) {
            $n[10] = 0;
        }

        return implode('', $n);
    }
}
