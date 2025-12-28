<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit;

use Kobana\Client;
use Kobana\Configuration;
use Kobana\Connection;
use Kobana\Resources\Charge\ChargeProxy;
use Kobana\Resources\Financial\FinancialProxy;
use Kobana\Tests\TestCase;

class ClientTest extends TestCase
{
    public function testCanCreateClientWithOptions(): void
    {
        $client = new Client([
            'apiToken' => 'test_token',
            'environment' => 'sandbox',
        ]);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Configuration::class, $client->getConfig());
        $this->assertInstanceOf(Connection::class, $client->getConnection());
    }

    public function testChargeProxyIsLazyLoaded(): void
    {
        $client = new Client(['apiToken' => 'test']);

        $charge1 = $client->charge();
        $charge2 = $client->charge();

        $this->assertInstanceOf(ChargeProxy::class, $charge1);
        $this->assertSame($charge1, $charge2);
    }

    public function testFinancialProxyIsLazyLoaded(): void
    {
        $client = new Client(['apiToken' => 'test']);

        $financial1 = $client->financial();
        $financial2 = $client->financial();

        $this->assertInstanceOf(FinancialProxy::class, $financial1);
        $this->assertSame($financial1, $financial2);
    }

    public function testConfigurationIsAccessible(): void
    {
        $client = new Client([
            'apiToken' => 'my_token',
            'environment' => 'production',
        ]);

        $config = $client->getConfig();

        $this->assertEquals('my_token', $config->getApiToken());
        $this->assertEquals('production', $config->getEnvironment());
    }

    public function testConnectionIsAccessible(): void
    {
        $client = new Client(['apiToken' => 'test']);

        $connection = $client->getConnection();

        $this->assertInstanceOf(Connection::class, $connection);
        $this->assertSame($client->getConfig(), $connection->getConfig());
    }
}
