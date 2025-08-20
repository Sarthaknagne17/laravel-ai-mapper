<?php

namespace Araminco\AiMapper\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class GenerateMapCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai:map 
        {--output=ai-project-map.json : The name of the output file.}
        {--compact : Generate a more compact, summarized output.}
        {--no-db : Exclude the database schema from the output.}
        {--no-files : Exclude the directory structure from the output.}
        {--no-models : Exclude the model structure from the output.}
        {--no-routes : Exclude the routes list from the output.}
        {--no-deps : Exclude composer dependencies from the output.}
        {--no-filament : Exclude the Filament structure from the output.}';

    /**
     * The console command description.
     */
    protected $description = 'Generate an AI-friendly JSON map of the project structure.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating AI project map...');

        $isCompact = $this->option('compact');
        
        $projectMap = [
            'projectName' => config('app.name'),
            'laravelVersion' => app()->version(),
        ];

        if (!$this->option('no-db')) {
            $projectMap['databaseSchema'] = $this->getDatabaseSchema($isCompact);
        }
        if (!$this->option('no-files')) {
            $projectMap['directoryStructure'] = $this->getDirectoryStructure();
        }
        if (!$this->option('no-models')) {
            $projectMap['models'] = $this->getModelStructure();
        }
        if (!$this->option('no-routes')) {
            $projectMap['routes'] = $this->getRoutes($isCompact);
        }
        if (!$this->option('no-deps')) {
            $projectMap['composerDependencies'] = $this->getComposerDependencies($isCompact);
        }
        if (!$this->option('no-filament')) {
            $projectMap['filament'] = $this->getFilamentStructure($isCompact);
        }

        $outputFile = $this->option('output');
        File::put(base_path($outputFile), json_encode($projectMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Project map successfully generated at: {$outputFile}");
        return self::SUCCESS;
    }

    private function getDatabaseSchema(bool $compact = false): array
    {
        $schema = [];
        try {
            $tables = Schema::getAllTables();
            foreach ($tables as $table) {
                $tableName = current((array)$table);
                if ($compact) {
                    $schema[$tableName] = collect(Schema::getColumns($tableName))
                        ->map(fn($col) => $col['name'] . ': ' . $col['type_name'] . ($col['nullable'] ? ' (nullable)' : ''))
                        ->all();
                } else {
                    $schema[$tableName] = [
                        'columns' => Schema::getColumns($tableName),
                        'indexes' => Schema::getIndexes($tableName),
                        'foreign_keys' => Schema::getForeignKeys($tableName),
                    ];
                }
            }
        } catch (Throwable $e) {
            $this->warn('Could not get database schema: ' . $e->getMessage());
        }
        return $schema;
    }
    
    private function getDirectoryStructure(): array
    {
        $structure = [];
        $pathsToScan = ['app', 'routes', 'config', 'database/migrations', 'resources/views'];

        foreach ($pathsToScan as $path) {
            $fullPath = base_path($path);
            if (!File::exists($fullPath)) continue;

            $directory = new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);
            
            $pathStructure = [];
            foreach ($iterator as $file) {
                $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $pathParts = explode(DIRECTORY_SEPARATOR, $relativePath);
                
                $currentLevel = &$pathStructure;
                foreach ($pathParts as $part) {
                    if (!isset($currentLevel[$part])) {
                        $currentLevel[$part] = [];
                    }
                    $currentLevel = &$currentLevel[$part];
                }
            }
            $structure[$path] = $pathStructure[$path] ?? [];
        }
        return $structure;
    }
    
    private function getModelStructure(): array
    {
        $models = [];
        $path = app_path('Models');

        if (!File::isDirectory($path)) {
            return [];
        }

        foreach (File::allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') continue;

            $class = 'App\\Models\\' . $file->getFilenameWithoutExtension();

            if (!class_exists($class) || !is_subclass_of($class, Model::class)) {
                continue;
            }

            try {
                $reflection = new ReflectionClass($class);
                if ($reflection->isAbstract()) continue;

                $modelInstance = $reflection->newInstanceWithoutConstructor();
                
                $models[] = [
                    'class' => $class,
                    'table' => $modelInstance->getTable(),
                    'fillable' => $modelInstance->getFillable(),
                    'guarded' => $modelInstance->getGuarded(),
                    'hidden' => $modelInstance->getHidden(),
                    'casts' => $modelInstance->getCasts(),
                    'relationships' => $this->getModelRelationships($class),
                ];
            } catch (Throwable $e) {
                $this->warn("Could not analyze model {$class}: " . $e->getMessage());
            }
        }

        return $models;
    }

    private function getModelRelationships(string $class): array
    {
        $relationships = [];
        $reflection = new ReflectionClass($class);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() > 0 || $method->class !== $class) {
                continue;
            }

            try {
                $returnType = $method->getReturnType();
                if (!$returnType) continue;

                $returnTypeName = $returnType->getName();
                
                if (is_subclass_of($returnTypeName, Relation::class)) {
                    $relationInstance = $method->invoke($reflection->newInstanceWithoutConstructor());
                    $relationships[$method->getName()] = [
                        'type' => Str::afterLast($returnTypeName, '\\'),
                        'related_model' => get_class($relationInstance->getRelated()),
                    ];
                }
            } catch (Throwable $e) {
                // Ignore methods that cannot be invoked without parameters.
            }
        }

        return $relationships;
    }

    private function getRoutes(bool $compact = false): array
    {
        try {
            Artisan::call('route:list', ['--json' => true]);
            $output = Artisan::output();
            $output = preg_replace('/\e\[[\d;]*m/', '', $output);
            $routes = json_decode($output, true);

            if ($compact) {
                return collect($routes)->map(function ($route) {
                    $action = Str::replace('App\\Http\\Controllers\\', '', $route['action']);
                    return sprintf('[%s] %s -> %s (Name: %s)', $route['method'], $route['uri'], $action, $route['name'] ?? 'N/A');
                })->all();
            }
            return $routes ?? [];
        } catch (Throwable $e) {
            $this->warn('Could not get routes: ' . $e->getMessage());
            return [];
        }
    }

    private function getComposerDependencies(bool $compact = false): array
    {
        $composerJsonPath = base_path('composer.json');
        if (!File::exists($composerJsonPath)) return [];

        $composerJson = json_decode(File::get($composerJsonPath), true);
        $dependencies = [
            'require' => $composerJson['require'] ?? [],
            'require-dev' => $composerJson['require-dev'] ?? [],
        ];

        if (!$compact) {
            $composerLockPath = base_path('composer.lock');
            if (File::exists($composerLockPath)) {
                $composerLock = json_decode(File::get($composerLockPath), true);
                $dependencies['installed_versions'] = collect($composerLock['packages'] ?? [])
                    ->mapWithKeys(fn($pkg) => [$pkg['name'] => $pkg['version']])->all();
            }
        }
        return $dependencies;
    }

    private function getFilamentStructure(bool $compact = false): ?array
    {
        if (!class_exists(\Filament\Filament::class)) {
            return null;
        }

        try {
            $filamentData = [];
            foreach (app('filament')->getPanels() as $panel) {
                $resources = $panel->getResources();
                $pages = $panel->getPages();
                $widgets = $panel->getWidgets();

                if ($compact) {
                    $resources = array_map(fn($class) => Str::afterLast($class, '\\'), $resources);
                    $pages = array_map(fn($class) => Str::afterLast($class, '\\'), $pages);
                    $widgets = array_map(fn($class) => Str::afterLast($class, '\\'), $widgets);
                }

                $filamentData['panels'][] = [
                    'id' => $panel->getId(),
                    'path' => $panel->getPath(),
                    'resources' => $resources,
                    'pages' => $pages,
                    'widgets' => $widgets,
                ];
            }
            return $filamentData;
        } catch (Throwable $e) {
            $this->warn('Could not analyze Filament structure: ' . $e->getMessage());
            return null;
        }
    }
}