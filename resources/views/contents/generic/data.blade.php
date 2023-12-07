@extends($modal ? 'laravel-admin::layouts.none' : 'laravel-admin::layouts.app')
@section('title', $title)
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            {{ html()->form('GET', $editUrl)->class('ajax-form')->open() }}
            <div class="card card-primary card-outline">
                <div class="card-header rounded-0">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0 rounded-0">
                    @hasSection('main-content')
                        @yield('main-content')
                    @else
                        @include('laravel-admin::partials.data-gen')
                    @endif
                    @yield('additional-content')
                </div>
                <!-- /.card-body -->
                <div class="card-footer rounded-0">
                    @can('update', $model) <button type="button" onclick="window.location.href = '{{ $editUrl }}';" id="edit-button" class="btn btn-info rounded-0">Edit</button> @endcan
                    <button type="button" id="cancel-button" class="btn btn-default rounded-0 float-right" data-url="{{ $listUrl }}">Cancel</button>
                </div>
            </div>
            {{ html()->form()->close() }}
            <!-- /.card -->
        </div>
        <!--/.col (right) -->
    </div>
@endsection
