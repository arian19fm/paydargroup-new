{{-- Site footer. Content comes from settings/menus; structure only until Figma. --}}
@php
    $footerItems = app(\App\Support\Menus\MenuRepository::class)->tree('footer');
    $footerText = settings('general.footer_text');
    $copyright = settings('general.copyright_text');
@endphp
<footer class="pg-footer py-4 mt-auto">
    <x-layout.container>
        @if ($footerItems)
            <nav aria-label="{{ __('nav.footer_navigation') }}" class="mb-3">
                <ul class="list-inline mb-0">
                    @foreach ($footerItems as $item)
                        <li class="list-inline-item"><a href="{{ $item['url'] }}" @if ($item['target'] === '_blank') target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>
        @endif
        @if ($footerText)
            <p class="small">{{ $footerText }}</p>
        @endif
        <p class="mb-0 small">&copy; {{ now()->year }} {{ $copyright ?: \App\Support\Seo\SeoManager::siteName() }}</p>
    </x-layout.container>
</footer>
