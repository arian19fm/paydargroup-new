@extends('layouts.site')

{{--
    App download page (Figma 558:766 desktop / 578:1074 mobile). Hero with
    quick-jump chips, then one card per published business whose app is
    switched on in the admin: coloured cover (logo tile, QR on desktop,
    name + tagline over the enlarged logo), description, Android / iPhone
    buttons and the website row. The iPhone button opens the "add to home
    screen" guide (not in the design file; without JS it is a plain link
    to the web app address).
--}}

@php
    $icon = fn (string $name) => asset("images/apps/{$name}.svg");
@endphp

@section('content')
    <div class="pg-apps">
        <header class="pg-apps__hero">
            <div class="pg-container">
                <p class="pg-apps__eyebrow">{{ __('apps.eyebrow') }}</p>
                <h1 class="pg-apps__title"><span>{{ __('apps.heading_accent') }}</span> {{ __('apps.heading') }}</h1>
                <p class="pg-apps__lead">{{ __('apps.lead') }}</p>

                @if ($apps->isNotEmpty())
                    <nav class="pg-apps__jump" aria-labelledby="apps-jump-label">
                        <p class="pg-apps__jump-label" id="apps-jump-label">{{ __('apps.quick_jump') }}</p>
                        <ul class="pg-apps__chips">
                            @foreach ($apps as $app)
                                <li>
                                    <a class="pg-apps__chip" href="#app-{{ $app->slug }}">
                                        <img src="{{ $icon('download') }}" width="24" height="24" alt="" aria-hidden="true">
                                        <span>{{ $app->title }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif
            </div>
        </header>

        <section class="pg-apps__section" aria-labelledby="apps-section-title">
            <div class="pg-container">
                <header class="pg-apps__section-head">
                    <p class="pg-apps__section-eyebrow">{{ __('apps.section_eyebrow') }}</p>
                    <h2 class="pg-apps__section-title" id="apps-section-title"><span>{{ __('apps.section_heading_accent') }}</span> {{ __('apps.section_heading') }}</h2>
                </header>

                @if ($apps->isEmpty())
                    <p class="pg-apps__empty">{{ __('apps.empty') }}</p>
                @else
                    <ul class="pg-apps__cards">
                        @foreach ($apps as $app)
                            @php
                                $name = $app->appTitle();
                                $androidUrl = $app->androidUrl();
                                $host = $app->websiteHost();
                                $logo = $app->appLogo?->isImage() ? $app->appLogo : null;
                                $qr = $app->appQr?->isImage() ? $app->appQr : null;
                            @endphp
                            <li class="pg-apps__card">
                                <article class="pg-app-card" id="app-{{ $app->slug }}" @if ($app->appStyle()) style="{{ $app->appStyle() }}" @endif>
                                    <div class="pg-app-card__cover">
                                        @if ($logo)
                                            <span class="pg-app-card__mark" style="--pg-app-mark: url('{{ $logo->url() }}')" aria-hidden="true"></span>
                                        @endif
                                        <div class="pg-app-card__top">
                                            @if ($logo)
                                                <span class="pg-app-card__logo">
                                                    <img src="{{ $logo->url() }}" width="{{ $logo->width ?: 52 }}" height="{{ $logo->height ?: 52 }}" alt="{{ __('apps.logo_alt', ['name' => $name]) }}" loading="lazy" decoding="async">
                                                </span>
                                            @endif
                                            @if ($qr)
                                                <figure class="pg-app-card__qr">
                                                    <span class="pg-app-card__qr-box">
                                                        <img src="{{ $qr->url() }}" width="68" height="68" alt="{{ __('apps.qr_alt', ['name' => $name]) }}" loading="lazy" decoding="async">
                                                    </span>
                                                    <figcaption>{{ __('apps.qr_hint') }}</figcaption>
                                                </figure>
                                            @endif
                                        </div>
                                        <div class="pg-app-card__identity">
                                            <h3 class="pg-app-card__name">{{ $name }}</h3>
                                            @if ($app->tagline)
                                                <p class="pg-app-card__tagline">{{ $app->tagline }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="pg-app-card__body">
                                        @if ($app->appText())
                                            <p class="pg-app-card__text">{{ $app->appText() }}</p>
                                        @endif

                                        <div class="pg-app-card__platforms">
                                            @if ($androidUrl)
                                                <a class="pg-platform pg-platform--android" href="{{ $androidUrl }}"
                                                   @if ($app->androidIsFile()) download="{{ $app->slug }}.apk" @else target="_blank" rel="noopener" @endif
                                                   aria-label="{{ __('apps.android_label', ['name' => $name]) }}">
                                                    <img src="{{ $icon('android') }}" width="24" height="24" alt="" aria-hidden="true">
                                                    <span class="pg-platform__text">
                                                        <strong>{{ __('apps.android') }}</strong>
                                                        <small>{{ __($app->androidIsFile() ? 'apps.android_file' : 'apps.android_link') }}</small>
                                                    </span>
                                                </a>
                                            @endif
                                            @if ($app->app_ios_url)
                                                <a class="pg-platform pg-platform--ios" href="{{ $app->app_ios_url }}" rel="noopener"
                                                   data-bs-toggle="modal" data-bs-target="#ios-guide-{{ $app->slug }}"
                                                   aria-label="{{ __('apps.ios_label', ['name' => $name]) }}">
                                                    <img src="{{ $icon('apple') }}" width="24" height="24" alt="" aria-hidden="true">
                                                    <span class="pg-platform__text">
                                                        <strong>{{ __('apps.ios') }}</strong>
                                                        <small>{{ __('apps.ios_hint') }}</small>
                                                    </span>
                                                </a>
                                            @endif
                                        </div>

                                        @if ($host)
                                            <a class="pg-app-card__site" href="{{ $app->website_url }}" target="_blank" rel="noopener" aria-label="{{ __('apps.visit_label', ['name' => $name]) }}">
                                                <span class="pg-app-card__site-info">
                                                    <img src="{{ $icon('globe') }}" width="22" height="22" alt="" aria-hidden="true">
                                                    <span class="pg-app-card__site-text">
                                                        <small>{{ __('apps.website') }}</small>
                                                        <span dir="ltr">{{ $host }}</span>
                                                    </span>
                                                </span>
                                                <span class="pg-app-card__go">
                                                    <span>{{ __('apps.visit') }}</span>
                                                    <span class="pg-app-card__arrow" style="--pg-icon: url('{{ $icon('arrow-left') }}')" aria-hidden="true"></span>
                                                </span>
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>

    @foreach ($apps as $app)
        @continue(! $app->app_ios_url)
        <div class="modal fade pg-modal" id="ios-guide-{{ $app->slug }}" tabindex="-1" aria-labelledby="ios-guide-title-{{ $app->slug }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered pg-modal__dialog pg-modal__dialog--apply">
                <div class="modal-content pg-modal__content">
                    <button type="button" class="pg-modal__close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"><img src="{{ asset('images/icons/careers-close.svg') }}" width="24" height="24" alt="" aria-hidden="true"></button>
                    <div class="pg-ios-guide">
                        <h2 class="pg-ios-guide__title" id="ios-guide-title-{{ $app->slug }}">{{ __('apps.ios_guide.title', ['name' => $app->appTitle()]) }}</h2>
                        <p class="pg-ios-guide__intro">{{ __('apps.ios_guide.intro') }}</p>
                        <ol class="pg-ios-guide__steps">
                            @foreach (__('apps.ios_guide.steps') as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                        <div class="pg-ios-guide__address">
                            <small>{{ __('apps.ios_guide.address') }}</small>
                            <span dir="ltr">{{ $app->app_ios_url }}</span>
                        </div>
                        <a class="pg-platform pg-platform--android pg-ios-guide__open" href="{{ $app->app_ios_url }}" rel="noopener">{{ __('apps.ios_guide.open') }}</a>
                        <p class="pg-ios-guide__note">{{ __('apps.ios_guide.note') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
