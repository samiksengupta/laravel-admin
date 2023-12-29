<?php

namespace Samik\LaravelAdmin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Routing\Router;

use Samik\LaravelAdmin\Console\CreateUserCommand;
use Samik\LaravelAdmin\Console\CreatePermissionCommand;
use Samik\LaravelAdmin\Console\ModuleMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedModelMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedControllerMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedPolicyMakeCommand;
use Samik\LaravelAdmin\Console\LaravelAdminInstallCommand;
use Samik\LaravelAdmin\Console\FactoryResetCommand;
use Samik\LaravelAdmin\Console\SeedSpamCommand;

use Samik\LaravelAdmin\Http\Middlewares\AuthenticateAdmin;

class LaravelAdminServiceProvider extends ServiceProvider
{

    /**
     * Register the application services.
     */
    public function register()
    {       
        // Set auth.admin middleware alias
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('auth.admin', AuthenticateAdmin::class);

        // Register the main class to use with the facade
        $this->app->singleton('laravel-admin', function () {
            return new LaravelAdmin;
        });
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        $this->addConfigs();
        $this->registerRoutes();

        /*
         * Optional methods to load your package assets
         */
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-admin');
        
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-admin');
        
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-admin'),
            ], 'views');*/

            $this->publishes([
                __DIR__.'/../resources/views/partials/footer.blade.php' => resource_path('views/vendor/laravel-admin/partials/footer.blade.php'),
            ], 'footer');

            // Publishing assets.
            $this->publishes([
                __DIR__.'/../resources/assets/dist' => public_path('laravel-admin/dist'),
                __DIR__.'/../resources/assets/plugins' => public_path('laravel-admin/plugins'),
            ], 'primary-assets');

            $this->publishes([
                __DIR__.'/../resources/assets/scripts' => public_path('laravel-admin/scripts'),
                __DIR__.'/../resources/assets/styles' => public_path('laravel-admin/styles'),
            ], 'secondary-assets');

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-admin'),
            ], 'lang');*/

            // Publishing seeds.
            $this->publishes([
                __DIR__.'/../database/seeders/SettingSeeder.php' => database_path('seeders/SettingSeeder.php'),
                __DIR__.'/../database/seeders/PermissionSeeder.php' => database_path('seeders/PermissionSeeder.php'),
                __DIR__.'/../database/seeders/ApiResourceSeeder.php' => database_path('seeders/ApiResourceSeeder.php'),
                __DIR__.'/../database/seeders/MenuItemSeeder.php' => database_path('seeders/MenuItemSeeder.php'),
                __DIR__.'/../database/seeders/SpamSeeder.php' => database_path('seeders/SpamSeeder.php'),
                __DIR__.'/../database/seeders/UserSeeder.php' => database_path('seeders/UserSeeder.php'),
            ], 'seeders');

            $this->publishes([
                __DIR__.'/../database/seeders/DatabaseSeeder.php' => database_path('seeders/DatabaseSeeder.php'),
            ], 'database-seeder');

            // Publishing seed data.
            $this->publishes([
                __DIR__.'/../database/data' => database_path('data'),
            ], 'data');

            // Registering package commands.
            $this->commands([
                CreateUserCommand::class,
                CreatePermissionCommand::class,
                ModuleMakeCommand::class,
                ExtendedModelMakeCommand::class,
                ExtendedControllerMakeCommand::class,
                ExtendedPolicyMakeCommand::class,
                LaravelAdminInstallCommand::class,
                FactoryResetCommand::class,
                SeedSpamCommand::class
            ]);
        }
    }
    
    protected function addConfigs()
    {
        // publish admin configs
        if(!config('app.asset_url')) {
            config([
                'app.asset_url' => config('laravel-admin.app_asset_url'),
            ]);

            $this->publishes([
                __DIR__.'/../config/admin.php' => config_path('laravel-admin.php'),
                __DIR__.'/../config/constants.php' => config_path('constants.php'),
            ], 'admin-config');
        }

        // publish project configs
        if (!config('auth.guards.admin')) {
            config(['auth.guards.admin' => [
                'driver' => 'session',
                'provider' => 'users',
            ]]);

            $this->publishes([
                __DIR__.'/../config/auth.php' => config_path('auth.php'),
            ], 'config');
        }
    }
    
    protected function registerRoutes()
    {
        // prioritize application routes over package routes
        
        // load api routes
        Route::group(['prefix' => config('laravel-admin.api_prefix')], function () {
            $this->loadRoutesFrom(base_path('routes/api.php'));
            $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        });

        // load web routes
        $this->loadRoutesFrom(base_path('routes/web.php'));
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
    }
}
