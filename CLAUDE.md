# Starter kit (platform blueprint)

The reusable platform every site (Şahid first) extends from. See `../CLAUDE.md` for the workspace decisions and
`../HANDOFF.md` for the plan. UI text and many comments are Turkish; keep that.

## Current state: aligned with Şahid's stack and module conventions

The reference implementation is `../sahid-medya-haber-portal` on branch `furkan_dev`. Where the two differ, change
this repo to match Şahid. Don't copy starter-kit patterns into Şahid.

Done (2026-10-12, uncommitted on `furkan_dev`):

- Stack: PHP ≥ 8.4 · Laravel 13 · Filament 5 (Livewire 4) · Inertia 3 + Vue 3 · Pinia 4 · Tailwind 4 · Vite 8 ·
  Pest 5. Spatie: permission 8, medialibrary 11, responsecache 8, sluggable 4. Stripe 22, iyzico 2.
- Module registry `modules.list` (`prefix` env-overridable) read only through `App\Support\Modules` (`all()`, `get()`,
  `enabled()`, `isEnabled()`, `path()`, `namespace()`); never `config('modules...')` directly. `all()`/`get()` exist
  here only; add them to Şahid when the two are unified.
- `ModuleServiceProvider`: provider, `Routes/web.php` (route names `{key}.`), `Routes/api.php` (`/api/{prefix|key}`,
  names `api.{key}.`), `Views/`, `Database/migrations/`, `Console/*.php`. Route files use short names
  (`->name('pages.show')` → `corporate.pages.show`).
- `AdminPanelProvider` discovers `Filament/{Resources,Pages,Widgets}` for enabled modules only, keeps v3 layout/filter
  behaviour app-wide in `boot()`, and uses `PreventRequestForgery`.
- Configs hold only what differs from package defaults (`permission`, `media-library`, `sanctum`, `view` removed;
  `filament`, `responsecache` (v8 format, `InertiaAwareCacheProfile`) and `inertia` (`js/Pages` lookup) slimmed or
  added). Cache, session and queue default to `database` (shared cPanel, no Redis).
- Tests: `tests/Feature/ModuleToggleTest.php` boots the app with a module disabled; `AdminPanelTest.php` renders the
  list and create page of every module resource.

Ported on 2026-10-12 (second pass, uncommitted), generic versions of Şahid's platform support:

- SEO: `App\Support\Seo\Seo::page([...])` as each page's `head` prop (shared default in `HandleInertiaRequests`),
  printed by `app.blade.php`, kept in sync by `createInertiaApp({ serverHead: true })`. `/sitemap.xml` from
  `App\Support\Seo\Sitemap::add()` (every module provider registers its published records), `/robots.txt`,
  `config/seo.php` (`indexable`, `trailing_slash`, `title_separator`). Don't put `<meta>` in Vue `<Head>` without a
  matching `head-key`. `public/robots.txt` was removed: it shadowed the route.
- Publication and cache: content models use `App\Support\Content\Publishable` (`published()` scope, flag +
  optional publish-at column; show pages 404 for drafts and scheduled records) and
  `App\Support\ResponseCache\Concerns\InvalidatesResponseCache`. Public GET routes carry `cacheResponse`. Modules
  with a publish-at column register it with `ScheduledCacheBoundaries`; `routes/console.php` purges once a boundary
  passes (`Schedule::call` only: hosts disable `exec`/`proc_open`).
- Routing: root content paths via `App\Support\Routing\ContentPaths::register()` (route `content`, registered last;
  `ContentPaths::systemPrefixes()` keeps the panel/API/Livewire out), 301 rules via `LegacyRedirects::add()` (global
  `RedirectLegacyUrls`). Trailing slash (`SEO_TRAILING_SLASH`, default off) applies to module web routes and `content`
  (marker middleware `trailingSlash`); `EnforceTrailingSlash` 301s to the configured form in both directions, so the
  Apache slash-stripping rule is gone from `public/.htaccess`. `UrlGenerator`/`resources/js/route.js` build matching
  URLs. Vue uses `route()` from `@/route` (registered globally), not `ZiggyVue`. Tests: `tests/TestCase.php` keeps
  trailing slashes the test client would drop.
- cPanel deploy: `scripts/build-release.sh` (`EXCLUDES` = what never ships), `.github/workflows/release.yml`,
  `CPANEL_DEPLOY.md`, root `.htaccess`. The VPS workflow is now `deploy.yml.disabled`; `deploy/` and Docker files are
  not the production path.
- `/kurulum/{token}` (`SetupController`), hardened compared to Şahid: exists only while `SETUP_TOKEN` is set
  (`config('app.setup_token')`), throttled 5/min, runs without session middleware (works on an empty database with
  `SESSION_DRIVER=database`), clears the response cache, `noindex`. In production `DatabaseSeeder` creates the admin
  only from `SETUP_ADMIN_EMAIL`/`SETUP_ADMIN_PASSWORD`, never `admin@example.com`/`password`.
- Admin panel path is `config('filament.path')` (env `FILAMENT_PATH`), so `config:cache` keeps it.

Generic names differ from Şahid (`ResponseCacheInvalidator`, `InvalidatesResponseCache`, `Publishable`; Şahid still has
the Media-named `MediaHomepageCacheInvalidator`/`InvalidatesHomepageCache`). Take starter-kit's names when unifying.

Deliberately not ported (WordPress-specific): `wp:import*`, `WordPressDatabase`/`WordPressMediaUrlGenerator`,
`config/wordpress.php`, `/?p=` / `/?page_id=` redirects (`RedirectLegacyIds`, `ContentPaths::registerLegacyId`),
lowercase percent-encoding in `UrlGenerator`, `HasSlugPath` (nested page paths; port it with the first module that
needs `/a/b` paths). Also not ported: trusted proxies (Cloudflare deferred), Şahid's Media-module features (RSS feed,
view beacon, ads).

Starter-kit-only pieces that Şahid lacks and are worth keeping: Breeze-style auth (`app/Http/Controllers/Auth`,
`routes/auth.php`, `Pages/Auth`), an SSR entry (`resources/js/ssr.js`, `npm run build:ssr`), Ziggy (`@routes` +
`ZiggyVue`), the Ecommerce and RealEstate modules, and Filament resources for every module.

Filament's published assets under `public/js/filament`, `public/css/filament` and `public/fonts` are tracked in git.
Commit them after a Filament upgrade.

## Commands

```bash
composer install && npm ci
npm run build                           # feature tests need public/build/manifest.json
APP_KEY=base64:$(openssl rand -base64 32) vendor/bin/pest
vendor/bin/pint <touched-php-files>     # never format the whole repo
```

No `.env` is used locally; pass env vars inline and use SQLite. Every test then warns about the missing `.env`;
that is expected. Local preview: browser pane config `starter-kit-local` (workspace `.claude/launch.json`, port 8126,
DB `../.claude/local/starter-kit-local.sqlite`, seeded admin from `database/seeders/DatabaseSeeder.php`).

## Bundled Claude tooling (`.claude/`)

`skills/{corporate,ecommerce,media,realestate}-cms`, matching `agents/*-architect.md` and `commands/*` describe a
**planned** module design: a `status` enum workflow, `media_*` table names, tags, AMP/RSS, and Filament v3 APIs
(the code is on Filament 5 now).
Neither repo's code currently matches it. Treat these as design notes, not as the current convention. When they
conflict with the code or with this file, the code and this file win. Update them as modules get aligned.
