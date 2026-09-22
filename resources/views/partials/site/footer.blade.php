{{--
    Site footer (Figma 159:290 desktop / 178:449 mobile). Every value is
    managed content: pages column = "footer" menu, legal links = "legal"
    menu, services = the product line-up, contact + social = settings.
    Empty values are omitted rather than replaced with placeholder data.
--}}
@php
    $menus = app(\App\Support\Menus\MenuRepository::class);
    $footerItems = $menus->tree('footer');
    $legalItems = $menus->tree('legal');
    $siteName = \App\Support\Seo\SeoManager::siteName();
    $brandText = settings('general.footer_text');
    $copyrightName = settings('general.copyright_text') ?: $siteName;
    $contact = [
        'email' => settings('contact.email'),
        'phone' => settings('contact.phone'),
        'hours' => settings('contact.working_hours'),
    ];
    $social = collect([
        'instagram' => settings('social.instagram'),
        'telegram' => settings('social.telegram'),
        'linkedin' => settings('social.linkedin'),
    ])->filter();
    $services = collect(config('home.products', []))->map(fn ($product) => [
        'label' => __('home.products.items.'.$product['key'].'.plain_name'),
        'slug' => $product['slug'],
    ]);
    $serviceUrls = app(\App\Support\Home\HomePage::class)->pageUrls($services->pluck('slug')->all());
    $year = \App\Support\Localization\Jalali::year(now());
@endphp
<footer class="pg-footer mt-auto">
    <div class="pg-footer__inner">
        <div class="pg-footer__top" data-motion="reveal">
            <div class="pg-footer__brand">
                <a class="pg-footer__logo" href="{{ route('home') }}">
                    <img src="{{ asset('images/brand/paydar-logo-blue.png') }}" width="40" height="52" alt="{{ $siteName }}">
                </a>
                @if ($brandText)
                    <p class="pg-footer__tagline">{{ $brandText }}</p>
                @endif
                <img class="pg-footer__trust" src="{{ asset('images/brand/enamad.png') }}" width="60" height="60" alt="{{ __('home.footer.trust_badge_alt') }}" loading="lazy" decoding="async">
            </div>

            <div class="pg-footer__columns">
                @if ($footerItems)
                    <nav class="pg-footer__col pg-footer__col--pages" aria-label="{{ __('nav.footer_navigation') }}">
                        <h2 class="pg-footer__heading">{{ __('home.footer.pages') }}</h2>
                        <ul class="pg-footer__list">
                            @foreach ($footerItems as $item)
                                <li><a href="{{ $item['url'] }}" @if ($item['target'] === '_blank') target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                <div class="pg-footer__col pg-footer__col--services">
                    <h2 class="pg-footer__heading">{{ __('home.footer.services') }}</h2>
                    <ul class="pg-footer__list pg-footer__list--services">
                        @foreach ($services as $service)
                            <li><x-ui.cta :href="$serviceUrls[$service['slug']] ?? null">{{ $service['label'] }}</x-ui.cta></li>
                        @endforeach
                    </ul>
                </div>

                @if (array_filter($contact))
                    <div class="pg-footer__col pg-footer__col--contact">
                        <h2 class="pg-footer__heading">{{ __('home.footer.contact') }}</h2>
                        <dl class="pg-footer__contact">
                            @if ($contact['email'])
                                <div>
                                    <dt>{{ __('home.footer.email') }}</dt>
                                    <dd><a href="mailto:{{ $contact['email'] }}" dir="ltr">{{ $contact['email'] }}</a></dd>
                                </div>
                            @endif
                            @if ($contact['phone'])
                                <div>
                                    <dt>{{ __('home.footer.phone') }}</dt>
                                    <dd><a href="tel:{{ \App\Support\Localization\PersianNumbers::toLatin($contact['phone']) }}" dir="ltr">{{ fa_digits($contact['phone']) }}</a></dd>
                                </div>
                            @endif
                            @if ($contact['hours'])
                                <div>
                                    <dt>{{ __('home.footer.hours') }}</dt>
                                    <dd>{{ fa_digits($contact['hours']) }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                @if ($social->isNotEmpty())
                    <div class="pg-footer__col pg-footer__col--social">
                        <h2 class="pg-footer__heading">{{ __('home.footer.follow') }}</h2>
                        <ul class="pg-footer__list pg-footer__social">
                            @foreach ($social as $network => $url)
                                <li>
                                    <a href="{{ $url }}" target="_blank" rel="noopener">
                                        <img src="{{ asset('images/icons/'.$network.'.svg') }}" width="20" height="20" alt="" aria-hidden="true">
                                        <span>{{ __('home.footer.'.$network) }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <div class="pg-footer__bottom">
            <p class="pg-footer__copyright">{{ __('home.footer.copyright', ['year' => fa_digits($year), 'name' => $copyrightName]) }}</p>
            @if ($legalItems)
                <nav class="pg-footer__legal" aria-label="{{ __('home.footer.legal_navigation') }}">
                    <ul>
                        @foreach ($legalItems as $item)
                            <li><a href="{{ $item['url'] }}" @if ($item['target'] === '_blank') target="_blank" rel="noopener" @endif>{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</footer>
