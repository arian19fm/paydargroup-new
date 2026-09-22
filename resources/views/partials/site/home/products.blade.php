{{--
    Products / services (Figma 110:279 / 176:847). An ordered list of the
    product line-up; desktop alternates image/body per row, mobile stacks
    the image over the body. Accent colours come from the product key.
--}}
<section class="pg-products" id="products" aria-labelledby="products-heading">
    <div class="pg-container pg-products__inner">
        <div class="pg-products__intro">
            <p class="pg-eyebrow">{{ __('home.products.eyebrow') }}</p>
            <h2 id="products-heading" class="pg-products__title">
                <span class="pg-products__title-highlight">{{ __('home.products.heading_highlight') }}</span>
                <span class="pg-products__title-rest">{{ __('home.products.heading') }}</span>
            </h2>
            <p class="pg-products__text">{{ __('home.products.text') }}</p>
            <x-ui.cta class="btn pg-btn pg-btn--primary pg-products__cta" :href="$links['products'] ?? null">{{ __('home.products.cta') }}</x-ui.cta>
        </div>

        <ol class="pg-products__list">
            @foreach ($products as $product)
                <li class="pg-product pg-product--{{ $product['key'] }} @if ($loop->even) pg-product--flip @endif">
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
                        <picture>
                            <source type="image/webp" srcset="{{ asset('images/home/'.$product['image'].'.webp') }}">
                            <img src="{{ asset('images/home/'.$product['image'].'.'.$product['image_type']) }}" width="1024" height="768" alt="{{ $product['image_alt'] }}" loading="lazy" decoding="async">
                        </picture>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
