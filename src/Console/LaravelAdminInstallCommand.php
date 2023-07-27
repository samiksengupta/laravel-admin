<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;

use Samik\LaravelAdmin\Database\Seeders\DatabaseSeeder;

class LaravelAdminInstallCommand extends Command
{
    protected $signature = 'admin:install {--e|empty} {--f|force}';

    protected $description = 'Install Laravel Admin';

    public function handle()
    {
        $this->info("Installing LaravelAdmin...");
        try {
            
            // publish configuration
            $this->info("Publishing configuration...");
            $this->call('vendor:publish', ['--tag' => 'config', '--force' => $this->option('force'), '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            
            // publish assets
            $this->info("Publishing assets...");
            $this->call('vendor:publish', ['--tag' => 'assets', '--force' => true, '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
            
            // run migrations
            $this->info("Running migrations...");
            if($this->option('force')) $this->call('migrate:fresh');
            else $this->call('migrate');
            
            // if full installation is opted
            if(!$this->option('empty')) {
                // publish seeders
                $this->info("Publishing seeders...");
                $this->call('vendor:publish', ['--tag' => 'seeders', '--force' => $this->option('force'), '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);
                $this->call('vendor:publish', ['--tag' => 'data', '--force' => $this->option('force'), '--provider' => 'Samik\\LaravelAdmin\\LaravelAdminServiceProvider']);

                // run seeders
                $this->info("Running seeders...");
                $this->call('db:seed');
            }
            
            $this->info("Installed LaravelAdmin");

        } catch(\Illuminate\Database\QueryException $ex){
            $this->error($ex->getMessage());

        } catch(Exception $ex){ 
            $this->error($ex->getMessage());
        }
    }
}