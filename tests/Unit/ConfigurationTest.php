<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit;

use Kobana\Configuration;
use Kobana\Exceptions\ConfigurationException;
use Kobana\Tests\TestCase;

class ConfigurationTest extends TestCase
{
    private array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Save and clear environment variables that affect Configuration
        $envVars = ['KOBANA_API_TOKEN', 'KOBANA_ENVIRONMENT', 'KOBANA_DEBUG'];
        foreach ($envVars as $var) {
            $this->originalEnv[$var] = $_ENV[$var] ?? null;
            unset($_ENV[$var]);
        }
    }

    protected function tearDown(): void
    {
        // Restore environment variables
        foreach ($this->originalEnv as $var => $value) {
            if ($value !== null) {
                $_ENV[$var] = $value;
            } else {
                unset($_ENV[$var]);
            }
        }

        parent::tearDown();
    }

    public function testCanCreateConfigurationWithOptions(): void
    {
        $config = new Configuration([
            'apiToken' => 'my_token',
            'environment' => 'production',
            'apiVersion' => 'v2',
            'debug' => true,
            'timeout' => 60,
        ]);

        $this->assertEquals('my_token', $config->getApiToken());
        $this->assertEquals('production', $config->getEnvironment());
        $this->assertEquals('v2', $config->getApiVersion());
        $this->assertTrue($config->isDebug());
        $this->assertEquals(60, $config->getTimeout());
    }

    public function testDefaultValues(): void
    {
        $config = new Configuration([
            'apiToken' => 'test',
        ]);

        $this->assertEquals('sandbox', $config->getEnvironment());
        $this->assertEquals('v1', $config->getApiVersion());
        $this->assertFalse($config->isDebug());
        $this->assertEquals(30, $config->getTimeout());
    }

    public function testValidateThrowsExceptionWhenTokenMissing(): void
    {
        $config = new Configuration([]);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('API token is required');

        $config->validate();
    }

    public function testValidateThrowsExceptionForInvalidEnvironment(): void
    {
        $config = new Configuration([
            'apiToken' => 'test',
            'environment' => 'invalid',
        ]);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Invalid environment 'invalid'");

        $config->validate();
    }

    public function testValidatePassesWithValidConfiguration(): void
    {
        $config = new Configuration([
            'apiToken' => 'test_token',
            'environment' => 'sandbox',
        ]);

        $config->validate();

        $this->assertTrue(true); // No exception thrown
    }

    public function testGetBaseUrl(): void
    {
        $sandboxConfig = new Configuration([
            'apiToken' => 'test',
            'environment' => 'sandbox',
        ]);
        $this->assertEquals('https://api-sandbox.kobana.com.br', $sandboxConfig->getBaseUrl());

        $productionConfig = new Configuration([
            'apiToken' => 'test',
            'environment' => 'production',
        ]);
        $this->assertEquals('https://api.kobana.com.br', $productionConfig->getBaseUrl());

        $devConfig = new Configuration([
            'apiToken' => 'test',
            'environment' => 'development',
        ]);
        $this->assertEquals('http://localhost:5000/api', $devConfig->getBaseUrl());
    }

    public function testGetVersionedBaseUrl(): void
    {
        $config = new Configuration([
            'apiToken' => 'test',
            'environment' => 'sandbox',
            'apiVersion' => 'v1',
        ]);

        $this->assertEquals('https://api-sandbox.kobana.com.br/v1', $config->getVersionedBaseUrl());
        $this->assertEquals('https://api-sandbox.kobana.com.br/v2', $config->getVersionedBaseUrl('v2'));
    }

    public function testGetHeaders(): void
    {
        $config = new Configuration([
            'apiToken' => 'my_secret_token',
        ]);

        $headers = $config->getHeaders();

        $this->assertEquals('Bearer my_secret_token', $headers['Authorization']);
        $this->assertEquals('application/json', $headers['Content-Type']);
        $this->assertEquals('application/json', $headers['Accept']);
        $this->assertStringContainsString('Kobana-PHP-Client', $headers['User-Agent']);
    }

    public function testCustomHeaders(): void
    {
        $config = new Configuration([
            'apiToken' => 'test',
            'customHeaders' => [
                'X-Custom-Header' => 'custom-value',
            ],
        ]);

        $headers = $config->getHeaders();

        $this->assertEquals('custom-value', $headers['X-Custom-Header']);
    }

    public function testAddCustomHeader(): void
    {
        $config = new Configuration(['apiToken' => 'test']);
        $config->addCustomHeader('X-Request-Id', 'abc123');

        $headers = $config->getHeaders();

        $this->assertEquals('abc123', $headers['X-Request-Id']);
    }

    public function testFluentSetters(): void
    {
        $config = new Configuration(['apiToken' => 'initial']);

        $result = $config
            ->setApiToken('new_token')
            ->setEnvironment('production')
            ->setApiVersion('v2')
            ->setDebug(true)
            ->setTimeout(120);

        $this->assertSame($config, $result);
        $this->assertEquals('new_token', $config->getApiToken());
        $this->assertEquals('production', $config->getEnvironment());
        $this->assertEquals('v2', $config->getApiVersion());
        $this->assertTrue($config->isDebug());
        $this->assertEquals(120, $config->getTimeout());
    }

    public function testToStringRedactsToken(): void
    {
        $config = new Configuration([
            'apiToken' => 'super_secret_token',
            'environment' => 'sandbox',
        ]);

        $string = (string) $config;

        $this->assertStringNotContainsString('super_secret_token', $string);
        $this->assertStringContainsString('***', $string);
        $this->assertStringContainsString('sandbox', $string);
    }

    public function testSerializationExcludesToken(): void
    {
        $config = new Configuration([
            'apiToken' => 'secret',
            'environment' => 'sandbox',
        ]);

        $serialized = serialize($config);
        $unserialized = unserialize($serialized);

        $this->assertNull($unserialized->getApiToken());
        $this->assertEquals('sandbox', $unserialized->getEnvironment());
    }
}
