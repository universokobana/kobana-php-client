<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit\Support;

use Kobana\Support\Arr;
use Kobana\Tests\TestCase;

class ArrTest extends TestCase
{
    public function testCompact(): void
    {
        $input = [
            'name' => 'John',
            'email' => null,
            'age' => 30,
            'empty' => null,
        ];

        $expected = [
            'name' => 'John',
            'age' => 30,
        ];

        $this->assertEquals($expected, Arr::compact($input));
    }

    public function testGet(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'city' => 'New York',
                ],
            ],
        ];

        $this->assertEquals('John', Arr::get($array, 'user.name'));
        $this->assertEquals('New York', Arr::get($array, 'user.address.city'));
        $this->assertNull(Arr::get($array, 'user.email'));
        $this->assertEquals('default', Arr::get($array, 'user.email', 'default'));
    }

    public function testSet(): void
    {
        $array = [];

        Arr::set($array, 'user.name', 'John');
        Arr::set($array, 'user.address.city', 'New York');

        $this->assertEquals('John', $array['user']['name']);
        $this->assertEquals('New York', $array['user']['address']['city']);
    }

    public function testHas(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
            ],
        ];

        $this->assertTrue(Arr::has($array, 'user'));
        $this->assertTrue(Arr::has($array, 'user.name'));
        $this->assertFalse(Arr::has($array, 'user.email'));
        $this->assertFalse(Arr::has($array, 'nonexistent'));
    }

    public function testOnly(): void
    {
        $array = [
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'secret',
        ];

        $result = Arr::only($array, ['name', 'email']);

        $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $result);
        $this->assertArrayNotHasKey('password', $result);
    }

    public function testExcept(): void
    {
        $array = [
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'secret',
        ];

        $result = Arr::except($array, ['password']);

        $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $result);
        $this->assertArrayNotHasKey('password', $result);
    }

    public function testFlatten(): void
    {
        $array = [
            'user' => [
                'name' => 'John',
                'address' => [
                    'city' => 'New York',
                ],
            ],
            'active' => true,
        ];

        $expected = [
            'user.name' => 'John',
            'user.address.city' => 'New York',
            'active' => true,
        ];

        $this->assertEquals($expected, Arr::flatten($array));
    }
}
