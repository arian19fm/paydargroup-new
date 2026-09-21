{{-- Admin navigation. Visibility follows permissions; routes stay protected regardless. --}}
@php
    $user = auth()->user();
    $groups = [
        __('admin.nav.content') => [
            ['route' => 'admin.pages.index', 'label' => __('admin.nav.pages'), 'can' => 'pages.view', 'active' => 'admin.pages.*'],
            ['route' => 'admin.articles.index', 'label' => __('admin.nav.articles'), 'can' => 'articles.view', 'active' => 'admin.articles.*'],
            ['route' => 'admin.categories.index', 'label' => __('admin.nav.categories'), 'can' => 'categories.view', 'active' => 'admin.categories.*'],
            ['route' => 'admin.media.index', 'label' => __('admin.nav.media'), 'can' => 'media.view', 'active' => 'admin.media.*'],
        ],
        __('admin.nav.structure') => [
            ['route' => 'admin.menus.index', 'label' => __('admin.nav.menus'), 'can' => 'menus.view', 'active' => 'admin.menus.*'],
            ['route' => 'admin.redirects.index', 'label' => __('admin.nav.redirects'), 'can' => 'redirects.view', 'active' => 'admin.redirects.*'],
            ['route' => 'admin.settings.edit', 'params' => 'general', 'label' => __('admin.nav.settings'), 'can' => 'settings.view', 'active' => 'admin.settings.*'],
        ],
        __('admin.nav.system') => [
            ['route' => 'admin.users.index', 'label' => __('admin.nav.users'), 'can' => 'users.view', 'active' => 'admin.users.*'],
        ],
    ];
@endphp
<aside class="pg-admin__sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="admin-sidebar" aria-labelledby="admin-sidebar-label">
    <div class="offcanvas-header d-lg-none">
        <h2 class="offcanvas-title h6" id="admin-sidebar-label">{{ __('admin.title') }}</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="{{ __('ui.close') }}"></button>
    </div>
    <div class="offcanvas-body d-block p-3">
        <a class="d-block fw-bold mb-3 text-decoration-none" href="{{ route('admin.dashboard') }}">{{ \App\Support\Seo\SeoManager::siteName() }}</a>
        <nav aria-label="{{ __('admin.title') }}">
            <ul class="nav nav-pills flex-column gap-1 mb-3">
                <li class="nav-item">
                    <a class="nav-link @if (request()->routeIs('admin.dashboard')) active @endif" href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>{{ __('admin.dashboard') }}</a>
                </li>
            </ul>
            @foreach ($groups as $heading => $items)
                @php $visible = array_filter($items, fn ($i) => $user->can($i['can'])); @endphp
                @if ($visible)
                    <h3 class="text-uppercase small text-body-secondary fw-semibold mt-3 mb-1 px-2">{{ $heading }}</h3>
                    <ul class="nav nav-pills flex-column gap-1">
                        @foreach ($visible as $item)
                            @php $active = request()->routeIs($item['active']); @endphp
                            <li class="nav-item">
                                <a class="nav-link @if ($active) active @endif" href="{{ route($item['route'], $item['params'] ?? []) }}" @if ($active) aria-current="page" @endif>{{ $item['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        </nav>
    </div>
</aside>
