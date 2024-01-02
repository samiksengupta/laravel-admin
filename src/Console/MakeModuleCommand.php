<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The class name for the Model } {--p|permissions : Create Permissions } {--m|menuitems : Create MenuItems} {--s|seed : Seed Permissions and MenuItems} {--g|grant : Grant seeded permissions to all roles } {--a|all : Create seeders for Permissions and MenuItems, run Seeders and Grant permissions to all roles}';

    protected $description = 'Create a new extended model class with migration, controller and policy';

    public function handle()
    {
        $moduleName = $this->argument('name');
        $this->call('make:xmodel', ['name' => $moduleName, '--migration' => true]);
        $this->call('make:xpolicy', ['name' => "{$moduleName}Policy"]);
        $this->call('make:xcontroller', ['name' => "Admin\\{$moduleName}Controller"]);

        if($this->option('permissions') || $this->option('all')) {
            $file = database_path("data/permissions.json");
            if(\File::exists($file)) {
                $this->info("Generating Permissions...");
                $permissions = ["_crud"];
                $data = collect(\json_decode(\File::get($file)));
                $data->put($moduleName, $permissions);
                \File::put($file, $data->toJson(JSON_PRETTY_PRINT));

                if($this->option('seed') || $this->option('all')) {
                    $this->info("Seeding Permissions...");
                    // $this->call('db:seed', ['--class' => 'PermissionSeeder']);
                    exec('php artisan db:seed --class=PermissionSeeder');

                    if($this->option('grant') || $this->option('all')) {
                        $this->info("Granting Permissions...");
                        $permissionIds = \Samik\LaravelAdmin\Models\Permission::whereGroup($moduleName)->pluck('id');
                        \Samik\LaravelAdmin\Models\Role::whereUnrestricted(0)->get()->each(fn($role) => $role->permissions()->attach($permissionIds));
                    }
                }
            }
            else {
                $this->warn("Permission seeder data file not found at expected {$file}");
            }
        }

        if($this->option('menuitems') || $this->option('all')) {
            $file = database_path("data/menu-items.json");
            if(\File::exists($file)) {
                $this->info("Generating Menu Items...");
                $menuItems = [
                    [
                        'text' => Str::of(title($moduleName))->plural(),
                        'path' => Str::of($moduleName)->plural()->kebab(),
                        'permission' => "{$moduleName}.read"
                    ]
                ];
                $data = collect(\json_decode(\File::get($file)));
                $insertIndex = $data->search(fn($item) => $item->text == 'System');
                if($insertIndex) $data->splice($insertIndex, 0, $menuItems);
                else $data->push($menuItems);
                \File::put($file, $data->toJson(JSON_PRETTY_PRINT));

                if($this->option('seed') || $this->option('all')) {
                    $this->info("Seeding Menu Items...");
                    $this->call('db:seed', ['--class' => 'MenuItemSeeder']);
                }
            }
            else {
                $this->warn("Menu Item seeder data file not found at expected {$file}");
            }
        }
    }
}