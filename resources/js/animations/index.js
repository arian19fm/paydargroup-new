// Motion layer entry. Loaded lazily by app.js only on pages that contain a
// [data-motion] element (the home page), so other pages never download
// GSAP. Native scrolling is untouched: no wheel/touch listeners, no smooth
// scroll replacement — ScrollTrigger just reads the scroll position.

import { ScrollTrigger, prefersReducedMotion } from './motion';
import { initHero } from './hero';
import { initReveal } from './reveal';
import { initParallax } from './parallax';

let refreshTimer;

function scheduleRefresh() {
    window.clearTimeout(refreshTimer);
    refreshTimer = window.setTimeout(() => ScrollTrigger.refresh(), 120);
}

export function initMotion(root = document) {
    // A refresh scrolls to the top to measure and then back; Bootstrap's
    // `scroll-behavior: smooth` on <html> would animate that way back, and
    // ScrollTrigger would keep the half-way position — losing the place
    // restored by back/forward navigation or reached through a #hash.
    // Scroll instantly while a refresh runs; anchors stay smooth otherwise.
    const html = document.documentElement;
    ScrollTrigger.addEventListener('refreshInit', () => { html.style.scrollBehavior = 'auto'; });
    ScrollTrigger.addEventListener('refresh', () => { html.style.removeProperty('scroll-behavior'); });

    initHero(root);
    initReveal(root);
    initParallax(root);

    if (prefersReducedMotion()) {
        // Nothing was created (every context is gated on
        // no-preference), but keep the OS setting authoritative if it
        // changes while the page is open: gsap.matchMedia handles that.
        return;
    }

    // Layout can shift after the triggers were measured: web fonts
    // swapping in, images finishing (explicit dimensions keep this small),
    // Bootstrap collapsing/expanding FAQ answers, the offcanvas closing.
    document.fonts?.ready.then(scheduleRefresh);
    window.addEventListener('load', scheduleRefresh, { once: true });
    document.addEventListener('shown.bs.collapse', scheduleRefresh);
    document.addEventListener('hidden.bs.collapse', scheduleRefresh);
    // ScrollTrigger already refreshes itself on resize/orientation change.
}
