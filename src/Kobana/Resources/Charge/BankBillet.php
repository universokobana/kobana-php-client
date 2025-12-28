<?php

declare(strict_types=1);

namespace Kobana\Resources\Charge;

use Kobana\Connection;
use Kobana\Resources\BaseResource;
use Kobana\Resources\PaginatedList;

/**
 * Bank Billet (Boleto) resource.
 */
class BankBillet extends BaseResource
{
    protected static string $endpoint = '/bank_billets';
    protected static string $apiVersion = 'v1';

    // Status constants
    public const STATUS_GENERATING = 'generating';
    public const STATUS_GENERATION_FAILED = 'generation_failed';
    public const STATUS_VALIDATION_FAILED = 'validation_failed';
    public const STATUS_OPENED = 'opened';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_CHARGEBACK = 'chargeback';

    /**
     * Find a bank billet by ID.
     *
     * @param array<string, mixed> $params
     */
    public static function find(int|string $id, array $params = [], Connection $connection = null): ?static
    {
        return parent::find($id, $params, $connection);
    }

    /**
     * Get all bank billets.
     *
     * @param array<string, mixed> $params
     * @return PaginatedList<static>
     */
    public static function all(array $params = [], Connection $connection = null): PaginatedList
    {
        return parent::all($params, $connection);
    }

    /**
     * Create a new bank billet.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     */
    public static function create(array $attributes, array $options = [], Connection $connection = null): static
    {
        return parent::create($attributes, $options, $connection);
    }

    /**
     * Cancel the bank billet.
     */
    public function cancel(): bool
    {
        if (!$this->exists() || $this->getId() === null) {
            return false;
        }

        $response = $this->connection->put(
            static::$endpoint . '/' . $this->getId() . '/cancel',
            [],
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        $this->fill($data);
        $this->syncOriginal();

        return true;
    }

    /**
     * Duplicate the bank billet with new due date and/or amount.
     *
     * @param array<string, mixed> $attributes
     */
    public function duplicate(array $attributes = []): static
    {
        if (!$this->exists() || $this->getId() === null) {
            throw new \RuntimeException('Cannot duplicate a non-existent bank billet.');
        }

        $response = $this->connection->post(
            static::$endpoint . '/' . $this->getId() . '/duplicate',
            $attributes,
            [],
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        return new static($data, $this->connection);
    }

    // Status check methods

    /**
     * Check if the billet is generating.
     */
    public function isGenerating(): bool
    {
        return $this->status === self::STATUS_GENERATING;
    }

    /**
     * Check if the billet generation failed.
     */
    public function isGenerationFailed(): bool
    {
        return $this->status === self::STATUS_GENERATION_FAILED;
    }

    /**
     * Check if the billet validation failed.
     */
    public function isValidationFailed(): bool
    {
        return $this->status === self::STATUS_VALIDATION_FAILED;
    }

    /**
     * Check if the billet is opened (waiting payment).
     */
    public function isOpened(): bool
    {
        return $this->status === self::STATUS_OPENED;
    }

    /**
     * Check if the billet is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Check if the billet is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if the billet is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE;
    }

    /**
     * Check if the billet is blocked.
     */
    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    /**
     * Check if the billet has chargeback.
     */
    public function isChargeback(): bool
    {
        return $this->status === self::STATUS_CHARGEBACK;
    }

    /**
     * Check if the billet is in a final state.
     */
    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_CANCELED,
            self::STATUS_PAID,
            self::STATUS_CHARGEBACK,
        ], true);
    }

    /**
     * Check if the billet is pending (waiting for something).
     */
    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_GENERATING,
            self::STATUS_OPENED,
        ], true);
    }

    /**
     * Get the billet URL (PDF).
     */
    public function getPdfUrl(): ?string
    {
        return $this->shippingFulfilledUrl ?? $this->bankBilletPdfUrl ?? null;
    }

    /**
     * Get the barcode number.
     */
    public function getBarcode(): ?string
    {
        return $this->line ?? null;
    }

    /**
     * Get our number.
     */
    public function getOurNumber(): ?string
    {
        return $this->ourNumber ?? null;
    }
}
