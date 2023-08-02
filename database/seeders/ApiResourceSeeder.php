<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Samik\LaravelAdmin\Models\ApiResource;

class ApiResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createApiResources($this->apiResources());
    }

    private function createApiResources($structure)
    {
        foreach ($structure as $apiResource) {
            $apiResource = (object) $apiResource;
            ApiResource::updateOrCreate([
                'method' => $apiResource->method,
                'route' => $apiResource->route
            ],[
                'name' => $apiResource->name,
                'fields' => $apiResource->fields,
                'secure' => $apiResource->secure,
                'hidden' => 0,
                'disabled' => 0
            ]);
        }
    }

    private function apiResources()
    {
        $file = database_path("data/api-resources.json");
        return \File::exists($file) ? \json_decode(\File::get($file)) : [];
    }
}
