<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use VCR\VCR;

// Load environment variables from .env if exists
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// Configure VCR for HTTP request recording
VCR::configure()
    ->setCassettePath(__DIR__ . '/fixtures')
    ->setStorage('json')
    ->setMode('none')
    ->enableLibraryHooks(['curl', 'stream_wrapper'])
    ->enableRequestMatchers(['method', 'url']);

/**
 * Helper function to sanitize cassette files after recording.
 * Removes Authorization headers and sensitive data from recorded requests.
 */
function sanitizeCassette(string $cassettePath): void
{
    if (!file_exists($cassettePath)) {
        return;
    }

    $content = file_get_contents($cassettePath);
    $data = json_decode($content, true);

    if (!is_array($data)) {
        return;
    }

    foreach ($data as &$recording) {
        // Sanitize request headers
        if (isset($recording['request']['headers'])) {
            foreach ($recording['request']['headers'] as $key => &$values) {
                if (strtolower($key) === 'authorization') {
                    $values = ['Bearer [FILTERED]'];
                }
            }
        }

        // Sanitize response body if it contains tokens
        if (isset($recording['response']['body'])) {
            $body = $recording['response']['body'];
            if (is_string($body)) {
                $body = preg_replace(
                    '/"(api_token|access_token|token)":\s*"[^"]+"/i',
                    '"$1": "[FILTERED]"',
                    $body
                );
                $recording['response']['body'] = $body;
            }
        }
    }

    file_put_contents($cassettePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
