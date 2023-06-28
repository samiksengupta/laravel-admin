@extends('laravel-admin::layouts.app')
@section('title', $title)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-primary card-outline">
                <div class="card-header rounded-0">
                    <h3 class="card-title float-left row justify-content-center align-self-center">{{ $title }}</h3>
                    @if($showPageAction ?? false)
                    <div class="float-right page-action">
                        @yield('additional-page-actions')
                        {!! build_action_html($model::pageActions(), null, "{$modelName} Action"); !!}
                    </div>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    @yield('pre-filter')
                    @include('laravel-admin::partials.list-filter')
                    @yield('pre-table')
                    {{ html()->form()->id('datatable-form')->open() }}
                    <table id="datatable" class="table table-bordered table-striped" data-options="{{ $datatableOptions }}">
                        <thead />
                        <tbody />
                        {{-- <tbody>
                            <tr class="odd"><td valign="top" class="dataTables_empty"></td></tr>
                        </tbody> --}}
                    </table>
                    {{ html()->form()->close() }}
                    @yield('post-table')
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    @include('laravel-admin::partials.modal-remote')
@endsection
@section('page-styles')
@include('laravel-admin::partials.styles-datatables')
<style>
    .dt-buttons {
        margin-bottom: 10px;
    }
</style>
@endsection
@section('page-scripts')
@include('laravel-admin::partials.scripts-datatables')
@yield('additional-scripts')
@endsection