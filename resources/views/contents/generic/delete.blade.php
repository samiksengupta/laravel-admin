@extends($modal ? 'laravel-admin::layouts.none' : 'laravel-admin::layouts.app')
@section('title', $title)
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            {{ html()->form('DELETE', $apiDeleteUrl)->class('ajax-form')->open() }}
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h5 class="m-0">Are you sure you want to delete this?</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-title">This information will be lost forever.</h6>

                    <p class="card-text">Once deleted, this data cannot be restored. Proceed with caution. If you wish to delete this permanently, click on the <strong>Delete</strong> button below. You will not be warned again.</p>
                    
                    <button type="submit" id="delete-button" class="btn btn-danger rounded-0">Delete</button>
                    <button type="button" id="cancel-button" class="btn btn-default rounded-0 float-right" data-url="{{ $listUrl }}">Cancel</button>
                </div>
            </div>
            {{ html()->form()->close() }}
            <!-- /.card -->
        </div>
        <!--/.col (right) -->
    </div>
@endsection