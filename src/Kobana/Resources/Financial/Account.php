<?php

declare(strict_types=1);

namespace Kobana\Resources\Financial;

use Kobana\Connection;
use Kobana\Resources\BaseResource;

/**
 * Financial Account resource.
 */
class Account extends BaseResource
{
    protected static string $endpoint = '/financial/accounts';
    protected static string $apiVersion = 'v2';

    /**
     * Get the current account (associated with the API token).
     */
    public static function current(Connection $connection = null): ?static
    {
        if ($connection === null) {
            $connection = \Kobana\Kobana::getInstance()->getConnection();
        }

        $response = $connection->get(
            static::$endpoint . '/current',
            [],
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;

        if (empty($data)) {
            return null;
        }

        $instance = new static($data, $connection);
        $instance->exists = true;

        return $instance;
    }

    /**
     * Get the account balance.
     *
     * @return array<string, mixed>|null
     */
    public function getBalance(): ?array
    {
        if (!$this->exists() || $this->getId() === null) {
            return null;
        }

        $response = $this->connection->get(
            static::$endpoint . '/' . $this->getId() . '/balance',
            [],
            static::$apiVersion
        );

        return $response['data'] ?? $response;
    }

    /**
     * Get the available balance.
     */
    public function getAvailableBalance(): ?float
    {
        return $this->availableBalance ?? null;
    }

    /**
     * Get the blocked balance.
     */
    public function getBlockedBalance(): ?float
    {
        return $this->blockedBalance ?? null;
    }

    /**
     * Get the total balance.
     */
    public function getTotalBalance(): ?float
    {
        return $this->balance ?? null;
    }

    /**
     * Get the account name.
     */
    public function getName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Get the account email.
     */
    public function getEmail(): ?string
    {
        return $this->email ?? null;
    }
}
