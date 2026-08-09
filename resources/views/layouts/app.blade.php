<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ASDA MMS') — {{ config('app.name', 'ASDA Member Management System') }}</title>

    @hasSection('meta_description')
        <meta name="description" content="@yield('meta_description')">
    @endif

    @hasSection('meta_robots')
        <meta name="robots" content="@yield('meta_robots')">
    @endif

    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0B6E4F">

    @stack('head')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|sora:600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    @yield('body')
    @include('components.confirm-modal')
    @include('components.profile-crop-modal')
</body>
</html>
