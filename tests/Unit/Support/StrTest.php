<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit\Support;

use Kobana\Support\Str;
use Kobana\Tests\TestCase;

class StrTest extends TestCase
{
    public function testToSnakeCase(): void
    {
        $this->assertEquals('customer_person_name', Str::toSnakeCase('customerPersonName'));
        $this->assertEquals('expire_at', Str::toSnakeCase('expireAt'));
        $this->assertEquals('already_snake', Str::toSnakeCase('already_snake'));
        $this->assertEquals('', Str::toSnakeCase(''));
    }

    public function testToCamelCase(): void
    {
        $this->assertEquals('customerPersonName', Str::toCamelCase('customer_person_name'));
        $this->assertEquals('expireAt', Str::toCamelCase('expire_at'));
        $this->assertEquals('alreadyCamel', Str::toCamelCase('alreadyCamel'));
        $this->assertEquals('', Str::toCamelCase(''));
    }

    public function testToPascalCase(): void
    {
        $this->assertEquals('CustomerPersonName', Str::toPascalCase('customer_person_name'));
        $this->assertEquals('ExpireAt', Str::toPascalCase('expire_at'));
        $this->assertEquals('', Str::toPascalCase(''));
    }

    public function testKeysToSnakeCase(): void
    {
        $input = [
            'customerName' => 'John',
            'expireAt' => '2025-12-31',
            'nested' => [
                'subKey' => 'value',
            ],
        ];

        $expected = [
            'customer_name' => 'John',
            'expire_at' => '2025-12-31',
            'nested' => [
                'sub_key' => 'value',
            ],
        ];

        $this->assertEquals($expected, Str::keysToSnakeCase($input));
    }

    public function testKeysToCamelCase(): void
    {
        $input = [
            'customer_name' => 'John',
            'expire_at' => '2025-12-31',
            'nested' => [
                'sub_key' => 'value',
            ],
        ];

        $expected = [
            'customerName' => 'John',
            'expireAt' => '2025-12-31',
            'nested' => [
                'subKey' => 'value',
            ],
        ];

        $this->assertEquals($expected, Str::keysToCamelCase($input));
    }

    public function testInterpolate(): void
    {
        $template = '/accounts/{account.id}/billets/{billet_id}';
        $values = [
            'account' => ['id' => 123],
            'billet_id' => 456,
        ];

        $result = Str::interpolate($template, $values);

        $this->assertEquals('/accounts/123/billets/456', $result);
    }

    public function testInterpolateWithMissingValues(): void
    {
        $template = '/accounts/{account.id}/unknown/{missing}';
        $values = [
            'account' => ['id' => 123],
        ];

        $result = Str::interpolate($template, $values);

        $this->assertEquals('/accounts/123/unknown/{missing}', $result);
    }
}
