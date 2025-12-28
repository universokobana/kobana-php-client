<?php

declare(strict_types=1);

namespace Kobana\Tests\Unit\Exceptions;

use Kobana\Exceptions\ApiException;
use Kobana\Exceptions\ConfigurationException;
use Kobana\Exceptions\ConnectionException;
use Kobana\Exceptions\KobanaException;
use Kobana\Exceptions\ResourceNotFoundException;
use Kobana\Exceptions\UnauthorizedException;
use Kobana\Exceptions\ValidationException;
use Kobana\Tests\TestCase;

class ExceptionsTest extends TestCase
{
    public function testKobanaExceptionBase(): void
    {
        $exception = new KobanaException('Test error', 500);

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals('Test error', $exception->getOriginalMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testConfigurationException(): void
    {
        $missingToken = ConfigurationException::missingApiToken();
        $this->assertStringContainsString('API token is required', $missingToken->getMessage());

        $invalidEnv = ConfigurationException::invalidEnvironment('invalid');
        $this->assertStringContainsString("Invalid environment 'invalid'", $invalidEnv->getMessage());

        $notConfigured = ConfigurationException::notConfigured();
        $this->assertStringContainsString('not configured', $notConfigured->getMessage());
    }

    public function testConnectionException(): void
    {
        $timeout = ConnectionException::timeout('https://api.kobana.com.br/v1/test');
        $this->assertStringContainsString('timed out', $timeout->getMessage());

        $networkError = ConnectionException::networkError('Connection refused');
        $this->assertStringContainsString('Network error', $networkError->getMessage());
        $this->assertStringContainsString('Connection refused', $networkError->getMessage());
    }

    public function testUnauthorizedException(): void
    {
        $invalidToken = UnauthorizedException::invalidToken();
        $this->assertStringContainsString('Invalid or expired', $invalidToken->getMessage());

        $insufficientPerms = UnauthorizedException::insufficientPermissions();
        $this->assertStringContainsString('Insufficient permissions', $insufficientPerms->getMessage());
    }

    public function testResourceNotFoundException(): void
    {
        $exception = new ResourceNotFoundException('BankBillet', 123);

        $this->assertEquals('BankBillet', $exception->getResourceType());
        $this->assertEquals(123, $exception->getResourceId());
        $this->assertStringContainsString('BankBillet', $exception->getMessage());
        $this->assertStringContainsString('123', $exception->getMessage());
    }

    public function testValidationException(): void
    {
        $errors = [
            'amount' => ['Amount must be greater than 0', 'Amount is required'],
            'expireAt' => ['Expire date must be in the future'],
        ];

        $exception = new ValidationException($errors);

        $this->assertEquals($errors, $exception->getErrors());
        $this->assertEquals(['Amount must be greater than 0', 'Amount is required'], $exception->getFieldErrors('amount'));
        $this->assertTrue($exception->hasFieldError('amount'));
        $this->assertFalse($exception->hasFieldError('nonexistent'));
        $this->assertStringContainsString('amount', $exception->getMessage());
    }

    public function testApiException(): void
    {
        $exception = new ApiException(
            'Server error',
            500,
            ['error' => 'Internal server error'],
            [['title' => 'Server error']]
        );

        $this->assertEquals(500, $exception->getStatusCode());
        $this->assertEquals(['error' => 'Internal server error'], $exception->getResponseBody());
        $this->assertEquals([['title' => 'Server error']], $exception->getErrors());
    }

    public function testApiExceptionFromResponse(): void
    {
        $body = [
            'error' => 'Something went wrong',
            'errors' => [
                ['title' => 'Validation failed'],
            ],
        ];

        $exception = ApiException::fromResponse(422, $body);

        $this->assertEquals(422, $exception->getStatusCode());
        $this->assertEquals('Something went wrong', $exception->getMessage());
        $this->assertEquals($body['errors'], $exception->getErrors());
    }
}
