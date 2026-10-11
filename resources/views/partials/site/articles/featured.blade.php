{{--
    Featured post: the newest article on the first page of the listing, as a
    wide card — cover on one side, categories, title, excerpt, meta and a
    "read more" link on the other (stacked on mobile).
--}}
<article class="pg-feature-post" data-motion="reveal">
    <a class="pg-feature-post__cover" href="{{ route('articles.show', $article->slug) }}" tabindex="-1" aria-hidden="true">
        @if ($article->featuredImage)
            <img src="{{ $article->featuredImage->url() }}" width="{{ $article->featuredImage->width ?: 750 }}" height="{{ $article->featuredImage->height ?: 450 }}" alt="" fetchpriority="high" decoding="async">
        @endif
    </a>
    <div class="pg-feature-post__body">
        <p class="pg-feature-post__label">{{ __('articles.featured') }}</p>
        <h2 class="pg-feature-post__title"><a href="{{ route('articles.show', $article->slug) }}">{{ $article->title }}</a></h2>
        @if ($article->excerpt)
            <p class="pg-feature-post__excerpt">{{ $article->excerpt }}</p>
        @endif
        <p class="pg-post__meta">
            <time datetime="{{ $article->published_at->toDateString() }}">{{ \App\Support\Localization\Jalali::format($article->published_at) }}</time>
            <span aria-hidden="true">|</span>
            <span>{{ __('home.blog.read_time', ['minutes' => fa_digits($article->readingTimeMinutes())]) }}</span>
        </p>
        <div class="pg-feature-post__footer">
            @if ($article->categories->isNotEmpty())
                <ul class="pg-post__tags">
                    @foreach ($article->categories as $category)
                        <li class="pg-tag">{{ $category->name }}</li>
                    @endforeach
                </ul>
            @endif
            <a class="pg-feature-post__more" href="{{ route('articles.show', $article->slug) }}" aria-hidden="true" tabindex="-1">
                {{ __('articles.read_more') }}
                <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="16" height="16" alt="">
            </a>
        </div>
    </div>
</article>
