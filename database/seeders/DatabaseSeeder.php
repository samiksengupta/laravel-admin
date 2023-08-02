<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SpamSeeder::class);
        $this->call(UserSeeder::class);
        
        if(config('app.env') === 'local') {
            // seeders to run in local environment
        }
    }
}
