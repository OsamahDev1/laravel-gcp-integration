<?php

namespace Osamahdev1\LaravelGcpIntegration\Services;

use Illuminate\Support\Facades\Log;

abstract class ApplicationIntegration
{
    protected string $baseUrl;
    protected array $headers;
    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;
        $this->headers = $this->prepareHeaders($config);
        
        $this->validateConfig();
    }

    /**
     * Execute an API trigger with the given payload
     */
    abstract public function execute(string $triggerId, array $payload): array;

    /**
     * Get the status of a running process
     */
    abstract public function getStatus(string $processId): array;

    /**
     * Cancel a running process
     */
    abstract public function cancel(string $processId): bool;

    /**
     * Test the connection to the integration service
     */
    abstract public function testConnection(): bool;

    /**
     * Get the integration name/type
     */
    abstract public function getName(): string;

    /**
     * Validate configuration
     */
    protected function validateConfig(): void
    {
        if (empty($this->baseUrl)) {
            throw new \InvalidArgumentException('Base URL is required for ' . $this->getName());
        }
    }

    /**
     * Prepare headers for requests
     */
    protected function prepareHeaders(array $config): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        // Add authorization if provided
        if (isset($config['auth_token'])) {
            $headers['Authorization'] = 'Bearer ' . $config['auth_token'];
        } elseif (isset($config['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $config['api_key'];
        }

        return $headers;
    }

    /**
     * Log integration activity
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, "[{$this->getName()}] {$message}", $context);
    }

    /**
     * Get default configuration for this integration
     */
    public static function getDefaultConfig(): array
    {
        return [
            'base_url' => '',
            'timeout' => 30,
        ];
    }
}
