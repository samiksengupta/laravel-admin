<?php

use Illuminate\Support\Facades\Route;

// admin web routes
Route::group(['middleware' => config('laravel-admin.web_admin_middlewares'), 'prefix' => config('laravel-admin.admin_prefix'), 'namespace' => config('laravel-admin.admin_controller_namespace')], function()
{
    // Open routes that are Throttled to 60 hits / min with a 1 min ban
    Route::group(['middleware' => ['throttle:60,1']], function()
    {
        // setup and update
        Route::get('reset', 'SystemController@doReset');
    
        // login
        Route::get('login', 'AccountController@viewLogin')->name('admin.login');
    
    });
    
    // Secure routes that are Protected and Throttled to 120 hits / min with a 1 min ban
    Route::group(['middleware' => ['auth.admin']], function()
    {
        // dashboard
        Route::get('/', 'SystemController@viewDashboard');
        Route::get('dashboard', 'SystemController@viewDashboard')->name('admin.dashboard');

        // command panel
        Route::get('commands', 'SystemController@viewCommands');

        // file serve routes
        Route::get('download/stored/{file}', 'SystemController@downloadStoredFile')->where('file', '.*');
        Route::get('download/public/{file}', 'SystemController@downloadPublicFile')->where('file', '.*');
    
        // profile
        Route::get('profile', 'UserController@viewProfileForm')->name('admin.profile');
        
        // menu manager
        Route::get('menu-items', 'SystemController@viewMenuItems');
    
        // settings
        // setting do not use numeric ids like a generic CRUD view
        // so /new must take priority over /{id}
        Route::get('settings', 'SettingController@viewList');
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

        // api resources
        Route::get('api-resources', 'ApiResourceController@viewList');
        Route::get('api-resources/new', 'ApiResourceController@viewForm');
        Route::get('api-resources/{id}', 'ApiResourceController@viewData');
        Route::get('api-resources/{id}/edit', 'ApiResourceController@viewForm');
        Route::get('api-resources/id}/delete', 'ApiResourceController@viewDelete');
        Route::get('api-resources/{apiResource}/test', 'ApiResourceController@viewTest');

        // auto-routing handler
        if(config('laravel-admin.auto_routing')) {
            // generic CRUD views
            // list
            Route::get('{resource}', function($resource){
                $controller = Str::singular(Str::studly($resource, 1)) . 'Controller';
                $method = 'viewList';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
        
            // create
            Route::get('{resource}/new', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewForm';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
        
            // read
            Route::get('{resource}/{id}', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewData';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'id' => config('constants.REGEX_NUMERIC')]);
        
            // update
            Route::get('{resource}/{id}/edit', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewForm';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'id' => config('constants.REGEX_NUMERIC')]);
        
            // delete
            Route::get('{resource}/{id}/delete', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewDelete';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'id' => config('constants.REGEX_NUMERIC')]);
        
            // delete all
            Route::get('{resource}/delete', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewDeleteAll';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
        
            // import
            Route::get('{resource}/import', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewImport';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
        
            // export
            Route::get('{resource}/export', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'viewExport';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
        
            
            // others
            Route::get('{resource}/{action}', function($resource, $action){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'view' . Str::studly($action);
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'action' => config('constants.REGEX_KEBAB_CASE')]);
        }
    
    });

});