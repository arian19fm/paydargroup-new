@extends('layouts.site')

{{--
    Blog listing (Figma 276:13365 desktop / 302:8 mobile): the section
    header shared with the home page (Settings → صفحهٔ اصلی → blog), category chips ("همه" + the active
    categories), a 3-column grid of post cards (stacked on mobile) and the
    numbered pager.
--}}

@section('body_class', 'pg-page-articles')

@section('content')
    <div class="pg-articles">
        <div class="pg-container pg-articles__inner">
            <header class="pg-articles__header" data-motion="reveal">
                @if ($header['eyebrow'])
                    <p class="pg-eyebrow pg-eyebrow--blog">{{ $header['eyebrow'] }}</p>
                @endif
                <h1 class="pg-blog__title pg-articles__title" data-motion="reveal-heading">
                    @if ($header['heading'] || $header['heading_highlight'])
                        @if ($header['heading'])<span class="pg-blog__title-rest">{{ $header['heading'] }}</span>@endif
                        @if ($header['heading_highlight'])<span class="pg-blog__title-highlight">{{ $header['heading_highlight'] }}</span>@endif
                    @else
                        {{ __('articles.title') }}
                    @endif
                </h1>
                @if ($header['text'])
                    <p class="pg-blog__text">{{ $header['text'] }}</p>
                @endif
            </header>

            @if ($categories->isNotEmpty())
                <nav class="pg-articles__filters" aria-label="{{ __('articles.categories') }}">
                    <a class="pg-chip @if (! $current) is-active @endif" href="{{ route('articles.index') }}" @if (! $current) aria-current="page" @endif>{{ __('articles.all') }}</a>
                    @foreach ($categories as $category)
                        <a class="pg-chip @if ($current?->is($category)) is-active @endif" href="{{ route('articles.index', ['category' => $category->slug]) }}" @if ($current?->is($category)) aria-current="page" @endif>{{ $category->name }}</a>
                    @endforeach
                </nav>
            @endif

            @if ($articles->isEmpty())
                <p class="pg-blog__empty pg-articles__empty">{{ $header['empty'] ?: __('articles.empty') }}</p>
            @else
                <ul class="pg-articles__grid" data-motion="reveal-group">
                    @foreach ($articles as $article)
                        <li class="pg-post pg-articles__item">
                            @include('partials.site.articles.card', ['article' => $article])
                        </li>
                    @endforeach
                </ul>

                @if ($articles->hasPages())
                    @include('partials.site.articles.pager', ['paginator' => $articles])
                @endif
            @endif
        </div>
    </div>
@endsection
