@extends('layouts.site')

{{--
    Careers page (Figma 339:352 desktop / 348:79 mobile). Intro + photo,
    the benefits of working at the group (four designed cards, copy from
    Settings → careers with the designed text as fallback) and the active
    job openings (admin → فرصت‌های شغلی), seven per page.
--}}

@section('body_class', 'pg-page-careers')

@section('content')
    <div class="pg-careers">
        <section class="pg-careers-hero" aria-labelledby="careers-heading">
            <div class="pg-container pg-careers-hero__inner">
                <header class="pg-careers-hero__head" data-motion="reveal">
                    <p class="pg-careers-hero__eyebrow">{{ $copy['hero_eyebrow'] }}</p>
                    <h1 id="careers-heading" class="pg-careers-hero__title" data-motion="reveal-heading">{{ $copy['hero_title'] }}</h1>
                </header>
                <figure class="pg-careers-hero__media">
                    <picture data-motion="parallax">
                        @if ($heroImage)
                            <img src="{{ $heroImage->url() }}" width="{{ $heroImage->width ?: 1294 }}" height="{{ $heroImage->height ?: 391 }}" alt="{{ $heroImage->alt_text ?: __('careers.hero_alt') }}" fetchpriority="high" decoding="async">
                        @else
                            <source type="image/webp" srcset="{{ asset('images/careers/hero.webp') }}">
                            <img src="{{ asset('images/careers/hero.jpg') }}" width="1500" height="725" alt="{{ __('careers.hero_alt') }}" fetchpriority="high" decoding="async">
                        @endif
                    </picture>
                </figure>
            </div>
        </section>

        <section class="pg-careers-benefits" aria-labelledby="careers-benefits-heading">
            <div class="pg-container pg-careers-benefits__inner">
                <div class="pg-careers-benefits__intro">
                    <p class="pg-careers-benefits__eyebrow" data-motion="reveal">{{ $copy['benefits_eyebrow'] }}</p>
                    <div class="pg-careers-benefits__content" data-motion="reveal">
                        <h2 id="careers-benefits-heading" class="pg-careers-benefits__title" data-motion="reveal-heading">
                            <span class="pg-careers-benefits__title-highlight">{{ $copy['benefits_title_highlight'] }}</span>
                            <span class="pg-careers-benefits__title-rest">{{ $copy['benefits_title'] }}</span>
                        </h2>
                        <p class="pg-careers-benefits__text">{{ $copy['benefits_text'] }}</p>
                        <a class="pg-careers-benefits__cta" href="{{ $copy['benefits_cta_url'] }}">{{ $copy['benefits_cta_label'] }}</a>
                    </div>
                </div>

                @if ($benefits)
                    <ul class="pg-careers-benefits__cards" data-motion="reveal-group">
                        @foreach ($benefits as $benefit)
                            <li class="pg-benefit-card @if ($loop->first) pg-benefit-card--lead @endif">
                                <span class="pg-benefit-card__icon" aria-hidden="true">
                                    @foreach ($benefit['icons'] as $icon)
                                        <img src="{{ asset('images/icons/'.$icon) }}" width="24" height="24" alt="">
                                    @endforeach
                                </span>
                                <div class="pg-benefit-card__body">
                                    <h3 class="pg-benefit-card__title">{{ $benefit['title'] }}</h3>
                                    @if ($benefit['text'] !== '')
                                        <p class="pg-benefit-card__text">{{ $benefit['text'] }}</p>
                                    @endif
                                </div>
                                @if ($loop->first)
                                    <img class="pg-benefit-card__pointer" src="{{ asset('images/icons/careers-pointer.svg') }}" width="24" height="24" alt="" aria-hidden="true">
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="pg-careers-jobs" aria-labelledby="careers-jobs-heading" id="jobs">
            <div class="pg-container pg-careers-jobs__inner">
                <div class="pg-careers-jobs__intro" data-motion="reveal">
                    <p class="pg-careers-jobs__eyebrow">{{ $copy['jobs_eyebrow'] }}</p>
                    <h2 id="careers-jobs-heading" class="pg-careers-jobs__title" data-motion="reveal-heading">
                        <span class="pg-careers-jobs__title-highlight">{{ $copy['jobs_title_highlight'] }}</span>
                        <span class="pg-careers-jobs__title-rest">{{ $copy['jobs_title'] }}</span>
                    </h2>
                    <p class="pg-careers-jobs__text">{{ $copy['jobs_text'] }}</p>
                </div>

                <div class="pg-careers-jobs__list-wrap">
                    @if ($jobs->isEmpty())
                        <p class="pg-careers-jobs__empty">{{ $copy['jobs_empty'] }}</p>
                    @else
                        <ul class="pg-careers-jobs__list" data-motion="reveal-group">
                            @foreach ($jobs as $job)
                                @php $tone = $job->toneKey(); @endphp
                                <li class="pg-job pg-job--{{ $tone }}">
                                    <div class="pg-job__main">
                                        <div class="pg-job__head">
                                            <h3 class="pg-job__title">{{ $job->title }}</h3>
                                            @if ($job->category)
                                                <span class="pg-job__badge"><span>{{ $job->category }}</span><span class="pg-job__dot" aria-hidden="true"></span></span>
                                            @endif

                                        </div>
                                        @if ($job->description)
                                            <p class="pg-job__desc">{{ $job->description }}</p>
                                        @endif
                                        @if ($job->location || $job->employment_type)
                                            <ul class="pg-job__meta">
                                                @if ($job->location)
                                                    <li><img src="{{ asset('images/icons/careers-pin.svg') }}" width="20" height="20" alt="" aria-hidden="true"><span>{{ $job->location }}</span></li>
                                                @endif
                                                @if ($job->employment_type)
                                                    <li><img src="{{ asset('images/icons/careers-clock.svg') }}" width="20" height="20" alt="" aria-hidden="true"><span>{{ $job->employment_type }}</span></li>
                                                @endif
                                            </ul>
                                        @endif
                                    </div>
                                    @if ($href = $job->applyHref())
                                        <a class="pg-job__apply" href="{{ $href }}"{!! str_starts_with($href, 'mailto:') ? '' : ' target="_blank" rel="noopener"' !!} aria-label="{{ __('careers.apply_label', ['title' => $job->title]) }}">
                                            <span>{{ $copy['apply'] }}</span>
                                            <img src="{{ asset('images/icons/careers-apply.svg') }}" width="20" height="20" alt="" aria-hidden="true">
                                        </a>
                                    @else
                                        {{-- With JavaScript the link opens the detail modal; without it, the opening's own page. --}}
                                        <a class="pg-job__apply" href="{{ route('careers.show', $job) }}" data-bs-toggle="modal" data-bs-target="#job-modal-{{ $job->id }}" aria-label="{{ __('careers.apply_label', ['title' => $job->title]) }}">
                                            <span>{{ $copy['apply'] }}</span>
                                            <img src="{{ asset('images/icons/careers-apply.svg') }}" width="20" height="20" alt="" aria-hidden="true">
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        @if ($jobs->hasPages())
                            <nav class="pg-careers-jobs__pages" aria-label="{{ __('careers.pagination') }}">
                                @foreach ($jobs->getUrlRange(1, $jobs->lastPage()) as $number => $url)
                                    @if ($number === $jobs->currentPage())
                                        <span class="pg-careers-jobs__page is-active" aria-current="page"><span class="visually-hidden">{{ __('careers.page', ['number' => fa_digits($number)]) }}</span></span>
                                    @else
                                        <a class="pg-careers-jobs__page" href="{{ $url }}#jobs"><span class="visually-hidden">{{ __('careers.page', ['number' => fa_digits($number)]) }}</span></a>
                                    @endif
                                @endforeach
                            </nav>
                        @endif
                    @endif
                </div>
            </div>
        </section>
    </div>

    {{-- Detail + application modals, one pair per opening (Figma 350:6108 / 358:13045). --}}
    @foreach ($jobs as $job)
        @continue($job->applyHref())
        @php $applied = (int) session('job_applied') === $job->id || (int) old('job_id') === $job->id; @endphp
        <div class="modal fade pg-modal pg-modal--detail" id="job-modal-{{ $job->id }}" tabindex="-1" aria-labelledby="job-detail-title-{{ $job->id }}" aria-hidden="true">
            <div class="modal-dialog pg-modal__dialog">
                <div class="modal-content pg-modal__content">
                    <button type="button" class="pg-modal__close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"><img src="{{ asset('images/icons/careers-close.svg') }}" width="24" height="24" alt="" aria-hidden="true"></button>
                    @include('partials.site.careers.job-detail', ['job' => $job, 'applyTarget' => 'job-apply-'.$job->id])
                </div>
            </div>
        </div>
        <div class="modal fade pg-modal pg-modal--apply" id="job-apply-{{ $job->id }}" tabindex="-1" aria-labelledby="apply-{{ $job->id }}-title" aria-hidden="true" @if ($applied) data-auto-open @endif>
            <div class="modal-dialog pg-modal__dialog pg-modal__dialog--apply">
                <div class="modal-content pg-modal__content">
                    <button type="button" class="pg-modal__close" data-bs-dismiss="modal" aria-label="{{ __('ui.close') }}"><img src="{{ asset('images/icons/careers-close.svg') }}" width="24" height="24" alt="" aria-hidden="true"></button>
                    @include('partials.site.careers.apply-form', ['job' => $job])
                </div>
            </div>
        </div>
    @endforeach
@endsection
