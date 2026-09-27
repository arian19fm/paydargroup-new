{{-- Admin navigation. Visibility follows permissions; routes stay protected regardless. --}}
@php
    $user = auth()->user();
    $groups = [
        __('admin.nav.content') => [
            ['route' => 'admin.pages.index', 'icon' => 'pages', 'label' => __('admin.nav.pages'), 'can' => 'pages.view', 'active' => 'admin.pages.*'],
            ['route' => 'admin.articles.index', 'icon' => 'articles', 'label' => __('admin.nav.articles'), 'can' => 'articles.view', 'active' => 'admin.articles.*'],
            ['route' => 'admin.categories.index', 'icon' => 'categories', 'label' => __('admin.nav.categories'), 'can' => 'categories.view', 'active' => 'admin.categories.*'],
            ['route' => 'admin.media.index', 'icon' => 'media', 'label' => __('admin.nav.media'), 'can' => 'media.view', 'active' => 'admin.media.*'],
            ['route' => 'admin.team.members.index', 'icon' => 'users', 'label' => __('admin.nav.team'), 'can' => 'team.view', 'active' => 'admin.team.*'],
        ],
        __('admin.nav.structure') => [
            ['route' => 'admin.menus.index', 'icon' => 'menus', 'label' => __('admin.nav.menus'), 'can' => 'menus.view', 'active' => 'admin.menus.*'],
            ['route' => 'admin.redirects.index', 'icon' => 'redirects', 'label' => __('admin.nav.redirects'), 'can' => 'redirects.view', 'active' => 'admin.redirects.*'],
            ['route' => 'admin.settings.edit', 'params' => 'general', 'icon' => 'settings', 'label' => __('admin.nav.settings'), 'can' => 'settings.view', 'active' => 'admin.settings.*'],
        ],
        __('admin.nav.system') => [
            ['route' => 'admin.users.index', 'icon' => 'users', 'label' => __('admin.nav.users'), 'can' => 'users.view', 'active' => 'admin.users.*'],
            ['route' => 'admin.roles.index', 'icon' => 'roles', 'label' => __('admin.nav.roles'), 'can' => 'roles.view', 'active' => 'admin.roles.*'],
        ],
    ];
    $initials = mb_substr(trim($user->name), 0, 1);
    $roleNames = $user->getRoleNames()->map(fn ($r) => trans()->has('admin.roles.names.'.$r) ? __('admin.roles.names.'.$r) : $r)->join('، ');
@endphp
<aside class="pg-admin__sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="admin-sidebar" aria-labelledby="admin-sidebar-label">
    <div class="offcanvas-header d-lg-none">
        <span class="offcanvas-title h6 mb-0" id="admin-sidebar-label">{{ __('admin.title') }}</span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="{{ __('ui.close') }}"></button>
    </div>
    <div class="offcanvas-body pg-admin__sidebar-body">
        <a class="pg-admin__brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('images/brand/paydar-logo-blue.png') }}" width="28" height="38" alt="">
            <span>
                <strong>{{ \App\Support\Seo\SeoManager::siteName() }}</strong>
                <small>{{ __('admin.title') }}</small>
            </span>
        </a>

        <nav class="pg-admin__nav" aria-label="{{ __('admin.title') }}">
            <ul class="nav flex-column">
                @php $dash = request()->routeIs('admin.dashboard'); @endphp
                <li class="nav-item">
                    <a class="nav-link @if ($dash) active @endif" href="{{ route('admin.dashboard') }}" @if ($dash) aria-current="page" @endif>
                        <x-admin.icon name="dashboard" /><span>{{ __('admin.dashboard') }}</span>
                    </a>
                </li>
            </ul>
            @foreach ($groups as $heading => $items)
                @php $visible = array_filter($items, fn ($i) => $user->can($i['can'])); @endphp
                @if ($visible)
                    <div class="pg-admin__nav-heading">{{ $heading }}</div>
                    <ul class="nav flex-column">
                        @foreach ($visible as $item)
                            @php $active = request()->routeIs($item['active']); @endphp
                            <li class="nav-item">
                                <a class="nav-link @if ($active) active @endif" href="{{ route($item['route'], $item['params'] ?? []) }}" @if ($active) aria-current="page" @endif>
                                    <x-admin.icon :name="$item['icon']" /><span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endforeach
        </nav>

        <div class="pg-admin__user">
            <a class="pg-admin__user-card" href="{{ route('admin.profile.edit') }}" title="{{ __('admin.profile.title') }}">
                <span class="pg-admin__avatar" aria-hidden="true">{{ $initials }}</span>
                <span class="pg-admin__user-meta">
                    <strong>{{ $user->name }}</strong>
                    <small>{{ $roleNames }}</small>
                </span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="pg-admin__logout" title="{{ __('admin.logout') }}" aria-label="{{ __('admin.logout') }}"><x-admin.icon name="logout" /></button>
            </form>
        </div>
    </div>
</aside>
