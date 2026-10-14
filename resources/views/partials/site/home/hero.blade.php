{{--
    Hero. One backdrop on every size: the brand gradient (pages/home/_hero)
    with the particle field over it (canvas, site/hero-particles.js — empty
    without JavaScript or with reduced motion). There is no background
    photo. Mobile/tablet stack the business network (hero-network partial)
    above the copy column; desktop puts the copy at the start and the
    network in the media column.

    The admin's hero video (Settings → صفحهٔ اصلی) is a framed box at the
    top of the copy column — under the globe on mobile/tablet, above the
    eyebrow on desktop — with native controls. It does not autoplay: the
    hero image from the same settings is its cover (poster) until the
    visitor presses play; without a cover the first frame shows.
    site/hero-video.js pauses it when it scrolls out of view. All texts and
    the CTA come from Settings → صفحهٔ اصلی; a blank one is left out.
--}}
@php
    $video = $heroVideo ?? null;
    $image = $heroImage ?? null;
    $hero = $sections['hero'];
@endphp
<section class="pg-hero" aria-labelledby="hero-heading" data-motion="hero">
    <canvas class="pg-hero__particles" data-hero-particles aria-hidden="true"></canvas>

    <div class="pg-hero__inner">
        <div class="pg-hero__copy">
            <div class="pg-hero__text">
                @if ($hero['eyebrow'])
                    <p class="pg-hero__eyebrow">{{ $hero['eyebrow'] }}</p>
                @endif
                <h1 id="hero-heading" class="pg-hero__title" style="font-size: 1.5rem">
                    @foreach ($hero['headline'] as $line)
                        {{ $line }}@if (! $loop->last)@endif
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

        <div class="pg-hero__visual">
            @include('partials.site.home.hero-network', ['products' => $products])
        </div>
    </div>
</section>
