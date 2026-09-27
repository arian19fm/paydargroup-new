@extends('layouts.site')

{{--
    Business page (Figma 205:119 desktop / 220:5 mobile). Three blocks:
    the hero (eyebrow, name, lead, feature tags, wide image), the intro
    ("about …" eyebrow, name + tagline heading, body paragraphs, website
    button) and the benefits (eyebrow, heading, text, check-list, image or
    video with a play button). Empty blocks are skipped; the accent
    colours come from the business's accent key.
--}}

@section('body_class', 'pg-page-business')

@section('content')
    @php
        $accent = $business->accentKey();
        $benefits = $business->benefitList();
        $benefitsMedia = $business->benefitsMedia;
        $hasBenefits = $business->benefits_title || $business->benefits_text || $benefits || $benefitsMedia;
        $features = $business->featureList();
    @endphp
    <article class="pg-business pg-business--{{ $accent }}">
        <header class="pg-business-hero" data-motion="reveal">
            <div class="pg-container pg-business-hero__inner">
                @if ($business->eyebrow)
                    <p class="pg-business-hero__eyebrow">{{ $business->eyebrow }}</p>
                @endif
                <h1 class="pg-business-hero__title" data-motion="reveal-heading">{{ $business->title }}</h1>
                @if ($business->excerpt)
                    <p class="pg-business-hero__text">{{ $business->excerpt }}</p>
                @endif
                @if ($features)
                    <ul class="pg-business-hero__tags" aria-label="{{ __('businesses.features') }}">
                        @foreach ($features as $feature)
                            <li class="pg-business-tag">{{ $feature }}</li>
                        @endforeach
                    </ul>
                @endif
                @if ($business->image)
                    <div class="pg-business-hero__media" data-motion="reveal">
                        <img src="{{ $business->image->url() }}" width="{{ $business->image->width ?: 1294 }}" height="{{ $business->image->height ?: 366 }}" alt="{{ $business->image->alt_text ?: $business->title }}" decoding="async" fetchpriority="high">
                    </div>
                @endif
            </div>
        </header>

        @if ($business->tagline || $business->content || $business->website_url)
            <section class="pg-business-intro" aria-labelledby="business-intro-heading">
                <div class="pg-container pg-business-intro__inner">
                    <div class="pg-business-intro__head" data-motion="reveal">
                        <p class="pg-business-intro__eyebrow">{{ __('businesses.about', ['name' => $business->title]) }}</p>
                        <h2 id="business-intro-heading" class="pg-business-intro__title" data-motion="reveal-heading">
                            <span class="pg-business-intro__title-name">{{ $business->title }}؛</span>
                            @if ($business->tagline)
                                <span class="pg-business-intro__title-tagline">{{ $business->tagline }}</span>
                            @endif
                        </h2>
                    </div>
                    @if ($business->content)
                        <div class="pg-business-intro__body pg-content" data-motion="reveal">
                            {!! \App\Support\Content\PlainText::toHtml($business->content) !!}
                        </div>
                    @endif
                    @if ($business->website_url)
                        <a class="pg-business-intro__cta" href="{{ $business->website_url }}" target="_blank" rel="noopener" aria-label="{{ __('businesses.website_label', ['name' => $business->title]) }}">
                            <span>{{ __('businesses.website') }}</span>
                            <img src="{{ asset('images/icons/business-arrow.svg') }}" width="24" height="24" alt="" aria-hidden="true">
                        </a>
                    @endif
                </div>
            </section>
        @endif

        @if ($hasBenefits)
            <section class="pg-business-benefits" aria-labelledby="business-benefits-heading">
                <div class="pg-container pg-business-benefits__inner">
                    <div class="pg-business-benefits__top">
                        <div class="pg-business-benefits__head" data-motion="reveal">
                            <p class="pg-business-benefits__eyebrow">{{ __('businesses.benefits_eyebrow', ['name' => $business->title]) }}</p>
                            <h2 id="business-benefits-heading" class="pg-business-benefits__title" data-motion="reveal-heading">{{ $business->benefits_title ?: __('businesses.benefits_title', ['name' => $business->title]) }}</h2>
                            @if ($business->benefits_text)
                                <p class="pg-business-benefits__text">{{ $business->benefits_text }}</p>
                            @endif
                        </div>
                        @if ($benefits)
                            <ul class="pg-business-benefits__list" data-motion="reveal-group">
                                @foreach ($benefits as $benefit)
                                    <li class="pg-benefit">
                                        <img class="pg-benefit__icon" src="{{ asset('images/icons/business-check.svg') }}" width="26" height="26" alt="" aria-hidden="true">
                                        <div class="pg-benefit__text">
                                            <h3 class="pg-benefit__title">{{ $benefit['title'] }}</h3>
                                            @if ($benefit['description'] !== '')
                                                <p class="pg-benefit__desc">{{ $benefit['description'] }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    @if ($benefitsMedia?->isVideo())
                        {{-- Native controls work without JavaScript; the play button overlay (site/business-video.js) starts playback and hides itself. The poster is the dedicated banner, else the business image. --}}
                        <div class="pg-business-benefits__media pg-business-video" data-business-video data-motion="reveal">
                            @php $poster = $business->benefitsPosterImage(); @endphp
                            <video class="pg-business-video__player" controls preload="metadata" playsinline @if ($poster) poster="{{ $poster->url() }}" @endif width="1260" height="625">
                                <source src="{{ $benefitsMedia->url() }}" type="{{ $benefitsMedia->mime_type }}">
                            </video>
                            <button type="button" class="pg-business-video__play" data-business-video-play hidden aria-label="{{ __('businesses.play', ['name' => $business->title]) }}">
                                <img src="{{ asset('images/icons/business-play.svg') }}" width="90" height="90" alt="" aria-hidden="true">
                            </button>
                        </div>
                    @elseif ($benefitsMedia?->isImage())
                        <div class="pg-business-benefits__media" data-motion="reveal">
                            <img src="{{ $benefitsMedia->url() }}" width="{{ $benefitsMedia->width ?: 1260 }}" height="{{ $benefitsMedia->height ?: 625 }}" alt="{{ $benefitsMedia->alt_text ?: '' }}" loading="lazy" decoding="async">
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </article>
@endsection
