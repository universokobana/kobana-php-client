<?php

declare(strict_types=1);

namespace Kobana\Resources\Charge;

use Kobana\Connection;
use Kobana\Resources\BaseResource;
use Kobana\Resources\PaginatedList;

/**
 * PIX charge resource.
 */
class Pix extends BaseResource
{
    protected static string $endpoint = '/charge/pix';
    protected static string $apiVersion = 'v2';

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELED = 'canceled';

    /**
     * Find a PIX charge by ID.
     *
     * @param array<string, mixed> $params
     */
    public static function find(int|string $id, array $params = [], Connection $connection = null): ?static
    {
        return parent::find($id, $params, $connection);
    }

    /**
     * Get all PIX charges.
     *
     * @param array<string, mixed> $params
     * @return PaginatedList<static>
     */
    public static function all(array $params = [], Connection $connection = null): PaginatedList
    {
        return parent::all($params, $connection);
    }

    /**
     * Create a new PIX charge.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     */
    public static function create(array $attributes, array $options = [], Connection $connection = null): static
    {
        return parent::create($attributes, $options, $connection);
    }

    /**
     * List commands for this PIX charge.
     *
     * @return array<mixed>
     */
    public function listCommands(): array
    {
        if (!$this->exists() || $this->getId() === null) {
            return [];
        }

        $response = $this->connection->get(
            static::$endpoint . '/' . $this->getId() . '/commands',
            [],
            static::$apiVersion
        );

        return $response['data'] ?? $response;
    }

    /**
     * Find a specific command.
     *
     * @return array<string, mixed>|null
     */
    public function findCommand(int|string $commandId): ?array
    {
        if (!$this->exists() || $this->getId() === null) {
            return null;
        }

        $response = $this->connection->get(
            static::$endpoint . '/' . $this->getId() . '/commands/' . $commandId,
            [],
            static::$apiVersion
        );

        return $response['data'] ?? $response;
    }

    // Status check methods

    /**
     * Check if the PIX charge is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the PIX charge is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if the PIX charge is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Check if the PIX charge is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * Check if the PIX charge is in a final state.
     */
    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_PAID,
            self::STATUS_EXPIRED,
            self::STATUS_CANCELED,
        ], true);
    }

    /**
     * Get the QR code text.
     */
    public function getQrCode(): ?string
    {
        return $this->qrCode ?? $this->pixQrCode ?? null;
    }

    /**
     * Get the QR code image URL.
     */
    public function getQrCodeUrl(): ?string
    {
        return $this->qrCodeUrl ?? $this->pixQrCodeUrl ?? null;
    }

    /**
     * Get the transaction ID.
     */
    public function getTxid(): ?string
    {
        return $this->txid ?? null;
    }

    /**
     * Get the paid amount.
     */
    public function getPaidAmount(): ?float
    {
        return $this->paidAmount ?? null;
    }

    /**
     * Get the paid at timestamp.
     */
    public function getPaidAt(): ?string
    {
        return $this->paidAt ?? null;
    }
}
