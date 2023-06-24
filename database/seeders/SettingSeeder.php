<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use Samik\LaravelAdmin\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createSettings($this->settings());
    }
    
    private function createSettings($structure)
    {
        $deletableSettingKeys = Setting::pluck('key');

        // insert or update all settings data given
        foreach ($structure as $key => $item) {
            $item = (object) $item;
            $setting = Setting::updateOrCreate([
                'key' => $item->key
            ],[
                'name' => $item->name ?? $item->key,
                'value' => $item->value,
                'default' => $item->default ?? $item->value,
                'type' => $item->type ?? 'text',
                'options' => $item->options ?? null,
            ]);
            if($setting && $deletableSettingKeys->contains($setting->key)) {
                $deletableSettingKeys = $deletableSettingKeys->reject(fn($item) => $setting->key === $item);
            }
        }

        // delete existing settings that are not given
        Setting::whereIn('key', $deletableSettingKeys)->delete();
    }
    
    private function settings()
    {
        $file = database_path("data/settings.json");
        return \File::exists($file) ? \json_decode(\File::get($file)) : [];
    }
}
