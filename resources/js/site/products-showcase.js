// Home products showcase ([data-products-showcase], partials/site/home/products).
//
// The server renders a stacked list: an index of anchor links, then one
// article per product with its own screenshot — complete without
// JavaScript and the fallback under reduced motion. This script picks one
// of three modes and re-arranges the same elements (moved, never cloned,
// so every alt text exists once):
//
// - staged (≥lg): the screenshots and the index move into the sticky
//   aside next to the text; an IntersectionObserver marks the product
//   crossing the middle of the viewport as active (frame cross-fades in).
// - pinned (<lg, motion allowed): the index and the product cards move
//   into the same sticky aside, which is one viewport tall inside a track
//   a few viewports tall. The scroll position along the track drives the
//   cards: each next card rises from the bottom of the viewport over the
//   previous one, which settles back. Scrubbed, so scrolling up reverses
//   it exactly, and the page carries on once the track has passed.
// - stacked: nothing moves (no JS, reduced motion).
//
// Native scrolling throughout: one passive scroll listener that only reads
// the position (rAF-throttled) — no wheel/touch interception, no body lock.

const root = document.querySelector('[data-products-showcase]');

if (root && 'IntersectionObserver' in window) {
    const wide = window.matchMedia('(min-width: 992px)');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const nav = root.querySelector('[data-showcase-nav]');
    const list = root.querySelector('.pg-showcase__list');
    const aside = root.querySelector('[data-showcase-aside]');
    const sticky = aside.firstElementChild;
    const stage = root.querySelector('[data-showcase-stage]');
    const items = Array.from(root.querySelectorAll('[data-showcase-item]'));
    const tabs = Array.from(root.querySelectorAll('[data-showcase-tab]'));
    const media = items.map((item) => item.querySelector('[data-showcase-media]'));
    const last = items.length - 1;

    // Pinned track: each card rests for HOLD of a step before the next one
    // starts rising (the first card rests at the start, the last at the
    // end), so every product gets a moment fully in view.
    const HOLD = 0.35;
    const span = last + HOLD;

    let mode = 'stacked';
    let active = -1;
    let frame = 0;
    let travel = 0;

    const clamp = (value) => Math.min(1, Math.max(0, value));
    const ease = (t) => (t < 0.5 ? 4 * t * t * t : 1 - ((-2 * t + 2) ** 3) / 2);

    const activate = (index) => {
        if (index < 0 || index === active) {
            return;
        }

        active = index;
        items.forEach((item, i) => item.classList.toggle('is-active', i === index));
        media.forEach((figure, i) => figure.classList.toggle('is-active', i === index));
        tabs.forEach((tab, i) => (i === index ? tab.setAttribute('aria-current', 'true') : tab.removeAttribute('aria-current')));
        stage.style.setProperty('--pg-showcase-accent', getComputedStyle(media[index]).getPropertyValue('--pg-product-accent'));
    };

    // ------------------------------------------------------------ pinned

    const trackDistance = () => aside.offsetHeight - sticky.offsetHeight;

    // Document scroll offset at which the track shows `progress` (0…span).
    const scrollFor = (progress) => aside.getBoundingClientRect().top + window.scrollY + (progress / span) * trackDistance();

    // Progress at which card `index` sits fully in view, mid-rest.
    const restFor = (index) => index + HOLD / 2;

    const render = () => {
        frame = 0;

        if (mode !== 'pinned') {
            return;
        }

        const distance = trackDistance();
        const progress = distance > 0 ? clamp(-aside.getBoundingClientRect().top / distance) * span : 0;

        items.forEach((item, i) => {
            // How far this card has risen (the first card is always in) …
            const enter = i === 0 ? 1 : ease(clamp((progress - (i - 1) - HOLD) / (1 - HOLD)));
            // … and how far the next card has covered it.
            const cover = i === last ? 0 : ease(clamp((progress - i - HOLD) / (1 - HOLD)));
            const y = (1 - enter) * travel - cover * 12;
            const scale = 1 - cover * 0.05;

            item.style.transform = `translate3d(0, ${y.toFixed(1)}px, 0) scale(${scale.toFixed(4)})`;
            item.style.opacity = String(1 - cover * 0.55);
        });

        // The product most in view: the last card more than half risen.
        activate(Math.min(last, Math.max(0, Math.floor(progress - HOLD + 0.5))));
    };

    const schedule = () => {
        if (!frame) {
            frame = window.requestAnimationFrame(render);
        }
    };

    const measure = () => {
        // Cards start just below the deck, i.e. at the bottom of the viewport.
        travel = list.clientHeight + 24;
        schedule();
    };

    // ------------------------------------------------------------ layout

    const reset = () => {
        root.classList.remove('is-staged', 'is-pinned');
        aside.hidden = true;
        root.insertBefore(nav, aside);
        root.insertBefore(list, aside);
        items.forEach((item, i) => {
            item.style.removeProperty('transform');
            item.style.removeProperty('opacity');
            item.querySelector('.pg-product__slot').append(media[i]);
        });
    };

    const layout = () => {
        reset();
        mode = wide.matches ? 'staged' : (reduced.matches ? 'stacked' : 'pinned');

        if (mode === 'stacked') {
            return;
        }

        root.classList.add(`is-${mode}`);
        aside.hidden = false;

        if (mode === 'staged') {
            stage.append(...media);
            sticky.append(nav);
        } else {
            aside.style.setProperty('--pg-showcase-span', String(span));
            sticky.append(nav, list);
            measure();
        }
    };

    // Scroll so that product `index` is the one in view.
    const goTo = (index, behavior) => {
        if (mode === 'pinned') {
            window.scrollTo({ top: scrollFor(restFor(index)), behavior });
        } else {
            items[index].scrollIntoView({ behavior, block: 'start' });
        }
    };

    // Staged mode: a thin band just above the middle of the viewport;
    // whichever product crosses it is the one being read.
    const observer = new IntersectionObserver((entries) => {
        if (mode !== 'staged') {
            return;
        }

        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                activate(items.indexOf(entry.target));
            }
        });
    }, { rootMargin: '-40% 0px -55% 0px' });

    items.forEach((item, i) => {
        item.tabIndex = -1;
        observer.observe(item);

        // Keyboard users tabbing into a card that has not risen yet: bring
        // its stretch of the track into view so the focused link is visible.
        // Next frame: the browser's own focus scrolling runs after focusin.
        item.addEventListener('focusin', () => {
            if (mode === 'pinned' && active !== i) {
                window.requestAnimationFrame(() => goTo(i, 'instant'));
            }
        });
    });

    // Index: the links stay real anchors; this adds a smooth scroll (unless
    // reduced motion), moves focus to the product and keeps the hash in the
    // address bar without adding history entries.
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();
            activate(i);
            goTo(i, reduced.matches ? 'instant' : 'smooth');
            items[i].focus({ preventScroll: true });
            history.replaceState(history.state, '', tab.hash);
        });
    });

    // Arrow keys / Home / End move between index links (Tab still works).
    nav.addEventListener('keydown', (event) => {
        const current = tabs.indexOf(document.activeElement);

        if (current === -1) {
            return;
        }

        const rtl = getComputedStyle(nav).direction === 'rtl';
        const step = {
            ArrowDown: 1,
            ArrowUp: -1,
            ArrowRight: rtl ? -1 : 1,
            ArrowLeft: rtl ? 1 : -1,
        }[event.key];
        let next;

        if (step) {
            next = (current + step + tabs.length) % tabs.length;
        } else if (event.key === 'Home') {
            next = 0;
        } else if (event.key === 'End') {
            next = last;
        } else {
            return;
        }

        event.preventDefault();
        tabs[next].focus();
    });

    layout();
    activate(0);
    render();

    // A deep link to a product (#product-slug) lands on that product.
    const linked = items.findIndex((item) => `#${item.id}` === window.location.hash);

    // Wait for load: the browser's own fragment/restoration scroll runs up
    // to then and would undo an earlier jump.
    if (linked > 0) {
        const land = () => window.requestAnimationFrame(() => goTo(linked, 'instant'));

        if (document.readyState === 'complete') {
            land();
        } else {
            window.addEventListener('load', land, { once: true });
        }
    }

    // Browsers without overflow: clip (Safari < 16) can still scroll the
    // pinned panel's hidden overflow (a #hash, focus); undo that at once.
    sticky.addEventListener('scroll', () => {
        sticky.scrollTop = 0;
        sticky.scrollLeft = 0;
    }, { passive: true });

    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', measure, { passive: true });
    // Back/forward cache: the scroll position is restored, re-read it.
    window.addEventListener('pageshow', measure);
    wide.addEventListener('change', layout);
    reduced.addEventListener('change', layout);
}
