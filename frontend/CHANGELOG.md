# Conversion to English (LTR)

What changed in this pass, across both projects, and what you need to do to
pick it up.

---

## 0) Run the migration first

Three columns were dropped from the database. Run the migrations before
starting either project:

```bash
php artisan migrate
```

A dedicated migration (`drop_persian_columns`) backfills any English field
that is still empty from its Persian counterpart, then drops
`name_fa` / `description_fa` from `products`, `categories` and `brands`. It is
guarded column by column, so it is a safe no-op on a fresh install and on a
database where the columns are already gone.

If you would rather start clean:

```bash
php artisan migrate:fresh --seed
```

---

## 1) Colour palette

Your Vuetify palette is now the source of truth for the design tokens in
`app/assets/css/main.css`. The mapping from each Vuetify colour to its token
role is written out in a comment block at the top of that file.

Five hues were kept but lightened or darkened, because at their original
values they failed WCAG AA contrast (4.5:1 for body text):

| Token | Vuetify | Now | Why |
|---|---|---|---|
| `primary` | `#009E6D` | `#007F58` | label on the button was 3.15:1 |
| `warning` / `accent` | `#ED6C02` | `#BF5702` | was 3.11:1 on white |
| `subtitle` → `muted` (dark) | `#60707C` | `#8B9AA6` | was 3.31:1 on the dark surface |
| `icon` (dark) | `#7A8391` | `#8B96A3` | was 4.43:1 on the dark surface |
| `body` | `#000000` | `#414A54` | pure black made body copy darker than headings |

Every other colour is used exactly as you had it. The dark palette is derived
from the same hues, so both themes stay recognisably one brand.

Theme colours are still editable from **Site settings → Identity**; the
shipped defaults are now `#007F58` and `#BF5702`.

---

## 2) Single language, left to right

- `htmlAttrs` is `lang="en" dir="ltr"`.
- `name_fa` / `description_fa` are gone from the models, migrations, factories,
  seeders, form requests, resources, admin forms and TypeScript types.
- The settings schema collapsed every `*_en` / `*_fa` pair into one field, so
  `SettingsSchema::rules()` and `config/defaults.php` both describe a
  single-language payload.
- Persian digit conversion (`toPersianDigits`) was removed; `formatNumber`
  uses `en-US` grouping.
- The IRANSans `@font-face` blocks and the `.latin` helper class are gone.
  **Inter** is loaded from Google Fonts, with `preconnect` hints for both the
  stylesheet host and the font-file host.
- Every comment and every user-facing string in both projects is English.
  There are zero Persian characters left in either codebase.

The layout needed almost no direction work: the components were already
written with CSS logical properties (`start`/`end`, `ms`/`me`, `ps`/`pe`),
which flip automatically with `dir`. The three places that hard-coded a
physical direction were fixed: the hero scrim gradient, the hero chevron and
the admin sidebar's off-canvas transform.

### The two-line heading device

The house style you had — a Persian title under the English one — no longer
has a second language to carry. It became an **eyebrow**: a small-caps line
naming the *section* above the heading, which is editorial copy rather than a
translation. `UiTitle` now takes `eyebrow` / `title` / `description`, and
every eyebrow is editable in the admin panel like any other string.

Where the eyebrow only repeated the title — throughout the admin panel — it
was removed instead, since restating a heading in smaller type is noise.

---

## 3) Fixes found while verifying

**Duplicated navigation.** `Home / Products / Categories / Brands / About /
Contact` were the default menu, while the header separately renders its own
Categories and Brands mega-menus — so both labels appeared twice. The two are
no longer in the default menu; the mega-menus cover them.

**Duplicated fields.** Collapsing `name`/`name_fa` had left the brand,
category and product forms with two identical Name inputs, and each admin
table with the record's name printed twice. The forms keep one input; the
tables now show the slug on the second line, which is the identifier you
actually need next to a name.

**A throttled request was reported as "not found".** `useApi` swallows every
failure and returns `undefined`, which is right for a widget that should
render empty — but the three detail pages treated that as "this record does
not exist" and threw a 404. A rate-limited or briefly broken API therefore
produced a Not Found page, which is wrong to the visitor and an invitation for
search engines to drop a healthy URL. Those pages now opt into `throwOnError`
and map the status through `utils/catalogError.ts`, so a 429 stays a 429.

**Hard-coded light-mode colours.** The header bars, the mega-menu labels and
the footer used `bg-white` / `text-black` / `#ECEFF1` directly, so they stayed
light when the site switched to the dark theme. They read from tokens now. The
black logo band is deliberate and still black in both themes.

