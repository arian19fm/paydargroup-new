<!DOCTYPE html>
<html lang="{{ \App\Support\Localization\Direction::htmlLang() }}" dir="{{ \App\Support\Localization\Direction::for() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('admin.auth.login') }} — {{ \App\Support\Seo\SeoManager::siteName() }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="pg-admin pg-login">
    <main id="main" class="pg-login__layout">
        <aside class="pg-login__brand" aria-hidden="true">
            <img src="{{ asset('images/brand/paydar-logo-blue.png') }}" width="64" height="88" alt="">
            <p class="pg-login__brand-title">{{ \App\Support\Seo\SeoManager::siteName() }}</p>
            <p class="pg-login__brand-text">{{ __('admin.title') }}</p>
        </aside>
        <div class="pg-login__panel">
            <div class="pg-login__card">
                <img class="pg-login__logo d-lg-none" src="{{ asset('images/brand/paydar-logo-navy.png') }}" width="36" height="49" alt="">
                <h1 class="pg-login__title">{{ __('admin.auth.login') }}</h1>
                <p class="pg-login__text">{{ __('admin.auth.intro') }}</p>
                <x-ui.flash-messages class="mb-3" />
                <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
                    @csrf
                    <x-admin.form.input name="email" type="email" :label="__('admin.auth.email')" dir="ltr" required autocomplete="username" autofocus />
                    <x-admin.form.input name="password" type="password" :label="__('admin.auth.password')" dir="ltr" required autocomplete="current-password" />
                    <x-admin.form.checkbox name="remember" :label="__('admin.auth.remember')" />
                    <button type="submit" class="btn btn-primary btn-lg w-100">{{ __('admin.auth.submit') }}</button>
                </form>
            </div>
            <a class="pg-login__back" href="{{ route('home') }}">{{ __('admin.view_site') }}</a>
        </div>
    </main>
</body>
</html>
