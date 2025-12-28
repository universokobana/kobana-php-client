<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit\Resources;

use Kobana\Resources\Charge\BankBillet;
use Kobana\Tests\TestCase;

class BankBilletTest extends TestCase
{
    public function testCanCreateBankBilletInstance(): void
    {
        $data = $this->sampleBankBilletData();
        $billet = new BankBillet($data, $this->connection);

        $this->assertEquals(1, $billet->id);
        $this->assertEquals(100.50, $billet->amount);
        $this->assertEquals('opened', $billet->status);
        $this->assertEquals('John Doe', $billet->customerPersonName);
    }

    public function testStatusCheckMethods(): void
    {
        $billet = new BankBillet(['status' => 'opened'], $this->connection);
        $this->assertTrue($billet->isOpened());
        $this->assertFalse($billet->isPaid());
        $this->assertFalse($billet->isCanceled());
        $this->assertFalse($billet->isFinal());
        $this->assertTrue($billet->isPending());

        $billet = new BankBillet(['status' => 'paid'], $this->connection);
        $this->assertTrue($billet->isPaid());
        $this->assertTrue($billet->isFinal());
        $this->assertFalse($billet->isPending());

        $billet = new BankBillet(['status' => 'canceled'], $this->connection);
        $this->assertTrue($billet->isCanceled());
        $this->assertTrue($billet->isFinal());

        $billet = new BankBillet(['status' => 'overdue'], $this->connection);
        $this->assertTrue($billet->isOverdue());

        $billet = new BankBillet(['status' => 'generating'], $this->connection);
        $this->assertTrue($billet->isGenerating());
        $this->assertTrue($billet->isPending());
    }

    public function testGetBarcode(): void
    {
        $billet = new BankBillet([
            'line' => '23793.38128 60000.000003 00000.000406 1 84340000010050',
        ], $this->connection);

        $this->assertEquals('23793.38128 60000.000003 00000.000406 1 84340000010050', $billet->getBarcode());
    }

    public function testGetOurNumber(): void
    {
        $billet = new BankBillet([
            'ourNumber' => '00000001',
        ], $this->connection);

        $this->assertEquals('00000001', $billet->getOurNumber());
    }

    public function testGetEndpoint(): void
    {
        $this->assertEquals('/bank_billets', BankBillet::getEndpoint());
    }

    public function testGetApiVersion(): void
    {
        $this->assertEquals('v1', BankBillet::getApiVersion());
    }

    public function testAttributeAccess(): void
    {
        $billet = new BankBillet([], $this->connection);

        $billet->amount = 200.00;
        $billet->customerPersonName = 'Jane Doe';

        $this->assertEquals(200.00, $billet->amount);
        $this->assertEquals('Jane Doe', $billet->customerPersonName);
    }

    public function testToArray(): void
    {
        $data = $this->sampleBankBilletData();
        $billet = new BankBillet($data, $this->connection);

        $array = $billet->toArray();

        $this->assertEquals($data, $array);
    }

    public function testToJson(): void
    {
        $data = ['id' => 1, 'amount' => 100];
        $billet = new BankBillet($data, $this->connection);

        $json = $billet->toJson();

        $this->assertJson($json);
        $this->assertEquals($data, json_decode($json, true));
    }

    public function testIsDirty(): void
    {
        $billet = new BankBillet(['id' => 1, 'amount' => 100], $this->connection);

        $this->assertFalse($billet->isDirty());

        $billet->amount = 200;

        $this->assertTrue($billet->isDirty());
        $this->assertEquals(['amount' => 200], $billet->getDirty());
    }

    public function testExists(): void
    {
        $billet = new BankBillet([], $this->connection);
        $this->assertFalse($billet->exists());

        $billet = new BankBillet(['id' => 1], $this->connection);
        $this->assertTrue($billet->exists());
    }
}
