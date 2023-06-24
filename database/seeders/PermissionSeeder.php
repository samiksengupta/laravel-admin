<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use Samik\LaravelAdmin\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createPermissions($this->permissions());
    }
    
    private function createPermissions($structure)
    {
        $deletablePermissionActions = Permission::pluck('action');

        // insert or update all permissions data given
        foreach ($structure as $policyClass => $methods) {
            foreach($methods as $method) {
                if($method === '_crud') {
                    $crudMethods = ['create', 'read', 'update', 'delete', 'import', 'export'];
                    foreach ($crudMethods as $crudMethod) {
                        $permission = Permission::updateOrCreate([
                            'action' => Str::studly($policyClass) . '.' . Str::camel($crudMethod)
                        ],[
                            'name' =>title($crudMethod) . ' ' .  title($policyClass),
                            'group' => Str::studly($policyClass),
                        ]);
                        if($permission && $deletablePermissionActions->contains($permission->action)) {
                            $deletablePermissionActions = $deletablePermissionActions->reject(fn($item) => $permission->action === $item);
                        }
                    }
                }
                else {
                    $permission = Permission::updateOrCreate([
                        'action' => Str::studly($policyClass) . '.' . Str::camel($method)
                    ],[
                        'name' => title($method) . ' ' .  title($policyClass),
                        'group' => Str::studly($policyClass),
                    ]);
                    if($permission && $deletablePermissionActions->contains($permission->action)) {
                        $deletablePermissionActions = $deletablePermissionActions->reject(fn($item) => $permission->action === $item);
                    }
                }
            }
        }

        // delete existing permissions that are not given
        Permission::whereIn('action', $deletablePermissionActions)->delete();
    }

    private function permissions()
    {
        $file = database_path("data/permissions.json");
        return \File::exists($file) ? \json_decode(\File::get($file)) : [];
    }
}
