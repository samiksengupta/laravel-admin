<?php

namespace Samik\LaravelAdmin\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Samik\LaravelAdmin\Models\Role;
use Samik\LaravelAdmin\Models\Permission;
use Samik\LaravelAdmin\Http\Controllers\AdminModelController;

class RoleController extends AdminModelController
{
    public function viewPermissions(Request $request, $id)
    {
        $this->authorize('setPermissions', Role::class);
        $data = Role::findOrFail($id);
        $this->viewData['apiSetPermissionUrl'] = api_admin_url("role/{$id}/permissions");
        $this->viewData['title'] = "Setting permissions for {$this->viewData['title']} : {$data->label()}";
        $this->viewData['data'] = $data;
        // $this->viewData['permissions'] = Permission::query()->pluck('name', 'id');
        $this->viewData['permissions'] = Auth::user()->role->unrestricted ? Permission::all()->pluck('name', 'id') : Auth::user()->role->permissions()->get()->pluck('name', 'id');
        
        return view('laravel-admin::contents/role/permissions', $this->viewData);
    }

    public function viewPermissionSwitches(Request $request, $id)
    {
        $this->authorize('setPermissions', Role::class);
        $data = Role::findOrFail($id);
        $this->viewData['apiSetPermissionUrl'] = api_admin_url("role/{$id}/permissions");
        $this->viewData['title'] = "Setting permissions for {$this->viewData['title']} : {$data->label()}";
        $this->viewData['data'] = $data;
        $this->viewData['permissionChunks'] = Auth::user()->role->unrestricted ? Permission::all()->groupBy('group') : Auth::user()->role->permissions()->get()->groupBy('group');
        
        return view('laravel-admin::contents/role/permissions-switch', $this->viewData);
    }

    public function apiUpdatePermissions(Request $request, $id)
    {
        $this->authorize('setPermissions', Role::class);
        $data = Role::findOrFail($id);
        $data->permissions()->sync($request->get('permissions'));
        return response()->json(['message' => "{$this->viewData['title']} {$data->label()} permission was Set successfully!", Role::keyName() => $data->{Role::keyName()}, 'navigate' => $this->viewData['listUrl']], 200);
    }
}
