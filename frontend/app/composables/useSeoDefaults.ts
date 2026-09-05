import { useAsyncData, useRuntimeConfig, useRoute, useHead, useSeoMeta } from 'nuxt/app'
import { computed } from 'vue'
import { useApi } from '~/composables/useApi'

export interface SeoDefaults {
    site_name: string
    title: string
    title_template: string
    description: string
    keywords: string
    og_image: string
    twitter_handle: string
    robots: string
    google_site_verification: string
    locale: string
    base_url: string
}

export interface SeoPayload {
    defaults: SeoDefaults
    jsonld: { organization: Record<string, unknown>; website: Record<string, unknown> }
}

/**
 * Site-wide SEO values, served by the backend so an admin can change the
 * title template, description or OG image without a redeploy.
 */
export function useSeoDefaults() {
    const { data } = useAsyncData('seo-defaults', async () => {
        const response = await useApi<unknown>('/seo', { notifyOnError: false })
        return (response as unknown as SeoPayload) ?? null
    })

    return computed(() => data.value)
}

/**
 * Any field may be given as a plain value or as a getter.
 *
 * The getter form matters on pages whose copy comes from the settings API:
 * those values are still empty when setup() runs, so passing the raw value
 * would freeze an empty string into the tag. A getter is re-read when the
 * head is rendered, by which point the data has resolved.
 */
type SeoValue<T> = T | (() => T | undefined)

export interface PageSeoInput {
    title?: SeoValue<string>
    description?: SeoValue<string>
    image?: SeoValue<string>
    /** Path only, e.g. "/products/ball"; the origin is added here. */
    path?: SeoValue<string>
    type?: 'website' | 'article' | 'product'
    noindex?: SeoValue<boolean>
    /** Extra JSON-LD nodes appended to the site-wide ones. */
    jsonLd?: SeoValue<Record<string, unknown>[]>
}

/** Resolves a value that may have been supplied as a getter. */
function read<T>(value: SeoValue<T> | undefined): T | undefined {
    return typeof value === 'function' ? (value as () => T | undefined)() : value
}

/**
 * Applies one page's SEO tags: title, description, canonical, Open Graph,
 * Twitter card and JSON-LD.
 *
 * Every page calls this, so the rules for building a canonical URL and
 * falling back to the site defaults exist in exactly one place.
 */
export function usePageSeo(input: PageSeoInput) {
    const seo = useSeoDefaults()
    const route = useRoute()
    const config = useRuntimeConfig()

    const origin = computed(() =>
        (seo.value?.defaults?.base_url || config.public.siteUrl || '').replace(/\/$/, ''),
    )

    const canonical = computed(() => `${origin.value}${read(input.path) ?? route.path}`)

    const title = computed(() => read(input.title) || seo.value?.defaults?.title || '')

    const description = computed(
        () => read(input.description) || seo.value?.defaults?.description || '',
    )

    const image = computed(() => {
        const raw = read(input.image) || seo.value?.defaults?.og_image || ''
        if (!raw) return ''
        return raw.startsWith('http') ? raw : `${origin.value}${raw.startsWith('/') ? '' : '/'}${raw}`
    })

    const robots = computed(() =>
        read(input.noindex) ? 'noindex, nofollow' : seo.value?.defaults?.robots || 'index, follow',
    )

    useSeoMeta({
        title: () => title.value,
        titleTemplate: (chunk) => {
            const defaults = seo.value?.defaults
            const fallback = defaults?.title || ''

            if (!chunk) return fallback

            // The home page's own title is already the site name; running it
            // through the template would render "Site | Site".
            if (chunk === fallback) return chunk

            return defaults?.title_template
                ? defaults.title_template.replace('%s', chunk)
                : chunk
        },
        description: () => description.value,
        robots: () => robots.value,

        ogType: input.type === 'product' ? 'website' : (input.type ?? 'website'),
        ogTitle: () => title.value,
        ogDescription: () => description.value,
        ogUrl: () => canonical.value,
        ogImage: () => image.value || undefined,
        ogSiteName: () => seo.value?.defaults?.site_name || undefined,
        ogLocale: () => seo.value?.defaults?.locale || 'fa_IR',

        twitterCard: 'summary_large_image',
        twitterTitle: () => title.value,
        twitterDescription: () => description.value,
        twitterImage: () => image.value || undefined,
        twitterSite: () => seo.value?.defaults?.twitter_handle || undefined,
    })

    useHead(() => {
        const nodes = [
            seo.value?.jsonld?.organization,
            seo.value?.jsonld?.website,
            ...(read(input.jsonLd) ?? []),
        ].filter(Boolean)

        return {
            link: [{ rel: 'canonical', href: canonical.value }],
            meta: seo.value?.defaults?.google_site_verification
                ? [{ name: 'google-site-verification', content: seo.value.defaults.google_site_verification }]
                : [],
            script: nodes.map((node, index) => ({
                key: `jsonld-${index}`,
                type: 'application/ld+json',
                innerHTML: JSON.stringify(node),
            })),
        }
    })

    return { canonical, title, description, image }
}

/**
 * Builds a schema.org BreadcrumbList from the crumbs a page already renders,
 * so the visible breadcrumb and the structured one can never drift apart.
 */
export function buildBreadcrumbJsonLd(
    items: Array<{ title: string; to?: string }>,
    origin: string,
): Record<string, unknown> {
    return {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: items.map((item, index) => ({
            '@type': 'ListItem',
            position: index + 1,
            name: item.title,
            item: item.to ? `${origin.replace(/\/$/, '')}${item.to}` : undefined,
        })),
    }
}
