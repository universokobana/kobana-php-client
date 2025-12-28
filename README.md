# Kobana PHP Client

PHP SDK client for the Kobana API - Financial automation platform.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

```bash
composer require kobana/kobana-php-client
```

## Configuration

### Using Environment Variables

Create a `.env` file (copy from `.env.example`):

```env
KOBANA_API_TOKEN=your_api_token_here
KOBANA_ENVIRONMENT=sandbox
KOBANA_DEBUG=false
```

### Global Configuration

```php
use Kobana\Kobana;

Kobana::configure([
    'apiToken' => 'your_api_token',
    'environment' => 'sandbox', // 'sandbox', 'production', or 'development'
    'debug' => false,
]);
```

### Multi-Client Configuration

```php
use Kobana\Client;

$client1 = new Client([
    'apiToken' => 'token_for_client_1',
    'environment' => 'sandbox',
]);

$client2 = new Client([
    'apiToken' => 'token_for_client_2',
    'environment' => 'production',
]);
```

## Usage

### Bank Billets (Boletos)

```php
use Kobana\Kobana;
use Kobana\Resources\Charge\BankBillet;

// Configure the SDK
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
    'customerEmail' => 'john@example.com',
    'customerPhoneNumber' => '11999999999',
    'customerState' => 'SP',
    'customerCityName' => 'São Paulo',
    'customerZipcode' => '01310100',
    'customerAddress' => 'Av. Paulista',
    'customerAddressNumber' => '1000',
    'customerNeighborhood' => 'Bela Vista',
]);

echo "Billet created with ID: {$billet->id}\n";
echo "Status: {$billet->status}\n";
echo "Barcode: {$billet->getBarcode()}\n";

// List bank billets
$billets = Kobana::charge()->bankBillet()->all([
    'status' => 'opened',
    'page' => 1,
    'perPage' => 25,
]);

foreach ($billets as $billet) {
    echo "{$billet->id}: {$billet->amount} - {$billet->status}\n";
}

// Find a specific billet
$billet = BankBillet::find(123);

// Check status
if ($billet->isPaid()) {
    echo "Billet is paid!\n";
} elseif ($billet->isOverdue()) {
    echo "Billet is overdue.\n";
}

// Cancel a billet
$billet->cancel();

// Duplicate a billet
$newBillet = $billet->duplicate([
    'expireAt' => '2026-01-31',
    'amount' => 150.00,
]);
```

### PIX Charges

```php
use Kobana\Kobana;
use Kobana\Resources\Charge\Pix;

// Create a PIX charge
$pix = Kobana::charge()->pix()->create([
    'amount' => 50.00,
    'customerPersonName' => 'Jane Doe',
    'customerCnpjCpf' => '98765432100',
    'expireAt' => '2025-12-31T23:59:59Z',
]);

echo "PIX charge created!\n";
echo "QR Code: {$pix->getQrCode()}\n";
echo "QR Code URL: {$pix->getQrCodeUrl()}\n";

// List PIX charges
$charges = Kobana::charge()->pix()->all([
    'status' => 'pending',
]);

// Find a specific PIX charge
$pix = Pix::find(456);

// Check status
if ($pix->isPaid()) {
    echo "Paid at: {$pix->getPaidAt()}\n";
    echo "Amount paid: {$pix->getPaidAmount()}\n";
}

// List commands
$commands = $pix->listCommands();
```

### Bank Billet Accounts (Carteiras de Cobrança)

```php
use Kobana\Kobana;

// List bank billet accounts
$accounts = Kobana::financial()->bankBilletAccount()->all();

foreach ($accounts as $account) {
    echo "{$account->id}: {$account->getBeneficiaryName()}\n";
    echo "Status: {$account->status}\n";
    echo "Bank: {$account->getBankContractSlug()}\n";
}

// Request homologation
$account->askHomologation();

// Validate account
$account->validate();

// Set as default
$account->setDefault();
```

### Financial Accounts

