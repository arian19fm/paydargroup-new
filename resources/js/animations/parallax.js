// Gentle scroll-linked drift for photos ([data-motion="parallax"]).
//
// The element is a clipped wrapper (overflow hidden in CSS); it is scaled
// up slightly so a vertical translate never reveals its edges. Transform
// only — dimensions, sources and loading are never touched.

import { gsap, mm, MEDIA } from './motion';

function build(root, { travel, scale, scrub }) {
    root.querySelectorAll('[data-motion="parallax"]').forEach((el) => {
        gsap.set(el, { willChange: 'transform' });

        gsap.fromTo(el,
            { y: travel, scale },
            {
                y: -travel,
                scale,
                ease: 'none',
                scrollTrigger: {
                    trigger: el,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub,
                    invalidateOnRefresh: true,
                },
            });
    });
}

export function initParallax(root = document) {
    if (!root.querySelector('[data-motion="parallax"]')) {
        return;
    }

    mm.add(MEDIA.desktop, () => build(root, { travel: 20, scale: 1.08, scrub: 0.8 }));
    mm.add(MEDIA.tablet, () => build(root, { travel: 14, scale: 1.06, scrub: 0.6 }));
    mm.add(MEDIA.mobile, () => build(root, { travel: 8, scale: 1.05, scrub: 0.4 }));
}
