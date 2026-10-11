@extends('layouts.site')

{{--
    Single article (after Figma 313:13 desktop / 315:1483 mobile): a back
    link, category tags, H1 and a meta line (date, read time, author), the
    wide cover, then the body beside a sticky sidebar (table of contents
    from the top-level headings, highlighted while reading; share links),
    and the article's tags; the latest posts and the contact card follow,
    reusing the home page sections.
--}}

@section('body_class', 'pg-page-article')

@section('content')
    <article class="pg-article">
        <div class="pg-article__progress" data-article-progress aria-hidden="true"></div>
        <header class="pg-article__hero">
            <div class="pg-container pg-article__hero-inner">
                <a class="pg-article__back" href="{{ route('articles.index') }}" data-motion="reveal">
                    <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="14" height="14" alt="" aria-hidden="true">
                    <span>{{ __('articles.title') }}</span>
                </a>
                @if ($article->categories->isNotEmpty())
                    <ul class="pg-article__cats" aria-label="{{ __('articles.tags') }}" data-motion="reveal">
                        @foreach ($article->categories as $category)
                            <li><a class="pg-tag" href="{{ route('articles.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                @endif
                <h1 class="pg-article__title" data-motion="reveal-heading">{{ $article->title }}</h1>
                <p class="pg-article__meta" data-motion="reveal">
                    <span class="pg-article__meta-item">
                        <span class="visually-hidden">{{ __('articles.published_label') }}: </span>
                        <time datetime="{{ $article->published_at->toDateString() }}">{{ \App\Support\Localization\Jalali::format($article->published_at) }}</time>
                    </span>
                    <span class="pg-article__meta-item">{{ __('home.blog.read_time', ['minutes' => fa_digits($article->readingTimeMinutes())]) }}</span>
                    @if ($article->author)
                        <span class="pg-article__meta-item">{{ __('articles.by', ['name' => $article->author->name]) }}</span>
                    @endif
                </p>
                @if ($article->featuredImage)
                    <figure class="pg-article__cover">
                        <img src="{{ $article->featuredImage->url() }}" width="{{ $article->featuredImage->width ?: 1294 }}" height="{{ $article->featuredImage->height ?: 391 }}" alt="{{ $article->featuredImage->alt_text }}" fetchpriority="high" decoding="async">
                    </figure>
                @endif
            </div>
        </header>

        <div class="pg-container pg-article__layout">
            <aside class="pg-article__sidebar">
                @if ($toc)
                    <details class="pg-article__panel pg-article__toc" open>
                        <summary class="pg-article__panel-title">{{ __('articles.toc') }}</summary>
                        <nav aria-label="{{ __('articles.toc') }}">
                            <ol data-article-toc>
                                @foreach ($toc as $entry)
                                    <li><a href="#{{ $entry['id'] }}">{{ $entry['text'] }}</a></li>
                                @endforeach
                            </ol>
                        </nav>
                    </details>
                @endif

                <div class="pg-article__panel pg-article__share-panel">
                    <p class="pg-article__panel-title">{{ __('articles.share') }}</p>
                    <ul class="pg-article__share" aria-label="{{ __('articles.share') }}">
                        <li><a class="pg-article__share-btn" href="{{ $article->publicUrl() }}" data-copy-link data-copied-label="{{ __('articles.copied') }}" aria-label="{{ __('articles.copy_link') }}" title="{{ __('articles.copy_link') }}"><img src="{{ asset('images/icons/blog-link.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                        <li><a class="pg-article__share-btn" href="{{ $share['x'] }}" target="_blank" rel="noopener" aria-label="{{ __('articles.share_on', ['network' => 'X']) }}"><img src="{{ asset('images/icons/blog-x.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                        <li><a class="pg-article__share-btn" href="{{ $share['facebook'] }}" target="_blank" rel="noopener" aria-label="{{ __('articles.share_on', ['network' => 'Facebook']) }}"><img src="{{ asset('images/icons/blog-facebook.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                        <li><a class="pg-article__share-btn" href="{{ $share['linkedin'] }}" target="_blank" rel="noopener" aria-label="{{ __('articles.share_on', ['network' => 'LinkedIn']) }}"><img src="{{ asset('images/icons/blog-linkedin.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                    </ul>
                </div>
            </aside>

            <div class="pg-article__main">
                <div class="pg-article__body pg-content" data-article-body>
                    @if ($article->excerpt)
                        <p class="pg-article__lead">{{ $article->excerpt }}</p>
                    @endif
                    {!! $body !!}
                </div>

                <footer class="pg-article__end">
                    @if ($article->categories->isNotEmpty())
                        <ul class="pg-article__cats" aria-label="{{ __('articles.tags') }}">
                            @foreach ($article->categories as $category)
                                <li><a class="pg-tag" href="{{ route('articles.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                    <a class="pg-article__back pg-article__back--end" href="{{ route('articles.index') }}">
                        <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="14" height="14" alt="" aria-hidden="true">
                        <span>{{ __('articles.back') }}</span>
                    </a>
                </footer>
            </div>
        </div>
    </article>

    @if ($latest->isNotEmpty())
        @include('partials.site.home.blog', ['articles' => $latest, 'links' => $links])
    @endif

    @include('partials.site.home.contact')
@endsection
