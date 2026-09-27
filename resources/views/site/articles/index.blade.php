@extends('layouts.site')

{{--
    Blog listing (Figma 276:13365 desktop / 302:8 mobile): the section
    header shared with the home page, category chips ("همه" + the active
    categories), a 3-column grid of post cards (stacked on mobile) and the
    numbered pager.
--}}

@section('body_class', 'pg-page-articles')

@section('content')
    <div class="pg-articles">
        <div class="pg-container pg-articles__inner">
            <header class="pg-articles__header" data-motion="reveal">
                <p class="pg-eyebrow pg-eyebrow--blog">{{ __('home.blog.eyebrow') }}</p>
                <h1 class="pg-blog__title pg-articles__title" data-motion="reveal-heading">
                    <span class="pg-blog__title-rest">{{ __('home.blog.heading') }}</span>
                    <span class="pg-blog__title-highlight">{{ __('home.blog.heading_highlight') }}</span>
                </h1>
                <p class="pg-blog__text">{{ __('home.blog.text') }}</p>
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
                <p class="pg-blog__empty pg-articles__empty">{{ __('home.blog.empty') }}</p>
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
