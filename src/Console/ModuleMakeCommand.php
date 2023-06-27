<?php

namespace Samik\LaravelAdmin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ModuleMakeCommand extends Command
{
    protected $signature = 'make:module {name} {--p|permissions} {--m|menuitems} {--s|seed}';

    protected $description = 'Create a new extended model class with migration, controller and policy';

    public function handle()
    {
        $moduleName = $this->argument('name');
        $this->call('make:xmodel', ['name' => $moduleName, '--migration' => true]);
        $this->call('make:xpolicy', ['name' => "{$moduleName}Policy"]);
        $this->call('make:xcontroller', ['name' => "Admin\\{$moduleName}Controller"]);

        if($this->option('permissions')) {
            $file = database_path("data/permissions.json");
            if(\File::exists($file)) {
                $this->info("Generating Permissions...");
                $permissions = ["_crud"];
                $data = collect(\json_decode(\File::get($file)));
                $data->put($moduleName, $permissions);
                \File::put($file, $data->toJson(JSON_PRETTY_PRINT));

                if($this->option('seed')) {
                    $this->info("Seeding Permissions...");
                    $this->call('db:seed', ['--class' => 'PermissionSeeder']);
                }
            }
            else {
                $this->warn("Permission seeder data file not found at expected {$file}");
            }
        }

        if($this->option('menuitems')) {
            $file = database_path("data/menu-items.json");
            if(\File::exists($file)) {
                $this->info("Generating Menu Items...");
                $menuItems = [
                    [
                        'text' => presentable(Str::of($moduleName)->plural()),
                        'path' => Str::of($moduleName)->plural()->kebab(),
                        'permission' => "{$moduleName}.read"
                    ]
                ];
                $data = collect(\json_decode(\File::get($file)));
                $insertIndex = $data->search(fn($item) => $item->text == 'System');
                if($insertIndex) $data->splice($insertIndex, 0, $menuItems);
                else $data->push($menuItems);
                \File::put($file, $data->toJson(JSON_PRETTY_PRINT));

                if($this->option('seed')) {
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