@extends('laravel-admin::layouts.app')
@section('title', $title)
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <!-- /.card-header -->
            <div class="card-body">
                <div class="row">
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection
@section('page-styles')
@endsection
@section('page-scripts')
{{-- @include('laravel-admin::partials.scripts-datatables-multiple') --}}
@endsection