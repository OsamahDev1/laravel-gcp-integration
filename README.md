# Laravel Dynamic Integrations

A Laravel package for creating dynamic API integrations with artisan commands and facades. This package allows you to quickly create integration classes for any API service and use them through a dynamic facade system.

## Features

- 🚀 **Dynamic Facade**: Call integrations like `Integration::GcpIntegration()`
- 🛠️ **Artisan Command**: Generate integration classes with `php artisan make:integration`
- 🔧 **Flexible Configuration**: Support for multiple authentication methods
- 📦 **Portable**: Easy to move between projects
- 🎯 **IDE Friendly**: Full autocomplete and type hints
- ⚡ **Simple**: Clean, intuitive API

## Installation

Install the package via Composer:

```bash
composer require osamah/laravel-dynamic-integrations
```

The package will automatically register its service provider.

## Usage

### Creating Integration Classes

Use the artisan command to create new integration classes:

```bash
# Basic integration
php artisan make:integration MyApi

# With configuration
php artisan make:integration GcpApplication \
    --base-url="https://api.gcp.com" \
    --auth-token="your-token" \
    --timeout=60

# With custom namespace and path
php artisan make:integration CustomApi \
    --namespace="App\\MyIntegrations" \
    --path="app/MyIntegrations"
```

### Using the Dynamic Facade

```php
use Osamah\LaravelDynamicIntegrations\Facades\Integration;

// Create GCP integration
$gcp = Integration::GcpApplication([
    'base_url' => config('services.gcp.base_url'),
    'auth_token' => config('services.gcp.auth_token'),
    'timeout' => 60
]);

// Execute API trigger
$result = $gcp->execute('api_trigger/data-processor', $payload);

// Get status
$status = $gcp->getStatus($processId);

// Test connection
$isConnected = $gcp->testConnection();
```

### Direct Instantiation

```php
use App\Integrations\GcpApplicationIntegration;

$integration = new GcpApplicationIntegration([
    'base_url' => 'https://api.gcp.com',
    'auth_token' => 'your-token',
    'timeout' => 30
]);

$result = $integration->execute('trigger-id', $payload);
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Osamah\LaravelDynamicIntegrations\LaravelDynamicIntegrationsServiceProvider" --tag="config"
```

Configure your services in `config/services.php`:

```php
'gcp_application' => [
    'base_url' => env('GCP_APPLICATION_BASE_URL'),
    'auth_token' => env('GCP_APPLICATION_AUTH_TOKEN'),
    'timeout' => env('GCP_APPLICATION_TIMEOUT', 30),
],
```

## Available Methods

All integration classes extend the `ApplicationIntegration` base class and must implement:

- `execute(string $triggerId, array $payload): array` - Execute an API trigger
- `getStatus(string $processId): array` - Get process status
- `cancel(string $processId): bool` - Cancel a running process
- `testConnection(): bool` - Test API connectivity
- `getName(): string` - Get integration name

## Examples

### GCP Application Integration

```php
$gcp = Integration::GcpApplication([
    'base_url' => 'https://me-central2-integrations.googleapis.com/v1/projects/your-project/locations/me-central2/integrations/-:execute',
    'auth_token' => 'your-gcp-token',
    'timeout' => 60
]);

$result = $gcp->execute('api_trigger/learner-import-processor_API_1', [
    'bulk_request_input' => [
        'bulk' => $data,
        'config' => [
            'source_endpoint' => 'https://your-app.com/webhook/progress',
            'source_name' => 'laravel-app'
        ]
    ]
]);
```

### REST API Integration

```php
$api = Integration::RestApi([
    'base_url' => 'https://api.example.com/v1',
    'api_key' => 'your-api-key',
    'timeout' => 30
]);

$result = $api->execute('data-processor', [
    'data' => $yourData,
    'options' => ['format' => 'json']
]);
```

## Requirements

- PHP 8.1+
- Laravel 10.0+

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
