<?php

namespace Samik\LaravelAdmin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

use Samik\LaravelAdmin\Console\ModuleMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedModelMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedControllerMakeCommand;
use Samik\LaravelAdmin\Console\ExtendedPolicyMakeCommand;
use Samik\LaravelAdmin\Console\LaravelAdminInstallCommand;

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
                __DIR__.'/../resources/assets' => public_path('laravel-admin'),
            ], 'assets');

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-admin'),
            ], 'lang');*/

            // Publishing seeds.
            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('seeders'),
            ], 'seeders');

            // Publishing seed data.
            $this->publishes([
                __DIR__.'/../database/data' => database_path('data'),
            ], 'data');

            // Registering package commands.
            $this->commands([
                ModuleMakeCommand::class,
                ExtendedModelMakeCommand::class,
                ExtendedControllerMakeCommand::class,
                ExtendedPolicyMakeCommand::class,
                LaravelAdminInstallCommand::class
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
