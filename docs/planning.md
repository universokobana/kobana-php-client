# Kobana PHP SDK - Development Planning

## Overview

This document outlines the development plan for the Kobana PHP SDK, based on the existing Ruby, Node.js, and Python implementations.

## Technology Stack

| Component | Library | Purpose |
|-----------|---------|---------|
| HTTP Client | Guzzle 7.x | HTTP requests to Kobana API |
| Testing | PHPUnit 10.x | Unit and integration testing |
| HTTP Recording | php-vcr | Record/replay HTTP requests for tests |
| Environment | vlucas/phpdotenv | Load environment variables |
| PHP Version | ^8.1 | Modern PHP features (typed properties, enums) |

## Project Structure

```
kobana-php-client/
├── src/
│   └── Kobana/
│       ├── Client.php                    # Main client class
│       ├── Configuration.php             # Configuration management
│       ├── Connection.php                # HTTP connection handler
│       ├── Kobana.php                    # Global singleton access
│       ├── Exceptions/
│       │   ├── KobanaException.php       # Base exception
│       │   ├── ConfigurationException.php
│       │   ├── ConnectionException.php
│       │   ├── UnauthorizedException.php
│       │   ├── ResourceNotFoundException.php
│       │   ├── ValidationException.php
│       │   └── ApiException.php
│       ├── Resources/
│       │   ├── BaseResource.php          # Base resource with CRUD operations
│       │   ├── PaginatedList.php         # Paginated collection
│       │   ├── Charge/
│       │   │   ├── ChargeProxy.php       # Charge module proxy
│       │   │   ├── BankBillet.php        # Bank billets (boletos)
│       │   │   └── Pix.php               # PIX charges
│       │   └── Financial/
│       │       ├── FinancialProxy.php    # Financial module proxy
│       │       ├── Account.php           # Financial accounts
│       │       └── BankBilletAccount.php # Bank billet accounts
│       └── Support/
│           ├── Str.php                   # String utilities (snake_case/camelCase)
│           └── Arr.php                   # Array utilities
├── tests/
│   ├── Unit/
│   │   ├── ConfigurationTest.php
│   │   ├── ConnectionTest.php
│   │   ├── ClientTest.php
│   │   └── Resources/
│   │       ├── BankBilletTest.php
│   │       ├── PixTest.php
│   │       └── ...
│   ├── Integration/
│   │   └── ...
│   ├── fixtures/                         # VCR cassettes
│   │   └── ...
│   └── TestCase.php                      # Base test case
├── composer.json
├── phpunit.xml
├── .env.example
├── .gitignore
├── README.md
└── docs/
    ├── instructions.md
    └── planning.md
```

## API Resources to Implement

Based on the Swagger documentation and existing SDKs:

### 1. Bank Billets (Boletos) - v1

| Method | Endpoint | Operation |
|--------|----------|-----------|
| POST | `/v1/bank_billets` | Create new billet |
| GET | `/v1/bank_billets` | List billets with filtering |
| GET | `/v1/bank_billets/{id}` | Get specific billet |
| PUT | `/v1/bank_billets/{id}/cancel` | Cancel billet |
| POST | `/v1/bank_billets/{id}/duplicate` | Duplicate billet |

**Status values:** generating, generation_failed, validation_failed, opened, canceled, paid, overdue, blocked, chargeback

### 2. PIX Charges - v2

| Method | Endpoint | Operation |
|--------|----------|-----------|
| POST | `/v2/charge/pix` | Create PIX charge |
| GET | `/v2/charge/pix` | List PIX charges |
| GET | `/v2/charge/pix/{id}` | Get specific PIX charge |
| PUT | `/v2/charge/pix/{id}` | Update PIX charge |
| DELETE | `/v2/charge/pix/{id}` | Delete PIX charge |

**Status values:** pending, paid, expired, canceled

### 3. Bank Billet Accounts (Carteiras) - v1

| Method | Endpoint | Operation |
|--------|----------|-----------|
| POST | `/v1/bank_billet_accounts` | Create account |
| GET | `/v1/bank_billet_accounts` | List accounts |
| GET | `/v1/bank_billet_accounts/{id}` | Get specific account |
| PUT | `/v1/bank_billet_accounts/{id}` | Update account |
| GET | `/v1/bank_billet_accounts/{id}/ask` | Request homologation |
| PUT | `/v1/bank_billet_accounts/{id}/validate` | Validate account |
| PUT | `/v1/bank_billet_accounts/{id}/set_default` | Set as default |

**Status values:** pending, validating, active, canceled

### 4. Financial Accounts - v2

| Method | Endpoint | Operation |
|--------|----------|-----------|
| GET | `/v2/financial/accounts/current` | Get current account |
| GET | `/v2/financial/accounts/{id}/balance` | Get account balance |

## Core Classes Design

### 1. Configuration

```php
class Configuration
{
    private string $apiToken;
    private string $environment = 'sandbox';
    private string $apiVersion = 'v1';
    private array $customHeaders = [];
    private bool $debug = false;

    public function getBaseUrl(): string;
    public function getHeaders(): array;
    public function validate(): void;
}
```

### 2. Connection

