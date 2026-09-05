/**
 * Offline data source.
 *
 * When the API cannot be reached, `useApi` falls back to this module and
 * answers the request from the JSON snapshot in ./data - real responses
 * captured from a real backend by `pnpm fixtures`, not invented content.
 *
 * The snapshot stores collections, not response shapes: one array of
 * products, one of brands, one category tree, plus the detail pages that
 * carry something a list cannot (a product's related items, a category's
 * breadcrumb). Everything else - pagination, search, filters, sorting - is
 * recomputed here so a list page behaves offline the way it does online.
 *
 * Nothing here is imported statically. The whole module and its JSON load on
 * demand through a dynamic import, so a deployment with a working API never
 * ships this payload to a visitor.
 */

import type { ApiEnvelope } from '~/types/interfaces'

// ---------------------------------------------------------------- loading

interface Snapshot {
    settings: any
    seo: any
    brands: any[]
    categories: any[]
    tree: any[]
    products: any[]
    pages: {
        products: Record<string, { data: any; related: string[] }>
        categories: Record<string, { data: any; breadcrumb: any[]; products: string[] }>
        brands: Record<string, { data: any }>
    }
    adminSettings: any
    categoryOptions: any[]
    adminUser: any
    manifest: { generated_at: string; source: string }
}

let snapshot: Promise<Snapshot> | null = null

/**
 * Reads the JSON once per process and keeps it. `import()` returns the parsed
 * object, so there is no parse cost per request either.
 */
function load(): Promise<Snapshot> {
    snapshot ??= Promise.all([
        import('./data/settings.json'),
        import('./data/seo.json'),
        import('./data/brands.json'),
        import('./data/categories.json'),
        import('./data/category-tree.json'),
        import('./data/products.json'),
        import('./data/pages.json'),
        import('./data/admin-settings.json'),
        import('./data/category-options.json'),
        import('./data/admin-user.json'),
        import('./data/manifest.json'),
    ]).then(([settings, seo, brands, categories, tree, products, pages, adminSettings, categoryOptions, adminUser, manifest]) => ({
        settings: unwrap(settings),
        seo: unwrap(seo),
        brands: unwrap(brands),
        categories: unwrap(categories),
        tree: unwrap(tree),
        products: unwrap(products),
        pages: unwrap(pages),
        adminSettings: unwrap(adminSettings),
        categoryOptions: unwrap(categoryOptions),
        adminUser: unwrap(adminUser),
        manifest: unwrap(manifest),
    }))

    return snapshot
}

/** A JSON module arrives as `{ default: … }` in some bundlers and bare in others. */
const unwrap = (module: any) => (module && 'default' in module ? module.default : module)

// ------------------------------------------------------------------ helpers

const asBool = (value: unknown) => value === true || value === 'true' || value === 1 || value === '1'
const asInt = (value: unknown, fallback: number) => {
    const parsed = Number.parseInt(String(value ?? ''), 10)
    return Number.isFinite(parsed) ? parsed : fallback
}

/** The envelope every endpoint returns, so callers unwrap one shape. */
const envelope = (data: any, extra: Record<string, unknown> = {}): ApiEnvelope<any> => ({
    data,
    status: true,
    ...extra,
} as ApiEnvelope<any>)

/**
 * Rebuilds Laravel's pagination meta for a locally sliced list, so the
 * pager component receives exactly the fields it does online.
 */
function paginate(items: any[], page: number, perPage: number, path: string) {
    const total = items.length
    const lastPage = Math.max(1, Math.ceil(total / perPage))
    const current = Math.min(Math.max(1, page), lastPage)
    const offset = (current - 1) * perPage
    const slice = items.slice(offset, offset + perPage)

    return {
        data: slice,
        meta: {
            current_page: current,
            from: slice.length ? offset + 1 : null,
            to: slice.length ? offset + slice.length : null,
            last_page: lastPage,
            per_page: perPage,
            total,
            path,
            next_page_url: current < lastPage ? `${path}?page=${current + 1}` : null,
            prev_page_url: current > 1 ? `${path}?page=${current - 1}` : null,
        },
    }
}

