# Laravel GCP Integration

A Laravel package for creating dynamic GCP integrations with artisan commands and facades. This package allows you to quickly create integration classes for GCP Application Integration and other API services, using them through a dynamic facade system.

## Features

- 🚀 **Dynamic Facade**: Call integrations like `Integration::GcpIntegration()`
- 🛠️ **Artisan Command**: Generate integration classes with `php artisan make:integration`
- �� **GCP Optimized**: Built specifically for Google Cloud Platform integrations
- 📦 **Portable**: Easy to move between projects
- 🎯 **IDE Friendly**: Full autocomplete and type hints
- ⚡ **Simple**: Clean, intuitive API

## Installation

Install the package via Composer:

```bash
composer require osamahdev1/laravel-gcp-integration
```

The package will automatically register its service provider.

## Usage

### Creating Integration Classes

Use the artisan command to create new integration classes:

```bash
# Basic integration
php artisan make:integration MyGcpApi

# GCP Application Integration with configuration
php artisan make:integration GcpApplication \
    --base-url="https://me-central2-integrations.googleapis.com/v1/projects/your-project/locations/me-central2/integrations/-:execute" \
    --auth-token="your-gcp-token" \
    --timeout=60

# With custom namespace and path
php artisan make:integration CustomGcpApi \
    --namespace="App\\MyIntegrations" \
    --path="app/MyIntegrations"
```

### Using the Dynamic Facade

```php
use Osamahdev1\LaravelGcpIntegration\Facades\Integration;

// Create GCP integration
$gcp = Integration::GcpApplication([
    'base_url' => config('services.gcp.base_url'),
    'auth_token' => config('services.gcp.auth_token'),
    'timeout' => 60
]);

// Execute API trigger
$result = $gcp->execute('api_trigger/learner-import-processor_API_1', [
    'bulk_request_input' => [
        'bulk' => $data,
        'config' => [
            'source_endpoint' => config('app.url') . '/webhook/progress',
            'source_name' => 'laravel-app'
        ]
    ]
]);

// Get status
$status = $gcp->getStatus($processId);

// Test connection
$isConnected = $gcp->testConnection();
```

### Direct Instantiation

```php
use App\Integrations\GcpApplicationIntegration;

$integration = new GcpApplicationIntegration([
    'base_url' => 'https://me-central2-integrations.googleapis.com/v1/projects/your-project/locations/me-central2/integrations/-:execute',
    'auth_token' => 'your-gcp-token',
    'timeout' => 30
]);

$result = $integration->execute('api_trigger/data-processor', $payload);
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Osamahdev1\LaravelGcpIntegration\LaravelGcpIntegrationServiceProvider" --tag="config"
```

Configure your GCP services in `config/services.php`:

```php
'gcp_application' => [
    'base_url' => env('GCP_APPLICATION_BASE_URL', 'https://me-central2-integrations.googleapis.com/v1/projects/your-project/locations/me-central2/integrations/-:execute'),
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

## GCP Application Integration Example

```php
$gcp = Integration::GcpApplication([
    'base_url' => 'https://me-central2-integrations.googleapis.com/v1/projects/nelc-integration-platform/locations/me-central2/integrations/-:execute',
    'auth_token' => 'your-gcp-bearer-token',
    'timeout' => 60
]);

// Execute learner import
$result = $gcp->execute('api_trigger/learner-import-processor_API_1', [
    'bulk_request_input' => [
        'bulk' => $learnerData,
        'config' => [
            'source_endpoint' => 'https://your-app.com/api/v1/webhook/progress',
            'source_name' => 'laravel-learner-import',
            'chunk_size' => 500
        ],
        'auth' => [
            'target_token' => 'webhook-auth-token'
        ]
    ]
]);

// Execute entity import
$result = $gcp->execute('api_trigger/entity-import-processor_API_1', [
    'bulk_request_input' => [
        'bulk' => $entityData,
        'config' => [
            'source_endpoint' => 'https://your-app.com/api/v1/webhook/entity-progress',
            'source_name' => 'laravel-entity-import'
        ]
    ]
]);
```

## Environment Variables

Add these to your `.env` file:

```env
GCP_APPLICATION_BASE_URL=https://me-central2-integrations.googleapis.com/v1/projects/your-project/locations/me-central2/integrations/-:execute
GCP_APPLICATION_AUTH_TOKEN=your-gcp-bearer-token
GCP_APPLICATION_TIMEOUT=60
```

## Requirements

- PHP 8.1+
- Laravel 10.0+
- GCP Application Integration access

## License

MIT License. See [LICENSE](LICENSE) for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Author

Created by [osamahdev1](https://github.com/osamahdev1)
