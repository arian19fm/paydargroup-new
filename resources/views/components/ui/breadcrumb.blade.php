{{--
    Breadcrumb trail. Uses the items registered with seo()->breadcrumbs() so
    the visible trail and the BreadcrumbList JSON-LD always match. Renders
    nothing when there is no trail.
--}}
@props(['items' => null])
@php
    $items = $items ?? seo()->getBreadcrumbs();
@endphp
@if (count($items) > 1)
<nav aria-label="{{ __('nav.breadcrumb') }}" {{ $attributes->class(['pg-breadcrumb']) }}>
    <ol class="breadcrumb mb-0">
        @foreach ($items as $item)
            @if ($loop->last || empty($item['url']))
                <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
            @else
                <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
