<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;

class CreatePermissionCommand extends Command
{
    protected $signature = 'create:permission {permission : The permission to create in Model.action format} {--p|policy : Whether to add to policy} {--s|seed : Whether to seed permission seeder} {--a|all : Add to policy and run permission seeder}';

    protected $description = 'Create new Permission';

    public function handle()
    {
        $permission = $this->argument('permission');
        $policy = $this->option('policy');
        $seed = $this->option('seed');
        $all = $this->option('all');

        // Generate prompt for confirmation
        $tasks = [];
        $tasks[] = "add a Permission entry to the JSON data file";;
        if($all || $policy) $tasks[] = "insert a Policy entry in the policy file";
        if($all || $seed) $tasks[] = "seed PermissionSeeder to the database";
        
        $prompt = "This will " . (function() use($tasks) {
            $message = "";
            foreach($tasks as $i => $task) $message .= ($i < count($tasks) - 1) ? "{$task}, " : "and {$task}";
            return $message;
        })() . ". Continue?";

        if($this->confirm($prompt, true)) {
            $added = $this->addPermissionToJson($permission);

            if ($all || $policy) {
                $this->addToPolicy($permission);
            }
    
            if ($added) {
                if ($all || $seed) {
                    $this->call('db:seed', ['--class' => 'PermissionSeeder']);
                } else {
                    $this->info("Please run PermissionSeeder to persist changes to the database.");
                }
            }
        }
        else {
            $this->info("Permission creation aborted.");
        }

        
    }

    private function addPermissionToJson($permission)
    {
        list($model, $action) = explode('.', $permission);
        
        $filePath = base_path('database/data/permissions.json');

        if (!file_exists($filePath)) {
            $this->warn("Could not find file at {$filePath}. Skipping...");
            return false;
        }

        $jsonContent = json_decode(file_get_contents($filePath), true);
        if(!$jsonContent) {
            $this->warn("Could not read JSON file at {$filePath}. Skipping...");
            return false;
        }

        if (isset($jsonContent[$model])) {
            if(in_array($action, $jsonContent[$model])) {
                $this->info("Permission {$permission} already exists in {$filePath}. Skipping...");
                return false;
            }
            $jsonContent[$model][] = $action;
            file_put_contents($filePath, json_encode($jsonContent, JSON_PRETTY_PRINT));
            $this->info("New Permission written to {$filePath}.");
            return true;
        } 
        else {
            $this->warn("Model {$model} not found in {$filePath}. Skipping...");
            return false;
        }
    }

    private function addToPolicy($permission)
    {
        list($model, $action) = explode('.', $permission);

        $filePath = app_path("Policies/{$model}Policy.php");

        if (!file_exists($filePath)) {
            $this->warn("Could not find policy file at {$filePath}. Skipping...");
            return false;
        }

        // Load existing content of the policy file
        $policyContent = file_get_contents($filePath);

        // Check if there is an existing class and it's closed
        if (preg_match("/class\s+{$model}Policy\s+extends\s+CrudPolicy\s*{(.*)}/s", $policyContent, $matches) && $insertPosition = nth_last_index($policyContent, "}", 2)) {

            $functionName = str($action)->camel()->toString(); // Assuming function names are in camelCase

            if(strpos($policyContent, "function {$functionName}")) {
                $this->info("Function {$functionName} already exists in {$filePath}. Skipping...");
                return false;
            }

            // Append the new function after existing functions
            $existingFunctions = $matches[1];

            $newFunctionCode = "\n\tpublic function $functionName(User \$user)\n\t{\n\t\treturn is_policy_authorized(\$this, __FUNCTION__, \$user->role_id);\n\t}\n";

            $updatedContent = substr_replace($policyContent, $newFunctionCode . "}", $insertPosition, 1);

            file_put_contents($filePath, $updatedContent);
            $this->info("Function added to Policy class at {$filePath} successfully.");
            return true;
        } 

        // If the class is not found or not closed, handle accordingly
        $this->warn("Unable to update Policy class at {$filePath}.");
        return false;
    }

    private function nthLastPos(string $haystack, string $needle, int $n) { 
        $index = 0; 
        for($i = 1; $i < $n; $i++) $index = strrpos($haystack, $needle, $index); 
        return $index; 
    }
}
