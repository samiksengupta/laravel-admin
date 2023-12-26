<?php

use Illuminate\Support\Facades\Route;

// standard web routes
Route::get('/', function () {
    return view('welcome');
});

// admin web routes
Route::group(['prefix' => config('laravel-admin.admin_prefix'), 'middleware' => config('laravel-admin.web_middlewares')], function()
{
    Route::group(['before' => 'admin.auth', 'namespace' => config('laravel-admin.admin_controller_namespace')], function()
    {
        // command panel
        Route::get('commands', 'SystemController@viewCommands');
    });
    
    
    // Open API routes that are Throttled to 60 hits / min with a 1 min ban
    Route::group(['middleware' => ['throttle:60,1'], 'namespace' => config('laravel-admin.admin_controller_namespace')], function()
    {
        // setup and update
        Route::get('reset', 'SystemController@doReset');
    
        // login
        Route::get('login', 'AccountController@viewLogin')->name('admin.login');
        // Route::post('login', 'AccountController@doLogin');
    
        // logout
        // Route::post('logout', 'AccountController@doLogout');
    
        // recover
        // Route::get('recover', 'AccountController@viewRecover');
    
    });
    
    // Closed API routes that are Protected and Throttled to 120 hits / min with a 1 min ban
    Route::group(['middleware' => ['admin.auth'], 'namespace' => config('laravel-admin.admin_controller_namespace')], function()
    {
        // dashboard
        Route::get('/', 'SystemController@viewDashboard');
        
        Route::get('dashboard', 'SystemController@viewDashboard')->name('admin.dashboard');

        Route::get('download/stored/{file}', 'SystemController@downloadStoredFile')->where('file', '.*');
        Route::get('download/public/{file}', 'SystemController@downloadPublicFile')->where('file', '.*');
    
        // profile
        Route::get('profile', 'UserController@viewProfileForm')->name('admin.profile');
        
        // menu manager
        Route::get('menu-items', 'SystemController@viewMenuItems');
    
        // settings
        // setting do not use numeric ids like a generic CRUD view
        // so /new must take priority over /{id}
        Route::get('settings/new', 'SettingController@viewForm');
        Route::get('settings/{id}', 'SettingController@viewData');
        Route::get('settings/{id}/edit', 'SettingController@viewForm');
        Route::get('settings/{id}/value', 'SettingController@viewValueForm');
        Route::get('settings/{id}/delete', 'SettingController@viewDelete');
    
        // roles
        Route::get('roles', 'RoleController@viewList');
        Route::get('roles/new', 'RoleController@viewForm');
        Route::get('roles/{id}', 'RoleController@viewData');
        Route::get('roles/{id}/edit', 'RoleController@viewForm');
        Route::get('roles/{id}/delete', 'RoleController@viewDelete');

        Route::get('roles/{id}/permissions', 'RoleController@viewPermissions');
        Route::get('roles/{id}/permission/switches', 'RoleController@viewPermissionSwitches');
    
        // users
        Route::get('users', 'UserController@viewList');
        Route::get('users/new', 'UserController@viewForm');
        Route::get('users/{id}', 'UserController@viewData');
        Route::get('users/{id}/edit', 'UserController@viewForm');
        Route::get('users/{id}/delete', 'UserController@viewDelete');

        Route::get('api-resources/{apiResource}/test', 'ApiResourceController@viewTest');

        if(config('laravel-admin.auto_routing')) {
            // generic CRUD views
            // list
            Route::get('{resource}', function($resource){
                $controller = Str::singular(Str::studly($resource, 1)) . 'Controller';
                $method = 'viewList';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE')]);
        
            // create
            Route::get('{resource}/new', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewForm';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE')]);
        
            // read
            Route::get('{resource}/{id}', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewData';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE'), 'id' => Config::get('constants.REGEX_NUMERIC')]);
        
            // update
            Route::get('{resource}/{id}/edit', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewForm';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE'), 'id' => Config::get('constants.REGEX_NUMERIC')]);
        
            // delete
            Route::get('{resource}/{id}/delete', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewDelete';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE'), 'id' => Config::get('constants.REGEX_NUMERIC')]);
        
            // delete all
            Route::get('{resource}/delete', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewDeleteAll';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE')]);
        
            // import
            Route::get('{resource}/import', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewImport';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE')]);
        
            // export
            Route::get('{resource}/export', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewExport';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE')]);
        
            
            // others
            Route::get('{resource}/{action}', function($resource, $action){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'view' . Str::studly($action);
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => Config::get('constants.REGEX_KEBAB_CASE'), 'action' => Config::get('constants.REGEX_KEBAB_CASE')]);
        }
    
    });

});