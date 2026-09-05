import { defineStore } from 'pinia'
import { AdminService } from '../services/AdminService'
import type {
    BrandInterface,
    CategoryInterface,
    CategoryOption,
    CategoryTreeNode,
    PageMetaInterface,
    ProductInterface,
} from '~/types/interfaces'

interface CatalogState {
    brands: BrandInterface[]
    brandsMeta: PageMetaInterface | null
    allBrands: BrandInterface[]

    categories: CategoryInterface[]
    categoriesMeta: PageMetaInterface | null
    categoryTree: CategoryTreeNode[]
    categoryOptions: CategoryOption[]

    products: ProductInterface[]
    productsMeta: PageMetaInterface | null

    loading: Record<'brands' | 'categories' | 'products' | 'saving', boolean>
}

/**
 * Catalogue state for the admin panel.
 *
 * One store for all three resources: they are edited from the same screens
 * (a product form needs brands and the category tree), and keeping them
 * together avoids three stores that constantly call into each other.
 */
export const useCatalogStore = defineStore('admin-catalog', {
    state: (): CatalogState => ({
        brands: [],
        brandsMeta: null,
        allBrands: [],

        categories: [],
        categoriesMeta: null,
        categoryTree: [],
        categoryOptions: [],

        products: [],
        productsMeta: null,

        loading: { brands: false, categories: false, products: false, saving: false },
    }),

    actions: {
        // ---------------------------------------------------------- brands
        async fetchBrands(page = 1) {
            this.loading.brands = true
            try {
                const { items, meta } = await AdminService.brands.list(page)
                this.brands = items
                this.brandsMeta = meta
            } finally {
                this.loading.brands = false
            }
        },

        /** Full list for the select inputs on the category and product forms. */
        async fetchAllBrands() {
            if (this.allBrands.length) return
            this.allBrands = await AdminService.brands.all()
        },

        async saveBrand(payload: FormData, id?: string) {
            this.loading.saving = true
            try {
                const response = id
                    ? await AdminService.brands.update(id, payload)
                    : await AdminService.brands.create(payload)

                if (response) {
                    // The list and the cached "all" set are both stale now.
                    this.allBrands = []
                    await this.fetchBrands(this.brandsMeta?.current_page ?? 1)
                }

                return Boolean(response)
            } finally {
                this.loading.saving = false
            }
        },

        async deleteBrand(id: string) {
            const response = await AdminService.brands.remove(id)
            if (response) {
                this.allBrands = []
                await this.fetchBrands(this.brandsMeta?.current_page ?? 1)
            }
            return Boolean(response)
        },

        // ------------------------------------------------------ categories
        async fetchCategories(page = 1) {
            this.loading.categories = true
            try {
                const { items, meta } = await AdminService.categories.list(page)
                this.categories = items
                this.categoriesMeta = meta
            } finally {
                this.loading.categories = false
            }
        },

        async fetchCategoryTree() {
            this.categoryTree = await AdminService.categories.tree(true)
        },

        /**
         * Parent options for the tree picker. `exclude` is the id being
         * edited, so the API leaves out that node and its descendants and a
         * category can never be re-parented into its own branch.
         */
        async fetchCategoryOptions(exclude?: string) {
            this.categoryOptions = await AdminService.categories.options(exclude)
        },

        async saveCategory(payload: FormData, id?: string) {
            this.loading.saving = true
            try {
                const response = id
                    ? await AdminService.categories.update(id, payload)
                    : await AdminService.categories.create(payload)

                if (response) {
                    await Promise.all([
                        this.fetchCategories(this.categoriesMeta?.current_page ?? 1),
                        this.fetchCategoryTree(),
                    ])
                }

                return Boolean(response)
            } finally {
                this.loading.saving = false
            }
        },

        async deleteCategory(id: string) {
            const response = await AdminService.categories.remove(id)
            if (response) {
                await Promise.all([
                    this.fetchCategories(this.categoriesMeta?.current_page ?? 1),
                    this.fetchCategoryTree(),
                ])
            }
            return Boolean(response)
        },

        // -------------------------------------------------------- products
        async fetchProducts(page = 1, search = '') {
            this.loading.products = true
            try {
                const { items, meta } = await AdminService.products.list(page, 15, search)
                this.products = items
                this.productsMeta = meta
            } finally {
                this.loading.products = false
            }
        },

        async saveProduct(payload: FormData, id?: string) {
            this.loading.saving = true
            try {
                const response = id
                    ? await AdminService.products.update(id, payload)
                    : await AdminService.products.create(payload)

                if (response) await this.fetchProducts(this.productsMeta?.current_page ?? 1)

                return Boolean(response)
            } finally {
                this.loading.saving = false
            }
        },

        async deleteProduct(id: string) {
            const response = await AdminService.products.remove(id)
            if (response) await this.fetchProducts(this.productsMeta?.current_page ?? 1)
            return Boolean(response)
        },
    },
})
