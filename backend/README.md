# Storefront API (Laravel 12)

Modular JSON API behind the Nuxt storefront in the companion `frontend`
project. Built on `nwidart/laravel-modules`; each domain owns its models,
repositories, requests, resources and routes.

## Modules

| Module | Responsibility |
|---|---|
| `User` | Sanctum login/logout, the admin flag |
| `Brand` | Brand catalogue |
| `Category` | Category **tree** (adjacency list + cached `depth`) |
| `Product` | Products, media, attributes |
| `Settings` | Everything the public site renders, editable from the admin |
| `Seo` | `sitemap.xml`, `robots.txt`, structured data |

## Getting started

```bash
composer install
cp .env.example .env          # if you do not already have one
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

`migrate:fresh` is required on an existing install: the users table moved to a
UUID primary key and gained `is_admin`, categories gained `depth`, and the base
migrations were given timestamp prefixes so they run before the ones that
alter them.

The seeder creates the administrator from the `ADMIN_*` variables in `.env`.
**Change `ADMIN_PASSWORD` before deploying.**

### Environment

| Variable | Purpose |
|---|---|
| `FRONTEND_URLS` | Comma-separated CORS allow-list. Empty means no cross-origin request is accepted — never `*`. |
| `TRUSTED_PROXIES` | Proxies whose `X-Forwarded-For` is honoured. **Set this to the Nuxt server's IP.** |
| `SITE_URL` | Canonical public origin for sitemap and canonical links. Falls back to the first `FRONTEND_URLS` entry. |
| `OUTSIDE_STORAGE` | Root of the `outside` upload disk. |
| `ADMIN_*` | Seeded administrator account. |

> `TRUSTED_PROXIES` matters more than it looks. The storefront is server-side
> rendered, so during SSR every API call for a page arrives from the Nuxt
> server's single address. Without this, the rate limiter keys all visitors to
> that one IP and throttles the whole site after a handful of page views. It is
> empty by default on purpose: trusting an unknown proxy would let anyone spoof
> the header and slip past every per-IP limit.

## Authorization

`auth:sanctum` proves a caller holds a valid token; it says nothing about what
that token may do. Every `/api/admin/*` route therefore also runs the `admin`
middleware (`EnsureIsAdmin`), which checks the `is_admin` flag. Without it any
registered account could overwrite site settings.

## Rate limits

All limiters are defined in `AppServiceProvider::configureRateLimiting()` and
return JSON, so a blocked request stays readable by the frontend. The keying
strategy lives there; the per-minute budgets live in `config/rate_limits.php`
and are env-driven, so a deployment can be tuned without a code change.

| Limiter | Default | Env variable | Applied to |
|---|---|---|---|
| `api` | 120/min | `RATE_LIMIT_API` | whole API group (global ceiling) |
| `public-read` | 90/min | `RATE_LIMIT_PUBLIC_READ` | catalogue browsing, settings, SEO |
| `admin-write` | 30/min | `RATE_LIMIT_ADMIN_WRITE` | create / update / delete |
| `uploads` | 20/min | `RATE_LIMIT_UPLOADS` | file uploads |
| `auth` | 5/min | `RATE_LIMIT_AUTH` | login, keyed by **both** IP and submitted email |

> The frontend can snapshot this API into a JSON file and serve it when the
> API is unreachable (`pnpm fixtures` over there). A snapshot of a large
> catalogue makes hundreds of reads in a row and will trip `public-read`
> repeatedly; the script honours `Retry-After` and waits, so this is slow
> rather than broken. Raising `RATE_LIMIT_PUBLIC_READ` for the duration of a
> capture is a reasonable shortcut.

## Caching

Read-heavy repositories embed a version counter in every cache key. A write
bumps that counter, which orphans every cached read at once — tag-like
invalidation on the `database` and `file` stores, which do not support
`Cache::tags()`. Site settings use a single key cleared on every save.

In practice a cold repository call costs 3 queries and subsequent calls cost 0.

## The category tree

Stored as a plain adjacency list (`parent_id`) with a cached `depth` that the
model maintains — moving a node updates the depth of its whole subtree.

Reads never recurse through the database: `CategoryRepository::tree()` fetches
the flat table once and assembles the tree in memory.

- `GET /api/categories/tree` — nested tree for the site navigation
- `GET /api/admin/categories/options` — flat, depth-annotated parent picker;
  `?exclude=<id>` removes that node's own subtree, which is what prevents a
  cycle
- `GET /api/categories/{slug}/{childSlug?}` — a node, its ancestors, and the
  products of the **whole branch**

A category can be neither its own parent nor a child of one of its own
descendants; both the form request and the repository enforce this.

## Settings

One row per group, one cached payload for reads. `SettingsSchema` is the single
source of truth for which groups exist and how each is validated, so adding a
new editable section to the site is a two-step change (default + rules) rather
than touching four classes.

- `GET  /api/settings` — public, cached, `ETag`-enabled
- `GET  /api/admin/settings` — all groups plus the group list
- `POST /api/admin/settings` — save one group
- `POST /api/admin/settings/bulk` — save a whole tab at once
- `POST /api/admin/settings/{key}/reset` — restore shipped defaults

Images arrive inline as data URLs and are written to disk by
`SettingImageHandler`, which also deletes files that were replaced or removed.

## SEO

- `GET /api/sitemap.xml` — active products, categories and brands, streamed in
  chunks so memory stays flat on a large catalogue
- `GET /api/robots.txt` — generated from settings; one admin switch takes the
  whole site out of the index
- `GET /api/seo` — title template, defaults, OG image and the `Organization`
  and `WebSite` JSON-LD nodes

Products, categories and brands each carry optional `meta_title` and
`meta_description`, falling back to their real name and description so no page
ships an empty tag.

## Conventions

- The API is **single-locale English**. There is one string per field: the
  `name_fa` / `description_fa` columns were dropped, and the settings payload
  carries one value per key rather than an `*_en` / `*_fa` pair.
- Comments and user-facing messages are both in English.
- Every response uses the same envelope: `{ data, meta?, message?, status }`.
- API errors always render as JSON, including validation, auth and throttling.
