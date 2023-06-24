<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Samik\LaravelAdmin\Models\Role;
use Samik\LaravelAdmin\Models\User;
use Samik\LaravelAdmin\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createDefaultUsers();
    }

    private function createDefaultUsers()
    {
        // developer group and default permissions
        $developer = Role::create([
            'name' => 'Dev',
            'level' => 0,
            'unrestricted' => 1
        ]);

        // default dev user
        $user = User::create([
            'email' => 'dev@laravel.admin',
            'username' => 'dev',
            'password' => '123456',
            'name' => 'Developer',
            'phone' => null,
        ]);
        $user->role()->associate($developer)->save();

        // admin group and default permissions
        $admin = Role::create([
            'name' => 'Admin',
            'level' => 1,
            'unrestricted' => 0
        ]);

        $exceptions = ['System.commands', 'Setting.create', 'Setting.update', 'Setting.delete', 'MenuItem.create', 'MenuItem.update', 'MenuItem.delete'];
        $permissions = Permission::all();
        foreach($permissions as $permission) {
            if(\in_array($permission->action, $exceptions)) continue;
            $admin->permissions()->attach($permission->id);
        }

        // default admin user
        $user = User::create([
            'email' => 'admin@laravel.admin',
            'username' => 'admin',
            'password' => '123456',
            'name' => 'Administrator',
            'phone' => null,
        ]);
        $user->role()->associate($admin)->save();
        
    }
}
