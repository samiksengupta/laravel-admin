<?php

namespace Samik\LaravelAdmin\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Samik\LaravelAdmin\Models\Setting;
use Samik\LaravelAdmin\Http\Controllers\AdminModelController;

class SettingController extends AdminModelController
{
    public function viewValueForm(Request $request, $id)
    {
        $this->authorize('setValue', Setting::class);
        $data = Setting::findOrFail($id);
        $this->viewData['apiSetValueUrl'] = api_admin_url("settings/{$id}/value");
        $this->viewData['title'] = "Setting Value for {$this->viewData['title']} : {$data->label()}";
        $this->viewData['data'] = $data;
        return view('laravel-admin::contents/system/settings-value', $this->viewData);
    }

    public function apiCreate(Request $request)
    {
        $this->authorize('create', Setting::class);
        $input = Setting::validate();
        $input['value'] = $input['default'];
        $data = Setting::create($input);
        return response()->json(['message' => "New {$this->viewData['title']} was Created successfully!", Setting::keyName() => $data->{Setting::keyName()}, 'navigate' => $this->viewData['listUrl']], 200);
    }

    public function apiUpdateValue(Request $request, $id)
    {
        $this->authorize('setValue', Setting::class);
        $data = Setting::findOrFail($id);
        $data->value = $request->get('value');
        $data->save();
        return response()->json(['message' => "{$this->viewData['title']} {$data->label()} value was Set successfully!", Setting::keyName() => $data->{Setting::keyName()}, 'navigate' => $this->viewData['listUrl']], 200);
    }
}
