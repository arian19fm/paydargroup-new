<!DOCTYPE html>
{{-- Base layout for public pages. Deliberately minimal in Phase 1: the SEO
     head partial, navigation and footer are implemented in later phases. --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['fa', 'ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
    <main id="main">
        @yield('content')
    </main>
</body>
</html>
