{{--
    Hero (Figma 110:267 / 172:294). The background is the designed photo
    unless the admin sets a hero image and/or a hero video (Settings →
    صفحهٔ اصلی). With a video: it plays muted/looped over the photo, which
    stays as poster and as the fallback when motion is reduced, JavaScript
    is off or the browser cannot play it. The photo is the LCP image:
    eager, preloaded in the page head, with explicit dimensions. Desktop and
    mobile crops are set in CSS (object-position), not by swapping images.
    All texts and the CTA come from Settings → صفحهٔ اصلی; a blank one is
    left out.
--}}
@php
    $video = $heroVideo ?? null;
    $image = $heroImage ?? null;
    $hero = $sections['hero'];
@endphp
<section class="pg-hero{{ $video ? ' pg-hero--video' : '' }}" aria-labelledby="hero-heading" data-motion="hero">
    <picture class="pg-hero__media">
        @if ($image)
            <img src="{{ $image->url() }}" width="{{ $image->width ?: 1440 }}" height="{{ $image->height ?: 885 }}" alt="" fetchpriority="high" decoding="async">
        @else
            <source type="image/webp" srcset="{{ asset('images/home/hero.webp') }}">
            <img src="{{ asset('images/home/hero.jpg') }}" width="1440" height="885" alt="" fetchpriority="high" decoding="async">
        @endif
    </picture>
    @if ($video)
        <video class="pg-hero__video" autoplay muted loop playsinline preload="metadata" poster="{{ $image ? $image->url() : asset('images/home/hero.jpg') }}" aria-hidden="true" tabindex="-1" data-hero-video>
            <source src="{{ $video->url() }}" type="{{ $video->mime_type }}">
        </video>
    @endif
    <div class="pg-hero__overlay" aria-hidden="true"></div>

    <div class="pg-hero__inner">
        <div class="pg-hero__text">
            @if ($hero['eyebrow'])
                <p class="pg-hero__eyebrow">{{ $hero['eyebrow'] }}</p>
            @endif
            <h1 id="hero-heading" class="pg-hero__title">
                @foreach ($hero['headline'] as $line)
                    {{ $line }}@if (! $loop->last)<br>@endif
                @endforeach
            </h1>
        </div>
        <div class="pg-hero__aside">
            @if ($hero['text'])
                <p class="pg-hero__lead">{{ $hero['text'] }}</p>
            @endif
            @if ($hero['cta'])
                <x-ui.cta class="btn pg-btn pg-btn--light pg-hero__cta" :href="$hero['cta_url']">{{ $hero['cta'] }}</x-ui.cta>
            @endif
        </div>
    </div>
</section>
