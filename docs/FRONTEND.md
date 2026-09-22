# Frontend

Server-rendered Blade + Bootstrap 5, Persian-first and RTL, with every asset
self-hosted. This document describes how the frontend is built and how to
extend it. Architecture rules live in [`ARCHITECTURE.md`](ARCHITECTURE.md);
SEO rules in [`SEO.md`](SEO.md).

## 1. Asset build (Vite)

| | |
|---|---|
| Entry points | `resources/scss/app.scss`, `resources/js/app.js` (see `vite.config.js`) |
| Output | `public/build/` — hashed, minified files + `manifest.json` (gitignored) |
| Blade | `@vite(['resources/scss/app.scss', 'resources/js/app.js'])` in the layouts |
| Dev | `npm run dev` (HMR via the Vite dev server; Blade `refresh: true` reloads on view changes) |
| Production | `npm run build` — must run in CI or on the server for every release; nothing is loaded from a CDN |

Node/npm are **build-time only**. A production server needs only PHP and the
committed/built `public/build` directory.

Rules enforced by review: no `<script src>`/`<link href>` to third-party
hosts, no `@import url(https://…)`, no `url(https://…)` in SCSS. Verify with:

```bash
grep -rnE "https?://" resources/ public/build/assets/ | grep -vE "schema.org|w3.org|localhost"
```

(Strings inside library error messages, e.g. `vuejs.org/error-reference`, are
documentation text, not requests.)

## 2. Bootstrap RTL

