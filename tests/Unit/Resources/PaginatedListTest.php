<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit\Resources;

use Kobana\Resources\Charge\BankBillet;
use Kobana\Resources\PaginatedList;
use Kobana\Tests\TestCase;

class PaginatedListTest extends TestCase
{
    public function testCanCreatePaginatedList(): void
    {
        $items = [
            new BankBillet(['id' => 1], $this->connection),
            new BankBillet(['id' => 2], $this->connection),
        ];

        $list = new PaginatedList($items, 1, 25, 50, 2);

        $this->assertCount(2, $list);
        $this->assertEquals(1, $list->getCurrentPage());
        $this->assertEquals(25, $list->getPerPage());
        $this->assertEquals(50, $list->getTotalItems());
        $this->assertEquals(2, $list->getTotalPages());
    }

    public function testPaginationHelpers(): void
    {
        $items = [new BankBillet(['id' => 1], $this->connection)];

        $list = new PaginatedList($items, 1, 25, 50, 2);
        $this->assertTrue($list->hasNextPage());
        $this->assertFalse($list->hasPreviousPage());
        $this->assertTrue($list->isFirstPage());
        $this->assertFalse($list->isLastPage());
        $this->assertEquals(2, $list->getNextPage());
        $this->assertNull($list->getPreviousPage());

        $list = new PaginatedList($items, 2, 25, 50, 2);
        $this->assertFalse($list->hasNextPage());
        $this->assertTrue($list->hasPreviousPage());
        $this->assertFalse($list->isFirstPage());
        $this->assertTrue($list->isLastPage());
        $this->assertNull($list->getNextPage());
        $this->assertEquals(1, $list->getPreviousPage());
    }

    public function testIsEmptyAndNotEmpty(): void
    {
        $emptyList = new PaginatedList([]);
        $this->assertTrue($emptyList->isEmpty());
        $this->assertFalse($emptyList->isNotEmpty());

        $nonEmptyList = new PaginatedList([new BankBillet(['id' => 1], $this->connection)]);
        $this->assertFalse($nonEmptyList->isEmpty());
        $this->assertTrue($nonEmptyList->isNotEmpty());
    }

    public function testFirstAndLast(): void
    {
        $items = [
            new BankBillet(['id' => 1], $this->connection),
            new BankBillet(['id' => 2], $this->connection),
            new BankBillet(['id' => 3], $this->connection),
        ];

        $list = new PaginatedList($items);

        $this->assertEquals(1, $list->first()->id);
        $this->assertEquals(3, $list->last()->id);
    }

    public function testFirstAndLastOnEmptyList(): void
    {
        $list = new PaginatedList([]);

        $this->assertNull($list->first());
        $this->assertNull($list->last());
    }

    public function testIterable(): void
    {
        $items = [
            new BankBillet(['id' => 1], $this->connection),
            new BankBillet(['id' => 2], $this->connection),
        ];

        $list = new PaginatedList($items);
        $ids = [];

        foreach ($list as $item) {
            $ids[] = $item->id;
        }

        $this->assertEquals([1, 2], $ids);
    }

    public function testToArray(): void
    {
        $items = [
            new BankBillet(['id' => 1, 'amount' => 100], $this->connection),
        ];

        $list = new PaginatedList($items, 1, 25, 1, 1);
        $array = $list->toArray();

        $this->assertArrayHasKey('items', $array);
        $this->assertArrayHasKey('currentPage', $array);
        $this->assertArrayHasKey('perPage', $array);
        $this->assertArrayHasKey('totalItems', $array);
        $this->assertArrayHasKey('totalPages', $array);
        $this->assertEquals(1, $array['currentPage']);
        $this->assertEquals(1, count($array['items']));
    }

    public function testFromResponse(): void
    {
        $response = [
            'data' => [
                ['id' => 1, 'amount' => 100],
                ['id' => 2, 'amount' => 200],
            ],
            'meta' => [
                'currentPage' => 1,
                'perPage' => 25,
                'total' => 50,
                'totalPages' => 2,
            ],
        ];

        $list = PaginatedList::fromResponse($response, BankBillet::class, $this->connection);

        $this->assertCount(2, $list);
        $this->assertEquals(1, $list->getCurrentPage());
        $this->assertEquals(25, $list->getPerPage());
        $this->assertEquals(50, $list->getTotalItems());
        $this->assertEquals(2, $list->getTotalPages());
        $this->assertInstanceOf(BankBillet::class, $list->first());
    }
}
