@extends('layouts.site')

{{--
    Contact page (Figma 258:460 desktop / 258:622 mobile). Photo + request
    form (the same form the home page carries, posting to contact.store),
    then the contact channels from Settings → contact and an optional map.
--}}

@section('content')
    <div class="pg-contact-page">
        <section class="pg-contact-page__hero pg-container" aria-labelledby="contact-heading">
            <figure class="pg-contact-page__photo">
                {{-- Parallax drives the wrapper: the <img> carries the CSS mirror, which a transform tween would undo. --}}
                <picture data-motion="parallax">
                    <source type="image/webp" srcset="{{ asset('images/home/contact.webp') }}">
                    <img src="{{ asset('images/home/contact.jpg') }}" width="1737" height="905" alt="" fetchpriority="high" decoding="async">
                </picture>
            </figure>

            <form class="pg-contact-page__form" method="post" action="{{ route('contact.store') }}" novalidate data-motion="reveal">
                @csrf
                <div class="pg-contact-page__intro">
                    <p class="pg-contact-page__eyebrow">{{ __('contact.eyebrow') }}</p>
                    <h1 id="contact-heading" class="pg-contact-page__title">{{ __('home.contact.heading') }}</h1>
                    <p class="pg-contact-page__text">{{ __('home.contact.text') }}</p>
                </div>
                <div class="pg-contact-page__fields" id="contact">
                    @include('partials.site.contact-fields', ['idPrefix' => 'contact-page'])
                </div>
                <button class="btn pg-btn pg-btn--solid pg-contact-page__submit" type="submit">{{ __('home.contact.submit') }}</button>
            </form>
        </section>

        <section class="pg-contact-page__ways pg-container" aria-labelledby="contact-ways-heading">
            <div class="pg-contact-page__ways-intro" data-motion="reveal">
                <p class="pg-contact-page__eyebrow">{{ __('contact.ways_eyebrow') }}</p>
                <h2 id="contact-ways-heading" class="pg-contact-page__title">{{ __('contact.ways_heading') }}</h2>
                <p class="pg-contact-page__text">{{ __('contact.ways_text') }}</p>
            </div>

            @if ($address || $email || $phone)
                <ul class="pg-contact-page__channels" data-motion="reveal-group">
                    @if ($address)
                        <li class="pg-channel pg-channel--address">
                            <span class="pg-channel__icon" aria-hidden="true"><img src="{{ asset('images/icons/contact-location.svg') }}" width="24" height="24" alt=""></span>
                            <span class="pg-channel__body">
                                <span class="pg-channel__value">{{ $address }}</span>
                                <span class="pg-channel__label">{{ __('contact.address_label') }}</span>
                            </span>
                        </li>
                    @endif
                    @if ($phone)
                        <li class="pg-channel pg-channel--phone">
                            <span class="pg-channel__icon" aria-hidden="true"><img src="{{ asset('images/icons/contact-call.svg') }}" width="24" height="24" alt=""></span>
                            <span class="pg-channel__body">
                                <a class="pg-channel__value" href="tel:{{ preg_replace('/[^\d+]/', '', \App\Support\Localization\PersianNumbers::toLatin($phone)) }}" dir="ltr">{{ fa_digits($phone) }}</a>
                                <span class="pg-channel__label">{{ __('contact.phone_label') }}@if ($hours) <span class="pg-channel__hint">({{ fa_digits($hours) }})</span>@endif</span>
                            </span>
                        </li>
                    @endif
                    @if ($email)
                        <li class="pg-channel pg-channel--email">
                            <span class="pg-channel__icon" aria-hidden="true"><img src="{{ asset('images/icons/contact-email.svg') }}" width="24" height="24" alt=""></span>
                            <span class="pg-channel__body">
                                <a class="pg-channel__value" href="mailto:{{ $email }}" dir="ltr">{{ $email }}</a>
                                <span class="pg-channel__label">{{ __('contact.email_label') }}</span>
                            </span>
                        </li>
                    @endif
                </ul>
            @endif

            @if ($map)
                <div class="pg-contact-page__map" data-motion="reveal">
                    @if ($mapUrl)<a href="{{ $mapUrl }}" target="_blank" rel="noopener" aria-label="{{ __('contact.map_link') }}">@endif
                        <img src="{{ $map->url() }}" width="{{ $map->width ?: 1216 }}" height="{{ $map->height ?: 516 }}" alt="{{ $map->alt_text ?: __('contact.map_alt') }}" loading="lazy" decoding="async">
                        <img class="pg-contact-page__pin" src="{{ asset('images/icons/map-pin.svg') }}" width="24" height="32" alt="" aria-hidden="true">
                    @if ($mapUrl)</a>@endif
                </div>
            @endif
        </section>
    </div>
@endsection
