<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit;

use Kobana\Client;
use Kobana\Exceptions\ConfigurationException;
use Kobana\Kobana;
use Kobana\Resources\Charge\ChargeProxy;
use Kobana\Resources\Financial\FinancialProxy;
use Kobana\Tests\TestCase;

class KobanaTest extends TestCase
{
    public function testConfigureCreatesGlobalInstance(): void
    {
        Kobana::configure([
            'apiToken' => 'test_token',
            'environment' => 'sandbox',
        ]);

        $this->assertTrue(Kobana::isConfigured());
        $this->assertInstanceOf(Client::class, Kobana::getInstance());
    }

    public function testGetInstanceThrowsExceptionWhenNotConfigured(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('not configured');

        Kobana::getInstance();
    }

    public function testResetClearsGlobalInstance(): void
    {
        Kobana::configure(['apiToken' => 'test']);
        $this->assertTrue(Kobana::isConfigured());

        Kobana::reset();
        $this->assertFalse(Kobana::isConfigured());
    }

    public function testChargeAccessesChargeProxy(): void
    {
        Kobana::configure(['apiToken' => 'test']);

        $charge = Kobana::charge();

        $this->assertInstanceOf(ChargeProxy::class, $charge);
    }

    public function testFinancialAccessesFinancialProxy(): void
    {
        Kobana::configure(['apiToken' => 'test']);

        $financial = Kobana::financial();

        $this->assertInstanceOf(FinancialProxy::class, $financial);
    }

    public function testChargeThrowsExceptionWhenNotConfigured(): void
    {
        $this->expectException(ConfigurationException::class);

        Kobana::charge();
    }

    public function testFinancialThrowsExceptionWhenNotConfigured(): void
    {
        $this->expectException(ConfigurationException::class);

        Kobana::financial();
    }

    public function testIsConfiguredReturnsFalseInitially(): void
    {
        $this->assertFalse(Kobana::isConfigured());
    }

    public function testReconfigureUpdatesInstance(): void
    {
        Kobana::configure(['apiToken' => 'token1', 'environment' => 'sandbox']);
        $instance1 = Kobana::getInstance();

        Kobana::configure(['apiToken' => 'token2', 'environment' => 'production']);
        $instance2 = Kobana::getInstance();

        $this->assertNotSame($instance1, $instance2);
        $this->assertEquals('production', $instance2->getConfig()->getEnvironment());
    }
}
