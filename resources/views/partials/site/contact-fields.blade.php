{{--
    Contact form fields + status, shared by the home page card and the
    contact page. The surrounding <form> (action, CSRF, motion hooks) is
    owned by the including view; `idPrefix` keeps ids unique per page.
--}}
@php
    $idPrefix = $idPrefix ?? 'contact';
    $sent = session('contact_sent') === true;
    $hasErrors = $errors->hasAny(['name', 'phone', 'message', 'website']);
@endphp
@if ($sent)
    <p class="pg-contact__status pg-contact__status--ok" role="status">{{ __('home.contact.sent') }}</p>
@elseif ($hasErrors)
    <p class="pg-contact__status pg-contact__status--error" role="alert">{{ __('home.contact.failed') }}</p>
@endif

<div class="pg-field pg-field--inset">
    <label class="pg-field__label" for="{{ $idPrefix }}-name">{{ __('home.contact.name') }}</label>
    <input class="pg-field__control @error('name') is-invalid @enderror" id="{{ $idPrefix }}-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" @error('name') aria-describedby="{{ $idPrefix }}-name-error" aria-invalid="true" @enderror>
    @error('name')<span id="{{ $idPrefix }}-name-error" class="pg-field__error">{{ $message }}</span>@enderror
</div>

<div class="pg-field pg-field--inset">
    <label class="pg-field__label" for="{{ $idPrefix }}-phone">{{ __('home.contact.phone') }} <span class="pg-field__required">{{ __('home.contact.required') }}</span></label>
    <input class="pg-field__control @error('phone') is-invalid @enderror" id="{{ $idPrefix }}-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" dir="ltr" required @error('phone') aria-describedby="{{ $idPrefix }}-phone-error" aria-invalid="true" @enderror>
    @error('phone')<span id="{{ $idPrefix }}-phone-error" class="pg-field__error">{{ $message }}</span>@enderror
</div>

<div class="pg-field pg-field--textarea">
    <label class="pg-field__label" for="{{ $idPrefix }}-message">{{ __('home.contact.message') }} <span class="pg-field__required">{{ __('home.contact.required') }}</span></label>
    <textarea class="pg-field__control @error('message') is-invalid @enderror" id="{{ $idPrefix }}-message" name="message" rows="3" maxlength="2000" required @error('message') aria-describedby="{{ $idPrefix }}-message-error" aria-invalid="true" @enderror>{{ old('message') }}</textarea>
    @error('message')<span id="{{ $idPrefix }}-message-error" class="pg-field__error">{{ $message }}</span>@enderror
</div>

{{-- Honeypot: hidden from people, filled by bots, rejected by validation. --}}
<div class="pg-contact__hp" aria-hidden="true">
    <label for="{{ $idPrefix }}-website">Website</label>
    <input id="{{ $idPrefix }}-website" name="website" type="text" tabindex="-1" autocomplete="off">
</div>
