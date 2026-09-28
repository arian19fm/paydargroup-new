{{--
    Contact (Figma 142:289 / 178:424). A real form: POST /contact with CSRF,
    server-side validation (StoreContactRequest), a honeypot field and a
    per-IP rate limit. On success the visitor lands back here with a
    confirmation; nothing is e-mailed in this phase. Texts and the
    background photo come from Settings → صفحهٔ اصلی (blank photo = the
    designed one; a blank text is left out).
--}}
@php $section = $sections['contact']; @endphp
<section class="pg-contact" id="contact" @if ($sections['contact']['heading']) aria-labelledby="contact-heading" @endif>
    <picture class="pg-contact__media" data-motion="parallax">
        @if ($contactImage ?? null)
            <img src="{{ $contactImage->url() }}" width="{{ $contactImage->width ?: 1737 }}" height="{{ $contactImage->height ?: 905 }}" alt="" loading="lazy" decoding="async">
        @else
            <source type="image/webp" srcset="{{ asset('images/home/contact.webp') }}">
            <img src="{{ asset('images/home/contact.jpg') }}" width="1737" height="905" alt="" loading="lazy" decoding="async">
        @endif
    </picture>
    <div class="pg-contact__overlay" aria-hidden="true"></div>

    <div class="pg-contact__inner">
        <form class="pg-contact__card" method="post" action="{{ route('contact.store') }}" novalidate data-motion="reveal">
            @csrf
            <div class="pg-contact__intro">
                @if ($section['eyebrow'])
                    <p class="pg-contact__eyebrow">{{ $section['eyebrow'] }}</p>
                @endif
                @if ($section['heading'])
                    <h2 id="contact-heading" class="pg-contact__title">{{ $section['heading'] }}</h2>
                @endif
                @if ($section['text'])
                    <p class="pg-contact__text">{{ $section['text'] }}</p>
                @endif
            </div>

            @include('partials.site.contact-fields', ['idPrefix' => 'contact'])

            <button class="btn pg-btn pg-btn--solid pg-contact__submit" type="submit">{{ $section['submit'] }}</button>
        </form>
    </div>
</section>
