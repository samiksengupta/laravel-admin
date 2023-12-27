<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use ReflectionClass;
use LaravelAdmin;
use Samik\LaravelAdmin\Database\Seeders\DatabaseSeeder;

class LaravelAdminInstallCommand extends Command
{
    protected $signature = 'admin:install {--e|empty : Whether to prevent seeders from running} {--f|force : Whether to overwrite existing published files, drop tables and re-run migrations} {--u|update : Whether you are updating LaravelAdmin instead of installing for the first time}';

    protected $description = 'Install Laravel Admin';

    public function handle()
    {
        $empty = $this->option('empty');
        $force = $this->option('force');
        $update = $this->option('update');

        if($force && config('app.env') == 'production') {
            $this->warn('You are trying to force install LaravelAdmin while the App is in production mode. Existing code will be overwritten and some data may be lost.');
            $continue = $this->confirm('Are you sure you want to continue?');
            if(!$continue) {
                $this->info('LaravelAdmin installation aborted!');
                return;
            }
        }

        $this->info("Installing LaravelAdmin...");
        try {
            
            // publish configuration
            $this->info("Publishing configuration...");

            if(!$update || $force) $this->call('vendor:publish', ['--tag' => 'config', '--force' => true, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);

            $replaceConfig = ($update && !$force) ? $this->confirm('Do you want to overwrite any existing LaravelAdmin config files in your project?', false) : true;
            if($replaceConfig) $this->call('vendor:publish', ['--tag' => 'admin-config', '--force' => $replaceConfig, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            
            // publish assets
            $this->info("Publishing assets...");
            $this->call('vendor:publish', ['--tag' => 'primary-assets', '--force' => $force || $update, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            
            $replaceAssets = ($update && !$force) ? $this->confirm('Do you want to overwrite any existing LaravelAdmin asset files in your project?', false) : true;
            if($replaceAssets) $this->call('vendor:publish', ['--tag' => 'secondary-assets', '--force' => $replaceAssets, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            
            // publish views
            if(!file_exists(resource_path('views/vendor/laravel-admin/partials/footer.blade.php')) && $this->confirm('Do you want to override the footer in your project?', true)) {
                $this->info("Publishing views...");
                $this->call('vendor:publish', ['--tag' => 'footer', '--force' => false, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            }
            else $this->info('Package\'s Footer was not published. You will need to override the footer manually at views/vendor/laravel-admin/partials/footer.blade.php.');
            
            // run migrations
            $this->info("Running migrations...");
            if($force) $this->call('migrate:fresh');
            else $this->call('migrate');

            // publish seeders
            $this->info("Publishing seeders...");
            $replaceSeeders = ($update && !$force) ? $this->confirm('Do you want to overwrite any existing LaravelAdmin seeder files in your project?', false) : true;
            if($replaceSeeders) $this->call('vendor:publish', ['--tag' => 'seeders', '--force' => $replaceSeeders, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);

            $replaceData = ($update && !$force) ? $this->confirm('Do you want to overwrite any existing LaravelAdmin data files in your project?', false) : true;
            $this->call('vendor:publish', ['--tag' => 'data', '--force' => $replaceData, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);

            if(!$update && $this->confirm('Do you want to overwrite DatabaseSeeder in your Project from LaravelAdmin? (Recommended if installing in a fresh project)', (config('app.env') != 'production'))) {
                $this->call('vendor:publish', ['--tag' => 'database-seeder', '--force' => true, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            }
            else $this->info('Project\'s DatabaseSeeder was not changed. You will need to include seeders manually in your Project\'s DatabaseSeeder.');
            
            // if full installation is opted
            if(!$empty) {

                // run seeders
                $this->info("Running seeders...");
                $this->call('db:seed', ['--class' => 'Database\\Seeders\\SpamSeeder']);
                if(!$update) $this->call('db:seed', ['--class' => 'Database\\Seeders\\UserSeeder']);

                // give option to create an admin user if none exists
                $roleAdmin = \Samik\LaravelAdmin\Models\Role::where('name', 'Admin')->first();
                if($roleAdmin && $roleAdmin->users()->doesntExist()) {
                    if($this->confirm('Do you want to create an Administrator user?', true)) {
                        $user = null;
                        do {
                            $user = new \Samik\LaravelAdmin\Models\User;
                            $user->name = $this->ask('Enter name', 'Administrator');
                            $user->email = $this->ask('Enter email', 'admin@laravel.admin');
                            $user->username = $this->ask('Enter username', 'admin');
                            $user->password = $this->ask('Enter password', '123456');
        
                            // show user preview and create
                            $this->table(['Name', 'Email', 'Username'], [$user->only(['name', 'email', 'username'])]);
                            $choice = $this->choice('Do you want to create this user?', ['Yes', 'No', 'Skip'], 0); 
                            if($choice === 'Skip') $user = null;
                        }
                        while($choice === 'No');
                        if($user) {
                            $user->role_id = $roleAdmin->id;
                            $user->active = 1;
                            $validator = \Illuminate\Support\Facades\Validator::make($user->makeVisible('password')->toArray(), \Samik\LaravelAdmin\Models\User::validationRules());
                            if($validator->fails()) collect($validator->errors()->getMessages())->map(fn($m) => data_get($m, "0"))->push("User was not created")->each(fn($m) => $this->error($m));
                            else {
                                $user->save();
                                if($user->exists) $this->info("{$roleAdmin->name} {$user->name} was created!");
                                else $this->warn("{$roleAdmin->name} {$user->name} was not created!");
                            }
                        }
                    }
                }
            }

            if(!$update) {
                // try to make LaravelAdmin User model parent of the project's User model, if present
                // and then make adjustments to support fields from the parent model
                $this->info('Attempting to set LaravelAdmin User model as parent to your project\'s User model...');
                if (file_exists(app_path('User.php')) || file_exists(app_path('Models/User.php'))) {
                    $modelPath = file_exists(app_path('User.php')) ? app_path('User.php') : app_path('Models/User.php');
                    $modelContent = file_get_contents($modelPath);
                    $modelPathInfo = pathinfo($modelPath);
                    $modelClassNamespace = str($modelPathInfo['dirname'])
                                            ->after('app' . DIRECTORY_SEPARATOR)
                                            ->replace(DIRECTORY_SEPARATOR, '\\')
                                            ->prepend('App\\')
                                            ->append('\\')
                                            ->append($modelPathInfo['filename'])
                                            ->toString();

                    if ($modelContent !== false) {
                        // Make project model extend the package model
                        $modelContent = str_replace('extends Authenticatable', "extends \Samik\LaravelAdmin\Models\User", $modelContent);
                        
                        // Modify fillable property in child with parent if possible
                        $parentClass = LaravelAdmin::modelClass('User');
                        $parentReflection = new ReflectionClass($parentClass);
                        $parentFillables = $parentReflection->hasProperty('fillable') ? collect($parentReflection->getProperty('fillable')->getValue(new $parentClass())) : collect([]);
                        
                        $childClass = get_class(app($modelClassNamespace));
                        $childReflection = new ReflectionClass($childClass);
                        $childFillables = $childReflection->hasProperty('fillable') ? collect($childReflection->getProperty('fillable')->getValue(new $childClass())) : collect([]);

                        if($parentFillables->count()) {
                            $modelContent = str_replace(
                                "protected \$fillable = [",
                                "protected \$fillable = [" . PHP_EOL . "\t\t" . implode(
                                            ',' . PHP_EOL . "\t\t", 
                                            $parentFillables
                                            ->reject(fn($f) => $childFillables->contains($f))
                                            ->map(fn($f) => "'{$f}'")
                                            ->toArray()
                                ) . ",",
                                $modelContent
                            );
                        }

                        file_put_contents($modelPath, $modelContent);
                        
                        // create extended Crud policy for project's User model
                        $this->info("{$modelPath} updated Successfully! Generating policy for User...");
                        $this->call('make:xpolicy', ['name' => "UserPolicy"]);
                    }
                } 
                else {
                    $this->warn('Unable to locate "User.php" in app or app/Models. Did you move this file?');
                    $this->warn('You will need to update this manually.  Change "extends Authenticatable" to "extends \Samik\LaravelAdmin\Models\User" in your User model and generate a policy for it with "php artisan make:xpolicy UserPolicy"');
                }
            }

            $this->call('jwt:secret');

            $this->call('optimize:clear');

            $this->info("Installed LaravelAdmin");

        } catch(\Illuminate\Database\QueryException $ex){
            $this->error($ex->getMessage());

        } catch(Exception $ex){ 
            $this->error($ex->getMessage());
        }
    }
}