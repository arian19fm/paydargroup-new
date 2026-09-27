{{--
    Numbered pager (Figma 291:144–157): prev, first pages, an ellipsis,
    the last page, next — 40px boxes, the current one filled.
--}}
@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = collect(range(1, $last))->filter(fn ($n) => $n <= 3 || $n === $last || abs($n - $current) <= 1)->values();
@endphp
<nav class="pg-pager" aria-label="{{ __('articles.pagination') }}">
    @if ($paginator->onFirstPage())
        <span class="pg-pager__item pg-pager__item--nav is-disabled" aria-hidden="true"><img src="{{ asset('images/icons/blog-chevron.svg') }}" width="20" height="20" alt=""></span>
    @else
        <a class="pg-pager__item pg-pager__item--nav" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('articles.prev') }}"><img src="{{ asset('images/icons/blog-chevron.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a>
    @endif
    @foreach ($pages as $index => $number)
        @if ($index > 0 && $number - $pages[$index - 1] > 1)
            <span class="pg-pager__item pg-pager__item--gap" aria-hidden="true">…</span>
        @endif
        @if ($number === $current)
            <span class="pg-pager__item is-active" aria-current="page">{{ fa_digits($number) }}</span>
        @else
            <a class="pg-pager__item" href="{{ $paginator->url($number) }}" aria-label="{{ __('articles.page', ['number' => fa_digits($number)]) }}">{{ fa_digits($number) }}</a>
        @endif
    @endforeach
    @if ($paginator->hasMorePages())
        <a class="pg-pager__item pg-pager__item--nav pg-pager__item--next" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('articles.next') }}"><img src="{{ asset('images/icons/blog-chevron.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a>
    @else
        <span class="pg-pager__item pg-pager__item--nav pg-pager__item--next is-disabled" aria-hidden="true"><img src="{{ asset('images/icons/blog-chevron.svg') }}" width="20" height="20" alt=""></span>
    @endif
</nav>
