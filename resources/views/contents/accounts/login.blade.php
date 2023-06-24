@extends('laravel-admin::layouts.auth')
@section('title', $title)
@section('content')
<p class="login-box-msg">Sign in to start your session</p>
<form id="login-form" class="ajax-form" action="{{ $apiLoginUrl }}" method="post" autocomplete="off">
    <div class="input-group mb-3">
        <input name="username" type="text" class="form-control" placeholder="Username" autocomplete="nope">
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-user"></span>
            </div>
        </div>
    </div>
    <div class="input-group mb-3">
        <input name="password" type="password" class="form-control" placeholder="Password" autocomplete="nope">
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="fas fa-lock"></span>
            </div>
        </div>
        <p class="help-block"></p>
    </div>
    <div class="row">
        <div class="col-8">
            <div class="icheck-primary">
                <input type="checkbox" id="remember">
                <label for="remember">
                    Remember Me
                </label>
            </div>
        </div>
        <!-- /.col -->
        <div class="col-4">
            <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
            <button id="submit-button" type="submit" class="btn btn-primary btn-block">Sign In</button>
        </div>
        <!-- /.col -->
    </div>
</form>

<p class="mb-1">
    {{-- <a href="#">I forgot my password</a> --}}
</p>
<p class="mb-0">
    {{-- <a href="#" class="text-center">Register a new membership</a> --}}
</p>
@endsection
@section('page-scripts')
<!-- Login Script -->
{{-- TODO: eliminate login script altogather and rely on a single ajax form event binder --}}
{{-- <script src="{{ admin_asset_url('scripts/app/login.js') }}"></script> --}}
@endsection