<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Samik\LaravelAdmin\Models\Role;
use Samik\LaravelAdmin\Models\User;
use Samik\LaravelAdmin\Models\Permission;

class UserSeeder extends Seeder
{
    protected $roleDev;
    protected $roleAdmin;
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createDefaultRoles();
        $this->createDefaultUsers();
    }

    private function createDefaultRoles()
    {
        // developer role and default permissions
        $this->roleDev = Role::firstOrCreate([
            'name' => 'Dev',
            'level' => 0,
            'unrestricted' => 1
        ]);
        
        // administrator role and default permissions
        $this->roleAdmin = Role::firstOrCreate([
            'name' => 'Admin',
            'level' => 1,
            'unrestricted' => 0
        ]);

        $exceptions = ['System.commands', 'Setting.create', 'Setting.update', 'Setting.delete', 'MenuItem.create', 'MenuItem.update', 'MenuItem.delete'];
        $this->roleAdmin->permissions()->syncWithoutDetaching(Permission::whereNotIn('action', $exceptions)->pluck('id'));
    }

    private function createDefaultUsers()
    {
        if($this->roleDev && $this->roleDev->users()->doesntExist()) {
            // default dev user
            $this->roleDev->users()->save(User::create([
                'email' => 'dev@laravel.admin',
                'username' => 'dev',
                'password' => '123456',
                'name' => 'Developer',
                'phone' => null,
            ]));
        }
    }
}
