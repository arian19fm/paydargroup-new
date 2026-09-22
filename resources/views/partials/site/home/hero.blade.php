{{--
    Hero (Figma 110:267 / 172:294). The background is the designed photo
    unless the admin sets a hero image and/or a hero video (Settings →
    صفحهٔ اصلی). With a video: it plays muted/looped over the photo, which
    stays as poster and as the fallback when motion is reduced, JavaScript
    is off or the browser cannot play it. The photo is the LCP image:
    eager, preloaded in the page head, with explicit dimensions. Desktop and
    mobile crops are set in CSS (object-position), not by swapping images.
--}}
@php
    $video = $heroVideo ?? null;
    $image = $heroImage ?? null;
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
            <p class="pg-hero__eyebrow">{{ __('home.hero.eyebrow') }}</p>
            <h1 id="hero-heading" class="pg-hero__title">
                @foreach (__('home.hero.headline') as $line)
                    {{ $line }}@if (! $loop->last)<br>@endif
                @endforeach
            </h1>
        </div>
        <div class="pg-hero__aside">
            <p class="pg-hero__lead">{{ __('home.hero.text') }}</p>
            <x-ui.cta class="btn pg-btn pg-btn--light pg-hero__cta" :href="$links['about'] ?? null">{{ __('home.hero.cta') }}</x-ui.cta>
        </div>
    </div>
</section>
