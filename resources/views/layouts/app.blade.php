<!-- resources/views/components/app-layout.blade.php -->
<!DOCTYPE html>
<html lang="en">

@include('laravel-admin::partials.head')

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        @include('laravel-admin::partials.header')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('laravel-admin::partials.sidebar')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            @include('laravel-admin::partials.content-header')

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        @include('laravel-admin::partials.footer')

        <!-- Control Sidebar -->
        @include('laravel-admin::partials.control-sidebar')
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    @include('laravel-admin::partials.loader')
    @include('laravel-admin::partials.scripts-core')
    @yield('page-scripts')
</body>

</html>
