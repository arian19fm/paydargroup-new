<!DOCTYPE html>
{{-- Admin panel shell. Never indexable; no Open Graph / JSON-LD needed. --}}
<html lang="{{ \App\Support\Localization\Direction::htmlLang() }}" dir="{{ \App\Support\Localization\Direction::for() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard')) | {{ __('admin.title') }} — {{ \App\Support\Seo\SeoManager::siteName() }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="pg-admin bg-body-tertiary">
    <x-layout.skip-link />
    <div class="d-lg-flex">
        @include('admin.partials.sidebar')

        <div class="flex-grow-1 min-vh-100 d-flex flex-column">
            @include('admin.partials.topbar')

            <main id="main" tabindex="-1" class="flex-grow-1 p-3 p-lg-4">
                <x-admin.breadcrumb :items="$breadcrumbs ?? []" />
                <x-admin.page-header :title="$pageTitle ?? View::yieldContent('title', __('admin.dashboard'))">
                    @yield('actions')
                </x-admin.page-header>
                <x-ui.flash-messages class="mb-3" />
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
