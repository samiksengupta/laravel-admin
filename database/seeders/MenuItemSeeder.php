<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Samik\LaravelAdmin\Models\MenuItem;
use Samik\LaravelAdmin\Models\Permission;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncate();
        $this->createMenuItems($this->menuItems());
    }
    
    private function createMenuItems($structure, $parentId = null)
    {
        $order = 1;
        foreach ($structure as $item) {

            $item = (object) $item;
            $permission = isset($item->permission) ? Permission::where('action', $item->permission)->first() : null;
            
            // generate menu item
            $menuItem = new MenuItem();
            $menuItem->parent_id = $parentId;
            $menuItem->text = $item->text ?? null;
            $menuItem->path = $item->path ?? null;
            $menuItem->permission_id = $permission->id ?? null;
            $menuItem->order = $order++;
            $menuItem->icon_class = $item->icon_class ?? ($parentId ? 'far fa-circle' : 'fas fa-circle');
            $menuItem->target = $item->target ?? '_self';
            $saved = $menuItem->save();
            
            // generate submenu items
            $hasSub = (isset($item->sub) && is_array($item->sub) && count($item->sub));
            if($saved && $hasSub) {
                $this->createMenuItems($item->sub, $menuItem->id);
            }
        }
    }

    private function truncate() 
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MenuItem::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function menuItems()
    {
        $file = database_path("data/menu-items.json");
        return \File::exists($file) ? \json_decode(\File::get($file)) : [];
    }
}
