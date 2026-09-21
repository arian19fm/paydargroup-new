# Admin panel

Custom Blade/Bootstrap admin under `/admin`. No third-party admin package;
no public registration; staff accounts only.

## 1. Authentication

| Piece | Location |
|---|---|
| Routes | `routes/admin.php` — `GET/POST /admin/login`, `POST /admin/logout` (all other admin routes require `auth` + `admin`) |
| Controller | `App\Http\Controllers\Admin\Auth\LoginController` |
| Request | `App\Http\Requests\Admin\LoginRequest` — validation, `Auth::attempt` with `is_active = true`, rate limiting |
| Middleware | `auth` (framework), `admin` → `App\Http\Middleware\EnsureUserIsAdmin` |
| Guard | the default `web` session guard and `users` provider (`config/auth.php`) |

Behaviour:

- Credentials are checked together with `is_active`; an inactive account or a
  wrong password both return the same generic error (`auth.failed`).
- **Rate limiting:** 5 failed attempts per e-mail+IP lock the form for 60 s
  (`LoginRequest::MAX_ATTEMPTS`, `DECAY_SECONDS`), plus a route-level
  `throttle:10,1` on `POST /admin/login`.
- On success the session ID is regenerated (fixation defence),
  `last_login_at` is stored and the user is sent to the intended URL or the
  dashboard.
- Logout invalidates the session and regenerates the CSRF token.
- `EnsureUserIsAdmin` runs on every admin request: a user who was
  deactivated *after* logging in is logged out immediately; a user without
  `admin.access` gets 403.
- Guests are redirected to `/admin/login`; logged-in users hitting the login
  page go to the dashboard (`redirectGuestsTo` / `redirectUsersTo` in
  `bootstrap/app.php`).
- All forms are CSRF-protected by the `web` group. Passwords are hashed by
  the `hashed` cast on `User`.

## 2. Users

`users` table: `name`, `email` (unique), `password`, `is_active`,
`last_login_at`, timestamps. One model (`App\Models\User`) serves all staff;
roles distinguish capabilities. Accounts are never deleted from the admin —
they are deactivated — so `created_by`/`updated_by` references stay intact.

### Creating the first administrator

```bash
php artisan db:seed            # roles, permissions, menu locations, blank settings (idempotent)
php artisan admin:create       # interactive: name, e-mail, role, hidden password prompt
# or partially scripted (password is always prompted, never passed on the command line):
php artisan admin:create --name="Name" --email=admin@example.com --role=super_admin
```

Rules: valid unique e-mail; password ≥ 12 characters with letters and
numbers (`UserRequest::passwordRule()`, no external "pwned" lookup); the
password is never echoed. There is no default credential anywhere in the
repository or seeders.

### Managing users in the panel

`/admin/users`: list, create, edit (name, e-mail, optional new password,
roles, active flag) and activate/deactivate. Requires `users.view` /
`users.manage`. Nobody can deactivate themselves; only super admins can
change or deactivate other super admins (`UserPolicy`).

## 3. Roles and permissions

Implemented with `spatie/laravel-permission` (v8, Laravel 13 compatible),
`web` guard, no teams. Seeded by `Database\Seeders\RolesAndPermissionsSeeder`
(idempotent — re-run after adding a permission).

Permissions (`resource.action`):

```
admin.access
pages.view      pages.create      pages.update      pages.delete      pages.publish
articles.view   articles.create   articles.update   articles.delete   articles.publish
categories.view categories.manage
media.view      media.manage
menus.view      menus.manage
settings.view   settings.update
redirects.view  redirects.manage
users.view      users.manage
seo.manage
```

Roles:

| Role | Permissions |
|---|---|
| `super_admin` | **all** — synced to the full permission list on every seed run. Policies still execute for super admins so safety rules (e.g. self-deactivation) apply. |
| `admin` | everything except `users.manage` |
| `editor` | `admin.access`, pages/articles view+create+update (no publish, no delete), categories.view, media.view+manage, menus.view |

Enforcement (no scattered `if ($user->role === …)`):

- **Policies** (`app/Policies`) map abilities to permissions; controllers
  use `authorizeResource()`. `publish` abilities are checked inside the
  Page/Article FormRequests' `authorize()` whenever a submission sets
  `status = published`, so editors can save drafts but never publish.
- **Middleware**: `admin` on the whole group, `can:settings.view` on settings.
- **Views** only use `@can` to hide navigation/buttons.

## 4. Layout and screens

`resources/views/layouts/admin.blade.php`: sidebar (offcanvas below `lg`,
sticky on desktop, permission-filtered), topbar (view site, current user +
roles, logout), breadcrumb (`<x-admin.breadcrumb>` from a `$breadcrumbs`
array), page title (`@section('title')`), action slot (`@section('actions')`),
flash messages and validation summary. Form fields use
`<x-admin.form.input|textarea|select|checkbox>`; content forms embed the
reusable `<x-admin.seo-fields>` section (see `docs/SEO.md`). Lists paginate
20 rows (`config('cms.per_page')`) with Bootstrap 5 pagination.

| Screen | Route prefix | Notes |
|---|---|---|
| Dashboard | `/admin` | real counts only (pages, articles, media, redirects), filtered by permission |
| Pages | `/admin/pages` | search, status filter, SEO section |
| Articles | `/admin/articles` | categories (multi-select), author, featured image ID, SEO section |
| Categories | `/admin/categories` | |
| Media | `/admin/media` | upload, list, edit metadata (alt/title/caption), delete |
| Menus | `/admin/menus` | menu + inline item editor (nested via parent) |
| Redirects | `/admin/redirects` | |
| Settings | `/admin/settings/{group}` | generated from `config/settings.php` |
| Users | `/admin/users` | |

Visual design is structural only (pending Figma); Vue is not used in the
admin yet. Media pickers are plain numeric IDs for now.
