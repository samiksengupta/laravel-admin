<?php

/*
 * You can customize Laravel Admin Panel configuration here
 */
return [
    'auto_routing' => env('AUTO_ROUTING', true),
    'admin_prefix' => env('ADMIN_PREFIX'),
    'api_prefix' => env('API_PREFIX', 'api'),
    'app_asset_url' => env('ASSET_URL', config('app.url')),
    'web_middlewares' => ['web', 'throttle:120,1'],
    'api_middlewares' => ['api', 'throttle:60,1'],
    'web_admin_middlewares' => [
        'web', 
        'throttle:120,1'
    ],
    'api_admin_middlewares' => [
        'throttle:api', 
        'throttle:240,1', 
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class
    ],
    'api_controller_namespace' => 'Samik\\LaravelAdmin\\Http\\Controllers\\Api',
    'admin_controller_namespace' => 'Samik\\LaravelAdmin\\Http\\Controllers\\Admin',
    'model_namespace' => 'Samik\\LaravelAdmin\\Models',
];