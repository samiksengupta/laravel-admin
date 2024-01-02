<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Support\Arr;
use Illuminate\Console\Command;

class CreateApiCommand extends Command
{
    protected $signature = 'create:api {verb : HTTP Verbs like GET/POST/PUT/PATCH/DELETE to use or RES for resource} {path : The route path to use} {action : Use the format "ControllerName@methodName" for single route and just "ControllerName" for resource} {model? : The model for the API to use or none if ignored} {--r|api-resource= : Name for Api Resource in the format of name:field1,field2,field3... or skip if not provided} {--s|seed : Whether to seed ApiResource seeder} {--a|all : Creates ApiResource entry and Seeds}';

    protected $description = 'Create new API endpoint';

    public function handle()
    {
        $verb = $this->argument('verb');
        $path = $this->argument('path');
        $action = $this->argument('action');
        $model = $this->argument('model');
        $apiResource = $this->option('api-resource');
        $seed = $this->option('seed');
        $all = $this->option('all');

        $modelName = $model ? str($model)->studly()->toString() : null;
        
        // Extract route data from action string
        $routeAction = $this->extractFromRouteActionString($action);
        if(!$routeAction['controller']) {
            $this->warn("Controller name could not be read from action: {$action}. Aborting...");
            return false;
        }
        
        // Add routes to routes/api.php
        $this->addToRouteFile($verb, $path, $routeAction);
        
        // Create controller
        $this->createController($verb, $path, $routeAction, $modelName);

        // Generate ApiResource for testing
        if($all || $apiResource) {
            $this->addApiResourceToJson($verb, $path, $routeAction, $apiResource, $modelName);
        }

        // Seed ApiResource
        if ($all || $seed) {
            $this->call('db:seed', ['--class' => 'ApiResourceSeeder']);
        } else {
            $this->info("Please run ApiResourceSeeder to persist changes to the database.");
        }
    }

    private function addToRouteFile($verb, $path, $routeAction)
    {
        $filePath = base_path("routes/api.php");

        if (!file_exists($filePath)) {
            $this->warn("Could not find route file at {$filePath}. Skipping...");
            return false;
        }
        
        // Read the existing content of the api.php file
        $contents = file_get_contents($filePath);

        // Generate a namespace
        $groupNamespace = "App\\Http\\Controllers\\" . $routeAction['namespace'];

        // Build a pattern
        $pattern = "group[\W\w\s]*?namespace[\W\w\s]*?\'" . join("[\W]+", preg_split('/[\W]/', $groupNamespace)) . "\'[\W\w\s]*?function[\W\w\s]*?(?<OpeningBrace>\{)(?!\{)";

        // Prepare to store nesting level for Indent generation
        $level = 0;

        // Get Insert position and calculate level
        $position = $this->getInsertPosition($pattern, $contents, 0, strlen($contents), $level);

        // Check if namespace is required when generating route line
        $withNamespace = $position == strlen($contents);

        // Genertate indent based on Level
        $indent = generate_indent($level);

        // Generate Route Line
        $newCode = $this->generateRouteLine($verb, $path, $routeAction, $withNamespace);

        // Replace contents
        $contents = substr_replace($contents, "\n\n{$indent}{$newCode}", $position + 1, 0);

        // Save contents
        \file_put_contents($filePath, $contents);

        $inserted = true;
        
        if($inserted) $this->info("Inserted API route {$verb} {$path}");
        else $this->warn("API route could not be added to route file at {$filePath} and you must do this manually");
    }

