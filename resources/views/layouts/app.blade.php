<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <title>{{ config('app.name') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    @if(isset($hide_wrapper)&&$hide_wrapper)
        @yield('content')
    @else
        <!-- Navbar -->
        @include('layouts.header')
        <!-- /.navbar -->
        <!-- Main Sidebar Container -->
        @include('layouts.sidebar')
        @yield('content')
        <!-- /.content-wrapper -->
        <!-- Main Footer -->
        @include("layouts.footer")
    @endif
</div>
</body>
<script src="{{ asset('js/app.js') }}">
</script>
<script src="{{ asset('js/select2.js') }}">
</script>
@yield('script_plus')
</html>
