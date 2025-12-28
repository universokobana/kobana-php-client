<?php

declare(strict_types=1);

namespace Kobana;

use Kobana\Resources\Charge\ChargeProxy;
use Kobana\Resources\Financial\FinancialProxy;

/**
 * Kobana API Client.
 */
class Client
{
    private Configuration $config;
    private Connection $connection;
    private ?ChargeProxy $charge = null;
    private ?FinancialProxy $financial = null;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->config = new Configuration($options);
        $this->connection = new Connection($this->config);
    }

    /**
     * Get the configuration.
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Get the connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Access charge resources (BankBillet, Pix).
     */
    public function charge(): ChargeProxy
    {
        if ($this->charge === null) {
            $this->charge = new ChargeProxy($this->connection);
        }

        return $this->charge;
    }

    /**
     * Access financial resources (Account, BankBilletAccount).
     */
    public function financial(): FinancialProxy
    {
        if ($this->financial === null) {
            $this->financial = new FinancialProxy($this->connection);
        }

        return $this->financial;
    }
}
