<?php

declare(strict_types=1);

namespace Kobana\Tests\Integration;

use Kobana\Kobana;
use Kobana\Resources\Charge\BankBillet;

/**
 * Integration tests for BankBillet resource.
 *
 * These tests make real API calls to the sandbox environment
 * and record the responses using php-vcr.
 */
class BankBilletIntegrationTest extends IntegrationTestCase
{
    private const CASSETTE_PREFIX = 'bank_billet';

    public function testCanListBankBillets(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_list';
        $this->useCassette($cassette);

        try {
            $billets = Kobana::charge()->bankBillet()::all([
                'page' => 1,
                'perPage' => 5,
            ], $this->client->getConnection());

            $this->assertNotNull($billets);
            $this->assertGreaterThanOrEqual(0, $billets->count());
            $this->assertEquals(1, $billets->getCurrentPage());
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanCreateBankBillet(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_create';
        $this->useCassette($cassette);

        try {
            $billet = BankBillet::create([
                'amount' => 100.50,
                'expireAt' => $this->futureDate(30),
                'customerPersonName' => 'Test Customer ' . $this->uniqueId(),
                'customerCnpjCpf' => $this->generateCpf(),
                'customerEmail' => 'test@example.com',
                'customerPhoneNumber' => '11999999999',
                'customerState' => 'SP',
                'customerCityName' => 'São Paulo',
                'customerZipcode' => '01310100',
                'customerAddress' => 'Av. Paulista',
                'customerAddressNumber' => '1000',
                'customerNeighborhood' => 'Bela Vista',
                'description' => 'Test billet created by integration test',
            ], [], $this->client->getConnection());

            $this->assertNotNull($billet);
            $this->assertNotNull($billet->id);
            $this->assertEquals(100.50, $billet->amount);
            $this->assertTrue($billet->exists());
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanFindBankBillet(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_find';
        $this->useCassette($cassette);

        try {
            // First get a list to find an existing ID
            $billets = BankBillet::all(['page' => 1, 'perPage' => 1], $this->client->getConnection());

            if ($billets->isEmpty()) {
                $this->markTestSkipped('No billets available for find test.');
            }

            $billetId = $billets->first()->id;
            $billet = BankBillet::find($billetId, [], $this->client->getConnection());

            $this->assertNotNull($billet);
            $this->assertEquals($billetId, $billet->id);
            $this->assertNotNull($billet->status);
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanFilterBilletsByStatus(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_filter_status';
        $this->useCassette($cassette);

        try {
            $billets = BankBillet::all([
                'status' => 'opened',
                'page' => 1,
                'perPage' => 5,
            ], $this->client->getConnection());

            $this->assertNotNull($billets);

            // All returned billets should have 'opened' status
            foreach ($billets as $billet) {
                $this->assertEquals('opened', $billet->status);
            }
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testBilletStatusMethods(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_status_methods';
        $this->useCassette($cassette);

        try {
            $billets = BankBillet::all([
                'page' => 1,
                'perPage' => 1,
            ], $this->client->getConnection());

            if ($billets->isEmpty()) {
                $this->markTestSkipped('No billets available for status test.');
            }

            $billet = $billets->first();
            $this->assertNotNull($billet);

            // Test that status methods work correctly
            $status = $billet->status;
            $this->assertNotNull($status);

            // At least one status method should return true
            $hasStatus = $billet->isGenerating()
                || $billet->isOpened()
                || $billet->isPaid()
                || $billet->isCanceled()
                || $billet->isOverdue()
                || $billet->isBlocked();

            $this->assertTrue($hasStatus, "Billet should have a recognized status, got: {$status}");
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanGetBilletBarcode(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_barcode';
        $this->useCassette($cassette);

        try {
            $billets = BankBillet::all([
                'status' => 'opened',
                'page' => 1,
                'perPage' => 1,
            ], $this->client->getConnection());

            if ($billets->isEmpty()) {
                $this->markTestSkipped('No opened billets available for barcode test.');
            }

            $billet = $billets->first();

            // Opened billets should have a barcode
            if ($billet->isOpened()) {
                $barcode = $billet->getBarcode();
                // Barcode might be null if still generating
                if ($barcode !== null) {
                    $this->assertNotEmpty($barcode);
                }
            }
        } finally {
            $this->stopCassette($cassette);
        }
    }
}
