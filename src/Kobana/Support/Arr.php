<?php

declare(strict_types=1);

namespace Kobana\Support;

/**
 * Array utility functions.
 */
class Arr
{
    /**
     * Remove null values from an array.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function compact(array $array): array
    {
        return array_filter($array, fn ($value) => $value !== null);
    }

    /**
     * Get a value from an array using dot notation.
     *
     * @param array<string, mixed> $array
     */
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set a value in an array using dot notation.
     *
     * @param array<string, mixed> $array
     */
    public static function set(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $segment) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current[array_shift($keys)] = $value;
    }

    /**
     * Check if an array has a key using dot notation.
     *
     * @param array<string, mixed> $array
     */
    public static function has(array $array, string $key): bool
    {
        if (isset($array[$key])) {
            return true;
        }

        $keys = explode('.', $key);

        foreach ($keys as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Get only specified keys from an array.
     *
     * @param array<string, mixed> $array
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Get all keys except specified ones from an array.
     *
     * @param array<string, mixed> $array
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Flatten a multi-dimensional array using dot notation.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && !empty($value)) {
                $result = array_merge($result, self::flatten($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
