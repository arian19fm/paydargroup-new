# CMS data architecture

Content models, publishing rules, media, menus, settings and redirects.
Everything here is managed through the admin panel (`docs/ADMIN.md`) and
rendered server-side.

## 1. Content models

### Page — `pages`

`title`, `slug` (unique), `excerpt`, `content`, `status`, `published_at`,
`template`, `is_featured`, `created_by`, `updated_by`, timestamps, soft
deletes. Public URL: `/{slug}` (single segment, lowercase ASCII/hyphen).

**Pages are fixed.** The admin lists and edits them but never creates or
deletes them, and their slug and template are locked (the `pages.create`
/ `pages.delete` permissions are gone). The set lives in
`config/cms.php → fixed_pages`; the `2026_09_28_140000_ensure_fixed_pages`
migration guarantees each exists (restoring a trashed one, never touching
another page):

| Slug | Template | Created as |
|---|---|---|
| `about` | `about` | published, with the Figma copy (249:1038 / 258:33); its extras (photos, partner logos, history, stats) are written into blank Settings → about rows, the images copied from `public/images/about` into the media library |
| `privacy` | default | draft, empty — footer link hidden until published |
| `terms` | default | draft, empty — footer link hidden until published |

To add a page later: add it to `fixed_pages` and to a migration.

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
  the latest other posts and the contact card follow. `content` is editor
  HTML (§3) rendered by `HtmlContent::render()`: headings start at `<h2>`
  (an `<h1>` is shifted down) and each top-level one gets an anchor id
  (its own `id`, else `section-N`) that feeds the table of contents. Article JSON-LD is emitted
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

## 3. Editor strategy

Rich bodies — `articles.content`, `businesses.content`, `pages.content`,
`job_openings.body`, `faqs.answer` — are **HTML** written in the admin with
`<x-admin.form.rich-editor>`. Excerpts, taglines and other short fields stay
plain text.

- **Editor** (`resources/js/admin/rich-editor.js`, Tiptap, vanilla, its own
  lazy chunk): the textarea holds the HTML and is what the form submits.
  Two modes — **ویرایشگر** (visual: paragraph, H2–H4, bold/italic/
  underline/strike, lists, quote, code block, rule, link, image, undo) and
  **HTML** (the textarea, for hand-written markup). Content holding markup
  the visual editor does not model (tables, classes, figure…) opens in HTML
  mode; in visual mode the textarea is only rewritten once something is
  edited there, so switching modes never loses markup by itself. Without
  JS the textarea is a plain HTML source field.
