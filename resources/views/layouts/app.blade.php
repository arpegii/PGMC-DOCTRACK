<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $explicitTitle = trim($__env->yieldContent('title'));
        $routeName = request()->route()?->getName();
        $routeTitle = $routeName
            ? \Illuminate\Support\Str::of($routeName)->replace('.index', '')->replace(['.', '-'], ' ')->headline()->value()
            : null;
        $pageTitle = $explicitTitle !== '' ? $explicitTitle : $routeTitle;
    @endphp
    <title>{{ $pageTitle ?: config('app.name', 'AFPPGMC Document Tracking System') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" 
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="font-sans antialiased min-h-screen">

    {{-- Navigation --}}
    @include('layouts.navigation')

    {{-- Page Header (supports x-app-layout + @section) --}}
    @if (isset($header))
        <header class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="panel-surface px-6 py-5">
                <div class="text-slate-900">
                    {{ $header }}
                </div>
            </div>
        </header>
    @elseif(View::hasSection('header'))
        <header class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            @yield('header')
        </header>
    @endif

    {{-- Page Content --}}
    <main class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- GLOBAL LOGOUT MODAL --}}
    <x-logout-modal />

</body>
</html>
