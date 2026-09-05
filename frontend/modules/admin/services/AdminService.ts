import { useApi } from '~/composables/useApi'
import type {
    BrandInterface,
    CategoryInterface,
    CategoryOption,
    CategoryTreeNode,
    PageMetaInterface,
    ProductInterface,
} from '~/types/interfaces'
import type { SiteSettings, SettingsKey } from '~/types/settings'

/**
 * Every write the admin panel performs.
 *
 * Grouped by resource rather than split into one file per entity: the whole
 * admin API is small, and one module makes the shape of the surface obvious.
 */
export const AdminService = {
    // -------------------------------------------------------------- brands
    brands: {
        async list(page = 1, perPage = 15) {
            // Admin routes, not the public ones: record ids are only exposed
            // to an authenticated caller, and the panel needs them for every
            // edit and delete action.
            const response = await useApi<BrandInterface[]>('/admin/brands', { query: { page, perPage } })
            return {
                items: response?.data ?? [],
                meta: (response?.meta ?? null) as PageMetaInterface | null,
            }
        },

        async all() {
            const response = await useApi<BrandInterface[]>('/admin/brands', { query: { all: true } })
            return response?.data ?? []
        },

        async find(id: string) {
            const response = await useApi<BrandInterface>(`/admin/brands/id/${id}`)
            return response?.data ?? null
        },

        // Brands carry a logo file, so writes go out as multipart/form-data.
        async create(payload: FormData) {
            return await useApi('/admin/brands', { method: 'POST', body: payload, notifyOnSuccess: true })
        },

        async update(id: string, payload: FormData) {
            // PHP does not parse multipart bodies on PUT, so the update is
            // posted with a method override - the standard Laravel idiom.
            payload.append('_method', 'PUT')
            return await useApi(`/admin/brands/${id}`, { method: 'POST', body: payload, notifyOnSuccess: true })
        },

        async remove(id: string) {
            return await useApi(`/admin/brands/${id}`, { method: 'DELETE', notifyOnSuccess: true })
        },
    },

    // ---------------------------------------------------------- categories
    categories: {
        async list(page = 1, perPage = 15) {
            const response = await useApi<CategoryInterface[]>('/admin/categories', { query: { page, perPage } })
            return {
                items: response?.data ?? [],
                meta: (response?.meta ?? null) as PageMetaInterface | null,
            }
        },

        async tree(withInactive = true) {
            const response = await useApi<CategoryTreeNode[]>('/admin/categories/tree', {
                query: { withInactive },
            })
            return response?.data ?? []
        },

        /** Flat parent options; `exclude` removes a node's own subtree. */
        async options(exclude?: string) {
            const response = await useApi<CategoryOption[]>('/admin/categories/options', {
                query: { exclude },
            })
            return response?.data ?? []
        },

        async find(id: string) {
            const response = await useApi<CategoryInterface>(`/admin/categories/${id}`)
            return response?.data ?? null
        },

        async create(payload: FormData) {
            return await useApi('/admin/categories', { method: 'POST', body: payload, notifyOnSuccess: true })
        },

        async update(id: string, payload: FormData) {
            payload.append('_method', 'PUT')
            return await useApi(`/admin/categories/${id}`, { method: 'POST', body: payload, notifyOnSuccess: true })
        },

        async remove(id: string) {
            return await useApi(`/admin/categories/${id}`, { method: 'DELETE', notifyOnSuccess: true })
        },
    },

    // ------------------------------------------------------------ products
    products: {
        async list(page = 1, perPage = 15, search = '') {
            const response = await useApi<ProductInterface[]>('/admin/products', {
                query: { page, perPage, search: search || undefined },
            })
            return {
                items: response?.data ?? [],
                meta: (response?.meta ?? null) as PageMetaInterface | null,
            }
        },

        async find(id: string) {
            const response = await useApi<ProductInterface>(`/admin/products/${id}`)
            return response?.data ?? null
        },

        async create(payload: FormData) {
            return await useApi('/admin/products', { method: 'POST', body: payload, notifyOnSuccess: true })
        },

        async update(id: string, payload: FormData) {
            payload.append('_method', 'PUT')
            return await useApi(`/admin/products/${id}`, { method: 'POST', body: payload, notifyOnSuccess: true })
        },

        async remove(id: string) {
            return await useApi(`/admin/products/${id}`, { method: 'DELETE', notifyOnSuccess: true })
        },
    },

    // ------------------------------------------------------------ settings
    settings: {
        async all() {
            const response = await useApi<SiteSettings>('/admin/settings')
            return response?.data ?? null
        },

        /** Saves one group. */
        async save<K extends SettingsKey>(key: K, value: SiteSettings[K]) {
            return await useApi('/admin/settings', {
                method: 'POST',
                body: { key, value },
                notifyOnSuccess: true,
            })
        },

        /** Saves a whole tab (several groups) in one request. */
        async saveMany(groups: Partial<SiteSettings>) {
            return await useApi('/admin/settings/bulk', {
                method: 'POST',
                body: { settings: groups },
                notifyOnSuccess: true,
            })
        },

        async reset(key: SettingsKey) {
            return await useApi(`/admin/settings/${key}/reset`, {
                method: 'POST',
                notifyOnSuccess: true,
            })
        },
    },
}
