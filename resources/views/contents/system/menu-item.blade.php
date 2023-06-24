@extends('laravel-admin::layouts.app')
@section('title', $title)
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card rounded-0 border-primary mb-3">
            <div class="card-body rounded-0">
                @can('create', app(config('laravel-admin.model_namespace') . '\\MenuItem')::class)
                <div id="add-item" class="add-new d-flex justify-content-center"><i class="fas fa-plus-circle text-success"></i></div>
                @endcan
                <div id="list" class="nested-list dd with-margins custom-drag-button">
                    <ul id="main-list" class="dd-list"></ul>
                </div>
            </div>
            <div class="card-footer rounded-0">
                <input type="hidden" id="menu-items" name="menu_items" value="{{ $menuItems }}" data-reorder-url="{{ $reorderUrl }}" data-resource-url="{{ $resourceUrl }}" @can('update', app(config('laravel-admin.model_namespace') . '\\MenuItem')::class) data-can-update="1" @endcan @can('delete', app(config('laravel-admin.model_namespace') . '\\MenuItem')::class) data-can-delete="1" @endcan />
            </div>
        </div>
    </div>
    <div class="modal fade in" id="menu-item-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Menu Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form">
                        <input type="hidden" name="id" />
                        <input type="hidden" name="parent_id" />
                        <input type="hidden" name="order" />
                        <div class="form-group">
                            <label for="text" class="col-form-label">Text</label>
                            <input name="text" type="text" class="form-control rounded-0" placeholder="Display Text" id="text">
                        </div>
                        <div class="form-group">
                            <label for="path" class="col-form-label">URL/Path</label>
                            <input name="path" type="text" class="form-control rounded-0" placeholder="" id="path">
                        </div>
                        <div class="form-group">
                            <label for="icon-class" class="col-form-label">Icon Class <a href="https://fontawesome.com/v5/cheatsheet" target="_blank">(FontAwesome Cheatsheet)</a></label>
                            <input name="icon_class" type="text" class="form-control rounded-0" placeholder="fas fa-font" id="icon-class">
                        </div>
                        <div class="form-group">
                            <label for="target" class="col-form-label">Target</label>
                            <select name="target" class="form-control rounded-0" id="target">
                                <option value="_self">_self</option>
                                <option value="_blank">_blank</option>
                                <option value="_parent">_parent</option>
                                <option value="_top">_top</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="permission" class="col-form-label">Permission</label>
                            <select name="permission_id" class="form-control rounded-0" id="permission">
                                <option value="">None</option>
                                @foreach($permissionOptions as $optionKey => $optionValue)
                                <option value="{{ $optionKey }}">{{ $optionValue }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="display" class="col-form-label">Display</label>
                            <select name="display" class="form-control rounded-0" id="display">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success rounded-0" id="saveButton">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-styles')
<!-- Font Awesome -->
<link rel="stylesheet" href="{{ admin_asset_url('styles/jquery.nestable.css') }}">
@endsection
@section('page-scripts')
<!-- jQuery nestable -->
<script src="{{ admin_asset_url('scripts/jquery.nestable.js') }}"></script>
<script src="{{ admin_asset_url('scripts/jquery.nestable.menu.js') }}"></script>
<script type="text/javascript">
    var initialData = $('#menu-items').val();
    var reorderUrl = $('#menu-items').data('reorder-url');
    var resourceUrl = $('#menu-items').data('resource-url');
    var canUpdate = $('#menu-items').data('can-update') ? true : false;
    var canDelete = $('#menu-items').data('can-delete') ? true : false;

    editor.setData(initialData);
    editor.setContainer('#list');
    editor.setNewItemButton('#add-item');
    editor.setFormModal('#menu-item-modal', '#form', '#saveButton');
    editor.onCreate(function name(item) {
        axios.post(`${resourceUrl}`, { data: item }).then(function (response) {
            toastSuccess(response.data.message);
            editor.setData(response.data.menu);
            editor.redraw();
        }).catch(function (error) {
            c(error);
            toastResponse(error.response);
        });
    });
    editor.onUpdate(function name(item) {
        axios.put(`${resourceUrl}/${item.id}`, { data: item }).then(function (response) {
            toastSuccess(response.data.message);
            editor.setData(response.data.menu);
            editor.redraw();
        }).catch(function (error) {
            c(error);
            toastResponse(error.response);
        });
    });
    editor.onDelete(function name(item) {
        axios.delete(`${resourceUrl}/${item.id}`).then(function (response) {
            toastSuccess(response.data.message);
            editor.setData(response.data.menu);
            editor.redraw();
        }).catch(function (error) {
            c(error);
            toastResponse(error.response);
        });
    });
    editor.onReorder(function name(items) {
        axios.put(reorderUrl, { data: items }).then(function (response) {
            toastSuccess(response.data.message);
            editor.setData(response.data.menu);
            editor.redraw();
        }).catch(function (error) {
            c(error);
            toastResponse(error.response);
        });
    });
    editor.canUpdate = canUpdate;
    editor.canDelete = canDelete;
    editor.init();
</script>
@endsection
