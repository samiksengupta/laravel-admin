<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => config('laravel-admin.api_prefix')], function() {

    // general api routes
    Route::group(['middleware' => config('laravel-admin.api_middlewares'), 'namespace' => config('laravel-admin.api_controller_namespace')], function()
    {    
        // open api routes
        Route::post('login', 'AuthController@login');
        Route::post('refresh', 'AuthController@refresh');
        
        // secure api routes
        Route::group(['middleware' => ['auth:api']], function()
        {
            Route::post('logout', 'AuthController@logout');
            Route::get('user-details', 'AuthController@details');
        });
    });
    
    // admin api routes
    Route::group(['prefix' => 'admin', 'middleware' => config('laravel-admin.api_admin_middlewares'), 'namespace' => config('laravel-admin.admin_controller_namespace')], function()
    {
        // login
        Route::post('login', 'AccountController@apiLogin');
    
        // command
        Route::post('command', 'SystemController@apiCommand');
    
        // logout
        Route::post('logout', 'AccountController@apiLogout')->name('admin.logout');
    
        // profile
        Route::put('profile', 'UserController@apiUpdateProfile');
    
        // menu manager
        Route::post('menu-items', 'SystemController@apiCreateMenuItem');
        Route::put('menu-items/{id}', 'SystemController@apiUpdateMenuItem');
        Route::put('menu-items', 'SystemController@apiReorderMenuItems');
        Route::delete('menu-items/{id}', 'SystemController@apiDeleteMenuItem');
    
        // roles
        Route::get('roles', 'RoleController@apiList');
        Route::get('roles/dt', function() {
            $controller = 'RoleController';
            $method = 'apiList';
            $callable = get_admin_action_callable($controller, $method);
            if($callable) return App::call($callable, ['datatables' => true]);
            else throw new Exception("Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
        });
        Route::post('roles', 'RoleController@apiCreate');
        Route::get('roles/{id}', 'RoleController@apiRead');
        Route::put('roles/{id}', 'RoleController@apiUpdate');
        Route::delete('roles/{id}', 'RoleController@apiDelete');
        Route::delete('roles', 'RoleController@apiDeleteAll');
        Route::post('roles/import', 'RoleController@apiImport');
        Route::get('roles/verify', 'RoleController@apiVerify');
    
        Route::put('role/{id}/permissions', 'RoleController@apiUpdatePermissions');
    
        // users
        Route::get('users', 'UserController@apiList');
        Route::get('users/dt', function() {
            $controller = 'UserController';
            $method = 'apiList';
            $callable = get_admin_action_callable($controller, $method);
            if($callable) return App::call($callable, ['datatables' => true]);
            else throw new Exception("Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
        });
        Route::post('users', 'UserController@apiCreate');
        Route::get('users/{id}', 'UserController@apiRead');
        Route::put('users/{id}', 'UserController@apiUpdate');
        Route::delete('users/{id}', 'UserController@apiDelete');
        Route::delete('users', 'UserController@apiDeleteAll');
        Route::post('users/import', 'UserController@apiImport');
        Route::get('users/verify', 'UserController@apiVerify');
    
        if(config('laravel-admin.auto_routing')) {
            // generic CRUD api
            // list
            Route::get('{resource}', function($resource){
                $controller = Str::singular(Str::studly($resource, 1)) . 'Controller';
                $method = 'apiList';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
    
            // list datatables
            Route::get('{resource}/dt', function($resource){
                $controller = Str::singular(Str::studly($resource, 1)) . 'Controller';
                $method = 'apiList';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['datatables' => true]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
    
            // create
            Route::post('{resource}', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiCreate';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
    
            // read
            Route::get('{resource}/{id}', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiRead';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'id' => config('constants.REGEX_NUMERIC')]);
    
            // update
            Route::put('{resource}/{id}', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiUpdate';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'id' => config('constants.REGEX_NUMERIC')]);
    
            // delete
            Route::delete('{resource}/{id}', function($resource, $id){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiDelete';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable, ['id' => $id]);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'id' => config('constants.REGEX_NUMERIC')]);
    
            // delete all
            Route::delete('{resource}', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiDeleteAll';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
    
            // import
            Route::post('{resource}/import', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiImport';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
    
            // verify
            Route::get('{resource}/verify', function($resource){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiVerify';
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE')]);
    
            // bulk actions
            Route::post('{resource}/bulk/{action}', function($resource, $action){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'apiBulk' . Str::studly($action);
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'action' => config('constants.REGEX_KEBAB_CASE')]);
    
            // others
            Route::get('{resource}/{action}', function($resource, $action){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'api' . Str::studly($action);
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'action' => config('constants.REGEX_KEBAB_CASE')]);
            
            Route::post('{resource}/{action}', function($resource, $action){
                $controller = Str::singular(Str::studly($resource)) . 'Controller';
                $method = 'api' . Str::studly($action);
                $callable = get_admin_action_callable($controller, $method);
                if($callable) return App::call($callable);
                else throw new Exception("Auto Routing failed: Could not find a valid path to action target '{$controller}@{$method}'");
            })->where(['resource' => config('constants.REGEX_KEBAB_CASE'), 'action' => config('constants.REGEX_KEBAB_CASE')]);
            
        }
    
        // settings
        // because setting do not use numeric ids like a generic CRUD api
        Route::get('settings/{id}', 'SettingController@apiRead');
        Route::put('settings/{id}', 'SettingController@apiUpdate');
        Route::put('settings/{id}/value', 'SettingController@apiUpdateValue');
        Route::delete('settings/{id}', 'SettingController@apiDelete');
    });
});



