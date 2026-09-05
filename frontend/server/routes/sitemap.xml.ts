import { defineEventHandler, setHeader, setResponseStatus } from 'h3'
import { useRuntimeConfig } from 'nitropack/runtime'

/**
 * Serves /sitemap.xml from the API.
 *
 * The backend owns the catalogue, so it generates the document; this route
 * only exposes it on the public origin, which is where crawlers expect it and
 * where the URLs inside it actually live.
 *
 * When the API cannot be reached, the document is built from the same offline
 * snapshot the pages themselves fall back to (see app/fixtures). A site that
 * renders its whole catalogue should not also claim to have no pages.
 */
export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig(event)
    const apiBase = String(config.apiBaseServer || config.public.apiBase).replace(/\/$/, '')

    setHeader(event, 'Content-Type', 'application/xml; charset=utf-8')
    setHeader(event, 'Cache-Control', 'public, max-age=3600')

    try {
        return await $fetch<string>(`${apiBase}/sitemap.xml`, { responseType: 'text' })
    } catch {
        if (String(config.public.offlineFallback ?? 'auto') === 'never') {
            setResponseStatus(event, 503)
            return emptyDocument()
        }

        try {
            return await buildFromSnapshot(String(config.public.siteUrl || '').replace(/\/$/, ''))
        } catch {
            setResponseStatus(event, 503)
            return emptyDocument()
        }
    }
})

const emptyDocument = () =>
    '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>'

/** XML has five characters that cannot appear raw in a text node. */
const escape = (value: string) =>
    value.replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&apos;')

/**
 * Mirrors the API's document: the static pages, then every published brand,
 * category and product. Imported dynamically so the JSON is only read when
 * the API is actually down.
 */
async function buildFromSnapshot(siteUrl: string): Promise<string> {
    const [products, brands, pages] = await Promise.all([
        import('../../app/fixtures/data/products.json').then(m => m.default ?? m),
        import('../../app/fixtures/data/brands.json').then(m => m.default ?? m),
        import('../../app/fixtures/data/pages.json').then(m => m.default ?? m),
    ]) as [any[], any[], { categories: Record<string, any> }]

    const entries: { loc: string; lastmod?: string; priority: string }[] = [
        { loc: '/', priority: '1.0' },
        { loc: '/products', priority: '0.9' },
        { loc: '/categories', priority: '0.8' },
        { loc: '/brands', priority: '0.8' },
        { loc: '/about', priority: '0.5' },
        { loc: '/contact', priority: '0.5' },
    ]

    // Category keys are already the URL path, root and nested alike.
    for (const [path, page] of Object.entries(pages.categories ?? {})) {
        if (page?.data?.is_active === false) continue
        entries.push({ loc: `/categories/${path}`, priority: '0.7' })
    }

    for (const brand of brands) {
        if (!brand.is_active) continue
        entries.push({ loc: `/brands/${brand.slug}`, priority: '0.6' })
    }

    for (const product of products) {
        if (!product.is_active) continue
        entries.push({
            loc: `/products/${product.slug}`,
            lastmod: product.updated_at ?? product.created_at,
            priority: '0.6',
        })
    }

    const body = entries.map(entry => [
        '  <url>',
        `    <loc>${escape(siteUrl + entry.loc)}</loc>`,
        entry.lastmod ? `    <lastmod>${escape(String(entry.lastmod).slice(0, 10))}</lastmod>` : '',
        `    <priority>${entry.priority}</priority>`,
        '  </url>',
    ].filter(Boolean).join('\n')).join('\n')

    return `<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n${body}\n</urlset>`
}
