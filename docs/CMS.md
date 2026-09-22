# CMS data architecture

Content models, publishing rules, media, menus, settings and redirects.
Everything here is managed through the admin panel (`docs/ADMIN.md`) and
rendered server-side.

## 1. Content models

### Page — `pages`

`title`, `slug` (unique), `excerpt`, `content`, `status`, `published_at`,
`template`, `is_featured`, `created_by`, `updated_by`, timestamps, soft
deletes. Public URL: `/{slug}` (single segment, lowercase ASCII/hyphen).

### Article — `articles`

`title`, `slug` (unique), `excerpt`, `content` (required),
`featured_image_id` → media, `status`, `published_at`, `author_id` → users,
`created_by`, `updated_by`, timestamps, soft deletes. Public URL:
`/articles/{slug}`. Many-to-many with categories via `article_category`.

### ArticleCategory — `article_categories`

`name`, `slug` (unique), `description`, `sort_order`, `is_active`. Deleting
a category removes only pivot rows; articles are untouched.

### Shared behaviour (traits in `app/Models/Concerns`)

- `Publishable` — `status` cast to `App\Enums\ContentStatus` (`draft` |
  `published`), `published_at` datetime, `published()` scope,
  `isPublished()`, `isScheduled()`.
- `HasAuditFields` — fills `created_by`/`updated_by` from the authenticated
  user on create/update; `creator()`/`editor()` relations.
- `HasSeo` — `seo()` morphOne, `saveSeo()`, `indexable()` scope.

## 2. Publishing rules

**Public = `status = published` AND `published_at <= now()`.** That single
rule (the `published()` scope) is used by public controllers, menus and the
sitemap, so drafts and future-dated ("scheduled") items can never leak.

- Saving with `status = published` and no date sets `published_at = now()`.
- A future `published_at` schedules the item; it flips live automatically
  without a job.
