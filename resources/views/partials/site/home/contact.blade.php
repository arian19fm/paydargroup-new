{{--
    Contact (Figma 142:289 / 178:424). A real form: POST /contact with CSRF,
    server-side validation (StoreContactRequest), a honeypot field and a
    per-IP rate limit. On success the visitor lands back here with a
    confirmation; nothing is e-mailed in this phase.
--}}
@php
    $sent = session('contact_sent') === true;
    $hasErrors = $errors->hasAny(['name', 'phone', 'message', 'website']);
@endphp
<section class="pg-contact" id="contact" aria-labelledby="contact-heading">
    <picture class="pg-contact__media" data-motion="parallax">
        <source type="image/webp" srcset="{{ asset('images/home/contact.webp') }}">
        <img src="{{ asset('images/home/contact.jpg') }}" width="1737" height="905" alt="" loading="lazy" decoding="async">
    </picture>
    <div class="pg-contact__overlay" aria-hidden="true"></div>

    <div class="pg-contact__inner">
        <form class="pg-contact__card" method="post" action="{{ route('contact.store') }}" novalidate data-motion="reveal">
            @csrf
            <div class="pg-contact__intro">
                <p class="pg-contact__eyebrow">{{ __('home.contact.eyebrow') }}</p>
                <h2 id="contact-heading" class="pg-contact__title">{{ __('home.contact.heading') }}</h2>
                <p class="pg-contact__text">{{ __('home.contact.text') }}</p>
            </div>

            @if ($sent)
                <p class="pg-contact__status pg-contact__status--ok" role="status">{{ __('home.contact.sent') }}</p>
            @elseif ($hasErrors)
                <p class="pg-contact__status pg-contact__status--error" role="alert">{{ __('home.contact.failed') }}</p>
            @endif

            <div class="pg-field pg-field--inset">
                <label class="pg-field__label" for="contact-name">{{ __('home.contact.name') }}</label>
                <input class="pg-field__control @error('name') is-invalid @enderror" id="contact-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" @error('name') aria-describedby="contact-name-error" aria-invalid="true" @enderror>
                @error('name')<span id="contact-name-error" class="pg-field__error">{{ $message }}</span>@enderror
            </div>

            <div class="pg-field pg-field--inset">
                <label class="pg-field__label" for="contact-phone">{{ __('home.contact.phone') }} <span class="pg-field__required">{{ __('home.contact.required') }}</span></label>
                <input class="pg-field__control @error('phone') is-invalid @enderror" id="contact-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" dir="ltr" required @error('phone') aria-describedby="contact-phone-error" aria-invalid="true" @enderror>
                @error('phone')<span id="contact-phone-error" class="pg-field__error">{{ $message }}</span>@enderror
            </div>

            <div class="pg-field pg-field--textarea">
                <label class="pg-field__label" for="contact-message">{{ __('home.contact.message') }} <span class="pg-field__required">{{ __('home.contact.required') }}</span></label>
                <textarea class="pg-field__control @error('message') is-invalid @enderror" id="contact-message" name="message" rows="3" maxlength="2000" required @error('message') aria-describedby="contact-message-error" aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                @error('message')<span id="contact-message-error" class="pg-field__error">{{ $message }}</span>@enderror
            </div>

            {{-- Honeypot: hidden from people, filled by bots, rejected by validation. --}}
            <div class="pg-contact__hp" aria-hidden="true">
                <label for="contact-website">Website</label>
                <input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <button class="btn pg-btn pg-btn--solid pg-contact__submit" type="submit">{{ __('home.contact.submit') }}</button>
        </form>
    </div>
</section>
