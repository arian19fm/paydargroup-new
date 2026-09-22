// Hero (.pg-hero[data-motion="hero"]).
//
// The load-in (photo zoom settle, header drop, copy rise) is pure CSS —
// keyframes in pages/home/_hero.scss gated on <html class="pg-js"> — so it
// starts with first paint, never flashes, and needs no script to finish.
// This module only adds the scroll-away drift: the copy eases up and dims
// as the hero leaves, scrubbed so it reverses on the way back.

import { gsap, mm, MEDIA } from './motion';

function build(hero, lift) {
    const inner = hero.querySelector('.pg-hero__inner');

    if (!inner) {
        return;
    }

    gsap.to(inner, {
        y: -lift,
        opacity: 0.6,
        ease: 'none',
        immediateRender: false,
        scrollTrigger: {
            trigger: hero,
            start: 'top top',
            end: 'bottom 35%',
            scrub: 0.6,
        },
    });
}

export function initHero(root = document) {
    const hero = root.querySelector('[data-motion="hero"]');

    if (!hero) {
        return;
    }

    mm.add(MEDIA.desktop, () => build(hero, 60));
    mm.add(MEDIA.tablet, () => build(hero, 40));
    mm.add(MEDIA.mobile, () => build(hero, 24));
}