- Only users with `pages.publish` / `articles.publish` may submit
  `status = published` (enforced in the FormRequest's `authorize()`).
- Slugs: lowercase letters, digits and hyphens (`config('cms.slug_pattern')`),
  generated from the title when blank (`Str::slug`, which transliterates
  Persian titles — give Persian content an explicit slug). Page slugs may not
  use reserved words (`config('cms.reserved_slugs')`: admin, articles, build,
  storage, up, robots.txt, sitemap.xml, …).
- Soft deletes: pages and articles are trashed, not destroyed, so menu
  items, SEO rows and audit fields never dangle.

## 3. Editor strategy (intentional limitation)

`content` is a **plain textarea** in this phase. No CKEditor/TinyMCE yet,
because the HTML policy (allowed tags, sanitisation library, image uploads
inside content, embed rules) must be decided first. Until then stored
content is treated as text: `App\Support\Content\PlainText::toHtml()`
escapes everything and turns blank lines into paragraphs. When a rich editor
is introduced, stored HTML must be sanitised server-side before rendering
with `{!! !!}`.

## 4. Media library — `media`

`disk`, `path`, `filename`, `original_filename`, `mime_type`, `extension`,
`size`, `width`, `height`, `alt_text`, `title`, `caption`, `uploaded_by`.

- Files live on a Laravel disk (`public` → `storage/app/public/media/YYYY/MM/`,
  served via `public/storage`); the database holds metadata only. Switching
  to S3-compatible storage later means changing the disk, nothing else.
- Uploads go through `App\Support\Media\MediaService::upload()`: random UUID
  filenames, image dimensions detected, uploader recorded.
- Validation (`MediaStoreRequest`): allow-list `jpg, jpeg, png, webp, gif,
  pdf`, MIME sniffed from content (`mimetypes`), max 10 MB. **SVG and any
  executable/archive types are rejected** until an SVG sanitiser is chosen.
- `alt_text` is the accessibility/SEO text; leave it empty only for
  decorative images.
- Deleting a media row deletes the file; references (`featured_image_id`,
  SEO images, settings) are nulled by the database.

## 5. Menus — `menus`, `menu_items`

`Menu` (`name`, `location` unique: `main`, `footer`) → `MenuItem`
(`parent_id`, `label`, `url` | `page_id`, `target`, `sort_order`,
`is_active`). Exactly one of `url`/`page_id` is required; parents must be in
the same menu and cycles are rejected (`MenuItemRequest`).

`App\Support\Menus\MenuRepository::tree($location)` returns a cached,
render-ready tree; items pointing at unpublished pages or marked inactive
are dropped. Caches are flushed when menus, items or pages change. The
public header uses the `main` menu (falling back to `config/site.php`), the
footer uses `footer`.

## 6. Settings — `settings`

Typed key/value rows (`group`, `key`, `value`, `type`, `is_public`), unique
on `group+key`. The allowed keys, types and labels are declared in
`config/settings.php` (groups: `general`, `seo`, `contact`, `social`); the
seeder creates blank rows for every declared key.

Use the service, never the model directly:

```php
settings('general.site_name', config('site.name'));   // read with default
app(Settings::class)->set('seo.default_description', '…');
app(Settings::class)->group('contact');
```

All values are cached as one array and the cache is invalidated on every
write. `SeoManager` reads site name, tagline, default title/description,
default OG image and Twitter handle from settings first, then
`config/site.php` / `config/seo.php`.

## 7. Redirects — `redirects`

`source_path` (unique, site-relative), `destination_url` (relative or
absolute), `http_status` (301 | 302), `is_active`, `hit_count`,
`last_hit_at`, audit fields.

- `App\Http\Middleware\HandleRedirects` (global, before routing) handles
  GET/HEAD only, skips infrastructure prefixes
  (`/admin`, `/build`, `/storage`, `/up`, `/robots.txt`, `/sitemap.xml`,
  `/vendor`) and appends the original query string.
- `App\Support\Redirects\RedirectResolver` caches each looked-up path for
  an hour (misses included); the table is never scanned per request. Model
  events invalidate the affected paths.
- Hits are counted with a single `UPDATE … hit_count + 1` deferred until
  after the response is sent (`defer()`), so tracking adds no latency.
- Validation rejects protected/root sources, non-301/302 codes and loops:
  the resolver follows existing internal redirects up to 5 hops and refuses a
  rule that would lead back to its own source.

## 8. SEO metadata — `seo_meta`

See `docs/SEO.md` §1.1. Polymorphic overrides attached to Page and Article
via `HasSeo`; reusable by any future model that implements
`App\Contracts\Seoable`.

## 9. Sitemap

Sources in `config/seo.php`: static routes, published+indexable pages,
published+indexable articles (`lazy(500)` queries). Cache is flushed when a
page or article is saved/deleted.

## 10. Seeders

`php artisan db:seed` runs `RolesAndPermissionsSeeder`, `MenusSeeder`
(locations only) and `SettingsSeeder` (blank keys). No users, no content, no
company data — ever.

## Contact requests (home page form)

`contact_requests` stores submissions of the public contact form
(`POST /contact`, `App\Http\Controllers\Site\ContactRequestController`):
`name` (optional), `phone` (normalised to ASCII digits), `message`, `ip`,
`user_agent`, `handled_at`. Protection: CSRF, `StoreContactRequest`
validation, a hidden honeypot field (`website` must stay empty) and the
`contact-form` rate limiter (5 per minute per IP). Nothing is e-mailed; an
admin listing is a later phase.

## Menus used by the public layout

| Location | Rendered in |
|---|---|
| `main` | header navigation (split in two groups around the logo on desktop; offcanvas on mobile) |
| `footer` | footer "صفحات" column |
| `legal` | footer bottom row (privacy / terms links) |

Any location string is accepted by the admin; the three above are the ones
the layout reads. Footer contact and social blocks come from the `contact.*`
and `social.*` settings; the brand text from `general.footer_text`.

## Home page hero media

Settings → **صفحهٔ اصلی** (`home` group): `hero_video_media_id` (an MP4/WebM
uploaded to the media library) and `hero_image_media_id` (optional
replacement for the designed photo). With a video the hero renders a
muted, looping, `playsinline` `<video>` over the photo; the photo stays as
poster and as the fallback for reduced motion, no JavaScript, blocked
autoplay or unsupported formats. The media library accepts `video/mp4` and
`video/webm` up to 60 MB (images/PDF keep the 10 MB cap). IDs that do not
resolve to the right media type are ignored, so the page always renders.

## Page templates

`config/cms.php → page_templates` maps a template key (chosen in the page
form) to the public Blade view that renders it; a page without a template
uses `default` (`site/pages/show`).

| Template | View | Extras |
|---|---|---|
| `default` | `site.pages.show` | — |
| `about` | `site.pages.templates.about` (Figma 249:1038 / 258:33) | Settings → **صفحهٔ درباره ما**: two intro photos, partner logos (comma-separated media IDs), history title/text/photo, three statistics — read by `App\Support\Pages\AboutPage`; anything empty is not rendered and photo slots show the design's grey placeholder |

The page's own title, lead (`excerpt`) and body (`content`) are the CMS
fields. Inner pages get the light header pill (`.pg-header--light`); only
the home page floats the header over its hero.
