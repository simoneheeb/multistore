import { useApi } from '~/composables/useApi'
import type {
    BrandInterface,
    CategoryInterface,
    CategoryTreeNode,
    PageMetaInterface,
    ProductInterface,
    ProductQuery,
} from '~/types/interfaces'

/**
 * Read-only API surface used by the public site.
 *
 * Every method returns the parsed envelope so callers can pass it straight to
 * useAsyncData without a second unwrapping layer.
 */
export const CatalogService = {
    // ------------------------------------------------------------- brands
    async brands() {
        const response = await useApi<BrandInterface[]>('/brands', { query: { all: true } })
        return response?.data ?? []
    },

    async brandsPaginated(page = 1, perPage = 24) {
        const response = await useApi<BrandInterface[]>('/brands', { query: { page, perPage } })
        return {
            items: response?.data ?? [],
            meta: (response?.meta ?? null) as PageMetaInterface | null,
        }
    },

    /**
     * Single brand. `throwOnError` is on because the page built from this
     * response must distinguish a missing brand from a failing API - see
     * ApiError in useApi.
     */
    async brand(slug: string) {
        const response = await useApi<BrandInterface>(`/brands/${slug}`, { throwOnError: true })
        return response?.data ?? null
    },

    // --------------------------------------------------------- categories
    async categories() {
        const response = await useApi<CategoryInterface[]>('/categories', { query: { all: true } })
        return response?.data ?? []
    },

    async categoryTree() {
        const response = await useApi<CategoryTreeNode[]>('/categories/tree')
        return response?.data ?? []
    },

    /**
     * A category page needs the node, its ancestors and the products of the
     * whole branch; the API returns all three in one response so the page
     * renders from a single round trip.
     */
    async category(slug: string, childSlug?: string) {
        const path = childSlug ? `/categories/${slug}/${childSlug}` : `/categories/${slug}`
        const response = await useApi<CategoryInterface>(path, { throwOnError: true })

        if (!response) return null

        return {
            category: response.data,
            breadcrumb: (response.breadcrumb ?? []) as CategoryTreeNode[],
            products: (response.products ?? []) as ProductInterface[],
        }
    },

    // ----------------------------------------------------------- products
    async products(query: ProductQuery = {}) {
        const response = await useApi<ProductInterface[]>('/products', { query: query as Record<string, unknown> })
        return {
            items: response?.data ?? [],
            meta: (response?.meta ?? null) as PageMetaInterface | null,
        }
    },

    async latestProducts(limit = 8) {
        const response = await useApi<ProductInterface[]>('/products', { query: { latest: true, limit } })
        return response?.data ?? []
    },

    async featuredProducts(limit = 8) {
        const response = await useApi<ProductInterface[]>('/products', { query: { featured: true, limit } })
        return response?.data ?? []
    },

    async product(slug: string) {
        const response = await useApi<ProductInterface>(`/products/${slug}`, { throwOnError: true })

        if (!response) return null

        return {
            product: response.data,
            related: (response.related ?? []) as ProductInterface[],
        }
    },
}