    private function createController($verb, $path, $routeAction, $modelName = null)
    {
        $params = ['--api' => true];
        if($modelName) $params['--model'] = $modelName;
        $name = $routeAction['namespace'] . "/" . $routeAction['controller'];
        $params['name'] = $name;
        $this->call("make:controller", $params);
        
        $isResource = $this->isResource($verb, $routeAction);
        if(!$isResource && $routeAction['method']) {

            // Append method to controller class
            $filePath = base_path("app/Http/Controllers/{$name}.php");

            if (!file_exists($filePath)) {
                $this->warn("Could not find controller file at {$filePath}. Skipping...");
                return false;
            }

            // Check if method already exists
            $class = "\\App\\Http\\Controllers\\" . str_replace("/", "\\", $routeAction['namespace']) . "\\" . $routeAction['controller'];
            if(method_exists($class, $routeAction['method'])) {
                $this->info("Method {$routeAction['method']} already exists in {$filePath}. Skipping...");
                return false;
            }

            // Read the existing content of the api.php file
            $contents = file_get_contents($filePath);

            // Prepare to store nesting level for Indent generation
            $level = 1;
    
            // Get Insert position and calculate level
            $position = \nth_last_index($contents, '}', 2);
    
            // Genertate indent based on Level
            $indent = generate_indent($level);
    
            // Generate Route Line
            $methodSignature = $routeAction['method'];

            // paramters
            $parameters = ["Request \$request"];
            
            if(preg_match_all("#[\{](?<Param>[a-z]*)[\}]#", $path, $matches)) { 

                foreach($matches['Param'] as $parameter) {
                    if($modelName && str($modelName)->camel()->toString() == $parameter) $parameters[] = "{$modelName} \${$parameter}";
                    else $parameters[] = "mixed \${$parameter}";
                }
            }
            
            $verb = strtoupper($verb);

            $methodParams = array_map(function($item) use($modelName) {
                list($type, $variableName) = explode(' ', $item);
                if($type == 'Request') $type = "\\Illuminate\\Http\\Request";
                if($type == $modelName) $type = "\\App\\Models\\{$modelName}";
                return "@param {$type} {$variableName}";
            }, $parameters);

            $methodParams[] = "@return \\Illuminate\\Http\\Response";
            $methodParams = join("\n\t * ", $methodParams);

            $methodComments = "/**\n\t * {$verb} {$path} API\n\t * \n\t * {$methodParams}\n\t */";

            $parameters = join(', ', $parameters);
            $methodSignature = "{$methodSignature} ({$parameters})";

            $newCode = "{$methodComments}\n\tpublic function {$methodSignature}\n\t{\n\t\t//\n\t}\n";
    
            // Replace contents
            $contents = substr_replace($contents, "\n{$indent}{$newCode}", $position, 0);

            // Save contents
            \file_put_contents($filePath, $contents);
        }
    }

