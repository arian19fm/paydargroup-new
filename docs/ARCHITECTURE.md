# Paydar Group Website — Architecture

Status: Phase 2 (frontend foundation, RTL, SEO core). This document records
the decisions that later phases must build on. It is intentionally
prescriptive. Implementation details: [`FRONTEND.md`](FRONTEND.md),
[`SEO.md`](SEO.md).

## 1. Purpose and scope

A corporate / introduction / content-driven website for Paydar Group:
company pages, services, articles and news, media, and a custom admin panel
to manage all of it. It is **not** a web application or SPA, and it is
**completely independent from Paydar Fund** (separate repository, `.env`,
MySQL database and user, storage, and deployment).

## 2. Core decisions

| Decision | Rule |
|---|---|
| **Rendering** | Laravel + Blade **server-side rendering**. Every public page is fully rendered HTML on first response. Content must never depend on JavaScript to appear. |
| **Backend** | Laravel 13, standard MVC (routes → controllers → Eloquent models → Blade views). Form requests for validation, policies for authorization, services in `app/Support` where logic outgrows controllers. |
| **Database** | MySQL 8, dedicated database `paydar_group`, dedicated user, `utf8mb4` / `utf8mb4_unicode_ci` for every table and column. |
| **UI framework** | **Bootstrap 5**, compiled from SCSS (`resources/scss/app.scss`) so brand variables are overridden at the source. No Tailwind. |
| **JavaScript** | **Vanilla JavaScript by default**, written as **progressive enhancement**: the page works without it; JS only improves it (menus, tabs, lazy media, forms UX). |
| **Vue** | **Vue 3 only for isolated components that genuinely need reactivity** (e.g. an interactive calculator, a filterable list). Each widget mounts onto a specific element (`data-vue="..."`) inside a server-rendered page. **No Vue SPA, no Nuxt, no React, no client-side routing.** |
| **Assets** | **Everything self-hosted.** Bootstrap CSS/JS, Vue runtime, icons, fonts and any JS library are bundled by Vite into `public/build` or copied into `public/` and served from this server. |
| **Admin panel** | **Custom-built** under `/admin` (`routes/admin.php`, `App\Http\Controllers\Admin`, `resources/views/admin`). No Filament, Nova, Backpack, etc. |
| **SEO** | **First-class requirement**; see §5. Every content type gets SEO fields, every page a proper head, and the site ships sitemap, robots and redirect management. |

## 3. Availability: no external runtime dependencies

Iranian internet access may be restricted. The production site must render
correctly with **zero requests to third-party hosts**.

- Do **not** load anything from jsDelivr, unpkg, cdnjs, Google CDN/Fonts,
  Bunny Fonts, or similar.
- Bootstrap, Popper, Vue (production build), Bootstrap Icons (if used) and
  every other library are installed via npm and bundled locally by Vite, or
  vendored into `public/`.
- Fonts are stored in `resources/fonts` (WOFF2 only, licence alongside) and
  declared with `@font-face` in `resources/scss/base/_fonts.scss`; Vite
  fingerprints them into `public/build`. Interim family: Vazir (open
  licence). See `resources/fonts/README.md`.
- Maps, video embeds, analytics or CAPTCHAs that require external services
  are optional enhancements: the page must degrade gracefully if they fail
  to load, and they must never be on the critical rendering path.
- Reviewers should grep built assets and Blade templates for `https?://`
  before release.

## 4. Frontend structure

```
resources/scss/              abstracts (tokens + Bootstrap overrides) → bootstrap → base → layout → components → pages
resources/js/app.js          selective Bootstrap modules, vanilla enhancements, isolated Vue mounting
resources/js/vue/mount.js    [data-vue-component] registry — Vue loaded only when a target exists
resources/js/components/     *.vue single-file components (widgets only)
resources/fonts/             self-hosted WOFF2 fonts
resources/views/layouts/     site.blade.php (public), admin.blade.php
resources/views/components/  seo/, layout/, ui/ Blade components
resources/views/partials/    site/header, navigation, mobile-navigation, footer
resources/views/errors/      404 / 500 / 503
```

- `vite.config.js` builds one CSS and one JS bundle (Vue in a lazy chunk);
  `@vite()` in the layout resolves hashed filenames from
  `public/build/manifest.json`.
- Vue uses the **runtime-only** build (templates compiled at build time).
- **RTL**: the compiled stylesheet is flipped by RTLCSS (`postcss.config.js`),
  Bootstrap's official RTL method. `<html dir>` comes from
  `App\Support\Localization\Direction` based on the locale. The site is
  RTL-only; the LTR/RTL decision lives in the build + layout only.
- **Design tokens**: `$pg-*` SCSS variables in `abstracts/_variables.scss`
  drive both Bootstrap variables and `--pg-*` custom properties. All values
  are neutral placeholders until mapped from Figma (`TODO(figma)`).
- Icons: if Bootstrap Icons are needed, install `bootstrap-icons` via npm
  and bundle the font files locally; alternatively inline SVGs.

Full details: [`FRONTEND.md`](FRONTEND.md).

## 5. SEO architecture (requirements for later phases)

SEO is designed in, not bolted on. The system **must** support:

### 5.1 Per-page head
- Unique `<title>` per page (with a site-wide template, e.g. `Page — Paydar Group`).
- Meta description.
- Canonical URL (absolute, always emitted, respects pagination and query stripping).
- Robots directives (`index/noindex`, `follow/nofollow`, and per-page overrides).
- OpenGraph metadata (`og:title`, `og:description`, `og:image` with dimensions, `og:type`, `og:url`, `og:locale`, `og:site_name`).
- Twitter card metadata (`twitter:card`, `twitter:title`, `twitter:description`, `twitter:image`).
- Structured data / **JSON-LD** (`Organization`, `WebSite`, `WebPage`, `Article`/`NewsArticle`, `BreadcrumbList`, `Service`, `FAQPage` where relevant).
- `hreflang` / alternate links if a second language is ever added.

