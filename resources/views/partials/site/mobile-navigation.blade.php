{{--
    Offcanvas navigation below the lg breakpoint (the desktop header renders
    the same items inline). Bootstrap handles focus trapping, Escape and the
    backdrop; without JS the links are still ordinary anchors on the page.
    The panel slides in from the end edge — the left in RTL, where the
    hamburger button sits in the design.
--}}
<div class="offcanvas offcanvas-end pg-offcanvas d-lg-none" tabindex="-1" id="site-nav" aria-labelledby="site-nav-label">
    <div class="offcanvas-header">
        <a class="pg-offcanvas__brand" href="{{ route('home') }}">
            <img src="{{ asset('images/brand/paydar-logo-navy.png') }}" width="32" height="44" alt="{{ \App\Support\Seo\SeoManager::siteName() }}">
        </a>
        <span class="offcanvas-title visually-hidden" id="site-nav-label">{{ __('nav.menu') }}</span>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#site-nav" aria-label="{{ __('nav.close_menu') }}"></button>
    </div>
    <div class="offcanvas-body">
        <nav aria-label="{{ __('nav.main_navigation') }}">
            @include('partials.site.navigation', ['items' => $items, 'variant' => 'offcanvas'])
        </nav>
    </div>
</div>
