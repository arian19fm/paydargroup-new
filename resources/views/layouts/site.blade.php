<!DOCTYPE html>
<html lang="{{ \App\Support\Localization\Direction::htmlLang() }}" dir="{{ \App\Support\Localization\Direction::for() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo.head />
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="d-flex flex-column min-vh-100">
    <x-layout.skip-link />
    @include('partials.site.header')

    <main id="main" tabindex="-1">
        <x-ui.flash-messages class="container mt-3" />
        @yield('content')
    </main>

    @include('partials.site.footer')
    @stack('scripts')
</body>
</html>
