{{--
    Businesses / "our products" (Figma 110:279 / 176:847). An ordered list
    of the published businesses (admin → businesses; the list is omitted
    when none is published);
    desktop alternates image/body per row, mobile stacks the image over the
    body. Accent colours come from the business's accent key; the section
    copy comes from Settings → صفحهٔ اصلی (a blank text is left out).
--}}
@php $section = $sections['products']; @endphp
<section class="pg-products" id="products" aria-labelledby="products-heading">
    <div class="pg-container pg-products__inner">
        <div class="pg-products__intro" data-motion="reveal">
            @if ($section['eyebrow'])
                <p class="pg-eyebrow">{{ $section['eyebrow'] }}</p>
            @endif
            <h2 id="products-heading" class="pg-products__title" data-motion="reveal-heading">
                @if ($section['heading_highlight'])<span class="pg-products__title-highlight">{{ $section['heading_highlight'] }}</span>@endif
                @if ($section['heading'])<span class="pg-products__title-rest">{{ $section['heading'] }}</span>@endif
            </h2>
            @if ($section['text'])
                <p class="pg-products__text">{{ $section['text'] }}</p>
            @endif
            @if ($section['cta'])
                <x-ui.cta class="btn pg-btn pg-btn--primary pg-products__cta" :href="$section['cta_url']">{{ $section['cta'] }}</x-ui.cta>
            @endif
        </div>

        {{-- Each card carries its stack index; CSS makes the cards sticky and layered (no JS needed), GSAP adds the scale/opacity settle. --}}
        @if (count($products))
        <ol class="pg-products__list" data-motion="product-stack">
            @foreach ($products as $product)
                <li class="pg-product pg-product--{{ $product['key'] }} @if ($loop->even) pg-product--flip @endif" style="--stack-index: {{ $loop->index }}">
                    <div class="pg-product__body">
                        <div class="pg-product__content">
                            <span class="pg-product__number" aria-hidden="true">{{ fa_digits($product['number']) }}</span>
                            <h3 class="pg-product__name">{{ $product['name'] }}</h3>
                            @if ($product['description'])
                                <p class="pg-product__desc">{{ $product['description'] }}</p>
                            @endif
                            <ul class="pg-product__features">
                                @foreach ($product['features'] as $feature)
                                    <li class="pg-feature">
                                        <img class="pg-feature__dot" src="{{ asset('images/icons/dot-'.$product['key'].'.svg') }}" width="8" height="8" alt="" aria-hidden="true">
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @if ($section['card_cta'])
                            <x-ui.cta class="pg-product__more" :href="$product['url']">
                                <span>{{ $section['card_cta'] }}</span>
                                <img class="pg-product__arrow" src="{{ asset('images/icons/arrow-'.$product['key'].'.svg') }}" width="20" height="20" alt="" aria-hidden="true">
                            </x-ui.cta>
                        @endif
                    </div>
                    <div class="pg-product__media">
                        @if ($product['media'])
                            <picture data-motion="parallax">
                                <img src="{{ $product['media']->url() }}" width="{{ $product['media']->width ?: 1024 }}" height="{{ $product['media']->height ?: 768 }}" alt="{{ $product['image_alt'] }}" loading="lazy" decoding="async">
                            </picture>
                        @else
                            <div class="pg-product__placeholder" aria-hidden="true"></div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
        @endif
    </div>
</section>
