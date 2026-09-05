# Storefront (Nuxt 4)

Server-side rendered storefront and admin panel for the Laravel API in the
companion `backend` project.

The site is **single-locale English (LTR)**. There is no translation layer and
no locale switch: one string per field, one direction, one font.

Section headings still use a two-line device - a small-caps *eyebrow* naming
the section above the heading itself - but the eyebrow is editorial copy, not
a translation. `UiTitle` takes `eyebrow` / `title` / `description`, and every
eyebrow is editable from the admin panel like any other string.

## Stack

| | |
|---|---|
| Framework | Nuxt 4 (SSR enabled) |
| Styling | Tailwind CSS 4 via `@tailwindcss/vite` |
| State | Pinia |
| Icons | inline SVG (`UiIcon`) — no icon font |
| Font | Inter, loaded from Google Fonts |

There is no UI framework. Every component in `app/components/ui` is written
for this project.

## Getting started

```bash
pnpm install
cp .env.example .env     # then set NUXT_PUBLIC_API_BASE
pnpm dev                 # http://localhost:3001
```

Production:

```bash
pnpm build
node .output/server/index.mjs
```

### Environment

| Variable | Purpose |
|---|---|
| `NUXT_PUBLIC_API_BASE` | Laravel API base URL, including `/api` |
| `NUXT_PUBLIC_SITE_URL` | Canonical public origin, used for canonical links and OG tags |
| `NUXT_API_BASE_SERVER` | Optional internal API URL used during SSR |
| `NUXT_PUBLIC_OFFLINE_FALLBACK` | `auto` (default), `always` or `never` — see *Working without the API* |

Nitro does not read `.env` in production. When running the built server,
export these variables in the environment (or bake them in at build time);
otherwise the values compiled into `nuxt.config.ts` are used.

## Layout

```
app/
  assets/css/main.css     design tokens, fonts, base layer, @source directives
  components/common/      header, footer, menus, search overlay
  components/ui/          the component library (Ui* prefix)
  composables/            useApi, useSiteSettings, useSeoDefaults, useTheme, …
  helpers/rules.ts        form validation rules
  layouts/                default, auth, dashboard
  middleware/             global auth guard for /admin
  types/                  domain and settings types
  fixtures/               offline data: JSON snapshot + the resolver
  utils/                  formatting helpers, catalogue error mapping
modules/client/           public storefront: routes, pages, sections, services
modules/admin/            admin panel: routes, pages, forms, stores, services
server/routes/            /sitemap.xml and /robots.txt proxied from the API
```

Both `modules/*` folders are Nuxt modules that register their own routes and
auto-register their components.

## Working without the API

The site runs with no backend at all. When a request cannot reach the API,
`useApi` answers it from a JSON snapshot in `app/fixtures/data` instead —
real responses captured from a real backend, not invented sample content.

Nothing has to be switched on, and nothing has to be switched off later:

```bash
# no backend yet — the site renders from the snapshot
pnpm build && node .output/server/index.mjs

# backend is ready — point at it, and that is the whole change
NUXT_PUBLIC_API_BASE=https://api.example.com/api node .output/server/index.mjs
```

**Refreshing the snapshot.** The shipped copy is a snapshot of the seeded
demo catalogue. Once the real API has real content, capture it:

```bash
pnpm fixtures -- --api=https://api.example.com/api \
                 --email=admin@example.com --password=...
```

Credentials are optional. With them the snapshot also carries the record ids
and unpublished records the admin panel needs; without them it is a public
snapshot and the panel has nothing to show. The script waits out the API's
rate limiter rather than failing on it, so a large catalogue is slow, not
broken.

**What the snapshot covers.** Every read: settings, SEO, the catalogue, the
category tree, all detail pages, the admin panel's own lists, and
`/sitemap.xml` (rebuilt from the snapshot rather than served empty). Writes
are not covered — nothing pretends to save. Attempting one says so plainly
rather than appearing to succeed and losing the change.

**How the switch works.** A request that is refused, times out, or comes back
`502`/`503`/`504` puts the app in offline mode; a `404` or `422` does not,
because those came from a working backend and are real answers. Offline mode
lasts 30 seconds and then the API is tried again, so a backend that comes back
is picked up on its own. `NUXT_PUBLIC_OFFLINE_FALLBACK` overrides the
decision: `always` never calls the API, `never` never uses the snapshot.

**What it costs when the API works.** Nothing. The fixtures module and its
JSON sit behind a dynamic import, so a deployment with a live backend never
loads any of it.

## Things worth knowing

**SSR is required, not optional.** The catalogue lives behind an API, and
search engines must see rendered markup, meta tags and JSON-LD in the first
response. Pages fetch through `useAsyncData` so the payload is serialised into
the HTML and reused on the client.

**Almost nothing is hard-coded.** The header, footer, every landing section,
the about and contact pages, the theme colours and the SEO defaults all come
from the API's settings endpoint and are editable at `/admin/settings`. The
order of the landing sections is itself a setting.

**Tailwind sources are declared explicitly.** `main.css` lists `@source`
directives for `app` and `modules`. Automatic detection missed classes used
only inside lazily-chunked module components, which caused whole responsive
variants to be silently omitted from the stylesheet.

**The auth token lives in a cookie**, not `localStorage` — it is the only
store readable during SSR, which is what lets the server render admin pages
for a signed-in visitor instead of flashing the logged-out shell first.

**A failed fetch is not a 404.** `useApi` normally swallows failures and
returns `undefined`, which is right for a widget that should render empty. The
three detail pages opt into `throwOnError` instead and map the status through
`utils/catalogError.ts`, so a throttled visitor gets a real 429 rather than
being told the product does not exist - and search engines are not invited to
drop a page that is perfectly healthy.

**SSR forwards the visitor's IP.** Every API call made while rendering a page
originates from this server, so `useApi` forwards `X-Forwarded-For`. The API
only honours it from a proxy listed in its `TRUSTED_PROXIES`; without that
setting on the backend, its rate limiter would treat all visitors as one
client.
