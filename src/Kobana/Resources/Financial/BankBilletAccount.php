<?php

declare(strict_types=1);

namespace Kobana\Resources\Financial;

use Kobana\Connection;
use Kobana\Resources\BaseResource;
use Kobana\Resources\PaginatedList;

/**
 * Bank Billet Account (Carteira de Cobrança) resource.
 */
class BankBilletAccount extends BaseResource
{
    protected static string $endpoint = '/bank_billet_accounts';
    protected static string $apiVersion = 'v1';

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_VALIDATING = 'validating';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELED = 'canceled';

    /**
     * Find a bank billet account by ID.
     *
     * @param array<string, mixed> $params
     */
    public static function find(int|string $id, array $params = [], Connection $connection = null): ?static
    {
        return parent::find($id, $params, $connection);
    }

    /**
     * Get all bank billet accounts.
     *
     * @param array<string, mixed> $params
     * @return PaginatedList<static>
     */
    public static function all(array $params = [], Connection $connection = null): PaginatedList
    {
        return parent::all($params, $connection);
    }

    /**
     * Create a new bank billet account.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     */
    public static function create(array $attributes, array $options = [], Connection $connection = null): static
    {
        return parent::create($attributes, $options, $connection);
    }

    /**
     * Request homologation for the account.
     *
     * @return array<string, mixed>
     */
    public function askHomologation(): array
    {
        if (!$this->exists() || $this->getId() === null) {
            return [];
        }

        $response = $this->connection->get(
            static::$endpoint . '/' . $this->getId() . '/ask',
            [],
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        $this->fill($data);
        $this->syncOriginal();

        return $data;
    }

    /**
     * Validate the account.
     */
    public function validate(): bool
    {
        if (!$this->exists() || $this->getId() === null) {
            return false;
        }

        $response = $this->connection->put(
            static::$endpoint . '/' . $this->getId() . '/validate',
            [],
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        $this->fill($data);
        $this->syncOriginal();

        return true;
    }

    /**
     * Set the account as default.
     */
    public function setDefault(): bool
    {
        if (!$this->exists() || $this->getId() === null) {
            return false;
        }

        $response = $this->connection->put(
            static::$endpoint . '/' . $this->getId() . '/set_default',
            [],
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        $this->fill($data);
        $this->syncOriginal();

        return true;
    }

    // Status check methods

    /**
     * Check if the account is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the account is validating (homologating).
     */
    public function isValidating(): bool
    {
        return $this->status === self::STATUS_VALIDATING;
    }

    /**
     * Alias for isValidating.
     */
    public function isHomologating(): bool
    {
        return $this->isValidating();
    }

    /**
     * Check if the account is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if the account is homologated (alias for isActive).
     */
    public function isHomologated(): bool
    {
        return $this->isActive();
    }

    /**
     * Check if the account is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Get the bank contract slug.
     */
    public function getBankContractSlug(): ?string
    {
        return $this->bankContractSlug ?? null;
    }

    /**
     * Get the beneficiary name.
     */
    public function getBeneficiaryName(): ?string
    {
        return $this->beneficiaryName ?? null;
    }

    /**
     * Get the beneficiary CNPJ/CPF.
     */
    public function getBeneficiaryCnpjCpf(): ?string
    {
        return $this->beneficiaryCnpjCpf ?? null;
    }

    /**
     * Get the next our number.
     */
    public function getNextOurNumber(): ?int
    {
        return $this->nextOurNumber ?? null;
    }

    /**
     * Get the homologated at date.
     */
    public function getHomologatedAt(): ?string
    {
        return $this->homologatedAt ?? null;
    }
}
