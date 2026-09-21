// Paydar Group — main script entry.
//
// Bootstrap's JS (with Popper) is bundled locally by Vite. Pages are
// server-rendered Blade; JavaScript is progressive enhancement only.
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

// Vue is reserved for isolated interactive widgets mounted onto specific
// elements (never a full-page SPA). Register them here in later phases, e.g.:
//
//   import { createApp } from 'vue';
//   import ExampleWidget from './components/ExampleWidget.vue';
//   document.querySelectorAll('[data-vue="example-widget"]').forEach((el) => {
//       createApp(ExampleWidget, { ...el.dataset }).mount(el);
//   });