The site is RTL-only. We use Bootstrap's **official** approach: compile the
SCSS normally (LTR) and flip the *entire* stylesheet with
[RTLCSS](https://rtlcss.com) — the same tool Bootstrap uses to ship
`bootstrap.rtl.css`.

- `postcss.config.js` registers `rtlcss` as a PostCSS plugin; Vite runs it on
  the compiled CSS automatically. There is no separate RTL build step.
- Bootstrap's source already contains the `/* rtl:… */` control directives
  RTLCSS understands (e.g. for `.form-select` arrows, breadcrumb dividers,
  `text-start/end`, `offcanvas-start/end`), so grid, spacing (`ms-*`, `me-*`,
  `ps-*`, `pe-*`), dropdown alignment, navbar, offcanvas, modal, forms,
  accordion, pagination, breadcrumbs, buttons and utilities all flip
  correctly. **Do not write manual `margin-left → margin-right` overrides.**
- Our own SCSS is flipped too. Write it in LTR terms and let RTLCSS flip it,
  or use logical properties (`inset-inline-start`, `margin-inline-end`),
  which need no flipping. To exempt a rule: `/* rtl:ignore */`; to force a
  value: `/* rtl:remove */`, `/* rtl:raw: … */`.
- `<html dir>` is set from the locale by `App\Support\Localization\Direction`
  (`config/site.php → rtl_locales`).
- Latin snippets inside Persian text use `.pg-ltr` (`direction: ltr; unicode-bidi: isolate`)
  or `dir="ltr"` on the element (e-mail addresses, phone numbers).
- RTLCSS does **not** flip flex/grid alignment: under `dir="rtl"`
  `flex-start`/column 1 already mean the right edge, so write those values
  for the RTL result directly. It *does* negate `transform: rotate()` and
  flip `left/right`; an intentional rotation (the product arrows, the FAQ
  toggle) or a physical crop (`object-position`, the mirrored contact
  photo) carries `/* rtl:ignore */`. Asymmetric insets use
  `padding-inline: <start> <end>`.
- Never `flex-direction: row-reverse` to "fix" RTL — the document direction
  already reverses the row.

Verify after a build:

```bash
grep -o '\.me-1{[^}]*}' public/build/assets/app-*.css   # → margin-left
grep -o '\.text-start{[^}]*}' public/build/assets/app-*.css   # → text-align:right
```

If the site ever becomes bilingual (LTR + RTL), switch to
`postcss-rtlcss` in "combined" mode (emits `[dir="rtl"]`-scoped rules) or
build two stylesheets; the SCSS stays unchanged.

## 3. SCSS organisation

```
resources/scss/
  app.scss                 load order (abstracts → bootstrap → base → layout → components → pages)
  abstracts/
    _variables.scss        DESIGN TOKENS ($pg-*) + Bootstrap variable overrides — single source of truth
    _functions.scss        pg-token()
    _mixins.scss           pg-visually-hidden, pg-focus-ring, pg-motion-safe
  base/
    _fonts.scss            @font-face (self-hosted)
    _root.scss             :root { --pg-* } custom properties generated from the tokens
    _typography.scss       Persian/RTL text refinements
    _utilities.scss        skip link, focus-visible, reduced motion, media defaults
  layout/                  _header (glass pill), _navigation, _footer
  components/              _buttons (.pg-btn variants), _forms (.pg-field), _cards, _badges (.pg-feature, .pg-tag)
  pages/_home.scss         home page; one partial per section in pages/home/
```

Principles:

1. **Customise Bootstrap through its variables** (in `abstracts/_variables.scss`,
   *before* Bootstrap is imported). No `!important` walls, no re-styling of
   Bootstrap selectors when a variable exists.
2. Bootstrap parts are imported individually in `app.scss` (mirrors
   `bootstrap.scss`); drop a part that the design never uses to trim CSS.
3. Project selectors are prefixed `pg-` (`.pg-header`, `.pg-section`) so they
   never collide with Bootstrap.
4. Runtime CSS (Vue widgets, inline styles) uses the `--pg-*` custom
   properties; SCSS uses the `$pg-*` variables. Both come from one file.
5. Dark theme: Bootstrap's `data-bs-theme` mechanism (`$enable-dark-mode:
   true`). The attribute is set on `<html>` before first paint by an inline
   script in `layouts/site.blade.php` — light by default, dark only when
   the visitor's stored choice (`localStorage` `pg-theme`) says so; the OS
   preference is not consulted — toggled by the header switch
   (`components/ui/theme-toggle`, `site/theme.js`). Only the `--pg-*`
   tokens change, under `[data-bs-theme="dark"]` in `base/_root.scss`; the
   dark values are derived from the light palette (`$pg-dark-*` in the
   token file) because the Figma file has no dark design. The admin panel
   stays light.

## 4. Design tokens

Defined once as SCSS variables in `abstracts/_variables.scss`, mirrored to
CSS custom properties in `base/_root.scss` and mapped onto Bootstrap
variables in the same file.

| Token | CSS property | Feeds Bootstrap |
|---|---|---|
| `$pg-primary`, `$pg-secondary`, `$pg-accent` | `--pg-primary`, `--pg-secondary`, `--pg-accent` | `$primary`, `$secondary` |
| `$pg-background`, `$pg-surface` | `--pg-background`, `--pg-surface` | `$body-bg` |
| `$pg-text`, `$pg-text-muted`, `$pg-border` | `--pg-text`, `--pg-text-muted`, `--pg-border` | `$body-color`, `$body-secondary-color`, `$border-color`, `$input-border-color` |
| `$pg-radius-sm/md/lg` | `--pg-radius-*` | `$border-radius-*`, `$btn-border-radius-*`, `$input-border-radius-*`, `$card-border-radius` |
| `$pg-shadow-sm/md` | `--pg-shadow-*` | `$box-shadow-sm`, `$box-shadow` |
| `$pg-container-max` | `--pg-container-max` | `$container-max-widths.xxl` |
| `$pg-font-family-base`, sizes, weights, line heights | `--pg-font-family-base` | `$font-family-base`, `$font-size-base`, `$line-height-base`, `$headings-*` |

Values are mapped from the Figma homepage frames (desktop `110:262`, mobile
`172:289`) and named by semantic role. Where the two frames disagree (heading
highlight and solid buttons are navy `#182c4f` on desktop, blue
`#1c94e1`/`#1f43a4` on mobile) the desktop value is the token and
`base/_root.scss` switches `--pg-highlight` / `--pg-button-bg` below `lg`.
Product accents live in the `$pg-products` map and are exposed per card as
`--pg-product-*` custom properties.

## 5. Self-hosted fonts

- Source files: `resources/fonts/<family>/*.woff2` (+ licence), documented in
  `resources/fonts/README.md`. WOFF2 only, and only the weights in use.
