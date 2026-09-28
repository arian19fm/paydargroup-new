{{--
    Latest posts (Figma 169:289 / 178:353). Cards come from published
    articles only (App\Support\Home\HomePage). With no articles the section
    keeps its heading and shows the empty-state text. Texts and the "see
    more" link come from Settings → صفحهٔ اصلی; a blank one is left out.
--}}
@php $section = $sections['blog']; @endphp
<section class="pg-blog" id="blog" aria-labelledby="blog-heading">
    <div class="pg-container">
        <div class="pg-blog__header" data-motion="reveal" data-motion-exit>
            <div class="pg-blog__intro">
                @if ($section['eyebrow'])
                    <p class="pg-eyebrow pg-eyebrow--blog">{{ $section['eyebrow'] }}</p>
                @endif
                <h2 id="blog-heading" class="pg-blog__title" data-motion="reveal-heading">
                    @if ($section['heading'])<span class="pg-blog__title-rest">{{ $section['heading'] }}</span>@endif
                    @if ($section['heading_highlight'])<span class="pg-blog__title-highlight">{{ $section['heading_highlight'] }}</span>@endif
                </h2>
                @if ($section['text'])
                    <p class="pg-blog__text">{{ $section['text'] }}</p>
                @endif
            </div>
            @if ($section['cta'] && $section['cta_url'])
                <a class="pg-blog__more pg-blog__more--header" href="{{ $section['cta_url'] }}">
                    <span>{{ $section['cta'] }}</span>
                    <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="16" height="16" alt="" aria-hidden="true">
                </a>
            @endif
        </div>

        @if ($articles->isEmpty())
            @if ($section['empty'])
                <p class="pg-blog__empty">{{ $section['empty'] }}</p>
            @endif
        @else
            <ul class="pg-blog__list" data-motion="reveal-group" data-motion-exit data-drag-scroll>
                @foreach ($articles as $article)
                    <li class="pg-post">
                        @include('partials.site.articles.card', ['article' => $article])
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($section['cta'] && $section['cta_url'])
            <a class="pg-blog__more pg-blog__more--footer" href="{{ $section['cta_url'] }}">
                <span>{{ $section['cta'] }}</span>
                <img src="{{ asset('images/icons/blog-arrow.svg') }}" width="16" height="16" alt="" aria-hidden="true">
            </a>
        @endif
    </div>
</section>
