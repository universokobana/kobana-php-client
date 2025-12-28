<?php

declare(strict_types=1);

namespace Kobana\Resources\Financial;

use Kobana\Connection;

/**
 * Proxy for financial-related resources.
 */
class FinancialProxy
{
    private Connection $connection;
    private ?Account $account = null;
    private ?BankBilletAccount $bankBilletAccount = null;

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
     * Access Account resource.
     */
    public function account(): Account
    {
        if ($this->account === null) {
            $this->account = new Account([], $this->connection);
        }

        return $this->account;
    }

    /**
     * Access BankBilletAccount resource.
     */
    public function bankBilletAccount(): BankBilletAccount
    {
        if ($this->bankBilletAccount === null) {
            $this->bankBilletAccount = new BankBilletAccount([], $this->connection);
        }

        return $this->bankBilletAccount;
    }
}
