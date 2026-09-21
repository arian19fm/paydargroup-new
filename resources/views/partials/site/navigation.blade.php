{{--
    Primary navigation list. Items come from the managed "main" menu
    (App\Support\Menus\MenuRepository, cached); when that menu has no
    resolvable items, the static list in config/site.php is used. The same
    markup serves desktop and the mobile offcanvas.
--}}
@php
    $items = $items ?? app(\App\Support\Menus\MenuRepository::class)->tree($location ?? 'main');

    if ($items === []) {
        $items = collect(config('site.navigation', []))->map(fn ($item) => [
            'label' => __($item['label']),
            'url' => isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#'),
            'target' => '_self',
            'children' => [],
        ])->all();
    }

    $current = rtrim(request()->url(), '/');
@endphp
<ul class="navbar-nav" data-site-nav>
    @foreach ($items as $item)
        @php $active = rtrim($item['url'], '/') === $current; @endphp
        @if (! empty($item['children']))
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle @if ($active) active @endif" href="{{ $item['url'] }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{ $item['label'] }}</a>
                <ul class="dropdown-menu">
                    @foreach ($item['children'] as $child)
                        <li><a class="dropdown-item" href="{{ $child['url'] }}" @if ($child['target'] === '_blank') target="_blank" rel="noopener" @endif>{{ $child['label'] }}</a></li>
                    @endforeach
                </ul>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link @if ($active) active @endif" href="{{ $item['url'] }}" @if ($active) aria-current="page" @endif @if ($item['target'] === '_blank') target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a>
            </li>
        @endif
    @endforeach
</ul>
