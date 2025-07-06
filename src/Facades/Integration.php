<?php

namespace Osamah\LaravelDynamicIntegrations\Facades;

use Osamah\LaravelDynamicIntegrations\Services\ApplicationIntegration;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;

/**
 * Dynamic Integration Facade
 * 
 * Usage:
 * Integration::GcpIntegration() - creates new GcpIntegration instance
 * Integration::RestApiIntegration() - creates new RestApiIntegration instance
 * Integration::MyCustomIntegration() - creates new MyCustomIntegration instance
 * 
 * @method static ApplicationIntegration GcpIntegration(array $config = [])
 * @method static ApplicationIntegration RestApiIntegration(array $config = [])
 * @method static ApplicationIntegration ImportEntity(array $config = [])
 * @method static ApplicationIntegration MyCustomIntegration(array $config = [])
 * 
 * @see \Osamah\LaravelDynamicIntegrations\Services\ApplicationIntegration
 */
class Integration extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-dynamic-integrations.manager';
    }

    /**
     * Handle dynamic method calls to create integration instances
     */
    public static function __callStatic($method, $arguments)
    {
        $config = $arguments[0] ?? [];
        
        // Convert method name to class name (e.g., ImportEntity -> ImportEntityIntegration)
        $className = self::resolveClassName($method);
        
        // Try different namespaces
        $possibleClasses = [
            "App\\Integrations\\{$className}",
            "App\\Services\\Integration\\{$className}",
            "App\\Services\\Integrations\\{$className}",
        ];
        
        $fullClassName = null;
        foreach ($possibleClasses as $possibleClass) {
            if (class_exists($possibleClass)) {
                $fullClassName = $possibleClass;
                break;
            }
        }
        
        // Check if class exists
        if (!$fullClassName) {
            throw new \InvalidArgumentException("Integration class '{$className}' not found in any of the expected namespaces: " . implode(', ', $possibleClasses));
        }
        
        // Check if class extends ApplicationIntegration
        if (!is_subclass_of($fullClassName, ApplicationIntegration::class)) {
            throw new \InvalidArgumentException("Class '{$fullClassName}' must extend ApplicationIntegration");
        }
        
        // Create and return new instance
        return new $fullClassName($config);
    }

    /**
     * Resolve method name to class name
     */
    protected static function resolveClassName(string $method): string
    {
        // Handle different naming patterns:
        
        // 1. Already has "Integration" suffix
        if (Str::endsWith($method, 'Integration')) {
            return $method;
        }
        
        // 2. Add "Integration" suffix
        return $method . 'Integration';
    }

    /**
     * Get available integration classes
     */
    public static function getAvailableIntegrations(): array
    {
        $integrations = [];
        
        // Search in multiple possible directories
        $searchPaths = [
            app_path('Integrations'),
            app_path('Services/Integration'),
            app_path('Services/Integrations'),
        ];
        
        foreach ($searchPaths as $integrationPath) {
            if (is_dir($integrationPath)) {
                $files = glob($integrationPath . '/*.php');
                
                foreach ($files as $file) {
                    $className = basename($file, '.php');
                    
                    // Skip base class and abstract classes
                    if ($className === 'ApplicationIntegration') {
                        continue;
                    }
                    
                    // Determine namespace based on path
                    $namespace = 'App\\' . str_replace(['app/', '/'], ['', '\\'], str_replace(app_path(), '', dirname($file)));
                    $fullClassName = $namespace . '\\' . $className;
                    
                    if (class_exists($fullClassName) && is_subclass_of($fullClassName, ApplicationIntegration::class)) {
                        // Convert class name to facade method name
                        $methodName = Str::endsWith($className, 'Integration') 
                            ? Str::beforeLast($className, 'Integration')
                            : $className;
                        
                        $integrations[$methodName] = $fullClassName;
                    }
                }
            }
        }
        
        return $integrations;
    }

    /**
     * Create integration instance directly
     */
    public static function create(string $className, array $config = []): ApplicationIntegration
    {
        return self::__callStatic($className, [$config]);
    }
}
