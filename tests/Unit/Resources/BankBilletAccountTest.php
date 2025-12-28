<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit\Resources;

use Kobana\Resources\Financial\BankBilletAccount;
use Kobana\Tests\TestCase;

class BankBilletAccountTest extends TestCase
{
    public function testCanCreateBankBilletAccountInstance(): void
    {
        $data = $this->sampleBankBilletAccountData();
        $account = new BankBilletAccount($data, $this->connection);

        $this->assertEquals(1, $account->id);
        $this->assertEquals('bradesco-bs-9', $account->bankContractSlug);
        $this->assertEquals('active', $account->status);
        $this->assertEquals('Company LTDA', $account->beneficiaryName);
    }

    public function testStatusCheckMethods(): void
    {
        $account = new BankBilletAccount(['status' => 'pending'], $this->connection);
        $this->assertTrue($account->isPending());
        $this->assertFalse($account->isActive());

        $account = new BankBilletAccount(['status' => 'validating'], $this->connection);
        $this->assertTrue($account->isValidating());
        $this->assertTrue($account->isHomologating());

        $account = new BankBilletAccount(['status' => 'active'], $this->connection);
        $this->assertTrue($account->isActive());
        $this->assertTrue($account->isHomologated());

        $account = new BankBilletAccount(['status' => 'canceled'], $this->connection);
        $this->assertTrue($account->isCanceled());
    }

    public function testGetBankContractSlug(): void
    {
        $account = new BankBilletAccount([
            'bankContractSlug' => 'bradesco-bs-9',
        ], $this->connection);

        $this->assertEquals('bradesco-bs-9', $account->getBankContractSlug());
    }

    public function testGetBeneficiaryName(): void
    {
        $account = new BankBilletAccount([
            'beneficiaryName' => 'Company LTDA',
        ], $this->connection);

        $this->assertEquals('Company LTDA', $account->getBeneficiaryName());
    }

    public function testGetBeneficiaryCnpjCpf(): void
    {
        $account = new BankBilletAccount([
            'beneficiaryCnpjCpf' => '12345678000199',
        ], $this->connection);

        $this->assertEquals('12345678000199', $account->getBeneficiaryCnpjCpf());
    }

    public function testGetNextOurNumber(): void
    {
        $account = new BankBilletAccount([
            'nextOurNumber' => 1,
        ], $this->connection);

        $this->assertEquals(1, $account->getNextOurNumber());
    }

    public function testGetHomologatedAt(): void
    {
        $account = new BankBilletAccount([
            'homologatedAt' => '2025-01-01T00:00:00Z',
        ], $this->connection);

        $this->assertEquals('2025-01-01T00:00:00Z', $account->getHomologatedAt());
    }

    public function testGetEndpoint(): void
    {
        $this->assertEquals('/bank_billet_accounts', BankBilletAccount::getEndpoint());
    }

    public function testGetApiVersion(): void
    {
        $this->assertEquals('v1', BankBilletAccount::getApiVersion());
    }
}
