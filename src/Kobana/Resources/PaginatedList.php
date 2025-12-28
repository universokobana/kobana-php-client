<?php

declare(strict_types=1);

namespace Kobana\Resources;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Paginated list of resources.
 *
 * @template T of BaseResource
 * @implements IteratorAggregate<int, T>
 */
class PaginatedList implements IteratorAggregate, Countable
{
    /**
     * @var array<T>
     */
    private array $items;

    private int $currentPage;
    private int $perPage;
    private int $totalItems;
    private int $totalPages;

    /**
     * @param array<T> $items
     */
    public function __construct(
        array $items,
        int $currentPage = 1,
        int $perPage = 25,
        int $totalItems = 0,
        int $totalPages = 1
    ) {
        $this->items = $items;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->totalItems = $totalItems ?: count($items);
        $this->totalPages = $totalPages ?: (int) ceil($this->totalItems / max($perPage, 1));
    }

    /**
     * Get all items.
     *
     * @return array<T>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Get the current page number.
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Get items per page.
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get total number of items.
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * Get total number of pages.
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * Check if there is a next page.
     */
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Check if there is a previous page.
     */
    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Check if this is the first page.
     */
    public function isFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    /**
     * Check if this is the last page.
     */
    public function isLastPage(): bool
    {
        return $this->currentPage >= $this->totalPages;
    }

    /**
     * Get next page number.
     */
    public function getNextPage(): ?int
    {
        return $this->hasNextPage() ? $this->currentPage + 1 : null;
    }

    /**
     * Get previous page number.
     */
    public function getPreviousPage(): ?int
    {
        return $this->hasPreviousPage() ? $this->currentPage - 1 : null;
    }

    /**
     * Check if the list is empty.
     */
    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * Check if the list is not empty.
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Get the first item.
     *
     * @return T|null
     */
    public function first(): ?BaseResource
    {
        return $this->items[0] ?? null;
    }

    /**
     * Get the last item.
     *
     * @return T|null
     */
    public function last(): ?BaseResource
    {
        $count = count($this->items);
        return $count > 0 ? $this->items[$count - 1] : null;
    }

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Get the count of items in the current page.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(fn ($item) => $item->toArray(), $this->items),
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
            'totalItems' => $this->totalItems,
            'totalPages' => $this->totalPages,
        ];
    }

    /**
     * Create from API response.
     *
     * @param array<string, mixed> $response
     * @param class-string<T> $resourceClass
     * @return self<T>
     */
    public static function fromResponse(array $response, string $resourceClass, \Kobana\Connection $connection): self
    {
        $data = $response['data'] ?? $response;

        if (!is_array($data)) {
            $data = [];
        }

        // Check if data is a list of items or a single item wrapped
        if (isset($data[0]) || empty($data)) {
            $items = array_map(
                fn ($attrs) => new $resourceClass($attrs, $connection),
                $data
            );
        } else {
            $items = [new $resourceClass($data, $connection)];
        }

        return new self(
            $items,
            (int) ($response['meta']['currentPage'] ?? $response['currentPage'] ?? 1),
            (int) ($response['meta']['perPage'] ?? $response['perPage'] ?? 25),
            (int) ($response['meta']['total'] ?? $response['total'] ?? count($items)),
            (int) ($response['meta']['totalPages'] ?? $response['totalPages'] ?? 1)
        );
    }
}
