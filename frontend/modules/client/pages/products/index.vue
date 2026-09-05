<template>
    <div class="py-8 md:py-10">
        <div class="site-container">
            <UiBreadcrumbs :items="crumbs" class="mb-4" />

            <UiTitle
                eyebrow="Products"
                title="Products"
                :description="searchTerm ? `Search results for “${searchTerm}”` : 'Every product in the store'"
                size="lg"
                rule
                tag="h1" />

            <!-- Filter bar -->
            <form class="mt-6 grid gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4 md:grid-cols-4" @submit.prevent="applyFilters">
                <UiInput
                    v-model="filters.search"
                    icon="search"
                    label="Search"
                    placeholder="Product name…" />

                <UiSelect
                    v-model="filters.brand_id"
                    label="Brand"
                    placeholder="All brands"
                    :options="brandOptions" />

                <UiSelect
                    v-model="filters.category_id"
                    label="Category"
                    placeholder="All categories"
                    :options="categoryOptions" />

                <div class="flex items-end gap-2">
                    <UiSelect
                        v-model="filters.sort"
                        label="Order"
                        :options="sortOptions"
                        class="flex-1" />

                    <UiButton type="submit" icon="filter">Apply</UiButton>
                </div>
            </form>

            <p v-if="meta" class="mt-4 text-xs text-muted">
                {{ formatNumber(meta.total) }} products found
            </p>

            <div class="mt-4">
                <ProductGrid :products="products" :loading="pending" />
            </div>

            <div v-if="meta && meta.last_page > 1" class="mt-8">
                <UiPagination
                    :current-page="meta.current_page"
                    :last-page="meta.last_page"
                    @change="goToPage" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import { useAsyncData, useRoute, useRouter } from 'nuxt/app'
import { CatalogService } from '~~/modules/client/services/CatalogService'
import { formatNumber } from '~/utils/format'

const route = useRoute()
const router = useRouter()

/**
 * Filters live in the URL, not in component state.
 *
 * That makes every filtered view linkable and crawlable, and it means the
 * server render already reflects the filters instead of applying them after
 * hydration.
 */
const filters = reactive({
    search: (route.query.search as string) ?? '',
    brand_id: (route.query.brand_id as string) ?? '',
    category_id: (route.query.category_id as string) ?? '',
    sort: (route.query.sort as string) ?? 'latest',
})

const searchTerm = computed(() => (route.query.search as string) ?? '')
const currentPage = computed(() => Number(route.query.page ?? 1))

const sortOptions = [
    { value: 'latest', label: 'Newest first' },
    { value: 'oldest', label: 'Oldest first' },
    { value: 'name', label: 'By name' },
]

const { data: taxonomy } = useAsyncData('products-taxonomy', async () => {
    const [brands, categories] = await Promise.all([
        CatalogService.brands(),
        CatalogService.categories(),
    ])
    return { brands, categories }
})

const brandOptions = computed(() =>
    (taxonomy.value?.brands ?? []).map(brand => ({
        value: brand.id ?? brand.slug,
        label: brand.name,
    })),
)

const categoryOptions = computed(() =>
    (taxonomy.value?.categories ?? []).map(category => ({
        value: category.id ?? category.slug,
        label: `${'— '.repeat(category.depth ?? 0)}${category.name}`,
    })),
)

// The key includes the query, so navigating to a different filter set
// refetches rather than showing the previous page's results.
const { data, pending } = useAsyncData(
    () => `products-${JSON.stringify(route.query)}`,
    () => CatalogService.products({
        page: currentPage.value,
        perPage: 24,
        search: (route.query.search as string) || undefined,
        brand_id: (route.query.brand_id as string) || undefined,
        category_id: (route.query.category_id as string) || undefined,
        sort: (route.query.sort as any) || 'latest',
    }),
    { watch: [() => route.query] },
)

const products = computed(() => data.value?.items ?? [])
const meta = computed(() => data.value?.meta ?? null)

const crumbs = [
    { title: 'Home', to: '/' },
    { title: 'Products', to: '/products' },
]

const applyFilters = () => {
    router.push({
        path: '/products',
        // Changing a filter always resets to page 1; keeping the old page
        // number would frequently land on an empty page.
        query: cleanQuery({ ...filters, page: undefined }),
    })
}

const goToPage = (page: number) => {
    router.push({ path: '/products', query: cleanQuery({ ...route.query, page }) })
}

/** Drops empty values so the URL stays readable and cache keys stay stable. */
function cleanQuery(input: Record<string, unknown>) {
    return Object.fromEntries(
        Object.entries(input).filter(([, value]) => value !== '' && value !== undefined && value !== null),
    )
}

// Keep the form in step when the visitor navigates with the back button.
watch(() => route.query, (query) => {
    filters.search = (query.search as string) ?? ''
    filters.brand_id = (query.brand_id as string) ?? ''
    filters.category_id = (query.category_id as string) ?? ''
    filters.sort = (query.sort as string) ?? 'latest'
})

usePageSeo({
    title: searchTerm.value ? `Search: ${searchTerm.value}` : 'Products',
    description: 'The complete product catalogue, filterable by brand and category.',
    path: '/products',
    // A filtered or paginated listing is thin, near-duplicate content; only
    // the clean listing should compete in the index.
    noindex: Object.keys(route.query).length > 0,
})
</script>
