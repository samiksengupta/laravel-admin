@extends($modal ? 'laravel-admin::layouts.none' : 'laravel-admin::layouts.app')
@section('title', $title)
@section('content')
<div class="row">
    <!-- left column -->
    <div class="col-md-12">
        {{ html()->form('PUT', $apiSetPermissionUrl)->class('ajax-form')->open() }}
        <div class="card card-primary card-outline">
            <div class="card-header rounded-0">
                <h3 class="card-title">{{ $title }}</h3>
            </div>
            <!-- /.card-header -->
            <div class="card-body rounded-0">
                @if($data->unrestricted)
                <div class="alert alert-info">
                    <strong>Info!</strong> {{ $data->name }} is an <strong>unrestricted</strong> group and does not need any permissions.
                </div>
                @endif
                {{-- {{ Form::label('permissions', 'Enable Permissions you want to grant this Role', ['class' => 'col-form-label']); }} --}}
                @foreach ($permissionChunks as $group => $permissions)
                {{ html()->label($group)->for('permission-group')->class('col-form-label') }}
                <div class="row">
                    @foreach ($permissions as $permission)
                    <div class="form-group col-md-3">
                        <div class="custom-control custom-switch">
                            {{ html()->checkbox('permissions[]')->value($permission->id)->checked($data->permissions->contains($permission->id))->id("permission-{$permission->action}")->class("custom-control-input {$group} {$permission->action}") }}
                            {{ html()->label($permission->name)->for("permission-{$permission->action}")->class('custom-control-label') }}
                        </div>
                    </div>    
                    @endforeach
                </div>
                @endforeach
            </div>
            <!-- /.card-body -->
            <div class="card-footer rounded-0">
                <button type="submit" id="submit-button" class="btn btn-success rounded-0">Submit</button>
                <button type="button" id="cancel-button" class="btn btn-default rounded-0 float-right" data-url="{{ $listUrl }}">Cancel</button>
            </div>
        </div>
        {{ html()->form()->close() }}
        <!-- /.card -->
    </div>
    <!--/.col (right) -->
</div>
@endsection