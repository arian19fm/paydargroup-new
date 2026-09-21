{{--
    Primary navigation list. Items come from config/site.php until menus are
    managed in the admin panel. The same markup serves desktop and the
    mobile offcanvas (Bootstrap collapses it into the offcanvas below lg).
--}}
@php
    $items = $items ?? config('site.navigation', []);
@endphp
<ul class="navbar-nav" data-site-nav>
    @foreach ($items as $item)
        @php
            $url = isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
            $active = isset($item['route']) && request()->routeIs($item['route']);
        @endphp
        <li class="nav-item">
            <a class="nav-link @if ($active) active @endif" href="{{ $url }}" @if ($active) aria-current="page" @endif>
                {{ __($item['label']) }}
            </a>
        </li>
    @endforeach
</ul>
