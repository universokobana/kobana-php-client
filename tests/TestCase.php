<?php

declare(strict_types=1);

namespace Kobana\Tests;

use Kobana\Configuration;
use Kobana\Connection;
use Kobana\Kobana;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for Kobana SDK tests.
 */
abstract class TestCase extends BaseTestCase
{
    protected Configuration $config;
    protected Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset global instance before each test
        Kobana::reset();

        // Create default test configuration
        $this->config = new Configuration([
            'apiToken' => 'test_token_12345',
            'environment' => 'sandbox',
            'debug' => false,
        ]);

        $this->connection = new Connection($this->config);
    }

    protected function tearDown(): void
    {
        Kobana::reset();
        parent::tearDown();
    }

    /**
     * Get a mock HTTP response.
     *
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    protected function mockResponse(array $body, int $statusCode = 200): array
    {
        return [
            'status' => $statusCode,
            'body' => $body,
        ];
    }

    /**
     * Get sample bank billet data.
     *
     * @return array<string, mixed>
     */
    protected function sampleBankBilletData(): array
    {
        return [
            'id' => 1,
            'amount' => 100.50,
            'status' => 'opened',
            'expireAt' => '2025-12-31',
            'customerPersonName' => 'John Doe',
            'customerCnpjCpf' => '12345678901',
            'customerEmail' => 'john@example.com',
            'ourNumber' => '00000001',
            'line' => '23793.38128 60000.000003 00000.000406 1 84340000010050',
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
        ];
    }

    /**
     * Get sample PIX data.
     *
     * @return array<string, mixed>
     */
    protected function samplePixData(): array
    {
        return [
            'id' => 1,
            'txid' => 'PIX123456789',
            'amount' => 50.00,
            'status' => 'pending',
            'qrCode' => '00020126580014br.gov.bcb.pix...',
            'qrCodeUrl' => 'https://api.kobana.com.br/pix/qrcode/1.png',
            'customerPersonName' => 'Jane Doe',
            'customerCnpjCpf' => '98765432100',
            'expireAt' => '2025-12-31T23:59:59Z',
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
        ];
    }

    /**
     * Get sample bank billet account data.
     *
     * @return array<string, mixed>
     */
    protected function sampleBankBilletAccountData(): array
    {
        return [
            'id' => 1,
            'bankContractSlug' => 'bradesco-bs-9',
            'status' => 'active',
            'beneficiaryName' => 'Company LTDA',
            'beneficiaryCnpjCpf' => '12345678000199',
            'agencyNumber' => '1234',
            'accountNumber' => '12345',
            'nextOurNumber' => 1,
            'homologatedAt' => '2025-01-01T00:00:00Z',
            'createdAt' => '2025-01-01T00:00:00Z',
            'updatedAt' => '2025-01-01T00:00:00Z',
        ];
    }
}
