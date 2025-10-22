<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="javascript:;" class="brand-link">
        <img src="/favicon.ico" alt="AdminLTE Logo"
             class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">{{config('app.name')}}</span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{$admin_blank_avatar}}"
                     class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{\Auth::user()->email}}</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
                <a class="btn btn-sm btn-danger" href="{{ route('logout') }}"
                   onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
            </div>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
                     with font-awesome or any other icon font library -->
                @foreach ($sidebar_menu as $key => $item)
                    <li class="nav-item {!! (0 === strpos($active_menu, $key)) ? 'menu-open' : '' !!}">
                        <a href="{{ $item['url'] ?? 'javascript:;' }}" target="{{ $item['target'] ?? '_self' }}"
                           class="nav-link">
                            <i class="nav-icon fas fa-op fa-{{$item['icon'] ??''}}"></i>
                            <p>
                                {{ $item['alias'] }}
                                @isset($item['children'])
                                    <span class="badge badge-info right">{{count($item['children'])}}</span>
                                @endisset
                            </p>
                        </a>
                        @isset($item['children'])
                            <ul class="nav nav-treeview">
                                @foreach ($item['children'] as $child_key => $child_item)
                                    <li class="nav-item">
                                        <a href="{{ $child_item['url'] ?? 'javascript:;' }}"
                                           target="{{ $child_item['target'] ?? '_self' }}"
                                           class="{!! ($active_menu === "{$key}.{$child_key}") ? 'active' : '' !!} nav-link">
                                            <i class="far fa-{{ $child_item['icon'] ?? 'circle' }} nav-icon"></i>
                                            <p>{{ $child_item['alias'] }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endisset
                    </li>
                @endforeach
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
