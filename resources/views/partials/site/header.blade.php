{{--
    Glass header (Figma 119:656 desktop / 176:576 mobile). On pages that
    declare a hero (the home page) it floats over the hero image; elsewhere
    it sits on a solid brand band so the white navigation stays readable.

    Desktop: the menu is split in two groups around the logo (first half at
    the start, second half at the end). Mobile: logo + hamburger; the same
    items render in the offcanvas panel.
--}}
@php
    $items = app(\App\Support\Menus\MenuRepository::class)->tree('main');

    if ($items === []) {
        $items = collect(config('site.navigation', []))->map(fn ($item) => [
            'label' => __($item['label']),
            'url' => isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#'),
            'target' => '_self',
            'children' => [],
        ])->all();
    }

    $split = (int) ceil(count($items) / 2);
    $startItems = array_slice($items, 0, $split);
    $endItems = array_slice($items, $split);
    $siteName = \App\Support\Seo\SeoManager::siteName();
    $overlay = \Illuminate\Support\Facades\View::hasSection('hero');
    // Navy emblem over the hero photo (desktop frame); the blue emblem on the
    // mobile frame and on the solid band of undesigned pages.
    $desktopLogo = $overlay ? 'paydar-logo-navy' : 'paydar-logo-blue';
@endphp
<header class="pg-header @if ($overlay) pg-header--overlay @endif">
    <div class="pg-header__inner">
        <nav class="pg-header__bar" aria-label="{{ __('nav.main_navigation') }}">
            <div class="pg-header__group d-none d-lg-block">
                @include('partials.site.navigation', ['items' => $startItems, 'variant' => 'header'])
            </div>

            <a class="pg-header__brand" href="{{ route('home') }}">
                <picture>
                    <source media="(max-width: 991.98px)" srcset="{{ asset('images/brand/paydar-logo-blue.png') }}">
                    <img src="{{ asset('images/brand/'.$desktopLogo.'.png') }}" width="45" height="60" alt="{{ $siteName }}">
                </picture>
            </a>

            <div class="pg-header__group pg-header__group--end d-none d-lg-block">
                @include('partials.site.navigation', ['items' => $endItems, 'variant' => 'header'])
            </div>

            <button class="pg-header__toggle d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#site-nav" aria-controls="site-nav" aria-expanded="false" aria-label="{{ __('nav.open_menu') }}">
                <img src="{{ asset('images/icons/menu-01.svg') }}" width="24" height="24" alt="" aria-hidden="true">
            </button>
        </nav>
    </div>

    @include('partials.site.mobile-navigation', ['items' => $items])
</header>
