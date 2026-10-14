{{--
    Businesses / "our products": a showcase of the published businesses
    (admin → businesses, in their admin order; the showcase is omitted when
    none is published). The section copy comes from Settings → صفحهٔ اصلی
    (a blank text is left out).

    Server-rendered as a plain stacked list — numbered index, then one
    article per product with its own screenshot — so every word and image
    is there without JavaScript. On wide screens site/products-showcase.js
    moves the screenshots and the index into a sticky preview beside the
    text and marks the product being read as active (IntersectionObserver;
    native scrolling only). Accents come from the business's accent key.
--}}
@php $section = $sections['products']; @endphp
<section class="pg-products" id="products" aria-labelledby="products-heading">
    <div class="pg-container pg-products__inner">
        <header class="pg-products__intro" data-motion="reveal">
            <div class="pg-products__lead">
                @if ($section['eyebrow'])
                    <p class="pg-eyebrow">{{ $section['eyebrow'] }}</p>
                @endif
                <h2 id="products-heading" class="pg-products__title" data-motion="reveal-heading">
                    @if ($section['heading_highlight'])<span class="pg-products__title-highlight">{{ $section['heading_highlight'] }}</span>@endif
                    @if ($section['heading'])<span class="pg-products__title-rest">{{ $section['heading'] }}</span>@endif
                </h2>
            </div>
            @if ($section['text'] || $section['cta'])
                <div class="pg-products__aside">
                    @if ($section['text'])
                        <p class="pg-products__text">{{ $section['text'] }}</p>
                    @endif
                    @if ($section['cta'])
                        <x-ui.cta class="btn pg-btn pg-btn--solid pg-products__cta" :href="$section['cta_url']">{{ $section['cta'] }}</x-ui.cta>
                    @endif
                </div>
            @endif
        </header>

        @if (count($products))
        <div class="pg-showcase" data-products-showcase>
            <nav class="pg-showcase__nav" aria-label="{{ __('home.products.index_label') }}" data-showcase-nav>
                <ol class="pg-showcase__index">
                    @foreach ($products as $product)
                        <li class="pg-product--{{ $product['key'] }}">
                            <a class="pg-showcase__tab" href="#product-{{ $product['slug'] }}" data-showcase-tab>
                                <span class="pg-showcase__tab-number" aria-hidden="true">{{ fa_digits($product['number']) }}</span>
                                <span class="pg-showcase__tab-name">{{ $product['name'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </nav>

            <ol class="pg-showcase__list">
                @foreach ($products as $product)
                    <li class="pg-product pg-product--{{ $product['key'] }}" id="product-{{ $product['slug'] }}" data-showcase-item>
                        <article class="pg-product__body" aria-labelledby="product-{{ $product['slug'] }}-name">
                            <p class="pg-product__number" aria-hidden="true">{{ fa_digits($product['number']) }}</p>
                            <h3 class="pg-product__name" id="product-{{ $product['slug'] }}-name">{{ $product['name'] }}</h3>
                            @if ($product['description'])
                                <p class="pg-product__desc">{{ $product['description'] }}</p>
                            @endif
                            @if ($product['features'])
                                <ul class="pg-product__features">
                                    @foreach (array_slice($product['features'], 0, 3) as $feature)
                                        <li>{{ $feature }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @if ($section['card_cta'])
                                <x-ui.cta class="pg-product__more" :href="$product['url']">
                                    <span>{{ $section['card_cta'] }}<span class="visually-hidden"> — {{ $product['name'] }}</span></span>
                                    <svg class="pg-product__arrow" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="m6 3 5 5-5 5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </x-ui.cta>
                            @endif
                        </article>
                        <div class="pg-product__slot">
                            <figure class="pg-product__media pg-product--{{ $product['key'] }}" data-showcase-media>
                                <div class="pg-product__frame">
                                    <div class="pg-product__bar" aria-hidden="true">
                                        <span class="pg-product__bar-mark"></span>
                                        <span class="pg-product__bar-name">{{ $product['name'] }}</span>
                                    </div>
                                    @if ($product['media'])
                                        <img src="{{ $product['media']->url() }}" width="{{ $product['media']->width ?: 1024 }}" height="{{ $product['media']->height ?: 768 }}" alt="{{ $product['image_alt'] }}" loading="lazy" decoding="async">
                                    @else
                                        <div class="pg-product__placeholder" aria-hidden="true"></div>
                                    @endif
                                </div>
                            </figure>
                        </div>
                    </li>
                @endforeach
            </ol>

            {{-- Filled by site/products-showcase.js on wide screens only. --}}
            <div class="pg-showcase__aside" data-showcase-aside hidden>
                <div class="pg-showcase__sticky">
                    <div class="pg-showcase__stage" data-showcase-stage></div>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>
