{{--
    Offcanvas wrapper used below the lg breakpoint. Above it, Bootstrap's
    navbar-expand-lg renders the same list inline, so there is one DOM copy
    of the navigation. Works without JS: links are ordinary anchors.
--}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="site-nav" aria-labelledby="site-nav-label">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h5" id="site-nav-label">{{ __('nav.menu') }}</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#site-nav" aria-label="{{ __('nav.close_menu') }}"></button>
    </div>
    <div class="offcanvas-body">
        @include('partials.site.navigation')
    </div>
</div>
