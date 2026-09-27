{{--
    Latest posts (Figma 169:289 / 178:353). Cards come from published
    articles only (App\Support\Home\HomePage). With no articles the section
    keeps its heading and shows a neutral empty state.
--}}
<section class="pg-blog" id="blog" aria-labelledby="blog-heading">
    <div class="pg-container">
        <div class="pg-blog__header" data-motion="reveal" data-motion-exit>
            <div class="pg-blog__intro">
                <p class="pg-eyebrow pg-eyebrow--blog">{{ __('home.blog.eyebrow') }}</p>
                <h2 id="blog-heading" class="pg-blog__title" data-motion="reveal-heading">
                    <span class="pg-blog__title-rest">{{ __('home.blog.heading') }}</span>
                    <span class="pg-blog__title-highlight">{{ __('home.blog.heading_highlight') }}</span>
                </h2>
                <p class="pg-blog__text">{{ __('home.blog.text') }}</p>
            </div>
            @if ($links['blog'] ?? null)
                <a class="pg-blog__more pg-blog__more--header" href="{{ $links['blog'] }}">
                    <span>{{ __('home.blog.more') }}</span>
                    <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="16" height="16" alt="" aria-hidden="true">
                </a>
            @endif
        </div>

        @if ($articles->isEmpty())
            <p class="pg-blog__empty">{{ __('home.blog.empty') }}</p>
        @else
            <ul class="pg-blog__list" data-motion="reveal-group" data-motion-exit data-drag-scroll>
                @foreach ($articles as $article)
                    <li class="pg-post">
                        @include('partials.site.articles.card', ['article' => $article])
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($links['blog'] ?? null)
            <a class="pg-blog__more pg-blog__more--footer" href="{{ $links['blog'] }}">
                <span>{{ __('home.blog.more') }}</span>
                <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="16" height="16" alt="" aria-hidden="true">
            </a>
        @endif
    </div>
</section>
