// Section entrances and exits.
//
//   data-motion="reveal"          block fades/rises into place when it enters
//   data-motion="reveal-group"    same, children staggered
//   data-motion="reveal-heading"  large heading: clip-path wipe + rise
//   data-motion-exit              (modifier) block recedes as it scrolls out
//
// Entrances are tweens toggled by ScrollTrigger (play on enter, reverse
// when scrolled back above), exits are scrubbed to scroll progress so the
// hand-off between sections follows the finger/wheel in both directions.

import { gsap, mm, MEDIA, EASE } from './motion';

const ENTER = { y: 56, scale: 0.985, duration: 0.9 };
const ENTER_MOBILE = { y: 36, scale: 0.99, duration: 0.7 };
const EXIT = { opacity: 0.7, y: -36, scale: 0.985 };
const EXIT_MOBILE = { opacity: 0.8, y: -20, scale: 0.99 };

function trigger(el, extra = {}) {
    return {
        trigger: el,
        start: 'top 88%',
        toggleActions: 'play none none reverse',
        ...extra,
    };
}

function reveal(el, config) {
    gsap.fromTo(el,
        { opacity: 0, y: config.y, scale: config.scale },
        { opacity: 1, y: 0, scale: 1, duration: config.duration, ease: EASE, clearProps: 'scale', scrollTrigger: trigger(el) });
}

function revealGroup(el, config) {
    const items = Array.from(el.children);

    gsap.fromTo(items,
        { opacity: 0, y: config.y },
        { opacity: 1, y: 0, duration: config.duration, ease: EASE, stagger: 0.09, scrollTrigger: trigger(el) });
}

// Headings are wiped in with clip-path and a short rise. Whole block only:
// Persian text is never split, so shaping and kashida stay intact.
function revealHeading(el, config) {
    gsap.fromTo(el,
        { clipPath: 'inset(0 0 100% 0)', y: 35 },
        { clipPath: 'inset(0 0 -10% 0)', y: 0, duration: config.duration + 0.1, ease: EASE, clearProps: 'clipPath', scrollTrigger: trigger(el, { start: 'top 90%' }) });
}

// As the block leaves through the top of the viewport it eases back:
// slight fade, rise and shrink, scrubbed so it is exactly reversible.
function exit(el, config) {
    gsap.to(el, {
        opacity: config.opacity,
        y: config.y,
        scale: config.scale,
        ease: 'none',
        immediateRender: false,
        scrollTrigger: {
            trigger: el,
            start: 'bottom 45%',
            end: 'bottom 5%',
            scrub: 0.8,
        },
    });
}

function build(root, enterConfig, exitConfig) {
    root.querySelectorAll('[data-motion="reveal"]').forEach((el) => reveal(el, enterConfig));
    root.querySelectorAll('[data-motion="reveal-group"]').forEach((el) => revealGroup(el, enterConfig));
    root.querySelectorAll('[data-motion="reveal-heading"]').forEach((el) => revealHeading(el, enterConfig));
    root.querySelectorAll('[data-motion-exit]').forEach((el) => exit(el, exitConfig));
}

export function initReveal(root = document) {
    if (!root.querySelector('[data-motion^="reveal"]')) {
        return;
    }

    mm.add(MEDIA.desktop, () => build(root, ENTER, EXIT));
    mm.add(MEDIA.tablet, () => build(root, ENTER, EXIT));
    mm.add(MEDIA.mobile, () => build(root, ENTER_MOBILE, EXIT_MOBILE));
}