const matches = (haystack: unknown, needle: string) =>
    String(haystack ?? '').toLowerCase().includes(needle)

/**
 * The same ordering the repository applies. "latest" is the API's default,
 * and the snapshot already arrives in that order, so it is the identity.
 */
function sortProducts(items: any[], sort: string) {
    const sorted = [...items]

    switch (sort) {
        case 'oldest':
            return sorted.reverse()
        case 'name':
            return sorted.sort((a, b) => String(a.name).localeCompare(String(b.name)))
        default:
            return sorted
    }
}

// ------------------------------------------------------------------ routing

type Query = Record<string, unknown>

/**
 * Answers one request from the snapshot, or returns undefined when the
 * endpoint has no offline equivalent - which is the honest answer for every
 * write, and lets the caller report that the API is unavailable.
 *
 * @param endpoint Path with no base URL, e.g. "/products/some-slug".
 * @param query    Query parameters as they were passed to useApi.
 * @param method   HTTP method; anything but GET is a write.
 */
export async function resolveFixture(
    endpoint: string,
    query: Query = {},
    method = 'GET',
): Promise<ApiEnvelope<any> | undefined> {
    const path = `/${endpoint.replace(/^\/+/, '').replace(/\/+$/, '')}`
    const data = await load()

    // Signing in is the one non-GET the snapshot answers: without it the
    // admin panel is unreachable offline and its screens cannot be reviewed.
    // The message says plainly what happened, and the token is marked so it
    // can never be mistaken for a real one.
    if (method === 'POST' && path === '/auth/login') {
        return envelope(undefined, {
            token: `offline-${data.manifest.generated_at}`,
            user: data.adminUser,
            message: 'Signed in with offline sample data - the API is not reachable.',
        })
    }

    if (method !== 'GET') return undefined

    // ------------------------------------------------------------- session
    // The real endpoint answers with `user`, not `data`; the route guard
    // reads that key, so the shape has to match exactly.
    if (path === '/auth/me') return envelope(undefined, { user: data.adminUser })

    // ------------------------------------------------------------ settings
    if (path === '/settings') return data.settings
    if (path === '/seo') return data.seo
    if (path === '/admin/settings') return data.adminSettings

    // -------------------------------------------------------------- brands
    if (path === '/brands' || path === '/admin/brands') {
        // Only an admin sees unpublished records, exactly as on the API.
        const all = path.startsWith('/admin') ? data.brands : data.brands.filter(brand => brand.is_active)

        if (asBool(query.all)) return envelope(all)

        const { data: items, meta } = paginate(all, asInt(query.page, 1), asInt(query.perPage, 15), path)
        return envelope(items, { meta })
    }

    if (path.startsWith('/admin/brands/id/')) {
        const id = path.slice('/admin/brands/id/'.length)
        return envelope(data.brands.find(brand => brand.id === id) ?? null)
    }

    if (path.startsWith('/brands/')) {
        const slug = path.slice('/brands/'.length)
        const page = data.pages.brands[slug]
        return page ? envelope(page.data) : undefined
    }

    // ---------------------------------------------------------- categories
    if (path === '/categories/tree' || path === '/admin/categories/tree') {
        const withInactive = path.startsWith('/admin') && asBool(query.withInactive)
        return envelope(withInactive ? data.tree : pruneInactive(data.tree))
    }

    if (path === '/admin/categories/options') {
        // `exclude` removes a node and its descendants, so a category can
        // never be reparented under itself.
        const exclude = query.exclude ? String(query.exclude) : ''
        if (!exclude) return envelope(data.categoryOptions)

        const banned = subtreeIds(data.categories, exclude)
        return envelope(data.categoryOptions.filter(option => !banned.has(option.id)))
    }

    if (path === '/categories' || path === '/admin/categories') {
        const all = path.startsWith('/admin')
            ? data.categories
            : data.categories.filter(category => category.is_active)

        if (asBool(query.all)) return envelope(all)

        const { data: items, meta } = paginate(all, asInt(query.page, 1), asInt(query.perPage, 15), path)
        return envelope(items, { meta })
    }

    if (path.startsWith('/admin/categories/')) {
        const id = path.slice('/admin/categories/'.length)
        return envelope(data.categories.find(category => category.id === id) ?? null)
    }

    if (path.startsWith('/categories/')) {
        // Keyed the way the URL addresses it: "parent" or "parent/child".
        const key = path.slice('/categories/'.length)
        const page = data.pages.categories[key]
        if (!page) return undefined

        return envelope(page.data, {
            breadcrumb: page.breadcrumb,
            products: hydrate(data, page.products),
        })
    }

    // ------------------------------------------------------------ products
    if (path === '/products' || path === '/admin/products') {
        const isAdmin = path.startsWith('/admin')
        let items = isAdmin ? data.products : data.products.filter(product => product.is_active)

        // The landing page's two shortcut endpoints.
        if (asBool(query.latest)) return envelope(items.slice(0, asInt(query.limit, 10)))
        if (asBool(query.featured)) {
            return envelope(items.filter(product => product.is_new).slice(0, asInt(query.limit, 8)))
        }

        if (query.category_id) items = items.filter(product => product.category_id === query.category_id)
        if (query.brand_id) items = items.filter(product => product.brand_id === query.brand_id)
        if (asBool(query.is_new)) items = items.filter(product => product.is_new)

        if (query.search) {
            const needle = String(query.search).toLowerCase()
            items = items.filter(product =>
                matches(product.name, needle)
                || matches(product.pid, needle)
                || matches(product.description, needle))
        }

        items = sortProducts(items, String(query.sort ?? 'latest'))

        if (asBool(query.all)) return envelope(items)

        // The API caps perPage at 60; mirroring it keeps offline and online
        // pagination identical.
        const perPage = Math.min(asInt(query.perPage, 15), 60)
        const { data: page, meta } = paginate(items, asInt(query.page, 1), perPage, path)
        return envelope(page, { meta })
    }

    if (path.startsWith('/admin/products/')) {
        const id = path.slice('/admin/products/'.length)
        return envelope(data.products.find(product => product.id === id) ?? null)
    }

    if (path.startsWith('/products/')) {
        const slug = path.slice('/products/'.length)
        const page = data.pages.products[slug]
        if (!page) return undefined

        return envelope(page.data, { related: hydrate(data, page.related) })
    }

    return undefined
}

/** Turns the stored slug list back into full product records. */
function hydrate(data: Snapshot, slugs: string[]): any[] {
    if (!Array.isArray(slugs)) return []

    return slugs
        .map(slug => data.products.find(product => product.slug === slug))
        .filter(Boolean)
}

/** Drops unpublished branches, which is what the public tree endpoint does. */
function pruneInactive(nodes: any[]): any[] {
    return (nodes ?? [])
        .filter(node => node.is_active)
        .map(node => ({ ...node, children: pruneInactive(node.children ?? []) }))
}

/** A category's own id plus every descendant's, for the parent picker. */
function subtreeIds(categories: any[], rootId: string): Set<string> {
    const ids = new Set<string>([rootId])
    let grew = true

    // The list is flat, so one pass per level is enough; repeat until no new
    // descendant is found rather than assuming a maximum depth.
    while (grew) {
        grew = false
        for (const category of categories) {
            if (category.parent_id && ids.has(category.parent_id) && !ids.has(category.id)) {
                ids.add(category.id)
                grew = true
            }
        }
    }

    return ids
}

/** Where and when the snapshot came from, for the console notice. */
export async function fixtureOrigin(): Promise<{ generated_at: string; source: string }> {
    const { manifest } = await load()
    return manifest
}
