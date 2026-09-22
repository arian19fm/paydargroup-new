// Paydar Group — main script entry.
//
// The site is server-rendered Blade; JavaScript is progressive enhancement.
// Keep this bundle small: import only the Bootstrap components that are
// actually used, and mount Vue only where a page opts in (see vue/mount.js).
// Everything is bundled locally by Vite — no CDN.

import './site/bootstrap';
import './site/navigation';
import './site/theme';
import './site/drag-scroll';
import './site/hero-video';
import './admin/char-counter';
import { mountVueComponents } from './vue/mount';

mountVueComponents();

// Scroll-driven motion (GSAP + ScrollTrigger) is a separate chunk fetched
// only when the page opts in with [data-motion] elements (the home page).
if (document.querySelector('[data-motion]')) {
    import('./animations/index').then(({ initMotion }) => initMotion());
}
