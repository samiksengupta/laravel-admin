<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;

use Samik\LaravelAdmin\Database\Seeders\DatabaseSeeder;

class CreateUserCommand extends Command
{
    protected $signature = 'create:user {username : The Username for the user} {role=Admin : The role name/ID for the user} {--a|auto : Wheather to autofill the required data}';

    protected $description = 'Create new User';

    public function handle()
    {
        $auto = $this->option('auto');

        $this->info("Installing LaravelAdmin...");
        try {
            $role = \Samik\LaravelAdmin\Models\Role::where('id', $this->argument('role'))->orWhere('name', $this->argument('role'))->first();
            if($role) {
                $user = null;
                do {
                    if($auto) {
                        $user = new \Samik\LaravelAdmin\Models\User;
                        $user->name = $this->argument('username');
                        $user->email = sprintf('%s@laravel.admin', \Str::snake($user->name));
                        $user->username = $this->argument('username');
                        $user->password = $this->ask('Enter password', '123456');
                    }
                    else {
                        $user = new \Samik\LaravelAdmin\Models\User;
                        $user->name = $this->ask('Enter name', $this->argument('username'));
                        $user->email = $this->ask('Enter email', sprintf('%s@laravel.admin', \Str::snake($user->name)));
                        $user->username = $this->ask('Enter username', \Str::snake($user->name));
                        $user->password = $this->ask('Enter password', '123456');
                    }

                    // show user preview and create
                    $this->table(['Name', 'Email', 'Username'], [$user->only(['name', 'email', 'username'])]);
                    $choice = $this->choice('Do you want to create this user?', ['Yes', 'No', 'Skip'], 0); 
                    if($choice === 'Skip') $user = null;
                }
                while($choice === 'No');
                if($user) {
                    $user->role_id = $role->id;
                    $user->active = 1;
                    $validator = \Illuminate\Support\Facades\Validator::make($user->makeVisible('password')->toArray(), \Samik\LaravelAdmin\Models\User::validationRules());
                    if($validator->fails()) collect($validator->errors()->getMessages())->map(fn($m) => data_get($m, "0"))->push("User was not created")->each(fn($m) => $this->error($m));
                    else {
                        $user->save();
                        if($user->exists) $this->info("{$role->name} {$user->name} was created!");
                        else $this->warn("{$role->name} {$user->name} was not created!");
                    }
                }
                else {
                    $this->info("User was not created");
                }
            }
            else {
                $this->warn("Could not create User because, Role with '{$this->argument('role')}' ID or Name doesn't exist");
            }

        } catch(\Illuminate\Database\QueryException $ex){
            $this->error($ex->getMessage());

        } catch(Exception $ex){ 
            $this->error($ex->getMessage());
        }
    }
}