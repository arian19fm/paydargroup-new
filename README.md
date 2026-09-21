# Paydar Group — Corporate Website

Server-rendered, SEO-first corporate website for Paydar Group: company
introduction, services, articles/news and other managed content, with a
custom-built admin panel.

> **Independent project.** This is **not** Paydar Fund. It has its own
> repository, `.env`, MySQL database (`paydar_group`), database user,
> storage and deployment configuration. Never point it at a Paydar Fund
> database or share credentials between the two.

## Architecture (summary)

Full details: [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

| Layer     | Choice                                                                 |
|-----------|------------------------------------------------------------------------|
| Backend   | Laravel 13 (PHP ≥ 8.3), standard MVC, Blade server-side rendering       |
| Database  | MySQL 8, `utf8mb4` / `utf8mb4_unicode_ci` everywhere                    |
| UI        | Bootstrap 5 compiled from SCSS and flipped to RTL with RTLCSS; vanilla JavaScript, progressive enhancement |
| Widgets   | Vue 3 **only** for isolated interactive components — no SPA, no Nuxt/React, no Tailwind |
| Assets    | Everything self-hosted and built with Vite into `public/build`. **No runtime CDNs** (no Google Fonts, jsDelivr, unpkg, cdnjs, …) |
| Admin     | Custom admin panel under `/admin` (no Filament/Nova/etc.)               |
| SEO       | First-class: `seo()` manager + `<x-seo.head>`, JSON-LD, environment-aware robots.txt/sitemap.xml — see [`docs/SEO.md`](docs/SEO.md) |

### Directory conventions

```
app/Http/Controllers/Site/    public corporate pages
app/Http/Controllers/Admin/   custom admin panel
app/Support/Seo/              SeoManager, JsonLd, Sitemap sources/builder
app/Support/Localization/     text direction helper
app/Http/Middleware/          SetRobotsHeader (noindex outside production)
routes/web.php                public routes
routes/admin.php              admin routes (prefix /admin, name admin.*)
resources/views/layouts/      base layouts
resources/views/site/         public page views
resources/views/admin/        admin views
resources/views/components/   Blade components (seo/, layout/, ui/)
resources/views/partials/site shared partials (header, navigation, footer)
resources/views/errors/       404 / 500 / 503
resources/scss/               design tokens, Bootstrap overrides, base, layout, components
resources/js/                 app.js, site/ (vanilla), vue/mount.js, components/ (*.vue)
resources/fonts/              self-hosted WOFF2 fonts
lang/fa, lang/en              UI strings (Persian default)
config/site.php, config/seo.php   site identity, navigation, SEO defaults
docs/                         ARCHITECTURE.md, FRONTEND.md, SEO.md
```

## Requirements

- PHP 8.3+ with `pdo_mysql`, `mbstring`, `intl`, `gd`, `zip`, `openssl`, `xml`, `ctype`, `fileinfo`, `bcmath`
- Composer 2
- MySQL 8.x
- Node.js 20+ and npm (build-time only; production servers only need the built `public/build`)

## Local setup

```bash
git clone <repository-url> paydargroup-site
cd paydargroup-site

composer install
npm install

cp .env.example .env
php artisan key:generate
# edit .env → set DB_PASSWORD (and anything else you need)

# create the database (see "Database setup" below), then:
php artisan migrate
php artisan storage:link

npm run build          # or: npm run dev (Vite dev server with HMR)
php artisan serve      # http://localhost:8000
```

## Database setup

The project uses a **dedicated** MySQL database and user. Nothing here
touches any other database.

| Setting     | Value                 |
|-------------|-----------------------|
| Database    | `paydar_group`        |
| User        | `paydar_group`@`localhost` |
| Charset     | `utf8mb4`             |
| Collation   | `utf8mb4_unicode_ci`  |

Create them once as a MySQL admin (edit the password first — it must match
`DB_PASSWORD` in `.env`):

```bash
# option A: run the script
sudo mysql < docs/database/setup.sql

# option B: run the statements by hand
sudo mysql
```

```sql
CREATE DATABASE IF NOT EXISTS `paydar_group`
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'paydar_group'@'localhost' IDENTIFIED BY '<DB_PASSWORD from .env>';
GRANT ALL PRIVILEGES ON `paydar_group`.* TO 'paydar_group'@'localhost';
FLUSH PRIVILEGES;
```

Then verify:

```bash
php artisan db:show
php artisan migrate
```

Rules:

- Never `DROP DATABASE`.
- Never reuse or modify the Paydar Fund database (`paydargroup_db`) — note
  the similar name; the two are unrelated.
- All tables must be `utf8mb4` (the default connection enforces this).

Tests run against an in-memory SQLite database (see `phpunit.xml`) and never
touch MySQL.

## Development commands

```bash
php artisan serve                 # dev server on :8000
npm run dev                       # Vite dev server (HMR)
npm run build                     # production assets → public/build

php artisan migrate               # run migrations
php artisan migrate:status        # migration state
php artisan db:show               # connection / table overview

php artisan route:list            # all routes
php artisan about                 # environment overview

php artisan optimize:clear        # clear config/route/view/event caches
php artisan optimize              # cache config/routes/views (production)

php artisan test                  # PHPUnit test suite
vendor/bin/pint                   # code style (Laravel Pint)
```

## Environment variables worth knowing

| Variable | Purpose |
|---|---|
| `APP_URL` | Exact public origin — canonical URLs, sitemap and JSON-LD are built from it |
| `SEO_INDEXABLE` | Defaults to true only when `APP_ENV=production`; local/staging emit `noindex` + `X-Robots-Tag` and a disallow-all robots.txt |
| `SEO_DEFAULT_DESCRIPTION`, `SEO_DEFAULT_IMAGE`, `SEO_TWITTER_SITE` | Site-wide SEO fallbacks (omitted when empty) |
| `SITE_TAGLINE`, `SITE_LOGO_URL`, `SITE_SAME_AS` | Identity used in titles and Organization JSON-LD (omitted when empty) |

## Documentation

- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — architecture decisions and phase status
- [`docs/FRONTEND.md`](docs/FRONTEND.md) — Bootstrap RTL, SCSS/tokens, fonts, Vite, isolated Vue, Blade structure, accessibility, performance
- [`docs/SEO.md`](docs/SEO.md) — title/description/canonical/robots strategy, Open Graph, JSON-LD, sitemap plan, URL and image rules, Core Web Vitals
- [`docs/database/setup.sql`](docs/database/setup.sql) — database bootstrap script
