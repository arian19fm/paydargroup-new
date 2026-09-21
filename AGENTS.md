# Paydar Group Website — project guidelines

Read `docs/ARCHITECTURE.md`, `docs/FRONTEND.md`, `docs/SEO.md`,
`docs/ADMIN.md` and `docs/CMS.md` before making changes. Hard rules:

- Independent from Paydar Fund: never reference its code, database
  (`paydargroup_db`), credentials or storage.
- Laravel + Blade server-side rendering, standard MVC. No SPA.
- Bootstrap 5 (SCSS) + vanilla JS with progressive enhancement. Vue 3 only for
  isolated interactive widgets mounted in server-rendered pages. Never
  Nuxt, React or Tailwind.
- All assets self-hosted (bundled via Vite into `public/build` or placed in
  `public/`). Never load CSS/JS/fonts from external CDNs.
- SEO is first-class: set page metadata with `seo()->title()->description()…`
  in controllers; never hand-write `<title>`/`<meta>` in views. Semantic HTML,
  one H1, clean URLs, alt text and image dimensions.
- RTL comes from RTLCSS in the build; write SCSS in LTR terms or with logical
  properties — no manual RTL overrides. Design tokens live in
  `resources/scss/abstracts/_variables.scss`; values marked `TODO(figma)` are
  placeholders — do not invent brand values.
- Vue widgets mount only via `[data-vue-component]` (`resources/js/vue/mount.js`);
  no router, no Pinia, no global app.
- Custom admin panel under `/admin`; do not install admin packages. Authorise
  through policies/permissions (`resource.action`), never role string checks.
- Public content = `published()` scope only (status + date). Managed content
  gets SEO via `seo()->fromModel()`; overrides live in `seo_meta`, fallbacks
  are never stored. Content is plain text until the editor policy is decided.
- Never seed users, passwords or company data. Blade: use `@php … @endphp`
  blocks, not inline `@php(...)`, when a file also has a block.
- MySQL with `utf8mb4` everywhere. Never `DROP DATABASE`.
- Public controllers in `App\Http\Controllers\Site`, admin in `...\Admin`,
  routes in `routes/web.php` / `routes/admin.php`.
- Run `vendor/bin/pint` on changed PHP files; `php artisan test` before finishing.
