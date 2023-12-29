<?php

namespace Samik\LaravelAdmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;

use Samik\LaravelAdmin\Http\Controllers\AdminBaseController;

class AdminModelController extends AdminBaseController
{
    protected $modelName = null;
    protected $model = null;

    function __construct()
    {
        parent::__construct();

        if(!$this->modelName) $this->modelName = str_replace('Controller', '', \class_basename($this));
        if(!($this->model = get_model($this->modelName))) throw new \Exception("Model not found: Could not find a valid path to class '{$this->modelName}'");
        $this->viewData['model'] = $this->model;
        $this->viewData['modelName'] = $this->model::displayName();
        $this->viewData['title'] =  $this->model::displayName();
        $this->viewData['showEditButton'] = true;
        $this->viewData['showCancelButton'] = true;
        $this->viewData['showPageAction'] = true;
        $this->viewData['listUrl'] = admin_url($this->model::resourceName());
        $this->viewData['dataUrl'] = admin_url($this->model::resourceName() . "/$1");
        $this->viewData['createUrl'] = admin_url($this->model::resourceName() . '/new');
        $this->viewData['editUrl'] = admin_url($this->model::resourceName() . '/$1/edit');
        $this->viewData['deleteUrl'] = admin_url($this->model::resourceName() . '/$1/delete');
        $this->viewData['apiListUrl'] = api_admin_url($this->model::resourceName() . '/dt');
        $this->viewData['apiCreateUrl'] = api_admin_url($this->model::resourceName());
        $this->viewData['apiUpdateUrl'] = api_admin_url($this->model::resourceName() . '/$1');
        $this->viewData['apiDeleteUrl'] = api_admin_url($this->model::resourceName() . '/$1');
        $this->viewData['apiImportUrl'] = api_admin_url($this->model::resourceName() . '/import/');
        $this->viewData['apiVerifyUrl'] = api_admin_url($this->model::resourceName() . '/verify/');
        $this->viewData['form'] = null;
        $this->viewData['keys'] = \array_keys($this->model::make()->getAttributes());
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewList(Request $request)
    {
        $this->authorize('read', $this->model);
        $this->viewData['title'] = "{$this->viewData['title']} List";
        $this->viewData['datatableOptions'] = $this->model::getDataTableOptions($this->viewData['apiListUrl']);
        $this->viewData['filterElements'] = $this->model::filterElements();
        return \view()->exists("laravel-admin::contents/{$this->model::uriName()}/list") ? view("laravel-admin::contents/{$this->model::uriName()}/list", $this->viewData) : view('laravel-admin::contents/generic/list', $this->viewData);
    }

    /**
     * Show the form for creating a new resource or editing a resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function viewForm(Request $request, $id = null)
    {
        if($id) {
            $this->authorize('update', $this->model);
            $data = $this->model::findOrFail($id);
            $this->viewData['title'] = "Editing {$this->viewData['title']} : {$data->label()}";
            $this->viewData['data'] = $data;
        }
        else {
            $this->authorize('create', $this->model);
            $this->viewData['title'] = "Creating {$this->viewData['title']}";
            $this->viewData['data'] = null;
        }

        $this->viewData['form'] = $this->model::getFormData($id, $this->viewData['apiCreateUrl'], $this->viewData['apiUpdateUrl']);
        return \view()->exists("laravel-admin::contents/{$this->model::uriName()}/form") ? view("laravel-admin::contents/{$this->model::uriName()}/form", $this->viewData) : view('laravel-admin::contents/generic/form', $this->viewData);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function viewData(Request $request, $id)
    {
        $this->authorize('read', $this->model);
        $data = $this->model::getViewData($id);
        $this->viewData['title'] = "Viewing {$this->viewData['title']} : {$data->label()}";
        $this->viewData['editUrl'] = str_replace('$1', $id, $this->viewData['editUrl']);
        $this->viewData['data'] = $data;
        return \view()->exists("laravel-admin::contents/{$this->model::uriName()}/data") ? view("laravel-admin::contents/{$this->model::uriName()}/data", $this->viewData) : view('laravel-admin::contents/generic/data', $this->viewData);
    }

    /**
     * Display the confirmation for removing a specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewDelete(Request $request, $id)
    {
        $this->authorize('delete', $this->model);
        $data = $this->model::findOrFail($id);
        $this->viewData['title'] = "Are you sure you want to delete {$this->viewData['title']} : {$data->label()}";
        $this->viewData['apiDeleteUrl'] = str_replace('$1', $id, $this->viewData['apiDeleteUrl']);
        $this->viewData['data'] = $data;
        return \view()->exists("laravel-admin::contents/{$this->model::uriName()}/delete") ? view("laravel-admin::contents/{$this->model::uriName()}/delete", $this->viewData) : view('laravel-admin::contents/generic/delete', $this->viewData);
    }

    /**
     * Display the confirmation for removing all resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewDeleteAll(Request $request)
    {
        $this->authorize('delete', $this->model);
        dump(__CLASS__ . '@' . __FUNCTION__);
    }

    /**
     * Show the form for Importing a resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewImport(Request $request)
    {
        $this->authorize('import', $this->model);
        $this->viewData['title'] = "Importing {$this->viewData['title']}";
        $this->viewData['form'] = $this->model::getFormData(null, $this->viewData['apiImportUrl']);
        return \view()->exists("laravel-admin::contents/{$this->model::uriName()}/import") ? view("laravel-admin::contents/{$this->model::uriName()}/import", $this->viewData) : view('laravel-admin::contents/generic/import', $this->viewData);
    }

    /**
     * Show the form for Exporting a resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function viewExport(Request $request)
    {
        $this->authorize('export', $this->model);
        dump(__CLASS__ . '@' . __FUNCTION__);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function apiList(Request $request, $datatables = false)
    {
        $this->authorize('read', $this->model);
        return $datatables ? $this->model::getDataTableResponse() : $this->model::filter()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function apiCreate(Request $request)
    {
        $this->authorize('create', $this->model);
        $input = $this->model::validate();
        $data = $this->model::create($input);
        return response()->json(['message' => "New {$this->viewData['title']} was Created successfully!", $this->model::keyName() => $data->{$this->model::keyName()}, 'navigate' => $this->viewData['listUrl']], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function apiRead(Request $request, $id)
    {
        $this->authorize('read', $this->model);
        return response()->json($this->model::findOrFail($id), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->authorize('update', $this->model);
        $input = $this->model::validate($id);
        $data = $this->model::findOrFail($id);
        $data->fill($input);
        $data->save();
        return response()->json(['message' => "{$this->viewData['title']} {$data->label()} was Updated successfully!", $this->model::keyName() => $data->{$this->model::keyName()}, 'navigate' => $this->viewData['listUrl']], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function apiDelete(Request $request, $id)
    {
        $this->authorize('delete', $this->model);
        $data = $this->model::findOrFail($id);
        $this->model::destroy($id);
        return response()->json(['message' => "{$this->viewData['title']} {$data->label()} was Deleted successfully!", 'navigate' => $this->viewData['listUrl']], 200);
    }

    /**
     * Remove all resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function apiDeleteAll(Request $request)
    {
        $this->authorize('delete', $this->model);
        $this->model::all()->each(fn($model) => $model->delete());
        $this->model::truncate();
        return response()->json(['message' => "All {$this->viewData['modelName']} data were Deleted successfully!", 'navigate' => $this->viewData['listUrl']], 200);
    }

    /**
     * Remove a file of the specified resource from storage.
     *
     * @param  int  $id
     * @param  string  $field
     * @param  int  $index
     * @return \Illuminate\Http\Response
     */
    public function apiDeleteFile(Request $request, $id, $field, $file)
    {
        $this->authorize('update', $this->model);
        $data = $this->model::findOrFail($id);
        $data->removeFileByName($field, $file);
        return response()->json(['message' => "File {$file} was Deleted successfully!", 'navigate' => str_replace('$1', $id, $this->viewData['editUrl'])], 200);
    }

    /**
     * Verify importable record
     *
     * @return \Illuminate\Http\Response
     */
    public function apiVerify(Request $request)
    {
        $import = $this->model::verifyImport($request->input('records'));
        return response()->json(['message' => "Record Verification", 'records' => $import['records'], 'verified' => $import['verified']], 200);
    }

    /**
     * Import records
     *
     * @return \Illuminate\Http\Response
     */
    public function apiImport(Request $request)
    {
        $this->authorize('import', $this->model);
        $input = $this->model::validate();
        $data = $this->model::create($input);
        return response()->json(['message' => "New {$this->viewData['title']} was Created successfully!", $this->model::keyName() => $data->{$this->model::keyName()}, 'navigate' => $this->viewData['listUrl']], 200);
    }
}
