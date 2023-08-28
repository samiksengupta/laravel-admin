<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base id="base-url" href="{{ url('/') }}" target="_blank">
    <base id="base-api-url" href="{{ url('api') }}" target="_blank">
    <base id="base-api-admin-url" href="{{ api_admin_url('/') }}" target="_blank">
    <link rel="icon" href="{{ admin_asset_url('favicon.ico') }}">
    <title>{{ setting('app.title') }} | {{ $title }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- jQuery UI -->
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/jquery-ui/jquery-ui.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">      
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/daterangepicker/daterangepicker.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ admin_asset_url('plugins/summernote/summernote-bs4.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset_url('dist/css/adminlte.min.css') }}">
    <!-- Override style -->
    <link rel="stylesheet" href="{{ admin_asset_url('styles/app/override.css') }}">
    @yield('page-styles')

</head>