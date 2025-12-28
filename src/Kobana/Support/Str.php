<?php

declare(strict_types=1);

namespace Kobana\Support;

/**
 * String utility functions.
 */
class Str
{
    /**
     * Convert a string to snake_case.
     */
    public static function toSnakeCase(string $value): string
    {
        if (ctype_lower($value)) {
            return $value;
        }

        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = preg_replace('/(.)(?=[A-Z])/u', '$1_', $value);

        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Convert a string to camelCase.
     */
    public static function toCamelCase(string $value): string
    {
        $value = ucwords(str_replace(['_', '-'], ' ', $value));
        $value = str_replace(' ', '', $value);

        return lcfirst($value);
    }

    /**
     * Convert a string to PascalCase.
     */
    public static function toPascalCase(string $value): string
    {
        $value = ucwords(str_replace(['_', '-'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Convert all keys of an array to snake_case recursively.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function keysToSnakeCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $snakeKey = is_string($key) ? self::toSnakeCase($key) : $key;

            if (is_array($value)) {
                $result[$snakeKey] = self::keysToSnakeCase($value);
            } else {
                $result[$snakeKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Convert all keys of an array to camelCase recursively.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    public static function keysToCamelCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $camelKey = is_string($key) ? self::toCamelCase($key) : $key;

            if (is_array($value)) {
                $result[$camelKey] = self::keysToCamelCase($value);
            } else {
                $result[$camelKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Interpolate placeholders in a template string.
     *
     * @param array<string, mixed> $values
     */
    public static function interpolate(string $template, array $values): string
    {
        return preg_replace_callback(
            '/\{([^}]+)\}/',
            function ($matches) use ($values) {
                $key = $matches[1];
                $parts = explode('.', $key);
                $value = $values;

                foreach ($parts as $part) {
                    if (is_array($value) && isset($value[$part])) {
                        $value = $value[$part];
                    } else {
                        return $matches[0];
                    }
                }

                return (string) $value;
            },
            $template
        ) ?? $template;
    }
}
