// Shared motion setup: GSAP + ScrollTrigger registration, the responsive
// contexts every module uses and a couple of helpers.
//
// Everything here is progressive enhancement over server-rendered Blade:
// nothing is hidden by CSS up front, so without JavaScript (or with
// reduced motion) the page is simply static and fully visible.

import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

// Breakpoints mirror Bootstrap's md (768) and lg (992).
export const MEDIA = {
    desktop: '(min-width: 992px) and (prefers-reduced-motion: no-preference)',
    tablet: '(min-width: 768px) and (max-width: 991.98px) and (prefers-reduced-motion: no-preference)',
    mobile: '(max-width: 767.98px) and (prefers-reduced-motion: no-preference)',
    reduced: '(prefers-reduced-motion: reduce)',
};

// One matchMedia instance for the whole motion layer; gsap.matchMedia()
// reverts every tween/trigger created in a context when it stops matching
// (resize, orientation change, OS motion setting).
export const mm = gsap.matchMedia();

export const EASE = 'power2.out';

export function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export { gsap, ScrollTrigger };
