<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ $homeUrl }}" class="brand-link">
        <img src="{{ admin_asset_url('dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{ setting('app.title') }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ admin_asset_url('dist/img/default-avatar-160x160.png') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="{{ route('admin.profile') }}" class="d-block">{{ @Auth::user()->name ?? "Guest" }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
                with font-awesome or any other icon font library -->
                @foreach(app(config('laravel-admin.model_namespace') . '\\MenuItem')::hierarchy()->get() as $item)
                <li class="nav-item {{ $item->active ? 'menu-open' : '' }}">
                    <a href="{{ $item->url }}" class="nav-link {{ $item->active ? 'active' : '' }}" target="{{ $item->target }}">
                        <i class="nav-icon {{ $item->icon_class }}"></i>
                        <p>
                            {{ $item->text }}
                            @if($item->has_children)<i class="right fas fa-angle-left"></i>@endif
                        </p>
                    </a>
                    @if($item->has_children)
                    <ul class="nav nav-treeview">
                        @foreach($item->children as $item)
                        <li class="nav-item">
                            <a href="{{ $item->url }}" class="nav-link {{ $item->active ? 'active' : '' }}" target="{{ $item->target }}">
                                <i class="nav-icon {{ $item->icon_class }}"></i>
                                <p>{{ $item->text }}</p>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </li>
                @endforeach
                <li class="nav-item">
                    <a href="{{ api_admin_url('logout') }}" class="nav-link ajax-url" data-method="post" data-navigate="{{ admin_url('login') }}">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>