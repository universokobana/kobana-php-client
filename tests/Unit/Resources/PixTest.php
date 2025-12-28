<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit\Resources;

use Kobana\Resources\Charge\Pix;
use Kobana\Tests\TestCase;

class PixTest extends TestCase
{
    public function testCanCreatePixInstance(): void
    {
        $data = $this->samplePixData();
        $pix = new Pix($data, $this->connection);

        $this->assertEquals(1, $pix->id);
        $this->assertEquals('PIX123456789', $pix->txid);
        $this->assertEquals(50.00, $pix->amount);
        $this->assertEquals('pending', $pix->status);
    }

    public function testStatusCheckMethods(): void
    {
        $pix = new Pix(['status' => 'pending'], $this->connection);
        $this->assertTrue($pix->isPending());
        $this->assertFalse($pix->isPaid());
        $this->assertFalse($pix->isFinal());

        $pix = new Pix(['status' => 'paid'], $this->connection);
        $this->assertTrue($pix->isPaid());
        $this->assertTrue($pix->isFinal());

        $pix = new Pix(['status' => 'expired'], $this->connection);
        $this->assertTrue($pix->isExpired());
        $this->assertTrue($pix->isFinal());

        $pix = new Pix(['status' => 'canceled'], $this->connection);
        $this->assertTrue($pix->isCanceled());
        $this->assertTrue($pix->isFinal());
    }

    public function testGetQrCode(): void
    {
        $pix = new Pix([
            'qrCode' => '00020126580014br.gov.bcb.pix...',
        ], $this->connection);

        $this->assertEquals('00020126580014br.gov.bcb.pix...', $pix->getQrCode());
    }

    public function testGetQrCodeUrl(): void
    {
        $pix = new Pix([
            'qrCodeUrl' => 'https://api.kobana.com.br/pix/qrcode/1.png',
        ], $this->connection);

        $this->assertEquals('https://api.kobana.com.br/pix/qrcode/1.png', $pix->getQrCodeUrl());
    }

    public function testGetTxid(): void
    {
        $pix = new Pix([
            'txid' => 'PIX123456789',
        ], $this->connection);

        $this->assertEquals('PIX123456789', $pix->getTxid());
    }

    public function testGetPaidAmount(): void
    {
        $pix = new Pix([
            'paidAmount' => 50.00,
        ], $this->connection);

        $this->assertEquals(50.00, $pix->getPaidAmount());
    }

    public function testGetEndpoint(): void
    {
        $this->assertEquals('/charge/pix', Pix::getEndpoint());
    }

    public function testGetApiVersion(): void
    {
        $this->assertEquals('v2', Pix::getApiVersion());
    }
}
