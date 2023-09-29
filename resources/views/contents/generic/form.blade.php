@extends($modal ? 'laravel-admin::layouts.none' : 'laravel-admin::layouts.app')
@section('title', $title)
@section('content')
    <div class="row">
        <!-- left column -->
        <div id="@yield('container', 'form-container')" class="col-md-12">
            {{ html()->form($form['method'], $form['url'])->class('ajax-form')->acceptsFiles()->open() }}
            <div class="card card-info card-outline">
                <div class="card-header rounded-0">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body rounded-0">
                    @include('laravel-admin::partials.form-alert')
                    @hasSection('main-content')
                        @yield('main-content')
                    @else
                        @include('laravel-admin::partials.form-gen')
                    @endif
                    @yield('additional-content')
                </div>
                <!-- /.card-body -->
                <div class="card-footer rounded-0">
                    <button type="submit" class="btn btn-success rounded-0">Submit</button>
                    <button type="button" id="cancel-button" class="btn btn-default rounded-0 float-right" data-url="{{ $listUrl }}">Cancel</button>
                </div>
            </div>
            {{ html()->form()->close() }}
            <!-- /.card -->
        </div>
        <!--/.col (right) -->
    </div>
@endsection