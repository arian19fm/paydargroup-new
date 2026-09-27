@extends('layouts.site')

{{--
    Single article (Figma 313:13 desktop / 315:1483 mobile): eyebrow + H1,
    the wide cover, then the body with a sidebar (publication date, table
    of contents from the `#` headings, share links); the latest posts and
    the contact card follow, reusing the home page sections.
--}}

@section('body_class', 'pg-page-article')

@section('content')
    <article class="pg-article">
        <header class="pg-article__hero">
            <div class="pg-container pg-article__hero-inner">
                <p class="pg-article__eyebrow" data-motion="reveal">{{ __('articles.eyebrow') }}</p>
                <h1 class="pg-article__title" data-motion="reveal-heading">{{ $article->title }}</h1>
                @if ($article->featuredImage)
                    <figure class="pg-article__cover">
                        <img src="{{ $article->featuredImage->url() }}" width="{{ $article->featuredImage->width ?: 1294 }}" height="{{ $article->featuredImage->height ?: 391 }}" alt="{{ $article->featuredImage->alt_text }}" fetchpriority="high" decoding="async" data-motion="parallax">
                    </figure>
                @endif
            </div>
        </header>

        <div class="pg-container pg-article__layout">
            <aside class="pg-article__sidebar" data-motion="reveal">
                <div class="pg-article__date">
                    <time class="pg-article__date-value" datetime="{{ $article->published_at->toDateString() }}">{{ \App\Support\Localization\Jalali::format($article->published_at) }}</time>
                    <span class="pg-article__date-label">
                        <span aria-hidden="true" class="pg-article__date-icon"><img src="{{ asset('images/icons/careers-tick.svg') }}" width="24" height="24" alt=""><img src="{{ asset('images/icons/careers-tick-ring.svg') }}" width="20" height="20" alt=""></span>
                        <span>{{ __('articles.published_label') }}</span>
                    </span>
                </div>

                @if ($toc)
                    <nav class="pg-article__toc" aria-label="{{ __('articles.toc') }}">
                        <ol>
                            @foreach ($toc as $entry)
                                <li{!! $loop->first ? ' class="is-lead"' : '' !!}><a href="#{{ $entry['id'] }}">{{ $entry['text'] }}</a></li>
                            @endforeach
                        </ol>
                    </nav>
                @endif

                <ul class="pg-article__share" aria-label="{{ __('articles.share') }}">
                    <li><a class="pg-article__share-btn" href="{{ $article->publicUrl() }}" data-copy-link aria-label="{{ __('articles.copy_link') }}" title="{{ __('articles.copy_link') }}"><img src="{{ asset('images/icons/blog-link.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                    <li><a class="pg-article__share-btn" href="{{ $share['x'] }}" target="_blank" rel="noopener" aria-label="{{ __('articles.share_on', ['network' => 'X']) }}"><img src="{{ asset('images/icons/blog-x.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                    <li><a class="pg-article__share-btn" href="{{ $share['facebook'] }}" target="_blank" rel="noopener" aria-label="{{ __('articles.share_on', ['network' => 'Facebook']) }}"><img src="{{ asset('images/icons/blog-facebook.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                    <li><a class="pg-article__share-btn" href="{{ $share['linkedin'] }}" target="_blank" rel="noopener" aria-label="{{ __('articles.share_on', ['network' => 'LinkedIn']) }}"><img src="{{ asset('images/icons/blog-linkedin.svg') }}" width="20" height="20" alt="" aria-hidden="true"></a></li>
                </ul>
            </aside>

            <div class="pg-article__body" data-motion="reveal">
                @if ($article->excerpt)
                    <p class="pg-article__lead">{{ $article->excerpt }}</p>
                @endif
                {!! $body !!}
            </div>
        </div>
    </article>

    @if ($latest->isNotEmpty())
        @include('partials.site.home.blog', ['articles' => $latest, 'links' => $links])
    @endif

    @include('partials.site.home.contact')
@endsection
