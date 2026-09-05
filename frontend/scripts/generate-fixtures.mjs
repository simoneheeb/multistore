/**
 * Snapshots the live API into app/fixtures/data/*.json.
 *
 * Those files are what the site falls back to when the API is unreachable
 * (see app/fixtures/index.ts). Re-run this whenever the real content changes
 * so the offline copy stays honest:
 *
 *     pnpm fixtures
 *     pnpm fixtures -- --api=https://api.example.com/api --email=... --password=...
 *
 * It signs in as an administrator on purpose. Admin responses carry the
 * internal ids that the admin panel needs, and include records that are not
 * published yet - the resolver hides both from public endpoints, so one
 * snapshot serves the storefront and the panel alike.
 */

import { mkdir, writeFile } from 'node:fs/promises'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const HERE = dirname(fileURLToPath(import.meta.url))
const OUT_DIR = resolve(HERE, '../app/fixtures/data')

// ---------------------------------------------------------------- arguments

const args = Object.fromEntries(
    process.argv.slice(2)
        .filter(arg => arg.startsWith('--'))
        .map(arg => {
            const [key, ...rest] = arg.slice(2).split('=')
            return [key, rest.join('=') || 'true']
        }),
)

const API = (args.api || process.env.NUXT_PUBLIC_API_BASE || 'http://127.0.0.1:8000/api').replace(/\/$/, '')
const EMAIL = args.email || process.env.FIXTURES_EMAIL || ''
const PASSWORD = args.password || process.env.FIXTURES_PASSWORD || ''

let token = ''
let admin = null

// ------------------------------------------------------------------ fetching

/**
 * One GET, waiting out the API's rate limiter rather than failing on it.
 *
 * A snapshot of a real catalogue makes hundreds of requests back to back,
 * which will exhaust a per-minute budget several times over. That is the
 * limiter doing its job, so the script simply honours `Retry-After` and
 * carries on; a run of a large catalogue is slow, not broken.
 */
const ATTEMPTS = 6

async function get(path) {
    for (let attempt = 1; attempt <= ATTEMPTS; attempt += 1) {
        const response = await fetch(`${API}${path}`, {
            headers: {
                Accept: 'application/json',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
            },
        })

        if (response.status === 429 && attempt < ATTEMPTS) {
            // Retry-After is in seconds and can legitimately be a full
            // minute; the extra second keeps us clear of the boundary.
            const wait = Number(response.headers.get('retry-after')) || 60
            process.stdout.write(`  rate limited - waiting ${wait}s (attempt ${attempt}/${ATTEMPTS})\n`)
            await new Promise(done => setTimeout(done, (wait + 1) * 1000))
            continue
        }

        if (!response.ok) {
            throw new Error(`GET ${path} -> ${response.status}`)
        }

        return response.json()
    }

    throw new Error(`GET ${path} -> gave up after ${ATTEMPTS} attempts`)
}

async function signIn() {
    if (!EMAIL || !PASSWORD) {
        console.log('No credentials given - taking a public snapshot.')
        console.log('Pass --email and --password to also capture admin ids and unpublished records.\n')
        return
    }

    const response = await fetch(`${API}/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ email: EMAIL, password: PASSWORD }),
    })

    if (!response.ok) {
        throw new Error(`Sign-in failed (${response.status}). Check --email / --password.`)
    }

    const payload = await response.json()
    token = payload.token
    admin = payload.user ?? null
    console.log('Signed in as administrator.\n')
}

async function save(name, value) {
    await writeFile(resolve(OUT_DIR, `${name}.json`), `${JSON.stringify(value, null, 2)}\n`, 'utf8')
    const size = Array.isArray(value) ? `${value.length} records` : `${Object.keys(value).length} keys`
    console.log(`  ${name}.json  (${size})`)
}

// --------------------------------------------------------------------- main

await mkdir(OUT_DIR, { recursive: true })
await signIn()

console.log(`Snapshotting ${API}\n`)

// Collections. Everything the resolver needs to rebuild any list response -
// pagination, search, filters and sorting are applied locally.
const settings = await get('/settings')
const seo = await get('/seo')
const brands = await get('/brands?all=true')
const categories = await get('/categories?all=true')
const tree = await get(`/categories/tree${token ? '?withInactive=true' : ''}`)
const products = await get('/products?all=true')

await save('settings', settings)
await save('seo', seo)
await save('brands', brands.data ?? [])
await save('categories', categories.data ?? [])
await save('category-tree', tree.data ?? [])
await save('products', products.data ?? [])

// Detail responses. These carry things a list cannot: a product's related
// items, a category's breadcrumb and the products of its whole branch.
// Capturing them is cheaper and more faithful than trying to recompute them.
const pages = { products: {}, categories: {}, brands: {} }

for (const product of products.data ?? []) {
    pages.products[product.slug] = await get(`/products/${product.slug}`)
}

// Categories are addressed as /categories/{slug} at the root and
// /categories/{parent}/{child} one level down, so both forms are keyed.
const walkTree = async (nodes, trail = []) => {
    for (const node of nodes ?? []) {
        const path = [...trail, node.slug]
        const key = path.join('/')
        pages.categories[key] = await get(`/categories/${key}`)

        // A grandchild is reached through its own parent, not the root.
        await walkTree(node.children, path.length >= 2 ? [node.slug] : path)
    }
}

await walkTree(tree.data ?? [])

for (const brand of brands.data ?? []) {
    pages.brands[brand.slug] = await get(`/brands/${brand.slug}`)
}

/**
 * Detail responses embed whole product records: a category page carries the
 * products of its entire branch, a product page its related items. Left as
 * they are, those copies made the snapshot several times larger than the
 * catalogue itself. Each embedded product is replaced by its slug and the
 * resolver rehydrates it from products.json, so there is exactly one copy of
 * every product and one place to correct it.
 */
const toSlugs = list => (Array.isArray(list) ? list.map(item => item?.slug).filter(Boolean) : [])

for (const page of Object.values(pages.products)) {
    page.related = toSlugs(page.related)
}

for (const page of Object.values(pages.categories)) {
    page.products = toSlugs(page.products)
}

await save('pages', pages)

// Admin-only snapshots. Without credentials these are skipped, and the
// offline panel simply has nothing to show - the storefront is unaffected.
if (token) {
    await save('admin-settings', await get('/admin/settings'))
    await save('category-options', (await get('/admin/categories/options')).data ?? [])
    await save('admin-user', admin)
}

await save('manifest', {
    generated_at: new Date().toISOString(),
    source: API,
    authenticated: Boolean(token),
    counts: {
        brands: (brands.data ?? []).length,
        categories: (categories.data ?? []).length,
        products: (products.data ?? []).length,
        product_pages: Object.keys(pages.products).length,
        category_pages: Object.keys(pages.categories).length,
        brand_pages: Object.keys(pages.brands).length,
    },
})

console.log('\nDone.')
