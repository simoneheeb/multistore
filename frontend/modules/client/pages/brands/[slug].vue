<template>
    <div v-if="brand" class="py-8 md:py-10">
        <div class="site-container">
            <UiBreadcrumbs :items="crumbs" class="mb-5" />

            <header class="flex flex-wrap items-start gap-5 border-b border-border pb-6">
                <UiImage :src="brand.logo" :alt="brand.name" fit="contain" ratio="square" class="h-max w-24 shrink-0 border border-border" />

                <div class="min-w-0 flex-1">
                    <h1 class="mt-1 text-2xl font-bold text-heading md:text-3xl">{{ brand.name }}</h1>
                    <p class="text-xs uppercase tracking-[0.18em] text-primary text-right mr-4">{{ brand.name }}</p>

                    <p v-if="brand.description" class="mt-3 max-w-3xl text-sm leading-8 text-muted">
                        {{ brand.description }}
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <UiBadge v-if="brand.categories_length" tone="neutral">
                            {{ formatNumber(brand.categories_length) }} categories
                        </UiBadge>
                        <UiBadge v-if="brand.products_length" tone="primary">
                            {{ formatNumber(brand.products_length) }} products
                        </UiBadge>
                    </div>
                </div>
            </header>

            <!-- Category tree of this brand, two levels deep. -->
            <section v-if="categories.length" class="mt-8">
                <UiTitle eyebrow="Categories" title="Categories from this brand" size="sm" rule />

                <ul class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <li v-for="node in categories" :key="node.slug">
                        <div class="flex h-full flex-col rounded-[var(--radius-base)] border border-border bg-surface p-4">
                            <NuxtLink :to="`/categories/${node.slug}`" class="group border-b border-border pb-2">
                                <span class="text-sm font-bold text-heading transition-colors group-hover:text-primary">
                                    {{ node.name }}
                                </span>
                                <span class="block text-[0.625rem] uppercase tracking-wider text-muted">{{ node.name }}</span>
                            </NuxtLink>

                            <ul v-if="node.children?.length" class="mt-2 flex flex-col gap-0.5">
                                <li v-for="child in node.children" :key="child.slug">
                                    <NuxtLink
                                        :to="`/categories/${node.slug}/${child.slug}`"
                                        class="block truncate rounded px-2 py-1 text-[0.8125rem] text-muted transition-colors hover:bg-surface-muted hover:text-primary">
                                        {{ child.name }}
                                    </NuxtLink>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="mt-10">
                <UiTitle eyebrow="Products" title="Products from this brand" size="sm" rule />

                <div class="mt-5">
                    <ProductGrid :products="products" :loading="productsPending" empty-title="No products for this brand yet" />
                </div>

                <div v-if="meta && meta.last_page > 1" class="mt-8">
                    <UiPagination :current-page="meta.current_page" :last-page="meta.last_page" @change="page = $event" />
                </div>
            </section>
        </div>
    </div>

    <div v-else class="site-container py-20">
        <UiEmpty title="Brand not found">
            <UiButton to="/brands" variant="outline">All brands</UiButton>
        </UiEmpty>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { createError, useAsyncData, useRoute, useRuntimeConfig } from 'nuxt/app'
import { throwCatalogError } from '~/utils/catalogError'
import { CatalogService } from '~~/modules/client/services/CatalogService'
import { buildBreadcrumbJsonLd, usePageSeo } from '~/composables/useSeoDefaults'
import { formatNumber } from '~/utils/format'

const route = useRoute()
const config = useRuntimeConfig()

const slug = computed(() => String(route.params.slug))
const page = ref(1)

const { data, error } = await useAsyncData(
    () => `brand-${slug.value}`,
    () => CatalogService.brand(slug.value),
    { watch: [slug] },
)

// A failed request keeps its own status; only an empty response is a 404.
if (error.value) {
    throwCatalogError(error.value, 'Brand')
}

if (!data.value) {
    throw createError({ statusCode: 404, statusMessage: 'Brand not found', fatal: true })
}

const brand = computed(() => data.value ?? null)

// root_categories is the brand's own tree; categories is the flat fallback
// for older payloads that did not carry it.
const categories = computed(() => brand.value?.root_categories ?? brand.value?.categories ?? [])

/**
 * Products are fetched separately and paginated, because a brand can hold far
 * more items than belong in the brand payload itself.
 */
const { data: productData, pending: productsPending } = useAsyncData(
    () => `brand-products-${slug.value}-${page.value}`,
    () => CatalogService.products({
        brand_id: brand.value?.id ?? undefined,
        page: page.value,
        perPage: 12,
    }),
    { watch: [slug, page] },
)

const products = computed(() => productData.value?.items ?? [])
const meta = computed(() => productData.value?.meta ?? null)

const crumbs = computed(() => [
    { title: 'Home', to: '/' },
    { title: 'Brands', to: '/brands' },
    { title: brand.value?.name ?? '', to: `/brands/${slug.value}` },
])

const origin = computed(() => String(config.public.siteUrl ?? ''))

usePageSeo({
    title: brand.value?.meta_title || brand.value?.name,
    description: brand.value?.meta_description || brand.value?.description,
    image: brand.value?.logo ?? undefined,
    path: `/brands/${slug.value}`,
    jsonLd: [
        buildBreadcrumbJsonLd(crumbs.value, origin.value),
        {
            '@context': 'https://schema.org',
            '@type': 'Brand',
            name: brand.value?.name,
            alternateName: brand.value?.name,
            logo: brand.value?.logo,
            description: brand.value?.description,
        },
    ],
})
</script>
