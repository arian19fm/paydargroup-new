{{--
    Post card (Figma 291:20 desktop / 302:26 mobile; the home page's
    169:289 cards are the same component). Cover, date | read time, title
    with the arrow, two-line excerpt and category tags.
--}}
<article class="pg-post__inner">
    <a class="pg-post__cover" href="{{ route('articles.show', $article->slug) }}" tabindex="-1" aria-hidden="true">
        @if ($article->featuredImage)
            <img src="{{ $article->featuredImage->url() }}" width="{{ $article->featuredImage->width ?: 389 }}" height="{{ $article->featuredImage->height ?: 250 }}" alt="" loading="lazy" decoding="async" @if ($parallax ?? true) data-motion="parallax" @endif>
        @endif
    </a>
    <div class="pg-post__body">
        <p class="pg-post__meta">
            <time datetime="{{ $article->published_at->toDateString() }}">{{ \App\Support\Localization\Jalali::format($article->published_at) }}</time>
            <span aria-hidden="true">|</span>
            <span>{{ __('home.blog.read_time', ['minutes' => fa_digits($article->readingTimeMinutes())]) }}</span>
        </p>
        <h3 class="pg-post__title">
            <a href="{{ route('articles.show', $article->slug) }}">
                <span>{{ $article->title }}</span>
                <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="16" height="16" alt="" aria-hidden="true">
            </a>
        </h3>
        @if ($article->excerpt)
            <p class="pg-post__excerpt">{{ $article->excerpt }}</p>
        @endif
        @if ($article->categories->isNotEmpty())
            <ul class="pg-post__tags">
                @foreach ($article->categories as $category)
                    <li class="pg-tag">{{ $category->name }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</article>
