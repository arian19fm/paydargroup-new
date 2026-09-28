{{--
    FAQ (Figma 133:400 / 180:349). Semantic accordion on Bootstrap Collapse:
    each question is a button inside a heading, each answer a collapsible
    region; the first item starts open as in the design. The "Ask AI" field
    is visual only — no assistant backend exists yet, so the controls are
    disabled and say so. Questions come from admin → FAQ
    (HomePage::faqItems()); an unanswered one shows the pending note. The
    section texts and the side card come from Settings → صفحهٔ اصلی; a
    blank one is left out (a blank placeholder hides the "Ask AI" field).
--}}
@php $section = $sections['faq']; @endphp
<section class="pg-faq" id="faq" aria-labelledby="faq-heading">
    <div class="pg-container">
        <div class="pg-faq__header" data-motion="reveal">
            @if ($section['eyebrow'])
                <p class="pg-eyebrow pg-eyebrow--faq">{{ $section['eyebrow'] }}</p>
            @endif
            <h2 id="faq-heading" class="pg-faq__title" data-motion="reveal-heading">
                @if ($section['heading_highlight'])<span class="pg-faq__title-highlight">{{ $section['heading_highlight'] }}</span>@endif
                @if ($section['heading'])<span class="pg-faq__title-rest">{{ $section['heading'] }}</span>@endif
            </h2>
        </div>

        <div class="pg-faq__body">
            <div class="pg-faq__main" data-motion="reveal" data-motion-exit>
                @if ($section['ask_placeholder'])
                    <div class="pg-ask" role="group" aria-labelledby="ask-ai-label">
                        <label id="ask-ai-label" class="visually-hidden" for="ask-ai-input">{{ __('home.faq.ask_label') }}</label>
                        <input id="ask-ai-input" class="pg-ask__input" type="text" placeholder="{{ $section['ask_placeholder'] }}" aria-describedby="ask-ai-note" disabled>
                        <button class="pg-ask__send" type="button" aria-label="{{ __('home.faq.ask_send') }}" disabled>
                            <img src="{{ asset('images/icons/faq-ask-button.svg') }}" width="46" height="54" alt="" aria-hidden="true">
                        </button>
                        <span id="ask-ai-note" class="visually-hidden">{{ __('home.faq.ask_pending') }}</span>
                    </div>
                @endif

                @if (count($faqItems))
                <div class="pg-faq__list" id="faq-accordion">
                    @foreach ($faqItems as $index => $item)
                        @php
                            $open = $index === 0;
                            $panelId = 'faq-panel-'.($index + 1);
                            $buttonId = 'faq-button-'.($index + 1);
                        @endphp
                        <div class="pg-faq__item">
                            <h3 class="pg-faq__question">
                                <button id="{{ $buttonId }}" class="pg-faq__toggle{{ $open ? '' : ' collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $panelId }}" aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $panelId }}">
                                    <span class="pg-faq__question-text">{{ $item['question'] }}</span>
                                    <span class="pg-faq__icon" aria-hidden="true">
                                        <img src="{{ asset('images/icons/faq-icon-plus.svg') }}" width="16" height="16" alt="">
                                    </span>
                                </button>
                            </h3>
                            <div id="{{ $panelId }}" class="collapse{{ $open ? ' show' : '' }}" data-bs-parent="#faq-accordion" role="region" aria-labelledby="{{ $buttonId }}">
                                <p class="pg-faq__answer">{{ $item['answer'] ?? __('home.faq.answer_pending') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            @if ($section['card_title'] || $section['card_text'] || $section['card_cta'])
                <aside class="pg-faq__card" @if ($section['card_title']) aria-labelledby="faq-card-title" @endif data-motion="reveal" data-motion-exit>
                    <span class="pg-faq__card-icon" aria-hidden="true">؟</span>
                    @if ($section['card_title'])
                        <h3 id="faq-card-title" class="pg-faq__card-title">{{ $section['card_title'] }}</h3>
                    @endif
                    @if ($section['card_text'])
                        <p class="pg-faq__card-text">{{ $section['card_text'] }}</p>
                    @endif
                    @if ($section['card_cta'])
                        <a class="btn pg-btn pg-btn--solid pg-faq__card-cta" href="{{ $section['card_cta_url'] }}">{{ $section['card_cta'] }}</a>
                    @endif
                </aside>
            @endif
        </div>
    </div>
</section>
