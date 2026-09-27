@extends('layouts.site')

{{--
    One opening on its own page: the same detail block and application
    form the careers page shows in modals, for deep links and visitors
    without JavaScript.
--}}

@section('body_class', 'pg-page-careers')

@section('content')
    <div class="pg-careers pg-careers--job">
        <div class="pg-container pg-careers-job">
            <nav class="pg-careers-job__back" aria-label="{{ __('careers.title') }}">
                <a href="{{ route('careers') }}#jobs">{{ __('careers.back') }}</a>
            </nav>
            <article class="pg-careers-job__panel">
                @include('partials.site.careers.job-detail', ['job' => $job, 'headingTag' => 'h1', 'applyHref' => '#apply'])
            </article>
            @unless ($job->applyHref())
                <section class="pg-careers-job__panel pg-careers-job__panel--form" id="apply" aria-labelledby="apply-{{ $job->id }}-title">
                    @include('partials.site.careers.apply-form', ['job' => $job])
                </section>
            @endunless
        </div>
    </div>
@endsection