```php
class Connection
{
    private Configuration $config;
    private ?Client $httpClient = null;

    public function get(string $path, array $params = []): array;
    public function post(string $path, array $data = [], array $options = []): array;
    public function put(string $path, array $data = []): array;
    public function delete(string $path): bool;

    private function request(string $method, string $path, array $options = []): array;
    private function handleError(RequestException $e): never;
}
```

### 3. BaseResource

```php
abstract class BaseResource
{
    protected static string $endpoint;
    protected static string $apiVersion = 'v1';
    protected array $attributes = [];
    protected Connection $connection;

    // Class methods
    public static function all(array $params = []): PaginatedList;
    public static function find(int|string $id): ?static;
    public static function create(array $attributes): static;
    public static function findBy(array $params): ?static;
    public static function findOrCreateBy(array $params, array $attributes = []): static;

    // Instance methods
    public function save(): bool;
    public function update(array $attributes): bool;
    public function delete(): bool;
    public function refresh(): static;

    // Magic methods for attribute access
    public function __get(string $name): mixed;
    public function __set(string $name, mixed $value): void;
    public function __isset(string $name): bool;
}
```

### 4. Client

```php
class Client
{
    private Configuration $config;
    private Connection $connection;
    private ?ChargeProxy $charge = null;
    private ?FinancialProxy $financial = null;

    public function __construct(array $options = []);

    public function charge(): ChargeProxy;
    public function financial(): FinancialProxy;
}
```

### 5. Kobana (Global Singleton)

```php
class Kobana
{
    private static ?Client $instance = null;

    public static function configure(array $options): void;
    public static function getInstance(): Client;
    public static function charge(): ChargeProxy;
    public static function financial(): FinancialProxy;
}
```

## Error Handling

| HTTP Status | Exception | Description |
|-------------|-----------|-------------|
| 401 | UnauthorizedException | Invalid or expired token |
| 403 | UnauthorizedException | Insufficient permissions |
| 404 | ResourceNotFoundException | Resource not found |
| 422 | ValidationException | Validation errors (with details) |
| 5xx | ApiException | Server errors |
| Network | ConnectionException | Connection/timeout issues |

## Design Patterns

1. **Proxy Pattern**: ChargeProxy and FinancialProxy for organized resource access
2. **Active Record Pattern**: BaseResource with CRUD operations
3. **Singleton Pattern**: Optional global client via Kobana class
4. **Factory Pattern**: findOrCreateBy() method
5. **Lazy Loading**: Resources instantiated on demand

## Data Transformation

- **Outbound (PHP → API)**: Convert camelCase to snake_case
- **Inbound (API → PHP)**: Convert snake_case to camelCase
- Automatic transformation in Connection class

## Authentication

- Bearer Token via Authorization header
- Support for environment variable: `KOBANA_API_TOKEN`
- Token redaction in logs and debug output

## Environments

| Environment | Base URL |
|-------------|----------|
| sandbox | https://api-sandbox.kobana.com.br |
| production | https://api.kobana.com.br |
| development | http://localhost:5000/api |

## Implementation Order

### Phase 1: Core Infrastructure
1. composer.json with dependencies
2. Configuration class
3. Exception classes
4. Connection class with Guzzle
5. Support utilities (Str, Arr)

### Phase 2: Base Resource
1. BaseResource abstract class
2. PaginatedList class
3. Proxy classes (ChargeProxy, FinancialProxy)
4. Client class
5. Kobana global singleton

### Phase 3: API Resources
1. BankBillet resource (most complete implementation)
2. BankBilletAccount resource
3. Pix resource
4. Account resource

### Phase 4: Testing
1. PHPUnit configuration
2. php-vcr setup
3. Unit tests for core classes
4. Integration tests with recorded fixtures
5. Test coverage reporting

### Phase 5: Documentation
1. README.md with usage examples
2. Inline PHPDoc documentation
3. Example scripts

## Security Considerations

1. **Token Protection**: Never log or expose API tokens
2. **Environment Variables**: Use .env for sensitive data
3. **.gitignore**: Exclude .env, vendor/, cassettes with real data
4. **.env.example**: Provide template without real values

## Example Usage

```php
use Kobana\Kobana;
use Kobana\Client;

// Global configuration
Kobana::configure([
    'apiToken' => getenv('KOBANA_API_TOKEN'),
    'environment' => 'sandbox',
]);

// Create a bank billet
$billet = Kobana::charge()->bankBillet()->create([
    'amount' => 100.50,
    'expireAt' => '2025-12-31',
    'customerPersonName' => 'John Doe',
    'customerCnpjCpf' => '12345678901',
]);

echo "Billet created: {$billet->id}";

// List billets
$billets = Kobana::charge()->bankBillet()->all([
    'status' => 'opened',
    'page' => 1,
    'perPage' => 25,
]);

foreach ($billets as $billet) {
    echo "{$billet->id}: {$billet->amount}\n";
}

// Multi-client usage
$client1 = new Client(['apiToken' => 'token1', 'environment' => 'sandbox']);
$client2 = new Client(['apiToken' => 'token2', 'environment' => 'production']);

$billet1 = $client1->charge()->bankBillet()->find(123);
$billet2 = $client2->charge()->bankBillet()->find(456);
```
