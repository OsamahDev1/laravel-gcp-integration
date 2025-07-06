<?php

namespace Osamahdev1\LaravelGcpIntegration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:integration {name} 
                            {--base-url= : Base URL for the integration}
                            {--auth-token= : Authentication token}
                            {--api-key= : API key}
                            {--timeout=30 : Request timeout in seconds}
                            {--namespace= : Custom namespace for the integration}
                            {--path= : Custom path for the integration file}
                            {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new GCP integration class';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $className = $this->resolveClassName($name);
        $namespace = $this->getNamespace();
        $filePath = $this->getFilePath($className);

        // Check if file already exists
        if (file_exists($filePath) && !$this->option('force')) {
            if (!$this->confirm("Integration class '{$className}' already exists. Overwrite?")) {
                $this->info('Integration creation cancelled.');
                return 0;
            }
        }

        // Create the integration class
        $this->createIntegrationClass($className, $name, $namespace);

        // Create configuration example
        $this->createConfigExample($name);

        // Show usage example
        $this->showUsageExample($name, $namespace);

        $this->info("✅ Integration class '{$className}' created successfully!");
        $this->info("📁 File: {$filePath}");

        return 0;
    }

    protected function resolveClassName(string $name): string
    {
        // Convert to PascalCase and add Integration suffix
        $pascalName = Str::studly($name);
        
        if (!Str::endsWith($pascalName, 'Integration')) {
            $pascalName .= 'Integration';
        }

        return $pascalName;
    }

    protected function getNamespace(): string
    {
        return $this->option('namespace') ?: 'App\\Integrations';
    }

    protected function getFilePath(string $className): string
    {
        if ($customPath = $this->option('path')) {
            return $customPath . '/' . $className . '.php';
        }

        $namespace = $this->getNamespace();
        $path = str_replace(['App\\', '\\'], ['app/', '/'], $namespace);
        
        return base_path($path . '/' . $className . '.php');
    }

    protected function createIntegrationClass(string $className, string $name, string $namespace): void
    {
        $stub = $this->getStub();
        
        $replacements = [
            '{{namespace}}' => $namespace,
            '{{className}}' => $className,
            '{{integrationName}}' => Str::snake($name),
            '{{baseUrl}}' => $this->option('base-url') ?: 'https://api.example.com',
            '{{authToken}}' => $this->option('auth-token') ?: 'your-auth-token',
            '{{apiKey}}' => $this->option('api-key') ?: 'your-api-key',
            '{{timeout}}' => $this->option('timeout') ?: '30',
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);

        // Ensure directory exists
        $directory = dirname($this->getFilePath($className));
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($this->getFilePath($className), $content);
    }

    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace {{namespace}};

use Osamahdev1\LaravelGcpIntegration\Services\ApplicationIntegration;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class {{className}} extends ApplicationIntegration
{
    public function execute(string $triggerId, array $payload): array
    {
        try {
            $this->log('info', 'Executing {{integrationName}} integration', [
                'trigger_id' => $triggerId,
                'payload_size' => count($payload)
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers)
                ->post($this->baseUrl, [
                    'trigger_id' => $triggerId,
                    'payload' => $payload
                ]);

            $this->validateResponse($response);
            
            $data = $response->json();
            
            $this->log('info', '{{integrationName}} integration executed successfully', [
                'trigger_id' => $triggerId,
                'process_id' => $data['process_id'] ?? $data['id'] ?? null
            ]);

            return [
                'success' => true,
                'process_id' => $data['process_id'] ?? $data['id'] ?? null,
                'status' => $data['status'] ?? 'running',
                'data' => $data
            ];

        } catch (\Exception $e) {
            $this->log('error', '{{integrationName}} integration execution failed', [
                'trigger_id' => $triggerId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception("Failed to execute {{integrationName}} trigger '{$triggerId}': {$e->getMessage()}", 0, $e);
        }
    }

    public function getStatus(string $processId): array
    {
        try {
            $endpoint = "{$this->baseUrl}/status/{$processId}";
            
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers)
                ->get($endpoint);

            $this->validateResponse($response);
            
            $data = $response->json();

            return [
                'success' => true,
                'process_id' => $processId,
                'status' => $data['status'] ?? 'unknown',
                'progress' => $data['progress'] ?? null,
                'data' => $data
            ];

        } catch (\Exception $e) {
            $this->log('error', 'Failed to get status', [
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception("Failed to get status for process '{$processId}': {$e->getMessage()}", 0, $e);
        }
    }

    public function cancel(string $processId): bool
    {
        try {
            $endpoint = "{$this->baseUrl}/cancel/{$processId}";
            
            $response = Http::timeout($this->timeout)
                ->withHeaders($this->headers)
                ->post($endpoint);

            $this->validateResponse($response);

            $this->log('info', 'Process cancelled', ['process_id' => $processId]);
            return true;

        } catch (\Exception $e) {
            $this->log('error', 'Failed to cancel process', [
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function testConnection(): bool
    {
        try {
            $endpoint = "{$this->baseUrl}/health";
            
            $response = Http::timeout(10)
                ->withHeaders($this->headers)
                ->get($endpoint);

            return $response->successful();
        } catch (\Exception $e) {
            $this->log('error', 'Connection test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getName(): string
    {
        return '{{integrationName}}_integration';
    }

    protected function validateResponse(Response $response): void
    {
        if (!$response->successful()) {
            $errorMessage = $response->json('error.message') ?? 
                           $response->json('message') ?? 
                           'Unknown error';
            
            throw new \Exception("{{integrationName}} API error: {$errorMessage}");
        }
    }
}
STUB;
    }

    protected function createConfigExample(string $name): void
    {
        $configName = Str::snake($name);
        
        $this->line('');
        $this->info('📋 Configuration Example:');
        $this->line('');
        $this->line("Add to your .env file:");
        $this->line('');
        $this->line("{{integrationName}}_base_url={{baseUrl}}");
        $this->line("{{integrationName}}_auth_token={{authToken}}");
        $this->line("{{integrationName}}_timeout={{timeout}}");
        $this->line('');
        $this->line("Or add to config/services.php:");
        $this->line('');
        $this->line("'{{integrationName}}' => [");
        $this->line("    'base_url' => env('{{integrationName}}_base_url', '{{baseUrl}}'),");
        $this->line("    'auth_token' => env('{{integrationName}}_auth_token', '{{authToken}}'),");
        $this->line("    'timeout' => env('{{integrationName}}_timeout', {{timeout}}),");
        $this->line("],");
    }

    protected function showUsageExample(string $name, string $namespace): void
    {
        $className = $this->resolveClassName($name);
        $methodName = Str::beforeLast($className, 'Integration');
        
        $this->line('');
        $this->info('🚀 Usage Example:');
        $this->line('');
        $this->line("use Osamahdev1\\LaravelGcpIntegration\\Facades\\Integration;");
        $this->line('');
        $this->line("\$integration = Integration::{$methodName}([");
        $this->line("    'base_url' => config('services.{{integrationName}}.base_url'),");
        $this->line("    'auth_token' => config('services.{{integrationName}}.auth_token'),");
        $this->line("    'timeout' => config('services.{{integrationName}}.timeout'),");
        $this->line("]);");
        $this->line('');
        $this->line("\$result = \$integration->execute('your-trigger-id', \$payload);");
        $this->line('');
        $this->line("Or with direct instantiation:");
        $this->line('');
        $this->line("use {$namespace}\\{$className};");
        $this->line('');
        $this->line("\$integration = new {$className}(\$config);");
        $this->line("\$result = \$integration->execute('your-trigger-id', \$payload);");
        $this->line('');
    }
}
