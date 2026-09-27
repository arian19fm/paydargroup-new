{{--
    Opening detail (Figma 350:6108): handshake tile, title + category
    badge, meta, hairline, the description (JobBody: headings, bullets,
    paragraphs), the spec rows and the "send application" button that
    leads to the form. `$applyTarget` is the form modal id (with JS) — the
    button is a plain link to the form on the opening's own page otherwise.
--}}
@php $tone = $job->toneKey(); @endphp
<div class="pg-job-detail pg-job--{{ $tone }}">
    <span class="pg-job-detail__tile" aria-hidden="true"><img src="{{ asset('images/icons/careers-handshake.svg') }}" width="24" height="24" alt=""></span>
    <div class="pg-job-detail__head">
        <div class="pg-job-detail__title-row">
            <{{ $headingTag ?? 'h2' }} class="pg-job-detail__title" id="{{ $titleId ?? 'job-detail-title-'.$job->id }}">{{ $job->title }}</{{ $headingTag ?? 'h2' }}>
            @if ($job->category)
                <span class="pg-job__badge"><span>{{ $job->category }}</span><span class="pg-job__dot" aria-hidden="true"></span></span>
            @endif
        </div>
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
    <hr class="pg-job-detail__rule">
    @if (trim((string) $job->body) !== '')
        <div class="pg-job-detail__body">{!! \App\Support\Content\JobBody::toHtml($job->body) !!}</div>
    @elseif ($job->description)
        <div class="pg-job-detail__body"><p>{{ $job->description }}</p></div>
    @endif
    @if ($specs = $job->specList())
        <dl class="pg-job-detail__specs">
            @foreach ($specs as $spec)
                <div class="pg-job-detail__spec">
                    <dt>{{ $spec['label'] }}</dt>
                    <dd>{{ fa_digits($spec['value']) }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
    <div class="pg-job-detail__actions">
        @if ($href = $job->applyHref())
            <a class="pg-job-detail__submit" href="{{ $href }}"{!! str_starts_with($href, 'mailto:') ? '' : ' target="_blank" rel="noopener"' !!}>
                <span>{{ __('careers.form.open') }}</span>
                <img src="{{ asset('images/icons/careers-document.svg') }}" width="20" height="20" alt="" aria-hidden="true">
            </a>
        @else
            <a class="pg-job-detail__submit" href="{{ $applyHref ?? route('careers.show', $job).'#apply' }}" @isset($applyTarget) data-bs-toggle="modal" data-bs-target="#{{ $applyTarget }}" @endisset>
                <span>{{ __('careers.form.open') }}</span>
                <img src="{{ asset('images/icons/careers-document.svg') }}" width="20" height="20" alt="" aria-hidden="true">
            </a>
        @endif
    </div>
</div>