```php
use Kobana\Kobana;

// Get current account
$account = Kobana::financial()->account()->current();

echo "Account: {$account->getName()}\n";
echo "Email: {$account->getEmail()}\n";
echo "Available Balance: {$account->getAvailableBalance()}\n";
echo "Blocked Balance: {$account->getBlockedBalance()}\n";
```

## Error Handling

```php
use Kobana\Kobana;
use Kobana\Exceptions\ValidationException;
use Kobana\Exceptions\UnauthorizedException;
use Kobana\Exceptions\ResourceNotFoundException;
use Kobana\Exceptions\ApiException;
use Kobana\Exceptions\ConnectionException;

try {
    $billet = Kobana::charge()->bankBillet()->create([
        'amount' => 0, // Invalid amount
    ]);
} catch (ValidationException $e) {
    echo "Validation failed:\n";
    foreach ($e->getErrors() as $field => $messages) {
        echo "  {$field}: " . implode(', ', $messages) . "\n";
    }
} catch (UnauthorizedException $e) {
    echo "Authentication failed: {$e->getMessage()}\n";
} catch (ResourceNotFoundException $e) {
    echo "Resource not found: {$e->getResourceType()} #{$e->getResourceId()}\n";
} catch (ConnectionException $e) {
    echo "Connection error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error ({$e->getStatusCode()}): {$e->getMessage()}\n";
}
```

## Pagination

```php
use Kobana\Kobana;

$billets = Kobana::charge()->bankBillet()->all([
    'page' => 1,
    'perPage' => 50,
]);

echo "Page {$billets->getCurrentPage()} of {$billets->getTotalPages()}\n";
echo "Showing {$billets->count()} of {$billets->getTotalItems()} items\n";

if ($billets->hasNextPage()) {
    echo "Next page: {$billets->getNextPage()}\n";
}

// Iterate over items
foreach ($billets as $billet) {
    echo "{$billet->id}: {$billet->amount}\n";
}

// Get first/last
$first = $billets->first();
$last = $billets->last();
```

## Development

### Setup

```bash
# Install dependencies
composer install

# Copy environment file and add your API token
cp .env.example .env
```

Edit `.env` and add your Kobana API token:

```env
KOBANA_API_TOKEN=your_api_token_here
KOBANA_ENVIRONMENT=sandbox
```

### Running Tests

```bash
# Run all tests
composer test

# Or using PHPUnit directly
./vendor/bin/phpunit
```

### Unit Tests

Unit tests don't require API access and test classes in isolation:

```bash
./vendor/bin/phpunit --testsuite Unit
```

### Integration Tests

Integration tests make real API calls to the sandbox environment and record responses using [php-vcr](https://github.com/php-vcr/php-vcr):

```bash
./vendor/bin/phpunit --testsuite Integration
```

**First run:** Tests will call the real API and save responses to `tests/fixtures/`.

**Subsequent runs:** Tests will use recorded responses (no API calls needed).

**Recording new fixtures:** Delete the cassette file in `tests/fixtures/` to re-record.

#### Security

- Authorization headers are automatically filtered from recordings
- Tokens in response bodies are replaced with `[FILTERED]`
- Never commit `.env` files (already in `.gitignore`)

### Running Tests with Coverage

```bash
composer test:coverage
```

Coverage report will be generated in the `coverage/` directory.

## API Environments

| Environment | Base URL |
|-------------|----------|
| sandbox | https://api-sandbox.kobana.com.br |
| production | https://api.kobana.com.br |
| development | http://localhost:5000/api |

## Resources

### Charge Module

- **BankBillet** - Bank billets (boletos) management
- **Pix** - PIX charges management

### Financial Module

- **Account** - Financial account information
- **BankBilletAccount** - Bank billet accounts (carteiras) management

## License

MIT License. See [LICENSE](LICENSE) for more information.

## Links

- [Kobana Website](https://kobana.com.br)
- [API Documentation](https://ai.kobana.com.br)
- [GitHub Repository](https://github.com/universokobana/kobana-php-client)
