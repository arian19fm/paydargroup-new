{{--
    Primary navigation list. Items come from the managed "main" menu
    (App\Support\Menus\MenuRepository, cached); when that menu has no
    resolvable items, the static list in config/site.php is used.

    Pass `items` to render a subset (the desktop header splits the menu in
    two groups around the logo) and `variant` = header | offcanvas.
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

    $variant = $variant ?? 'header';
    // Compare absolute URLs so a relative "/" from the CMS matches the home page.
    $current = rtrim(request()->url(), '/');
@endphp
<ul class="pg-nav pg-nav--{{ $variant }}" data-site-nav>
    @foreach ($items as $item)
        @php $active = rtrim(url($item['url']), '/') === $current; @endphp
        @if (! empty($item['children']))
            <li class="pg-nav__item dropdown">
                <a class="pg-nav__link dropdown-toggle @if ($active) is-active @endif" href="{{ $item['url'] }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span>{{ $item['label'] }}</span>
                    <img class="pg-nav__chevron" src="{{ asset('images/icons/chevron-down.svg') }}" width="20" height="20" alt="" aria-hidden="true">
                </a>
                <ul class="dropdown-menu pg-nav__dropdown">
                    @foreach ($item['children'] as $child)
                        <li><a class="dropdown-item" href="{{ $child['url'] }}" @if ($child['target'] === '_blank') target="_blank" rel="noopener" @endif>{{ $child['label'] }}</a></li>
                    @endforeach
                </ul>
            </li>
        @else
            <li class="pg-nav__item">
                <a class="pg-nav__link @if ($active) is-active @endif" href="{{ $item['url'] }}" @if ($active) aria-current="page" @endif @if ($item['target'] === '_blank') target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a>
            </li>
        @endif
    @endforeach
</ul>