- `@font-face` rules: `resources/scss/base/_fonts.scss`, `font-display: swap`.
- Vite fingerprints the files into `public/build/assets/` and rewrites the
  CSS `url()`s; nothing points outside the site.
- Families (from the Figma homepage frames): **Doran** 400/500/700/800 for
  display text (`--pg-font-family-display`), **IRANYekan** 400/500 for UI and
  body copy (`--pg-font-family-base`), **IRANYekanFN** 400/500 where Persian
  digits are required (`--pg-font-family-fn`), and **Vazir** as the stand-in
  for the design's Vazirmatn styles (`--pg-font-family-vazir`). Gaps against
  the design are listed in the fonts README.
- Optional later optimisation: `<link rel="preload" as="font">` for the
  above-the-fold weight via `Vite::asset('resources/fonts/…')`.

## 6. JavaScript

`resources/js/app.js` is the only entry. It stays small:

- `site/bootstrap.js` — **selective** Bootstrap imports (`collapse`,
  `dropdown`, `offcanvas`, `modal`, `alert`). Add a module only when markup
  uses it; never `import 'bootstrap'` wholesale. No jQuery.
- `site/navigation.js` — vanilla progressive enhancement for the header.
- `vue/mount.js` — isolated Vue mounting (below).

- `animations/` — scroll-driven motion on GSAP + ScrollTrigger
  (`index.js` bootstraps `hero.js`, `products-stack.js`, `reveal.js`,
  `parallax.js`). It is a separate Vite chunk that `app.js` imports lazily
  only when the page contains a `[data-motion]` element (the home page);
  other pages never download GSAP.

Scripts are ES modules (`type="module"`), so they are deferred by the
browser and never block rendering.

### Motion layer rules

- Blade stays the source of content; motion is progressive enhancement.
  Nothing is hidden by CSS up front — GSAP sets initial states at run time,
  and the hero load-in is a CSS keyframe gated on `<html class="pg-js">`
  (set inline in the home page head), so without JavaScript every section
  is static and visible.
- Hooks are explicit: `data-motion="hero | product-stack | reveal |
  reveal-group | reveal-heading | parallax"` plus the `data-motion-exit`
  modifier. Do not animate arbitrary selectors.
- Product stack: CSS does the structure (`position: sticky` per card at
  `--pg-stack-top + --stack-index × --pg-stack-step`, rising `z-index`; the
  list keeps its natural height), GSAP only scrubs scale/opacity of the
  card being covered. `--stack-index` comes from the markup.
- Only `transform` and `opacity` are animated; `will-change` is set by the
  scripts on the few elements that scrub. Headings reveal as whole blocks
  (clip-path + rise) — Persian text is never split.
- `gsap.matchMedia()` contexts: desktop (≥992), tablet (768–991), mobile
  (<768), each gated on `prefers-reduced-motion: no-preference`. With
  reduced motion no tween or trigger is created; the sticky stack remains
  (it is layout, not animation).
- Native scrolling only: no wheel/touch listeners, no smooth-scroll
  library, no pinning canvases. `ScrollTrigger.refresh()` is debounced
  after fonts/`load` and Bootstrap collapse events.

## 7. Isolated Vue components

Vue is an optional enhancement, never an app shell. No router, no Pinia, no
SPA, no global `createApp` on `<body>`.

**Mount pattern** (`resources/js/vue/mount.js`):

```html
{{-- Blade: server-rendered mount target; props are JSON --}}
<div data-vue-component="example-counter" data-props='@json(['start' => 3])'>
    {{-- optional no-JS fallback content --}}
</div>
```

```js
// resources/js/vue/mount.js — register lazily
const registry = {
    'example-counter': () => import('../components/ExampleCounter.vue'),
};
```

- On page load, `mountVueComponents()` looks for `[data-vue-component]`. If
  none exist it returns immediately — **Vue is not even downloaded** (it is
  a separate chunk loaded by dynamic `import()`, see `manualChunks` in
  `vite.config.js`).
- Each target gets its own `createApp(Component, props).mount(el)`.
- Components live in `resources/js/components/*.vue`, use the runtime-only
  Vue build, and read `--pg-*` tokens for styling.
