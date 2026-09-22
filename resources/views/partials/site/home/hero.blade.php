{{--
    Hero (Figma 110:267 / 172:294). The photograph is the LCP image: eager,
    preloaded in the page head, with explicit dimensions. Desktop and mobile
    crops are set in CSS (object-position), not by swapping images.
--}}
<section class="pg-hero" aria-labelledby="hero-heading">
    <picture class="pg-hero__media">
        <source type="image/webp" srcset="{{ asset('images/home/hero.webp') }}">
        <img src="{{ asset('images/home/hero.jpg') }}" width="1440" height="885" alt="" fetchpriority="high" decoding="async">
    </picture>
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
