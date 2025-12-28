<?php

declare(strict_types=1);

namespace Kobana\Tests\Integration;

use Kobana\Kobana;
use Kobana\Resources\Financial\Account;

/**
 * Integration tests for Account resource.
 *
 * These tests make real API calls to the sandbox environment
 * and record the responses using php-vcr.
 */
class AccountIntegrationTest extends IntegrationTestCase
{
    private const CASSETTE_PREFIX = 'account';

    public function testCanGetCurrentAccount(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_current';
        $this->useCassette($cassette);

        try {
            $account = Account::current($this->client->getConnection());

            $this->assertNotNull($account);
            $this->assertNotNull($account->id);
            $this->assertTrue($account->exists());
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanGetAccountDetails(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_details';
        $this->useCassette($cassette);

        try {
            $account = Account::current($this->client->getConnection());

            if ($account === null) {
                $this->markTestSkipped('No account available.');
            }

            // Test getter methods
            $name = $account->getName();
            $email = $account->getEmail();

            // At least some info should be present
            $this->assertNotNull($account->id);
        } finally {
            $this->stopCassette($cassette);
        }
    }
}
