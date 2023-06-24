<?php

namespace Samik\LaravelAdmin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Samik\LaravelAdmin\Models\User;
use Samik\LaravelAdmin\Http\Controllers\AdminModelController;

class UserController extends AdminModelController
{

    public function viewCustomImport(Request $request)
    {
        $this->authorize('import', $this->model);
        $this->viewData['title'] = "Importing {$this->viewData['title']}";
        $this->viewData['form'] = $this->model::getFormData(null, \api_admin_url('users/custom-import'));
        return \view()->exists("contents/{$this->model::uriName()}/import") ? view("contents/{$this->model::uriName()}/import", $this->viewData) : view('laravel-admin::contents/generic/import', $this->viewData);
    }

    public function viewProfileForm(Request $request)
    {
        $this->authorize('editProfile', User::class);
        $data = $this->model::findOrFail(Auth::user()->id);
        $this->viewData['title'] = "Editing Profile";
        $this->viewData['form'] = User::getFormData(Auth::user()->id, null, api_admin_url("profile"), User::editableProfile());
        return \view()->exists("contents/{$this->model::uriName()}/form") ? view("contents/{$this->model::uriName()}/form", $this->viewData) : view('laravel-admin::contents/generic/form', $this->viewData);
    }

    public function viewAssignArea(Request $request, User $user)
    {
        $this->authorize('assignArea', User::class);
        $this->viewData['title'] = "Assigning Area";
        $this->viewData['apiAssignAreaUrl'] = \api_admin_url("users/{$user->id}/assign-area");
        // $this->viewData['countries'] = \App\Models\Country::all()->pluck('name', 'id')->prepend('Select...', '');
        $this->viewData['districts'] = \App\Models\District::all()->pluck('name', 'id')->prepend('(Any)', '');
        $this->viewData['data'] = $user;
        return \view("contents/{$this->model::uriName()}/assign-area", $this->viewData);
    }

    public function apiCustomImport(Request $request)
    {
        $this->authorize('import', $this->model);
        $importData = User::importFromCSV($request->district_id, $request->municipality_id, $request->file('csv'));
        if(!empty($importData['errors'])) return \response()->json(['message' => $importData['message'], 'errors' => $importData['errors']], 400);
        return \response()->json(['message' => $importData['message']]);
    }

    public function apiUpdateProfile(Request $request)
    {
        $this->authorize('editProfile', User::class);
        $input = User::validate(Auth::user()->id, User::editableProfile());
        $data = User::findOrFail(Auth::user()->id);
        $data->fill($input);
        $data->save();
        return response()->json(['message' => "Profile was Updated successfully!"], 200);
    }

    public function apiAssignArea (Request $request, User $user)
    {
        $this->authorize('assignArea', User::class);
        $user->assignArea($request->input());
        return response()->json(['message' => "Area was assigned successfully!", User::keyName() => $user->{User::keyName()}, 'navigate' => $this->viewData['listUrl']], 200);
    }

    public function apiSupervisors(Request $request)
    {
        $this->authorize('read', User::class);
        $query = User::query();
        switch($request->type) {
            case 'HTH': $query->roleHthSupervisor(); break;
            case 'VCT': $query->roleVctSupervisor(); break;
        }
        $query->whereHas('wards', function($q) use($request) {
            $q->whereIn('ward_id', [$request->ward_id]);
        });
        return \response()->json($query->get());
    }

    public function apiTeamMembers(Request $request)
    {
        $this->authorize('read', User::class);
        $query = User::query();
        $query->whereHas('team', function($q) use($request) {
            $q->where('id', $request->team_id);
        });
        return \response()->json($query->get());
    }
    
}
