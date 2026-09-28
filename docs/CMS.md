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
`featured_image_id` → media (uploaded straight from the form or picked by
media ID), `status`, `published_at`, `author_id` → users, `created_by`,
`updated_by`, timestamps, soft deletes. Many-to-many with categories via
`article_category`.

- **Listing** `GET /articles` (Figma 276:13365 / 302:8): the home page's
  blog header, chips for the active categories (`?category={slug}`,
  unknown slug = 404, canonical keeps the filter), 9 cards per page in a
  3-column grid and the numbered pager. In the sitemap.
- **Article** `GET /articles/{slug}` (313:13 / 315:1483): eyebrow + H1,
  the cover, then the body beside a sidebar with the publication date, a
  table of contents and share links (copy link, X, Facebook, LinkedIn);
  the latest other posts and the contact card follow. `content` is plain
  text rendered by `App\Support\Content\ArticleBody`: `# …` lines become
  `<h2>` sections with anchor ids (these feed the table of contents),
  `- …` lines bullets, blank lines paragraphs. Article JSON-LD is emitted
  here only.

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

`Menu` (`name`, `location` unique: `main`, `footer`, `legal`) → `MenuItem`
(`parent_id`, `key`, `label`, `url` | `page_id`, `source`, `target`,
`sort_order`, `is_active`). Exactly one of `url`/`page_id` is required
unless the item has a `source`; parents must be in the same menu and cycles
are rejected (`MenuItemRequest`).

- **`source`** ("زیرمنوی خودکار", `MenuItem::SOURCES`): the item's children
  are generated from managed content instead of being entered by hand.
  `businesses` lists the `published()` businesses in their `sort_order`
  (label = title, link = the business page). Manually added children follow
  the generated ones. A source item without a page/URL of its own links to
  the source's default (`/#products`, the home page section).
- **`key`**: stable identifier of a default item (`home`, `about`,
  `businesses`, …) so `MenusSeeder` can create it once and never duplicate
  it. Items added in the admin have no key.

`App\Support\Menus\MenuRepository::tree($location)` returns a cached,
render-ready tree. Dropped from the tree: inactive items, items whose page
is unpublished and URL items that point at a CMS page slug (`/about`,
`/privacy`, …: a single segment that is not a reserved slug) while no
published page with that slug exists — so the seeded links appear by
themselves the moment the page goes live. Caches are flushed when menus,
items, pages or businesses change. The public header uses the `main` menu
(falling back to `config/site.php`), the footer uses `footer` and `legal`.

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

`php artisan db:seed` runs `RolesAndPermissionsSeeder`, `MenusSeeder` and
`SettingsSeeder` (blank keys). No users, no content, no company data — ever.

`MenusSeeder` creates the three locations and their default items, each with
a `key`, and is meant to run on every deploy (`php artisan db:seed --force`,
or `--class=MenusSeeder`):

| Menu | Default items (key → link) |
|---|---|
| `main` | home → `/`, about → `/about`, businesses → automatic submenu (`source = businesses`), team → `/team`, careers → `/careers`, articles → `/articles`, contact → `/contact` |
| `footer` | about, team, careers, articles, contact |
| `legal` | privacy → `/privacy`, terms → `/terms` |

Labels come from `lang/{locale}/nav.php`. A key that already exists is left
untouched, so labels, order and `is_active` edited in the admin survive
re-runs; a key that was deleted is recreated on the next run — deactivate a
default item instead of deleting it. Links to CMS pages stay hidden on the
site until the page is published (see §5), so the seeder never produces a
dead link.

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
the layout reads, and `MenusSeeder` fills them with the default items (§10).
Footer contact and social blocks come from the `contact.*`
and `social.*` settings; the brand text from `general.footer_text`; the
"خدمات" column lists the published businesses (the designed line-up until
one exists), exactly like the home page section.

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

## Contact page

`GET /contact` (`ContactPageController`, Figma 258:460 / 258:622) — a fixed
route, so `contact` is a reserved page slug. It renders the same request
form as the home page (`partials/site/contact-fields`, posting to
`contact.store`, which returns to whichever page carried the form), the
channels from Settings → contact (address, phone + working hours, e-mail —
empty values are skipped) and the map. `contact.map_url` takes any Google
Maps link (share link, place page, search URL or the embed code);
`App\Support\Contact\GoogleMapsEmbed` turns it into an embeddable URL
and the page shows a live map with an "open in Google Maps" link (short
`maps.app.goo.gl` links are resolved over HTTP once and cached for a
week). When the link is blank or not a Google Maps URL, the static image
`contact.map_image_media_id` is shown instead, linked to the URL. The page
is listed in the sitemap.

## Team page

`GET /team` (`TeamPageController`, Figma 229:12 / 246:675; `team` is a
reserved page slug, listed in the sitemap). Content is managed under
admin → **تیم**: `team_groups` (name, sort order, active) and
`team_members` (group, name, role, LinkedIn URL, photo, sort order,
active). The photo is uploaded straight from the member form (it lands in
the media library, titled after the member) or picked by media ID; a
transparent PNG cut-out works best on the toned card. Permissions `team.view` /
`team.manage` (editors view only). The page shows active groups in order,
each with its active members; groups without visible members are skipped,
and card tones alternate grey/warm per group automatically, as in the
frames. A member without a photo keeps the toned box.

## Businesses (کسب‌وکارها)

