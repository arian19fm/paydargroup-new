{{-- Site header: brand + primary navigation. Visual design pending Figma. --}}
<header class="pg-header">
    <nav class="navbar navbar-expand-lg" aria-label="{{ __('nav.main_navigation') }}">
        <x-layout.container>
            <a class="navbar-brand" href="{{ route('home') }}">
                {{-- Logo pending brand assets; text brand for now. --}}
                {{ config('site.name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#site-nav" aria-controls="site-nav" aria-expanded="false" aria-label="{{ __('nav.open_menu') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
            @include('partials.site.mobile-navigation')
        </x-layout.container>
    </nav>
</header>
