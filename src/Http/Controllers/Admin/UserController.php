<?php

namespace Samik\LaravelAdmin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Samik\LaravelAdmin\Models\User;
use Samik\LaravelAdmin\Http\Controllers\AdminModelController;

class UserController extends AdminModelController
{
    public function viewProfileForm(Request $request)
    {
        $this->authorize('editProfile', User::class);
        $data = $this->model::findOrFail(Auth::user()->id);
        $this->viewData['title'] = "Editing Profile";
        $this->viewData['form'] = User::getFormData(Auth::user()->id, null, api_admin_url("profile"), User::editableProfile());
        return \view()->exists("laravel-admin::contents/{$this->model::uriName()}/form") ? view("laravel-admin::contents/{$this->model::uriName()}/form", $this->viewData) : view('laravel-admin::contents/generic/form', $this->viewData);
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
}