`businesses` (`Business` model: `Publishable`, `HasSeo`, `HasAuditFields`,
soft deletes): `title`, `slug` (unique), `eyebrow` (small line above the
name at the top of the page), `tagline` (card description + second line
of the intro heading), `features` (JSON list of tag texts, entered one per
line), `accent` (one of `fund | exchange | broker | ai | bot` — the card
palettes in `$pg-products`; its deep tone colours the name, intro heading
and button), `image_media_id` (uploaded from the form or picked by media
ID; the home card image, the page's hero image and the video poster),
`excerpt` (hero paragraph + SEO description fallback), `content` (intro
paragraphs, plain text until the editor policy is decided), `website_url`
("مشاهده سایت" button), `benefits_title` (blank = "«name» چه مزایایی
دارد؟"), `benefits_text`, `benefits` (JSON `[{title, description}]`,
entered one per line as `title | description`, max 8),
`benefits_media_id` (image, or MP4/WebM video rendered with native
controls plus the designed play button), `benefits_poster_media_id`
(banner shown over the video before playback, uploaded from the form;
blank = the business image), `status` / `published_at`, `sort_order`.

- Public page: `GET /businesses/{slug}` (`Site\BusinessController`, Figma
  205:119 / 220:5): hero → intro → benefits; a block whose fields are all
  empty is skipped. Published only; SEO via `seo()->fromModel()` with the
  reusable SEO fieldset in the form; `businesses` is a reserved page slug;
  published + indexable businesses are in the sitemap
  (`BusinessesSource`), whose cache is flushed on every change.
- Home page "our products" section (`partials/site/home/products`):
  published businesses in sort order replace the designed line-up from
  `config/home.php`, which is rendered only while no business is
  published. The section copy (eyebrow, highlighted title, title rest,
  text, CTA label and URL) is Settings → **صفحهٔ اصلی** (`home.products_*`)
  with the designed text as fallback; the CTA falls back to the
  `products` CMS page when published.
- Permissions `businesses.view` / `businesses.manage` / `businesses.publish`
  (editors: view + manage, no publishing).

## Careers page

`GET /careers` (`CareersPageController`, Figma 339:352 / 348:79; `careers`
is a reserved page slug, listed in the sitemap). Three blocks:

- **Intro**: eyebrow, H1 and the photo — Settings → **صفحهٔ فرصت‌های
  شغلی** (`careers.hero_*`); blank values fall back to the designed copy in
  `lang/{locale}/careers.php` and the designed photo
  (`public/images/careers/hero.*`, 20% dark wash in CSS).
- **Benefits**: eyebrow, two-tone heading, text and a button (blank link
  = contact page), then four cards whose icons are fixed per slot (book,
  tick, chart, money — Figma 341:52) and whose title/text come from
  `careers.benefit_{1..4}_*` with the designed copy as fallback; a slot
  with no title anywhere is skipped. The first card is the highlighted one.
- **Openings**: `job_openings` (`JobOpening`: title, category + badge
  tone from `JobOpening::TONES`, short description, employment type,
  location, `apply_url` — an `https://` page or an e-mail address, rendered
  as the "ارسال رزومه" link/mailto **instead of** the built-in form — sort
  order, active flag, audit fields, plus `body` (the full description shown
  in the detail dialog: plain text where `# …` lines are section headings,
  `- …` lines bullets, blank lines separate paragraphs — rendered by
  `App\Support\Content\JobBody`) and `specs` (JSON `[{label, value}]`,
  entered one per line as `label | value`, the table under the
  description). Managed under admin → **فرصت‌های شغلی** (`jobs.view` /
  `jobs.manage`, editors included). Active openings render in sort order,
  seven per page with the dot pager; `careers.jobs_empty` is shown when
  none is active. `App\Support\Careers\CareersPage` assembles it all.
- **Applying** (Figma 350:6108 / 358:13045): "ارسال رزومه" opens the
  detail modal (title, badge, meta, body, specs) whose button opens the
  application modal (phone + PDF/DOCX résumé ≤ 5 MB). Both are
  server-rendered per opening; without JavaScript the link goes to
  `/careers/{id}`, which shows the same detail and form inline.
  `POST /careers/{id}/apply` (`job-apply` limiter, 3/min per IP, honeypot)
  stores a `job_applications` row (job, its title at the time, phone,
  résumé metadata, ip, user agent, `seen_at`) and the file on the
  **private** `local` disk under `resumes/YYYY/MM/<uuid>`; the visitor is
  redirected back and the form re-opens with the success message.
- **Inbox**: admin → **درخواست‌های همکاری** (`applications.view` for
  everyone incl. editors, `applications.manage` to delete). The sidebar
  item carries the unseen count; opening a row or downloading its résumé
  (`/admin/applications/{id}/resume`, streamed from the private disk) marks
  it seen. Deleting a row deletes the file; deleting an opening keeps its
  applications.

## Home page FAQ

Admin → **سؤالات متداول** (`/admin/faqs`, `faqs.view` / `faqs.manage`,
editors included) manages the `faqs` table: `question`, `answer` (plain
text), `sort_order`, `is_active`. `HomePage::faqItems()` feeds the home
page accordion with the active questions in order (the first one starts
open). Until any row exists the designed samples in `lang/{locale}/home.php`
are shown; once questions exist but none is active, the list is omitted
and the rest of the section (heading, contact card) stays. The section's
headings and card copy are still the designed text in `home.faq.*`.
