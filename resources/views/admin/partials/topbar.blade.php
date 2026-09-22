<header class="pg-admin__topbar">
    <button class="pg-admin__menu-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="{{ __('admin.toggle_sidebar') }}"><x-admin.icon name="menu" /></button>
    <x-admin.breadcrumb :items="$breadcrumbs ?? []" />
    <div class="pg-admin__topbar-end">
        <a class="btn btn-sm btn-outline-secondary pg-admin__site-link" href="{{ route('home') }}" target="_blank" rel="noopener"><x-admin.icon name="external" size="16" /><span>{{ __('admin.view_site') }}</span></a>
        <a class="pg-admin__topbar-user" href="{{ route('admin.profile.edit') }}">
            <span class="visually-hidden">{{ __('admin.logged_in_as') }}</span>
            <span class="pg-admin__avatar pg-admin__avatar--sm" aria-hidden="true">{{ mb_substr(trim(auth()->user()->name), 0, 1) }}</span>
            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
        </a>
    </div>
</header>
