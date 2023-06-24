<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $this->call(SettingSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(ApiResourceSeeder::class);
        $this->call(MenuItemSeeder::class);
    }
}
