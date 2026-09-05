<template>
    <DashboardPage
        title="Dashboard"
        description="An overview of the store’s content.">
        <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <li v-for="card in cards" :key="card.to">
                <NuxtLink
                    :to="card.to"
                    class="group flex h-full items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-5 transition-colors hover:border-primary">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                        <UiIcon :name="card.icon" :size="20" />
                    </span>

                    <div class="min-w-0">
                        <p class="text-[0.625rem] uppercase tracking-[0.16em] text-muted">{{ card.labelEn }}</p>
                        <p class="text-sm font-medium text-heading">{{ card.label }}</p>

                        <p class="mt-1.5 text-2xl font-bold text-heading">
                            <UiSkeleton v-if="loading" width="3rem" height="1.5rem" />
                            <template v-else>{{ formatNumber(card.value) }}</template>
                        </p>
                    </div>
                </NuxtLink>
            </li>
        </ul>

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            <section class="rounded-[var(--radius-base)] border border-border bg-surface p-5">
                <UiTitle title="Quick actions" size="sm" rule />

                <div class="mt-4 flex flex-wrap gap-2">
                    <UiButton to="/admin/products" icon="plus" size="sm">New product</UiButton>
                    <UiButton to="/admin/categories" icon="plus" size="sm" variant="outline">New category</UiButton>
                    <UiButton to="/admin/brands" icon="plus" size="sm" variant="outline">New brand</UiButton>
                    <UiButton to="/admin/settings" icon="settings" size="sm" variant="outline">Site settings</UiButton>
                </div>
            </section>

            <section class="rounded-[var(--radius-base)] border border-border bg-surface p-5">
                <UiTitle title="Latest products" size="sm" rule />

                <ul v-if="catalog.products.length" class="mt-4 flex flex-col divide-y divide-border">
                    <li v-for="product in catalog.products.slice(0, 5)" :key="product.slug" class="flex items-center gap-3 py-2.5">
                        <UiImage :src="product.featured_img" :alt="product.name" ratio="square" class="h-10 w-10 shrink-0" />

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-xs font-medium text-heading">{{ product.name }}</p>
                            <p class="truncate text-[0.625rem] text-muted">{{ product.name }}</p>
                        </div>

                        <UiBadge v-if="product.is_new" tone="primary">New</UiBadge>
                    </li>
                </ul>

                <p v-else class="mt-4 text-xs text-muted">No products yet.</p>
            </section>
        </div>
    </DashboardPage>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { definePageMeta } from '#imports'
import { useCatalogStore } from '~~/modules/admin/stores/catalog'
import { formatNumber } from '~/utils/format'

definePageMeta({ layout: 'dashboard' })

const catalog = useCatalogStore()

// Client-side only: the dashboard is behind auth and its numbers are not
// worth a server render.
onMounted(async () => {
    await Promise.all([
        catalog.fetchBrands(),
        catalog.fetchCategories(),
        catalog.fetchProducts(),
    ])
})

const loading = computed(
    () => catalog.loading.brands || catalog.loading.categories || catalog.loading.products,
)

const cards = computed(() => [
    { label: 'Brands', labelEn: 'Brands', icon: 'tag', to: '/admin/brands', value: catalog.brandsMeta?.total ?? catalog.brands.length },
    { label: 'Categories', labelEn: 'Categories', icon: 'layers', to: '/admin/categories', value: catalog.categoriesMeta?.total ?? catalog.categories.length },
    { label: 'Products', labelEn: 'Products', icon: 'box', to: '/admin/products', value: catalog.productsMeta?.total ?? catalog.products.length },
    { label: 'Settings', labelEn: 'Settings', icon: 'settings', to: '/admin/settings', value: 0 },
])

usePageSeo({ title: 'Admin panel', noindex: true })
</script>
