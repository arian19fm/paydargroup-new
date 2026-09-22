// Product card stack (.pg-products__list[data-motion="product-stack"]).
//
// The stacking itself is CSS: each card is position: sticky at
// --pg-stack-top + index × --pg-stack-step (see pages/home/_products.scss),
// so scrolling naturally slides card N+1 up and over card N and the list
// keeps its normal height. This module only adds the "card being covered"
// settle: while card N+1 travels from card N's bottom edge to its own
// sticky offset, card N scales down a little and dims slightly. Everything
// is scrubbed, so it reverses exactly when scrolling back up.

import { gsap, mm, MEDIA } from './motion';

const SETTINGS = {
    desktop: { scale: 0.97, opacity: 0.88, y: -8, scrub: 0.8 },
    tablet: { scale: 0.975, opacity: 0.9, y: -6, scrub: 0.6 },
    mobile: { scale: 0.985, opacity: 0.94, y: -4, scrub: 0.4 },
};

function stickyTop(list, index) {
    const styles = getComputedStyle(list);
    const top = parseFloat(styles.getPropertyValue('--pg-stack-top')) || 0;
    const step = parseFloat(styles.getPropertyValue('--pg-stack-step')) || 0;

    return top + index * step;
}

function build(list, cards, config) {
    cards.forEach((card, index) => {
        const next = cards[index + 1];

        if (!next) {
            return;
        }

        gsap.set(card, { willChange: 'transform' });

        gsap.to(card, {
            scale: config.scale,
            opacity: config.opacity,
            y: config.y,
            ease: 'none',
            immediateRender: false,
            scrollTrigger: {
                trigger: next,
                // From the moment the next card's top reaches this card's
                // bottom edge (overlap begins) …
                start: () => `top ${stickyTop(list, index) + card.offsetHeight}px`,
                // … until the next card reaches its own sticky offset.
                end: () => `top ${stickyTop(list, index + 1)}px`,
                scrub: config.scrub,
                invalidateOnRefresh: true,
            },
        });
    });
}

export function initProductsStack(root = document) {
    const lists = root.querySelectorAll('[data-motion="product-stack"]');

    lists.forEach((list) => {
        const cards = Array.from(list.children).filter((el) => el.matches('.pg-product'));

        if (cards.length < 2) {
            return;
        }

        // Stack index/z-index come from the markup (--stack-index); make sure
        // every card has one even if the template ever omits it.
        cards.forEach((card, index) => {
            if (!card.style.getPropertyValue('--stack-index')) {
                card.style.setProperty('--stack-index', String(index));
            }
        });

        mm.add(MEDIA.desktop, () => build(list, cards, SETTINGS.desktop));
        mm.add(MEDIA.tablet, () => build(list, cards, SETTINGS.tablet));
        mm.add(MEDIA.mobile, () => build(list, cards, SETTINGS.mobile));
        // Reduced motion: the CSS stack stays (it is layout, not animation);
        // no scale/opacity interpolation is created.
    });
}