Implemented (Phase 2): the request-scoped `App\Support\Seo\SeoManager`
(`seo()` helper) is filled by controllers (or from a model's SEO fields)
and rendered by `<x-seo.head />` from the site layout, so every page emits a
complete, consistent head. `App\Support\Seo\JsonLd` builds structured
data; `SetRobotsHeader` middleware, `/robots.txt` and `/sitemap.xml` are
environment aware. Details: [`SEO.md`](SEO.md).

### 5.2 Managed-content SEO fields
Every manageable content type (pages, articles/news, categories, services,
media landing pages, …) carries custom SEO fields editable in the admin:
`meta_title`, `meta_description`, `canonical_url` (override), `robots`
(index/noindex, follow/nofollow), `og_title`, `og_description`, `og_image`,
`twitter_*` overrides, and structured-data extras. Sensible fallbacks derive
from the content itself when a field is empty.

Suggested modelling: a polymorphic `seo_meta` table (`seoable_type`,
`seoable_id`, fields above) attached via a `HasSeo` trait, plus site-wide
defaults in a settings table.

### 5.3 Site-level
- **XML sitemap** (`/sitemap.xml`, split into sitemap index + per-type sitemaps when large), generated from published content with `lastmod`; cached and regenerated on publish.
- **`robots.txt`** managed by the application (environment aware: disallow all on non-production).
- **301 redirect management**: a `redirects` table (`from_path`, `to_url`, `status_code`) editable in the admin, applied by middleware before routing; slug changes automatically create a redirect from the old URL.
- **Clean URLs**: lowercase, hyphenated slugs, no file extensions, no trailing slashes (enforced by middleware), no query-string driven pages; stable slugs stored per content item.
- **Breadcrumbs**: rendered in HTML (`<nav aria-label="breadcrumb">`) and as `BreadcrumbList` JSON-LD, driven by the same data.
- **SEO-friendly pagination**: `?page=N` with self-referencing canonical per page, `rel="prev"/"next"` links, and `noindex` for empty/filtered pages where needed.
- **Index/noindex control** globally (per environment) and per page.

### 5.4 Markup and media
- **Semantic HTML**: `header`, `nav`, `main`, `article`, `section`, `aside`, `footer`; one **H1 per page**, then a logical H2/H3 hierarchy that reflects the content outline — never used for styling.
- **Images**: mandatory `alt` text on managed media (a required admin field, with a "decorative" flag that yields `alt=""`); explicit `width`/`height` attributes; responsive variants (`srcset`/`sizes`) and modern formats (WebP) generated on upload; `loading="lazy"` for below-the-fold images only.
- **Accessibility**: landmarks, skip link, focus styles, form labels, sufficient contrast, keyboard-operable navigation; Bootstrap components used with their ARIA attributes.

### 5.5 Performance / Core Web Vitals
- Server-rendered HTML with critical content first; no render-blocking third-party requests (§3).
- One minified CSS and one JS bundle, hashed for long caching; JS `defer`red.
- Fonts self-hosted with `font-display: swap` and preloaded when used above the fold.
- Image dimensions always set (avoids CLS); responsive images; lazy loading below the fold.
- Full-page / fragment caching for public pages, and query caching for navigation and settings.
- Target: good LCP, INP and CLS scores on mobile.

## 6. Admin panel (custom)

- Lives under `/admin` (`routes/admin.php`, prefix `admin`, names `admin.*`), guarded by Laravel's session auth (`users` table = admin users initially; roles/permissions added when needed).
- Bootstrap-based Blade UI, same asset pipeline; Vue widgets only where a rich control is truly needed (e.g. media picker).
- Manages: pages, articles/news, categories/tags, media library, SEO fields, redirects, site settings, menus.
- Content editing produces sanitized HTML (server-side purification) and stores plain-text excerpts for meta descriptions.

## 7. Content and media (direction)

- Content types are Eloquent models with `slug`, publication status (`draft`/`published`, `published_at`), SEO relation (§5.2), and author.
- Media is stored on the `public` disk (`storage/app/public` → `public/storage` via `storage:link`); a `media` table records original + generated variants, dimensions, alt text and captions. Storage is private to this project.
- Persian (`fa`) is the default locale with `en` fallback; timezone `Asia/Tehran`. Dates are stored in UTC and displayed in the app timezone (Jalali formatting can be added at the presentation layer).

## 8. Environments and deployment

- `.env` is never committed; `.env.example` documents every variable.
- Production: `APP_DEBUG=false`, `APP_ENV=production`, `php artisan optimize`, `npm run build` output committed to the release artifact or built in CI — never built from a CDN.
- Separate deployment configuration and server paths from Paydar Fund.

## 9. Phase status

- **Phase 1** — Laravel, MySQL, build pipeline, repository structure. Done.
- **Phase 2** — Bootstrap RTL build, self-hosted fonts, SCSS/design-token
  architecture, Blade layout system, SEO core (head, JSON-LD, robots,
  sitemap, breadcrumbs), accessibility/performance foundations, error pages,
  tests, documentation. Done — with all visual values pending Figma.
- **Later** — Figma token mapping and visual pages, admin panel and
  authentication, content models (pages, articles, media) with SEO fields,
  redirects, media processing, full-page caching.