    private function addApiResourceToJson($verb, $path, $routeAction, $apiResource, $modelName = null)
    {
        $filePath = base_path('database/data/api-resources.json');

        if (!file_exists($filePath)) {
            $this->warn("Could not find file at {$filePath}. Skipping...");
            return false;
        }

        $jsonContent = json_decode(file_get_contents($filePath), true);
        if(!$jsonContent) {
            $this->warn("Could not read JSON file at {$filePath}. Skipping...");
            return false;
        }
        
        $added = false;
        if (is_array($jsonContent)) {
            $name = null;
            $fields = null;
            $secure = 0;
            $pattern = "(?<Name>[^:\r\n]+)(?::(?<Fields>[^:\r\n]*))?";
            if(preg_match("#$pattern#", $apiResource, $matches)) {
                $name = @$matches['Name'];
                $fields = @$matches['Fields'];
            }
            $isResource = $this->isResource($verb, $routeAction);
            if($isResource) {
                $resourcePathName = $path;
                $resourceIdentifierName = $modelName ? str($modelName)->singular()->toString() : 'id';
                foreach([
                    [
                        'method' => 'GET',
                        'path' => "{$resourcePathName}"
                    ],
                    [
                        'method' => 'POST',
                        'path' => "{$resourcePathName}"
                    ],
                    [
                        'method' => 'GET',
                        'path' => "{$resourcePathName}/{{$resourceIdentifierName}}"
                    ],
                    [
                        'method' => 'PUT',
                        'path' => "{$resourcePathName}/{{$resourceIdentifierName}}"
                    ],
                    [
                        'method' => 'PATCH',
                        'path' => "{$resourcePathName}/{{$resourceIdentifierName}}"
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => "{$resourcePathName}/{{$resourceIdentifierName}}"
                    ],
                ] as $endpoint) 
                {
                    if(Arr::first($jsonContent, fn($value, $key) => $value['method'] == $endpoint['method'] && $value['route'] == $endpoint['path'])) {
                        $this->info("ApiResource {$endpoint['method']} {$endpoint['path']} already exists in {$filePath}. Skipping...");
                        continue;
                    }

                    $jsonContent[] = [
                        'name' => $name ?? "{$endpoint['method']} {$endpoint['path']}",
                        'method' => $endpoint['method'],
                        'route' => $endpoint['path'],
                        'fields' => $fields,
                        'secure' => $secure,
                    ];
                    
                    $added = true;
                }
            }
            else {
                $method = strtoupper($verb);
                
                if(Arr::first($jsonContent, fn($value, $key) => $value['method'] == $method && $value['route'] == $path)) {
                    $this->info("ApiResource {$method} {$path} already exists in {$filePath}. Skipping...");
                }
                else {
                    $jsonContent[] = [
                        'name' => $name ?? "{$method} {$path}",
                        'method' => $method,
                        'route' => $path,
                        'fields' => $fields,
                        'secure' => $secure,
                    ];

                    $added = true;
                }
            }
            
            if($added) {
                file_put_contents($filePath, json_encode($jsonContent, JSON_PRETTY_PRINT));
                $this->info("New Api Resource written to {$filePath}.");
                return true;
            }
        } 
        else {
            $this->warn("ApiResource array not found in {$filePath}. Skipping...");
            return false;
        }
    }

    private function extractFromRouteActionString($action) 
    {
        // Define the regular expression pattern
        $pattern = "(?:(?<Namespace>[\w\d\-\_\\\/]*)[\\\/])?(?<ClassName>[\w\d\-\_]*)@?(?<ClassMethod>[\w\d\-\_]*)?";
        
        // Extract the parts from the string
        preg_match("#$pattern#", $action, $matches);
        
        // Output the results
        $namespace = @$matches['Namespace'] ?? '';
        $namespace = preg_replace('/\//', '\\', $namespace);

        $controller = @$matches['ClassName'] ?? null;

        $method = @$matches['ClassMethod'] ?? null;
        
        // Output the results
        return [
            'namespace' => $namespace,
            'controller' => $controller,
            'method' => $method,
        ];
    }

    private function isResource($verb, $routeAction) 
    {
        if(!@$routeAction['method']) return true;
        if(!in_array(strtolower($verb), ['get', 'post', 'put', 'patch', 'delete', 'options'])) return true;
        return false;
    }

    private function generateRouteLine($verb, $path, $routeAction, $withNamespace = true) 
    {
        $line = null;
        $namespace = $withNamespace ? "App\\Http\\Controllers\\{$routeAction['namespace']}\\" : '';
        $controllerName = $routeAction['controller'];
        if($this->isResource($verb, $routeAction)) {
            $line = "Route::apiResource('{$path}', '{$namespace}{$controllerName}');";
        }
        else {
            $verb = strtolower($verb);
            $methodName = $routeAction['method'];
            $line = "Route::{$verb}('{$path}', '{$namespace}{$controllerName}@{$methodName}');";
        }
        return $line;
    }

    // insert position finder
    private function getInsertPosition($pattern, $contents, $start, $stop, &$level = 0) 
    {
        if(preg_match("#$pattern#", $contents, $matches, PREG_OFFSET_CAPTURE)) {
            $opBracePos = @$matches['OpeningBrace'][1];
            $level = calculate_brace_nesting_Level($contents, 0, $opBracePos);
            return $opBracePos;
        }
        
        return strlen($contents);
    }
}
