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
                <div class="form-group">
                    {{ html()->label("Select Permissions you want to grant to {$data->name}")->for('permissions[]')->class('col-form-label') }}
                    {{ html()->select('permissions[]')->value($data->permissions->pluck('id'))->options($permissions)->class('form-control select2')->multiple() }}
                </div>
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