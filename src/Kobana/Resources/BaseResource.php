<?php

declare(strict_types=1);

namespace Kobana\Resources;

use Kobana\Connection;
use Kobana\Support\Arr;
use Kobana\Support\Str;

/**
 * Base resource class with CRUD operations.
 */
abstract class BaseResource
{
    /**
     * The API endpoint for this resource.
     */
    protected static string $endpoint = '';

    /**
     * The API version for this resource.
     */
    protected static string $apiVersion = 'v1';

    /**
     * Resource attributes.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * Original attributes (for tracking changes).
     *
     * @var array<string, mixed>
     */
    protected array $original = [];

    /**
     * The connection instance.
     */
    protected Connection $connection;

    /**
     * Whether the resource exists in the API.
     */
    protected bool $exists = false;

    /**
     * Validation errors.
     *
     * @var array<string, array<string>>
     */
    protected array $errors = [];

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [], ?Connection $connection = null)
    {
        if ($connection) {
            $this->connection = $connection;
        }

        $this->fill($attributes);
        $this->syncOriginal();

        if (isset($attributes['id'])) {
            $this->exists = true;
        }
    }

    /**
     * Get the API endpoint.
     */
    public static function getEndpoint(): string
    {
        return static::$endpoint;
    }

    /**
     * Get the API version.
     */
    public static function getApiVersion(): string
    {
        return static::$apiVersion;
    }

    /**
     * Fill the resource with attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Get all attributes.
     *
     * @return array<string, mixed>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute value.
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Set an attribute value.
     */
    public function setAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Check if an attribute exists.
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Sync the original attributes with current.
     */
    public function syncOriginal(): static
    {
        $this->original = $this->attributes;
        return $this;
    }

    /**
     * Get the changed attributes.
     *
     * @return array<string, mixed>
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $this->original[$key] !== $value) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Check if the resource has been modified.
     */
    public function isDirty(): bool
    {
        return count($this->getDirty()) > 0;
    }

    /**
     * Check if the resource exists in the API.
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Get the resource ID.
     */
    public function getId(): int|string|null
    {
        return $this->attributes['id'] ?? null;
    }

    /**
     * Get validation errors.
     *
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if the resource has validation errors.
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Check if the resource is valid (no errors).
     */
    public function isValid(): bool
    {
        return !$this->hasErrors();
    }

    /**
     * Set the connection.
     */
    public function setConnection(Connection $connection): static
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * Get the connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }

    // ==================== Static Methods ====================

    /**
     * Find a resource by ID.
     *
     * @param array<string, mixed> $params
     */
    public static function find(int|string $id, array $params = [], Connection $connection = null): ?static
    {
        if ($connection === null) {
            $connection = \Kobana\Kobana::getInstance()->getConnection();
        }

        try {
            $response = $connection->get(
                static::$endpoint . '/' . $id,
                $params,
                static::$apiVersion
            );

            $data = $response['data'] ?? $response;
            $instance = new static($data, $connection);
            $instance->exists = true;

            return $instance;
        } catch (\Kobana\Exceptions\ResourceNotFoundException) {
            return null;
        }
    }

    /**
     * Get all resources.
     *
     * @param array<string, mixed> $params
     * @return PaginatedList<static>
     */
    public static function all(array $params = [], Connection $connection = null): PaginatedList
    {
        if ($connection === null) {
            $connection = \Kobana\Kobana::getInstance()->getConnection();
        }

        $response = $connection->get(
            static::$endpoint,
            $params,
            static::$apiVersion
        );

        return PaginatedList::fromResponse($response, static::class, $connection);
    }

    /**
     * Create a new resource.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $options
     */
    public static function create(array $attributes, array $options = [], Connection $connection = null): static
    {
        if ($connection === null) {
            $connection = \Kobana\Kobana::getInstance()->getConnection();
        }

        $response = $connection->post(
            static::$endpoint,
            $attributes,
            $options,
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        $instance = new static($data, $connection);
        $instance->exists = true;

        return $instance;
    }

    /**
     * Find a resource by specific attributes.
     *
     * @param array<string, mixed> $params
     */
    public static function findBy(array $params, Connection $connection = null): ?static
    {
        $list = static::all($params, $connection);

        return $list->first();
    }

    /**
     * Find a resource by attributes or create it.
     *
     * @param array<string, mixed> $searchParams
     * @param array<string, mixed> $createAttributes
     */
    public static function findOrCreateBy(array $searchParams, array $createAttributes = [], Connection $connection = null): static
    {
        $existing = static::findBy($searchParams, $connection);

        if ($existing !== null) {
            return $existing;
        }

        return static::create(array_merge($searchParams, $createAttributes), [], $connection);
    }

    // ==================== Instance Methods ====================

    /**
     * Save the resource (create or update).
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update($this->getDirty());
        }

        $response = $this->connection->post(
            static::$endpoint,
            $this->attributes,
            [],
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        $this->fill($data);
        $this->syncOriginal();
        $this->exists = true;

        return true;
    }

    /**
     * Update the resource with new attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(array $attributes): bool
    {
        if (!$this->exists || $this->getId() === null) {
            return false;
        }

        $this->fill($attributes);

        $response = $this->connection->put(
            static::$endpoint . '/' . $this->getId(),
            $attributes,
            static::$apiVersion
        );

        $data = $response['data'] ?? $response;
        $this->fill($data);
        $this->syncOriginal();

        return true;
    }

    /**
     * Delete the resource.
     */
    public function delete(): bool
    {
        if (!$this->exists || $this->getId() === null) {
            return false;
        }

        $result = $this->connection->delete(
            static::$endpoint . '/' . $this->getId(),
            static::$apiVersion
        );

        if ($result) {
            $this->exists = false;
        }

        return $result;
    }

    /**
     * Refresh the resource from the API.
     */
    public function refresh(): static
    {
        if ($this->getId() === null) {
            return $this;
        }

        $fresh = static::find($this->getId(), [], $this->connection);

        if ($fresh !== null) {
            $this->fill($fresh->getAttributes());
            $this->syncOriginal();
        }

        return $this;
    }

    // ==================== Magic Methods ====================

    /**
     * Get attribute via property access.
     */
    public function __get(string $name): mixed
    {
        return $this->getAttribute($name);
    }

    /**
     * Set attribute via property access.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    /**
     * Check if attribute is set.
     */
    public function __isset(string $name): bool
    {
        return $this->hasAttribute($name);
    }

    /**
     * Unset an attribute.
     */
    public function __unset(string $name): void
    {
        unset($this->attributes[$name]);
    }

    /**
     * String representation.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
