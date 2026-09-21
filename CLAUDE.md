# Paydar Group Website — project guidelines

Read `docs/ARCHITECTURE.md` before making changes. Hard rules:

- Independent from Paydar Fund: never reference its code, database
  (`paydargroup_db`), credentials or storage.
- Laravel + Blade server-side rendering, standard MVC. No SPA.
- Bootstrap 5 (SCSS) + vanilla JS with progressive enhancement. Vue 3 only for
  isolated interactive widgets mounted in server-rendered pages. Never
  Nuxt, React or Tailwind.
- All assets self-hosted (bundled via Vite into `public/build` or placed in
  `public/`). Never load CSS/JS/fonts from external CDNs.
- SEO is first-class: semantic HTML, one H1, per-page meta/canonical/OG/JSON-LD
  through the shared SEO partial, clean URLs, alt text and image dimensions.
- Custom admin panel under `/admin`; do not install admin packages.
- MySQL with `utf8mb4` everywhere. Never `DROP DATABASE`.
- Public controllers in `App\Http\Controllers\Site`, admin in `...\Admin`,
  routes in `routes/web.php` / `routes/admin.php`.
- Run `vendor/bin/pint` on changed PHP files; `php artisan test` before finishing.
