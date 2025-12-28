<?php

declare(strict_types=1);

namespace Kobana\Resources\Charge;

use Kobana\Connection;

/**
 * Proxy for charge-related resources.
 */
class ChargeProxy
{
    private Connection $connection;
    private ?BankBillet $bankBillet = null;
    private ?Pix $pix = null;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Access BankBillet resource.
     */
    public function bankBillet(): BankBillet
    {
        if ($this->bankBillet === null) {
            $this->bankBillet = new BankBillet([], $this->connection);
        }

        return $this->bankBillet;
    }

    /**
     * Access Pix resource.
     */
    public function pix(): Pix
    {
        if ($this->pix === null) {
            $this->pix = new Pix([], $this->connection);
        }

        return $this->pix;
    }
}