**Missing default artwork.** The shipped settings pointed at hero, promo,
page-cover and OG images that were not in `public/`, so a fresh install showed
placeholder boxes. Neutral brand-tinted images are now included at those
paths, and the favicon link points at a file that exists.

**Carousel arrows on mobile.** They sat on top of the slide copy on a narrow
screen. They are hidden below the `sm` breakpoint — a touch device swipes, and
the dots remain.

**Login response** said `Wellcome Back`; it now says `Welcome back`.

---

## 4) Running without the backend

The site now works with no API at all. When a request cannot reach the
backend, it is answered from a JSON snapshot in `app/fixtures/data` instead.

That snapshot is **real data**, not invented sample content: `pnpm fixtures`
signs in to a running API and captures its actual responses — settings, SEO,
the catalogue, the category tree, every detail page, and the admin panel's own
lists. The copy shipped here was captured from your seeded catalogue. Once the
real API has real content, re-run it and the offline copy matches:

```bash
pnpm fixtures -- --api=https://api.example.com/api \
                 --email=admin@example.com --password=...
```

### Connecting the backend is a one-line change

There is no flag to flip and no code to edit. Point the frontend at the API:

```
NUXT_PUBLIC_API_BASE=https://api.example.com/api
```

The site tries the API first on every request and only falls back when it does
not answer. So the moment that URL resolves, live data is in use — and if the
backend goes down later, the site keeps serving rather than showing empty
pages.

### What counts as "not connected"

A refused connection, a DNS failure, a request that takes longer than eight
seconds, or a `502` / `503` / `504`. A `404` or a validation error does **not**
trigger it: those came from a working backend and are real answers that must
not be papered over. After a failure the app stays offline for 30 seconds and
then tries the API again, so recovery needs no restart.

`NUXT_PUBLIC_OFFLINE_FALLBACK` overrides the decision when you want it:
`auto` (default), `always` (never call the API — useful for a demo) or `never`
(never use the snapshot — useful when you want a dead backend to be loud).

### Scope

Every **read** is covered, including the admin panel: signing in offline is
accepted so the panel's screens can be reviewed, and the sign-in toast says
plainly that it used offline data. `/sitemap.xml` is rebuilt from the snapshot
too, so an offline site does not tell crawlers it has no pages. **Writes are
not** — the snapshot is read-only, and attempting to save reports that the API
is unreachable rather than appearing to succeed and quietly losing the change.

### Cost when the API works

None. The fixtures module and its JSON are behind a dynamic import, so a
deployment with a live backend never loads them. They are separate chunks,
not part of the main bundle.

---

## 5) Rate limits are configurable

The budgets moved out of `AppServiceProvider` into `config/rate_limits.php`,
each one env-driven:

```
RATE_LIMIT_API=120
RATE_LIMIT_PUBLIC_READ=90
RATE_LIMIT_ADMIN_WRITE=30
RATE_LIMIT_UPLOADS=20
RATE_LIMIT_AUTH=5
```

The keying strategy — per user, per IP, and for login per IP *and* per
submitted email — is unchanged and still lives in the provider. Values are
clamped to at least 1, so a stray `0` cannot lock the API shut.

---

## 6) Verification

Both projects were run and exercised end to end.

Backend: `migrate:fresh --seed` succeeds; `/api/settings`, `/api/categories/tree`
and `/api/products` return the single-language shape with correct tree depths.

Frontend: `nuxt build` and `nuxt typecheck` both pass with zero errors. Then a
headless browser walked 18 pages — home (light, dark, mobile), products,
product detail, categories, category detail, brands, brand detail, about,
contact, sign-in, and five admin pages, plus a deliberate 404. Every one
reported `dir=ltr`, `lang=en`, the palette token resolving to `#007F58`
(`#54EABB` in dark), Inter as the body font, no horizontal overflow, no
Persian characters, and no console or page errors.

The rate limiter was also verified by exhausting it deliberately: a throttled
detail page now answers `429 Too many requests`, not `404`.

The offline fallback was verified by stopping the backend entirely and walking
the site again: every public page and every admin screen rendered real
catalogue content, with the sign-in flow working end to end. The backend was
then started again and the site returned to live data with no restart, no flag
and no code change — confirmed by editing a value through the API and watching
it appear on the page. `always` and `never` were both exercised too.

---

## Note

The one place Persian text still appears is inside your **logo image files**
(`public/assets/images/logo-*.png`), which carry the company name in Persian
script. That is artwork, not code — replace those files, or upload new ones
from **Site settings → Identity**, whenever you have English versions.
