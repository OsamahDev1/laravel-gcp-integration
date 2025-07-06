<?php

namespace App\Integrations;

use Osamah\LaravelDynamicIntegrations\Services\ApplicationIntegration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GcpApplicationIntegration extends ApplicationIntegration
{
    public function execute(string $triggerId, array $payload): array
    {
        try {
            $this->log('info', 'Executing GCP Application Integration', [
                'trigger_id' => $triggerId,
                'payload_size' => count($payload)
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers)
                ->post($this->baseUrl, [
                    'trigger_id' => $triggerId,
                    'input_parameters' => $this->formatPayload($payload)
                ]);

            $this->validateResponse($response);
            
            $data = $response->json();
            
            $this->log('info', 'GCP Application Integration executed successfully', [
                'trigger_id' => $triggerId,
                'execution_id' => $data['execution_id'] ?? null
            ]);

            return [
                'success' => true,
                'process_id' => $data['execution_id'] ?? $data['process_id'] ?? null,
                'status' => $data['status'] ?? 'running',
                'data' => $data
            ];

        } catch (\Exception $e) {
            $this->log('error', 'GCP Application Integration execution failed', [
                'trigger_id' => $triggerId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception("Failed to execute GCP trigger '{$triggerId}': {$e->getMessage()}", 0, $e);
        }
    }

    public function getStatus(string $processId): array
    {
        // GCP doesn't have standard status endpoint
        return [
            'success' => true,
            'process_id' => $processId,
            'status' => 'running'
        ];
    }

    public function cancel(string $processId): bool
    {
        $this->log('info', 'Process cancelled', ['process_id' => $processId]);
        return true;
    }

    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->headers)
                ->get($this->baseUrl);

            return $response->status() < 500;
        } catch (\Exception $e) {
            $this->log('error', 'Connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getName(): string
    {
        return 'gcp_application_integration';
    }

    protected function formatPayload(array $payload): array
    {
        $formatted = [];
        foreach ($payload as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $formatted[$key] = ['jsonValue' => json_encode($value)];
            } else {
                $formatted[$key] = ['stringValue' => (string) $value];
            }
        }
        return $formatted;
    }

    protected function validateResponse(Response $response): void
    {
        if (!$response->successful()) {
            $errorMessage = $response->json('error.message') ?? 
                           $response->json('message') ?? 
                           'Unknown error';
            
            throw new \Exception("GCP API error: {$errorMessage}");
        }
    }
}
