<!DOCTYPE html>
<html lang="{{ \App\Support\Localization\Direction::htmlLang() }}" dir="{{ \App\Support\Localization\Direction::for() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('admin.auth.login') }} — {{ \App\Support\Seo\SeoManager::siteName() }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-body-tertiary">
    <main id="main" class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <div class="card shadow-sm" style="max-width: 26rem; width: 100%;">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">{{ __('admin.auth.login') }}</h1>
                <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
                    @csrf
                    <x-admin.form.input name="email" type="email" :label="__('admin.auth.email')" required autocomplete="username" autofocus />
                    <x-admin.form.input name="password" type="password" :label="__('admin.auth.password')" required autocomplete="current-password" />
                    <x-admin.form.checkbox name="remember" :label="__('admin.auth.remember')" />
                    <button type="submit" class="btn btn-primary w-100">{{ __('admin.auth.submit') }}</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