- Suitable uses: calculators, filterable lists, multi-step forms, media
  pickers in the admin. Not suitable: page layout, navigation, content.

## 8. Blade structure

```
resources/views/
  layouts/site.blade.php         public shell: <x-seo.head>, skip link, header, <main>, footer, stacks
  layouts/admin.blade.php        admin shell: noindex, no OG/JSON-LD
  components/seo/head            complete SEO <head> block (see SEO.md)
  components/seo/json-ld         safe <script type="application/ld+json">
  components/layout/skip-link    first focusable element
  components/layout/container    .container / .container-fluid
  components/layout/section      <section> + optional H2–H6 heading + container
  components/ui/breadcrumb       <nav aria-label> + <ol class="breadcrumb"> from seo()->breadcrumbs()
  components/ui/flash-messages   session flash → dismissible alerts in a live region
  partials/site/header           glass header: menu split around the logo (desktop), logo + hamburger (mobile)
  partials/site/navigation       <ul class="pg-nav"> from the managed "main" menu (config/site.php fallback)
  partials/site/mobile-navigation offcanvas panel (opens from the end edge — left in RTL)
  partials/site/footer           brand + pages ("footer" menu) + services + contact/social (settings) + legal ("legal" menu)
  partials/site/home/*           hero, products, blog, faq, contact sections of the home page
  components/ui/cta              <a> when a destination exists, <span> otherwise (unpublished CMS pages)
  errors/{layout,404,500,503}    branded error pages (noindex)
  site/                          public pages (@extends('layouts.site'))
```

Stacks: `@push('head')` for page-specific `<link>`/`<meta>`, `@push('scripts')`
for page-specific `<script type="module">`. A page that yields a `hero`
section (the home page) gets the header floated over it; other pages get the
header on a solid brand band.

Home page content: articles, menus and settings come from the CMS; the
product line-up and section copy are centralised in `config/home.php` and
`lang/{fa,en}/home.php` and assembled by `App\Support\Home\HomePage`.
Calls to action point at CMS page slugs and render as links only when that
page is published.

## 9. Semantic HTML & accessibility checklist

- One `<h1>` per page (the page's subject); `<x-layout.section heading>`
  renders H2+ only. Never pick a heading level for its size — use Bootstrap's
  `.h1`–`.h6`/`.fs-*` classes for visual size.
- Landmarks: `<header>`, `<nav aria-label>`, `<main id="main">`, `<footer>`;
  `<article>` for self-contained content, `<section>` for thematic groups.
- `<a>` navigates, `<button>` acts. Never `<a href="#">` with a click handler.
- Every form control has a `<label for>`; errors are associated with
  `aria-describedby`.
- Images: `alt` always present (empty for decorative), explicit
  `width`/`height` or `aspect-ratio`.
- Skip link, `:focus-visible` ring, `prefers-reduced-motion` handled in
  `base/_utilities.scss`.
- Bootstrap components carry their documented ARIA attributes; do not add
  ARIA where native semantics suffice.

## 10. Images & media (conventions)

- Formats: WebP (AVIF where the pipeline supports it) with a JPEG/PNG
  fallback via `<picture>` when needed; SVG for icons/logos.
- Always `width` and `height` attributes (or CSS `aspect-ratio`) to prevent
  layout shift. `img { max-width: 100%; height: auto }` is global.
- Responsive: `srcset` + `sizes` for content images; generate variants on
  upload in the media library (later phase — no image package yet).
- `loading="lazy"` and `decoding="async"` only for below-the-fold images;
  the LCP image is eager and may be preloaded.
- Descriptive, hyphenated filenames (`paydar-group-office-tehran.webp`), and
  required alt text in the admin.

## 11. Performance rules

- One CSS and one JS bundle, hashed and minified by Vite; Vue in a lazy chunk.
- No render-blocking third-party requests. No jQuery. No global Vue.
- Fonts: WOFF2, `font-display: swap`, three weights.
- Public pages should cache well (full-page/fragment caching arrives with
  the CMS).
- Budget to watch: keep `app-*.js` (gzip) under ~25 KB and `app-*.css`
  (gzip) under ~35 KB before the design layer; trim unused Bootstrap parts
  once the design is known.
