{{--
    Businesses / "our products" (Figma 110:279 / 176:847). An ordered list
    of the published businesses (the designed line-up until one exists);
    desktop alternates image/body per row, mobile stacks the image over the
    body. Accent colours come from the business's accent key; the section
    copy comes from Settings → home with the designed text as fallback.
--}}
<section class="pg-products" id="products" aria-labelledby="products-heading">
    <div class="pg-container pg-products__inner">
        <div class="pg-products__intro" data-motion="reveal">
            <p class="pg-eyebrow">{{ $productsSection['eyebrow'] }}</p>
            <h2 id="products-heading" class="pg-products__title" data-motion="reveal-heading">
                <span class="pg-products__title-highlight">{{ $productsSection['heading_highlight'] }}</span>
                <span class="pg-products__title-rest">{{ $productsSection['heading'] }}</span>
            </h2>
            <p class="pg-products__text">{{ $productsSection['text'] }}</p>
            <x-ui.cta class="btn pg-btn pg-btn--primary pg-products__cta" :href="$productsSection['cta_url']">{{ $productsSection['cta'] }}</x-ui.cta>
        </div>

        {{-- Each card carries its stack index; CSS makes the cards sticky and layered (no JS needed), GSAP adds the scale/opacity settle. --}}
        <ol class="pg-products__list" data-motion="product-stack">
            @foreach ($products as $product)
                <li class="pg-product pg-product--{{ $product['key'] }} @if ($loop->even) pg-product--flip @endif" style="--stack-index: {{ $loop->index }}">
                    <div class="pg-product__body">
                        <div class="pg-product__content">
                            <span class="pg-product__number" aria-hidden="true">{{ fa_digits($product['number']) }}</span>
                            <h3 class="pg-product__name">{{ $product['name'] }}</h3>
                            <p class="pg-product__desc">{{ $product['description'] }}</p>
                            <ul class="pg-product__features">
                                @foreach ($product['features'] as $feature)
                                    <li class="pg-feature">
                                        <img class="pg-feature__dot" src="{{ asset('images/icons/dot-'.$product['key'].'.svg') }}" width="8" height="8" alt="" aria-hidden="true">
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <x-ui.cta class="pg-product__more" :href="$product['url']">
                            <span>{{ __('home.products.more') }}</span>
                            <img class="pg-product__arrow" src="{{ asset('images/icons/arrow-'.$product['key'].'.svg') }}" width="20" height="20" alt="" aria-hidden="true">
                        </x-ui.cta>
                    </div>
                    <div class="pg-product__media">
                        @if ($product['media'])
                            <picture data-motion="parallax">
                                <img src="{{ $product['media']->url() }}" width="{{ $product['media']->width ?: 1024 }}" height="{{ $product['media']->height ?: 768 }}" alt="{{ $product['image_alt'] }}" loading="lazy" decoding="async">
                            </picture>
                        @elseif ($product['image'])
                            <picture data-motion="parallax">
                                <source type="image/webp" srcset="{{ asset('images/home/'.$product['image'].'.webp') }}">
                                <img src="{{ asset('images/home/'.$product['image'].'.'.$product['image_type']) }}" width="1024" height="768" alt="{{ $product['image_alt'] }}" loading="lazy" decoding="async">
                            </picture>
                        @else
                            <div class="pg-product__placeholder" aria-hidden="true"></div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
