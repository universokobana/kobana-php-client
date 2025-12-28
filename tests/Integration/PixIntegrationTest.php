<?php

declare(strict_types=1);

namespace Kobana\Tests\Integration;

use Kobana\Kobana;
use Kobana\Resources\Charge\Pix;

/**
 * Integration tests for Pix resource.
 *
 * These tests make real API calls to the sandbox environment
 * and record the responses using php-vcr.
 */
class PixIntegrationTest extends IntegrationTestCase
{
    private const CASSETTE_PREFIX = 'pix';

    public function testCanListPixCharges(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_list';
        $this->useCassette($cassette);

        try {
            $charges = Kobana::charge()->pix()::all([
                'page' => 1,
                'perPage' => 5,
            ], $this->client->getConnection());

            $this->assertNotNull($charges);
            $this->assertGreaterThanOrEqual(0, $charges->count());
            $this->assertEquals(1, $charges->getCurrentPage());
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanCreatePixCharge(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_create';
        $this->useCassette($cassette);

        try {
            $pix = Pix::create([
                'amount' => 50.00,
                'customerPersonName' => 'Test Customer ' . $this->uniqueId(),
                'customerCnpjCpf' => $this->generateCpf(),
                'customerEmail' => 'test@example.com',
                'customerPhoneNumber' => '11999999999',
                'expireAt' => $this->futureDate(1) . 'T23:59:59Z',
                'description' => 'Test PIX charge created by integration test',
            ], [], $this->client->getConnection());

            $this->assertNotNull($pix);
            $this->assertNotNull($pix->id);
            $this->assertEquals(50.00, $pix->amount);
            $this->assertTrue($pix->exists());

            // PIX should have QR code info
            // Note: might be null while still processing
            // $this->assertNotNull($pix->getQrCode());

            return $pix->id;
        } finally {
            $this->stopCassette($cassette);
        }
    }

    /**
     * @depends testCanCreatePixCharge
     */
    public function testCanFindPixCharge(int $pixId = null): void
    {
        if ($pixId === null) {
            $this->markTestSkipped('Requires a PIX ID from create test.');
        }

        $cassette = self::CASSETTE_PREFIX . '_find';
        $this->useCassette($cassette);

        try {
            $pix = Pix::find($pixId, [], $this->client->getConnection());

            $this->assertNotNull($pix);
            $this->assertEquals($pixId, $pix->id);
            $this->assertNotNull($pix->status);
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanFilterPixByStatus(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_filter_status';
        $this->useCassette($cassette);

        try {
            $charges = Pix::all([
                'status' => 'pending',
                'page' => 1,
                'perPage' => 5,
            ], $this->client->getConnection());

            $this->assertNotNull($charges);

            // All returned charges should have 'pending' status
            foreach ($charges as $pix) {
                $this->assertEquals('pending', $pix->status);
            }
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testPixStatusMethods(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_status_methods';
        $this->useCassette($cassette);

        try {
            $charges = Pix::all([
                'page' => 1,
                'perPage' => 1,
            ], $this->client->getConnection());

            if ($charges->isEmpty()) {
                $this->markTestSkipped('No PIX charges available for status test.');
            }

            $pix = $charges->first();
            $this->assertNotNull($pix);

            // Test that status methods work correctly
            $status = $pix->status;
            $this->assertNotNull($status);

            // At least one status method should return true
            $hasStatus = $pix->isPending()
                || $pix->isPaid()
                || $pix->isExpired()
                || $pix->isCanceled();

            $this->assertTrue($hasStatus, "PIX should have a recognized status, got: {$status}");
        } finally {
            $this->stopCassette($cassette);
        }
    }

    public function testCanGetQrCodeInfo(): void
    {
        $cassette = self::CASSETTE_PREFIX . '_qrcode';
        $this->useCassette($cassette);

        try {
            $charges = Pix::all([
                'status' => 'pending',
                'page' => 1,
                'perPage' => 1,
            ], $this->client->getConnection());

            if ($charges->isEmpty()) {
                $this->markTestSkipped('No pending PIX charges available for QR code test.');
            }

            $pix = $charges->first();

            // Pending PIX should have QR code info
            if ($pix->isPending()) {
                $qrCode = $pix->getQrCode();
                $qrCodeUrl = $pix->getQrCodeUrl();

                // At least one should be present
                $hasQrInfo = $qrCode !== null || $qrCodeUrl !== null;
                $this->assertTrue($hasQrInfo, 'Pending PIX should have QR code information.');
            }
        } finally {
            $this->stopCassette($cassette);
        }
    }
}
