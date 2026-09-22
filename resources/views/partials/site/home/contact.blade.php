{{--
    Contact (Figma 142:289 / 178:424). A real form: POST /contact with CSRF,
    server-side validation (StoreContactRequest), a honeypot field and a
    per-IP rate limit. On success the visitor lands back here with a
    confirmation; nothing is e-mailed in this phase.
--}}
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

            @include('partials.site.contact-fields', ['idPrefix' => 'contact'])

            <button class="btn pg-btn pg-btn--solid pg-contact__submit" type="submit">{{ __('home.contact.submit') }}</button>
        </form>
    </div>
</section>
