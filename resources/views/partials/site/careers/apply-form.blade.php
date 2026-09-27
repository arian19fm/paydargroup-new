{{--
    Application form (Figma 358:13045): handshake tile, title + text,
    phone (underlined field + hint), résumé dropzone (PDF/DOCX ≤ 5 MB) and
    the submit button. Posts to careers.apply; `$idPrefix` keeps ids unique
    when several forms share a page (one modal per opening).
--}}
@php
    $idPrefix = $idPrefix ?? 'apply-'.$job->id;
    $isThisJob = (int) session('job_applied') === $job->id || (int) old('job_id') === $job->id;
    $sent = $isThisJob && session('job_applied');
    $hasErrors = $isThisJob && $errors->hasAny(['phone', 'resume', 'website']);
@endphp
<form class="pg-apply" method="post" action="{{ route('careers.apply', $job) }}" enctype="multipart/form-data" novalidate data-apply-form>
    @csrf
    <input type="hidden" name="job_id" value="{{ $job->id }}">
    <span class="pg-job-detail__tile pg-apply__tile" aria-hidden="true"><img src="{{ asset('images/icons/careers-handshake.svg') }}" width="24" height="24" alt=""></span>
    <div class="pg-apply__intro">
        <{{ $headingTag ?? 'h2' }} class="pg-apply__title" id="{{ $idPrefix }}-title">{{ __('careers.form.title') }}</{{ $headingTag ?? 'h2' }}>
        <p class="pg-apply__text">{{ __('careers.form.text', ['title' => $job->title]) }}</p>
    </div>

    @if ($sent)
        <p class="pg-contact__status pg-contact__status--ok" role="status">{{ __('careers.form.sent') }}</p>
    @elseif ($hasErrors)
        <p class="pg-contact__status pg-contact__status--error" role="alert">{{ __('careers.form.failed') }}</p>
    @endif

    <div class="pg-apply__fields">
        <div class="pg-field pg-field--inset pg-apply__field">
            <label class="pg-field__label" for="{{ $idPrefix }}-phone">{{ __('careers.form.phone') }} <span class="pg-field__required">{{ __('home.contact.required') }}</span></label>
            <input class="pg-field__control @if ($hasErrors) @error('phone') is-invalid @enderror @endif" id="{{ $idPrefix }}-phone" name="phone" type="tel" value="{{ $isThisJob ? old('phone') : '' }}" autocomplete="tel" inputmode="tel" dir="ltr" required @if ($hasErrors && $errors->has('phone')) aria-describedby="{{ $idPrefix }}-phone-error" aria-invalid="true" @else aria-describedby="{{ $idPrefix }}-phone-hint" @endif>
            @if ($hasErrors && $errors->has('phone'))
                <span id="{{ $idPrefix }}-phone-error" class="pg-field__error">{{ $errors->first('phone') }}</span>
            @endif
            <span id="{{ $idPrefix }}-phone-hint" class="pg-apply__hint">{{ __('careers.form.phone_hint') }}</span>
        </div>

        <div class="pg-apply__upload">
            <span class="pg-field__label" id="{{ $idPrefix }}-resume-label">{{ __('careers.form.resume') }} <span class="pg-field__required">{{ __('home.contact.required') }}</span></span>
            <label class="pg-dropzone @if ($hasErrors && $errors->has('resume')) is-invalid @endif" for="{{ $idPrefix }}-resume" data-dropzone>
                <input class="pg-dropzone__input" id="{{ $idPrefix }}-resume" name="resume" type="file" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required aria-labelledby="{{ $idPrefix }}-resume-label" @if ($hasErrors && $errors->has('resume')) aria-describedby="{{ $idPrefix }}-resume-error" aria-invalid="true" @endif>
                <span class="pg-dropzone__icon" aria-hidden="true"><img src="{{ asset('images/icons/careers-upload.svg') }}" width="20" height="20" alt=""></span>
                <span class="pg-dropzone__text">
                    <span class="pg-dropzone__action">{{ __('careers.form.upload_click') }}</span>
                    <span class="pg-dropzone__or">{{ __('careers.form.upload_drop') }}</span>
                </span>
                <span class="pg-dropzone__help">{{ __('careers.form.upload_help') }}</span>
                <span class="pg-dropzone__file" data-dropzone-file hidden></span>
            </label>
            @if ($hasErrors && $errors->has('resume'))
                <span id="{{ $idPrefix }}-resume-error" class="pg-field__error">{{ $errors->first('resume') }}</span>
            @endif
        </div>

        {{-- Honeypot: hidden from people, filled by bots, rejected by validation. --}}
        <div class="pg-contact__hp" aria-hidden="true">
            <label for="{{ $idPrefix }}-website">Website</label>
            <input id="{{ $idPrefix }}-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>
    </div>

    <div class="pg-apply__footer">
        <button class="pg-job-detail__submit" type="submit">
            <span>{{ __('careers.form.submit') }}</span>
            <img src="{{ asset('images/icons/careers-document.svg') }}" width="20" height="20" alt="" aria-hidden="true">
        </button>
    </div>
</form>
