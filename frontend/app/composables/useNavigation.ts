import { useAsyncData } from 'nuxt/app'
import { computed } from 'vue'
import { useApi } from '~/composables/useApi'
import type { BrandInterface, CategoryTreeNode } from '~/types/interfaces'

/**
 * Catalogue data the site navigation is built from.
 *
 * Fetched once per request with shared useAsyncData keys, so the desktop
 * mega-menu, the mobile drawer and the footer all reuse the same payload
 * instead of each issuing its own request.
 */
export function useNavigation() {
    const { data: tree, pending: treePending } = useAsyncData('nav-category-tree', async () => {
        const response = await useApi<CategoryTreeNode[]>('/categories/tree', { notifyOnError: false })
        return response?.data ?? []
    })

    const { data: brands, pending: brandsPending } = useAsyncData('nav-brands', async () => {
        const response = await useApi<BrandInterface[]>('/brands', {
            query: { all: true },
            notifyOnError: false,
        })
        return response?.data ?? []
    })

    return {
        categoryTree: computed(() => tree.value ?? []),
        brands: computed(() => brands.value ?? []),
        pending: computed(() => treePending.value || brandsPending.value),
    }
}
