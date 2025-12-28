<?php

declare(strict_types=1);

namespace Kobana\Tests\Integration;

use Kobana\Kobana;
use Kobana\Resources\Financial\BankBilletAccount;

/**
 * Integration tests for BankBilletAccount resource.
 *
 * These tests make real API calls to the sandbox environment
 * and record the responses using php-vcr.
 */
class BankBilletAccountIntegrationTest extends IntegrationTestCase
{
    private const CASSETTE_PREFIX = 'bank_billet_account';

    public function testCanListBankBilletAccounts(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_list';
        $this->useCassette($cassette);

        try {
            $accounts = Kobana::financial()->bankBilletAccount()::all([
                'page' => 1,
                'perPage' => 10,
            ], $this->client->getConnection());

            $this->assertNotNull($accounts);
            $this->assertGreaterThanOrEqual(0, $accounts->count());
            $this->assertEquals(1, $accounts->getCurrentPage());
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanFindBankBilletAccount(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_find';
        $this->useCassette($cassette);

        try {
            // First, get a list to find an ID
            $accounts = BankBilletAccount::all([
                'page' => 1,
                'perPage' => 1,
            ], $this->client->getConnection());

            if ($accounts->isEmpty()) {
                $this->markTestSkipped('No bank billet accounts available.');
            }

            $accountId = $accounts->first()->id;

            // Now find by ID
            $account = BankBilletAccount::find($accountId, [], $this->client->getConnection());

            $this->assertNotNull($account);
            $this->assertEquals($accountId, $account->id);
            $this->assertNotNull($account->status);
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testAccountStatusMethods(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_status';
        $this->useCassette($cassette);

        try {
            $accounts = BankBilletAccount::all([
                'page' => 1,
                'perPage' => 5,
            ], $this->client->getConnection());

            if ($accounts->isEmpty()) {
                $this->markTestSkipped('No bank billet accounts available.');
            }

            foreach ($accounts as $account) {
                $status = $account->status;
                $this->assertNotNull($status);

                // At least one status method should return true
                $hasStatus = $account->isPending()
                    || $account->isValidating()
                    || $account->isActive()
                    || $account->isCanceled();

                $this->assertTrue($hasStatus, "Account should have a recognized status, got: {$status}");
            }
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanGetAccountDetails(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_details';
        $this->useCassette($cassette);

        try {
            $accounts = BankBilletAccount::all([
                'page' => 1,
                'perPage' => 1,
            ], $this->client->getConnection());

            if ($accounts->isEmpty()) {
                $this->markTestSkipped('No bank billet accounts available.');
            }

            $account = $accounts->first();

            // Test getter methods
            $this->assertNotNull($account->id);

            // These might be null depending on the account
            $bankSlug = $account->getBankContractSlug();
            $beneficiaryName = $account->getBeneficiaryName();

            // If active, should have beneficiary info
            if ($account->isActive()) {
                $this->assertNotNull($beneficiaryName);
            }
        } finally {
            $this->stopCassette($cassette);
        }
    }
}
