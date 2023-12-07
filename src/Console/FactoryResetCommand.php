<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;

class FactoryResetCommand extends Command
{
    protected $signature = 'factory:reset';

    protected $description = 'Drops all tables and re-runs migrations and seeders';

    public function handle()
    {
        $continue = true;
        
        if($continue) {
            $this->call('migrate:fresh', ['--seed' => true]);
            return;
        }
    }
}