- **Pasted HTML source** (e.g. an article ChatGPT wrote as HTML): pasting
  text that is, as a whole, HTML (optionally in a ```` ```html ```` fence)
  into the visual editor inserts it as markup; into an empty editor it
  becomes the whole body (HTML mode when it holds markup the visual editor
  cannot). `HtmlContent::pastedSource()` does the same on save and render
  for content that arrived escaped anyway (a fence, or the whole body
  escaped inside only p/pre/code wrappers); content that merely quotes
  some markup is untouched.
- **Images**: the image button uploads to the media library
  (`POST /admin/media/editor-image`, `media` create permission, JPG/PNG/
  WebP/GIF ≤ 10 MB) and inserts `<img>` with alt text, width and height; an
  image URL can be used instead. Uploads are stored as content images
  (WebP, ≤ 750 px wide — see §4).
- **Sanitising** (`App\Support\Content\HtmlContent`, symfony/html-sanitizer):
  on save (`SanitizesRichContent` in the form requests, before validation,
  so empty markup counts as empty) **and** on render. Allow-list: p, br, hr,
  div, span, h1–h6, inline text tags, blockquote/q, pre/code, lists (incl.
  dl), a (`href` http/https/mailto/tel/relative, `target`, `rel`), img/
  picture/source (http/https/relative, `alt`, `width`, `height`…), figure/
  figcaption, tables, section, aside, details/summary, address, time; global
  `id`, `class`, `dir`, `lang`, `title`. Dropped: script, style elements and
  `style` attributes, iframe/embeds, forms, event handlers, `javascript:` /
  `data:` URLs. Layout wrappers (article, header, footer, main, nav, font,
  center) are unwrapped, their content kept. Input is capped at 1 MB
  (FAQ answers at the TEXT column's 64 KB).
- **Rendering**: `HtmlContent::toHtml($html, topHeading)` /
  `render()` (with TOC anchors, articles only). Headings are shifted so the
  shallowest sits at `topHeading`, keeping the hierarchy — articles and
  pages 2, business intro and job dialog 3, FAQ answers 4 — so the page
  keeps one H1 and a logical outline. Images get `loading="lazy"
  decoding="async"`, `target="_blank"` links `rel="noopener"`, tables a
  scrolling `.pg-content__table` wrapper. Text without any tag (legacy, or
  typed without JS) renders as paragraphs via `PlainText::toHtml()`. Shared
  element styles live under `.pg-content` (`base/_typography.scss`).
- The `2026_10_11_100000_convert_content_to_html` migration converted the
  stored plain text (`#` headings, `-` bullets, blank-line paragraphs).

## 4. Media library — `media`

`disk`, `path`, `filename`, `original_filename`, `mime_type`, `extension`,
`size`, `width`, `height`, `alt_text`, `title`, `caption`, `uploaded_by`.

- Files live on a Laravel disk (`public` → `storage/app/public/media/YYYY/MM/`,
  served via `public/storage`); the database holds metadata only. Switching
  to S3-compatible storage later means changing the disk, nothing else.
- Uploads go through `App\Support\Media\MediaService::upload()`: random UUID
  filenames, image dimensions detected, uploader recorded.
- **Content images** (article covers uploaded from the article form, images
  uploaded from the editor) go through `uploadContentImage()`:
  `App\Support\Media\WebpImage` re-encodes JPG/PNG/WebP as WebP scaled
  down to `cms.content_image.width` (750 px, never enlarged, quality 82),
  applying the JPEG EXIF rotation and keeping transparency. GIFs (animation)
  and images GD cannot decode (or over 40 MP) are stored unchanged. Uploads
  in the media library itself keep the original file.
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

**Enamad seal** — `general.enamad_code` (type `enamad`): the admin pastes
the snippet Enamad issues (or its link). `App\Support\Trust\Enamad`
validates it (must contain `trustseal.enamad.ir` with `id` and `Code`) and
extracts those two values; the footer renders its own markup from them
(`<a referrerpolicy="origin">` + logo image from `trustseal.enamad.ir`,
with the `code` attribute Enamad's checker expects), so pasted HTML is
never output. Blank or invalid = no seal in the footer.

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
"خدمات" column lists the published businesses, exactly like the home page
section (the column is omitted when none is published).

## Home page content

Every text, button, link and background image of the home page is a
setting in Settings → **صفحهٔ اصلی** (`home` group in
`config/settings.php`), grouped in the form by section (hero, businesses,
latest articles, FAQ, contact; a key's `section` entry starts a heading).
The cards themselves come from their own screens: businesses, published
articles and FAQ questions. `HomePage::sections($links)` returns the copy
per section:

- **Blank text = not rendered.** Exceptions: the H1 (both hero lines
  blank → the site name) and the contact submit label (blank → the
  generic label in `lang/*/home.php`), since neither may be missing.
- **Links** use the `link` setting type: an `https://…` URL, a site path
  (`/about`) or an anchor (`#contact`); anything else (e.g. `javascript:`)
  fails validation. A blank link falls back to the natural target — the
  `about` / `products` CMS page when published, the article listing, the
  contact form on the page.
- **Images** (`hero_image_media_id`, `contact_image_media_id`): blank →
  the designed photo in `public/images/home`.
- The article listing header and the contact page intro reuse the blog
  and contact section copy, so one edit changes both places.

The designed copy and the two designed photos (copied into the media
library under `media/designed/`) were written into these settings by the
`2026_09_28_130000_fill_home_page_settings` migration, only into rows
that were still blank, so the admin shows exactly what the page renders.
`lang/*/home.php` now holds interface strings only (form labels,
accessibility labels, messages, footer headings).

## Home page hero media

Settings → **صفحهٔ اصلی** (`home` group): `hero_video_media_id` (an MP4/WebM
uploaded to the media library) and `hero_image_media_id` (the video's
cover). The hero has no background photo on any size — only the brand
gradient (see `FRONTEND.md`). The video renders as a framed 16:9 box at
the top of the copy column — under the globe on mobile/tablet, above the
eyebrow on desktop — with native controls; it does not autoplay, the
image is its poster until the visitor presses play (without one the
first frame shows), and it pauses when scrolled out of view. The media
library accepts `video/mp4` and `video/webm` up to 60 MB (images/PDF keep
the 10 MB cap). IDs that do not resolve to the right media type are
ignored, so the page always renders. Media URLs in pages are
root-relative (`Media::url()`), so they load from whatever host serves
the page; sharing metadata uses `Media::absoluteUrl()`.

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
body, editor HTML §3, headings from H3), `website_url`
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
  the published businesses in sort order — there is no hardcoded
  fallback; with none published the showcase is omitted. Each product
  shows its tagline and its first three features (the rest stay on the
  business page). The five
  designed businesses (Fund, Exchange, Broker, AI, Bot, with their card
  images copied into the media library under `media/designed/`) were
  moved into the table by the `2026_09_28_120000_import_designed_businesses`
  migration, which runs only when the table is completely empty (trashed
  rows included). The section copy (eyebrow, highlighted title, title rest,
  text, CTA label and URL, card link label) is Settings → **صفحهٔ اصلی**
  (`home.products_*`, see “Home page content”); the CTA falls back to the
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
  in the detail dialog: editor HTML (§3), headings from H3) and `specs` (JSON `[{label, value}]`,
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
editors included) manages the `faqs` table: `question`, `answer` (editor
HTML §3, optional), `sort_order`, `is_active`. `HomePage::faqItems()` feeds
the home page accordion with the active questions in order (the first one
starts open). The answer is rendered with `HtmlContent::toHtml()` (§3),
headings from H4, without generated anchors. An
unanswered question shows `home.faq.answer_pending` and is flagged in the
admin list. With no active question the list is omitted and
the rest of the section (heading, contact card) stays. The five designed
questions were moved into the table by the
`2026_09_28_110000_import_designed_faqs` migration (only when the table is
empty) — there is no hardcoded fallback, so the site shows exactly what the
admin lists. The section's headings and card copy are still the designed
text in `home.faq.*`.

## Automatic news (اخبار خودکار)

Admin → **اخبار خودکار** (`/admin/news`, `news.manage`: admin and super
admin, not editors) lets Claude write news articles from the latest
financial, investment and crypto news.

- **Setup**: put `ANTHROPIC_API_KEY` in the server's `.env` (read through
  `config('services.anthropic.key')`, so `config:cache` is fine; optional
  `ANTHROPIC_BASE_URL` for a relay — the API is not reachable from Iranian
  IPs directly). The Laravel scheduler must run:
  `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`. The
  admin page warns when the key or the scheduler is missing.
