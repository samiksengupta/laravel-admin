<?php

namespace Samik\LaravelAdmin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

use Samik\LaravelAdmin\Console\CreateUserCommand;
use Samik\LaravelAdmin\Console\ModuleMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedModelMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedControllerMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedPolicyMakeCommand;
use Samik\LaravelAdmin\Console\LaravelAdminInstallCommand;
use Samik\LaravelAdmin\Console\FactoryResetCommand;
use Samik\LaravelAdmin\Console\SeedSpamCommand;

use Samik\LaravelAdmin\Http\Middlewares\Authenticate;

class LaravelAdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        config(['app.asset_url' => config('laravel-admin.app_asset_url')]);
        /*
         * Optional methods to load your package assets
         */
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-admin');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-admin');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        // $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-admin.php'),
                __DIR__.'/../config/constants.php' => config_path('constants.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-admin'),
            ], 'views');*/

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

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-admin');
        $this->mergeConfigFrom(__DIR__.'/../config/constants.php', 'constants');
        
        // Set admin.auth middleware alias
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('admin.auth', Authenticate::class);

        // Register the main class to use with the facade
        $this->app->singleton('laravel-admin', function () {
            return new LaravelAdmin;
        });
    }
    
    /* protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    } */
}
