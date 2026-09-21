// Paydar Group — main script entry.
//
// The site is server-rendered Blade; JavaScript is progressive enhancement.
// Keep this bundle small: import only the Bootstrap components that are
// actually used, and mount Vue only where a page opts in (see vue/mount.js).
// Everything is bundled locally by Vite — no CDN.

import './site/bootstrap';
import './site/navigation';
import { mountVueComponents } from './vue/mount';

mountVueComponents();
