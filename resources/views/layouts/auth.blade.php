<!DOCTYPE html>
<html lang="en">

@include('laravel-admin::partials.head')

<body class="hold-transition login-page">
    <div class="login-box">
        <!-- /.login-logo -->
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                {{-- <img class="card-img-top" src="{{ admin_asset_url('dist/img/logo.png') }}" alt="Card image"> --}}
                <a href="{{ $loginUrl }}" class="h1"><b>{{ setting('app.title', env('APP_NAME')) }}</b></a>
                <div>{{ setting('app.description') }}</div>
            </div>
            <div class="card-body">
                @yield('content')
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <!-- /.login-box -->

    @include('laravel-admin::partials.loader')
    @include('laravel-admin::partials.scripts-core')
    @yield('page-scripts')
</body>

</html>