- **Settings** (hidden `news` settings group, edited only on that page): on/off,
  time of day (`config('news.timezone')`, default Asia/Tehran), articles per
  run (1–5), publish automatically or keep as draft, topics (one per line;
  blank = `config('news.default_topics')`), category, author, default cover
  media ID (no images are generated).
- **When it runs**: `news:tick` every minute (`routes/console.php`,
  `withoutOverlapping`). `App\Support\News\NewsRunner::tick()` marks runs
  stuck in "running" for 30 min as failed, executes a run queued by
  "اجرا همین حالا", otherwise creates the day's scheduled run once the
  configured time has passed (a missed minute still runs that day; one
  scheduled run per day). `php artisan news:run` runs once immediately.
- **Writing** (`NewsWriter`, official `anthropic-ai/sdk`): one Messages
  request to `config('news.model')` (default `claude-opus-5-5`, adaptive
  thinking, effort `config('news.effort')`) with the server-side
  `web_search_20260209` tool (`max_searches`) and a strict
  `submit_articles` tool whose schema is the article (title, English slug,
  excerpt, seo_title, seo_description, content_html, sources). `pause_turn`
  is resumed up to `max_continuations` times; the server-side refusal
  fallback (`fallbacks: "default"`) is enabled. The prompt carries the date
  and the last 7 days' headlines to avoid repeats.
- **Publishing** (`NewsPublisher`): body sanitised by `HtmlContent`; sources
  kept only if their host appeared in the run's search results; an item
  without a verifiable source or with under ~300 characters of text is
  skipped. A "منابع" list (`rel="nofollow noopener"`) is appended,
  `seo_meta` gets the SEO title/description and `schema_type = NewsArticle`,
  the slug is made unique, the sitemap cache is cleared. Articles keep
  `news_run_id` and `sources` (JSON) and show a «خودکار» badge in the list.
- **Log**: `news_runs` (trigger, status, error, token usage, searches); the
  admin page lists runs with their articles and an estimated cost from
  `config('news.pricing')`.
