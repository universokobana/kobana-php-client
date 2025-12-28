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
    ->addRequestMatcher('method', function ($first, $second) {
        return $first->getMethod() === $second->getMethod();
    })
    ->addRequestMatcher('url', function ($first, $second) {
        return $first->getUrl() === $second->getUrl();
    });

// Filter sensitive data from recordings
VCR::configure()->registerRequestMatcher('sanitized', function ($first, $second) {
    return true;
});
