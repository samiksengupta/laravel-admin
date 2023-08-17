<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;

use Samik\LaravelAdmin\Database\Seeders\DatabaseSeeder;

class LaravelAdminInstallCommand extends Command
{
    protected $signature = 'admin:install {--e|empty : Wheather to prevent seeders from running} {--f|force : Wheather to overwrite existing published files, drop tables and re-run migrations} {--u|update : Wheather you are updating LaravelAdmin instead of installing for the first time}';

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
            $this->call('vendor:publish', ['--tag' => 'config', '--force' => $force, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            
            // publish assets
            $this->info("Publishing assets...");
            $this->call('vendor:publish', ['--tag' => 'assets', '--force' => $force, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            
            // run migrations
            $this->info("Running migrations...");
            if($force) $this->call('migrate:fresh');
            else $this->call('migrate');

            // publish seeders
            $this->info("Publishing seeders...");
            $this->call('vendor:publish', ['--tag' => 'seeders', '--force' => $force, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            $this->call('vendor:publish', ['--tag' => 'data', '--force' => $force, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);

            if(!$update && $this->confirm('Do you want to overwrite DatabaseSeeder in your Project from LaravelAdmin? (Recommended if installing in a fresh project)', (config('app.env') != 'production'))) $this->call('vendor:publish', ['--tag' => 'database-seeder', '--force' => true, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
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
                $this->info('Attempting to set LaravelAdmin User model as parent to your project\'s User model...');
                if (file_exists(app_path('User.php')) || file_exists(app_path('Models/User.php'))) {
                    $userPath = file_exists(app_path('User.php')) ? app_path('User.php') : app_path('Models/User.php');
                    $str = file_get_contents($userPath);

                    if ($str !== false) {
                        $str = str_replace('extends Authenticatable', "extends \Samik\LaravelAdmin\Models\User", $str);
                        file_put_contents($userPath, $str);
                        
                        // create extended Crud policy for project's User model
                        $this->info("{$userPath} updated Successfully! Generating policy for User...");
                        $this->call('make:xpolicy', ['name' => "UserPolicy"]);
                    }
                } 
                else {
                    $this->warn('Unable to locate "User.php" in app or app/Models. Did you move this file?');
                    $this->warn('You will need to update this manually.  Change "extends Authenticatable" to "extends \Samik\LaravelAdmin\Models\User" in your User model and generate a policy for it with "php artisan make:xpolicy UserPolicy"');
                }
            }
            
            $this->info("Installed LaravelAdmin");

        } catch(\Illuminate\Database\QueryException $ex){
            $this->error($ex->getMessage());

        } catch(Exception $ex){ 
            $this->error($ex->getMessage());
        }
    }
}