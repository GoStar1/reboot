<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <h1 class="m-0">&nbsp;&nbsp;&nbsp;{{$title??'-'}}</h1>
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        @yield('tool')
    </ul>
</nav>
