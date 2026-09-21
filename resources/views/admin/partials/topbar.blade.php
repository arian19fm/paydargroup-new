<header class="pg-admin__topbar">
    <div class="d-flex align-items-center gap-3 px-3 py-2">
        <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="{{ __('admin.toggle_sidebar') }}">☰</button>
        <a class="small text-decoration-none" href="{{ route('home') }}" target="_blank" rel="noopener">{{ __('admin.view_site') }}</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="small text-body-secondary">
                <span class="visually-hidden">{{ __('admin.logged_in_as') }}</span>
                {{ auth()->user()->name }}
                <span class="text-body-tertiary">({{ auth()->user()->getRoleNames()->join(', ') }})</span>
            </span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-link btn-sm p-0">{{ __('admin.logout') }}</button>
            </form>
        </div>
    </div>
</header>
