@extends('layouts.site')

{{--
    "About" page template (Figma 249:1038 desktop / 258:33 mobile).
    Title, lead (excerpt) and body come from the CMS page; the photos,
    partner logos, history block and statistics from Settings → about
    (App\Support\Pages\AboutPage). Empty extras are simply not rendered —
    photo slots fall back to the design's neutral placeholder box.
--}}

@section('content')
    <article class="pg-about">
        <div class="pg-container">
            <header class="pg-about__intro" data-motion="reveal">
                <p class="pg-about__eyebrow">{{ __('about.eyebrow') }}</p>
                <h1 class="pg-about__title" data-motion="reveal-heading">{{ $page->title }}</h1>
                @if ($page->excerpt)
                    <p class="pg-about__lead">{{ $page->excerpt }}</p>
                @endif
            </header>

            <div class="pg-about__photos">
                @foreach ([$about['image1'], $about['image2']] as $index => $image)
                    <figure class="pg-about__photo pg-about__photo--{{ $index + 1 }}">
                        @if ($image)
                            <img src="{{ $image->url() }}" width="{{ $image->width ?: 1332 }}" height="{{ $image->height ?: 749 }}" alt="{{ $image->alt_text }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" decoding="async" data-motion="parallax">
                        @endif
                    </figure>
                @endforeach
            </div>

            @if (trim((string) $page->content) !== '')
                <div class="pg-about__body pg-content" data-motion="reveal">
                    {!! \App\Support\Content\PlainText::toHtml($page->content) !!}
                </div>
            @endif
        </div>

        @if ($about['partners']->isNotEmpty())
            <section class="pg-about__partners" aria-label="{{ __('about.partners') }}">
                <div class="pg-container">
                    <p class="pg-about__partners-text">{{ __('about.partners') }}</p>
                    <ul class="pg-about__logos" data-motion="reveal-group">
                        @foreach ($about['partners'] as $logo)
                            <li><img src="{{ $logo->url() }}" width="{{ $logo->width ?: 170 }}" height="{{ $logo->height ?: 48 }}" alt="{{ $logo->alt_text ?: __('about.partner_logo_alt') }}" loading="lazy" decoding="async"></li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @if ($about['history']['title'] || $about['history']['text'] || $about['stats']->isNotEmpty())
            <section class="pg-about__history" aria-labelledby="about-history-heading">
                <div class="pg-container pg-about__history-grid">
                    <p class="pg-about__history-eyebrow">{{ __('about.history_eyebrow') }}</p>
                    <div class="pg-about__history-main">
                        @if ($about['history']['title'] || $about['history']['text'])
                            <div class="pg-about__history-copy" data-motion="reveal">
                                @if ($about['history']['title'])
                                    <h2 id="about-history-heading" class="pg-about__history-title">{{ $about['history']['title'] }}</h2>
                                @endif
                                @if ($about['history']['text'])
                                    <div class="pg-about__history-text pg-content">{!! \App\Support\Content\PlainText::toHtml($about['history']['text']) !!}</div>
                                @endif
                            </div>
                        @endif
                        <figure class="pg-about__history-photo">
                            @if ($about['history']['image'])
                                <img src="{{ $about['history']['image']->url() }}" width="{{ $about['history']['image']->width ?: 1171 }}" height="{{ $about['history']['image']->height ?: 781 }}" alt="{{ $about['history']['image']->alt_text }}" loading="lazy" decoding="async" data-motion="parallax">
                            @endif
                        </figure>
                        @if ($about['stats']->isNotEmpty())
                            <dl class="pg-about__stats" data-motion="reveal-group">
                                @foreach ($about['stats'] as $key => $value)
                                    <div class="pg-about__stat pg-about__stat--{{ $key }}">
                                        <dt>{{ __('about.stats.'.$key) }}</dt>
                                        <dd>{{ fa_digits($value) }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </div>
                </div>
            </section>
        @endif
    </article>
@endsection
