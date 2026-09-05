<template>
    <div v-if="category" class="py-8 md:py-10">
        <div class="site-container">
            <UiBreadcrumbs :items="crumbs" class="mb-5" />

            <header class="flex flex-wrap items-start justify-between gap-5 border-b border-border pb-6">
                <div class="flex items-start gap-4">
                    <UiImage
                        v-if="category.logo"
                        :src="category.logo"
                        :alt="category.name"
                        fit="contain"
                        ratio="square"
                        class="h-16 w-16 shrink-0 border border-border" />

                    <div class="min-w-0">
                        <h1 class="mt-1 text-2xl font-bold text-heading md:text-3xl">
                            {{ category.name }}
                        </h1>
                        <p class="text-xs uppercase tracking-[0.18em] text-primary">
                            {{ category.name }}
                        </p>
                        <p v-if="category.description" class="mt-2 max-w-2xl text-sm leading-7 text-muted">
                            {{ category.description }}
                        </p>
                    </div>
                </div>

                <UiBadge tone="primary">
                    {{ formatNumber(products.length) }} products
                </UiBadge>
            </header>

            <!-- Sub-categories of the node being viewed. -->
            <nav v-if="children.length" class="mt-6" aria-label="Subcategories">
                <p class="mb-2 text-xs font-medium text-muted">Subcategories</p>

                <ul class="flex flex-wrap gap-2">
                    <li v-for="child in children" :key="child.slug">
                        <NuxtLink
                            :to="childLink(child.slug)"
                            class="flex items-center gap-2 rounded-full border border-border bg-surface px-3.5 py-1.5 text-xs text-body transition-colors hover:border-primary hover:text-primary">
                            {{ child.name }}
                            <span v-if="child.products_length" class="text-[0.625rem] text-muted">
                                {{ child.products_length }}
                            </span>
                        </NuxtLink>
                    </li>
                </ul>
            </nav>

            <div class="mt-8">
                <ProductGrid :products="products" empty-title="No products in this category yet" empty-description="Products for this category are on their way." />
            </div>
        </div>
    </div>

    <div v-else class="site-container py-20">
        <UiEmpty title="Category not found">
            <UiButton to="/categories" variant="outline">All categories</UiButton>
        </UiEmpty>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { createError, useAsyncData, useRoute, useRuntimeConfig } from 'nuxt/app'
import { throwCatalogError } from '~/utils/catalogError'
import { CatalogService } from '~~/modules/client/services/CatalogService'
import { buildBreadcrumbJsonLd, usePageSeo } from '~/composables/useSeoDefaults'
import { formatNumber } from '~/utils/format'

const route = useRoute()
const config = useRuntimeConfig()

const slug = computed(() => String(route.params.slug))
const childSlug = computed(() => (route.params.childSlug ? String(route.params.childSlug) : undefined))

const { data, error } = await useAsyncData(
    () => `category-${slug.value}-${childSlug.value ?? ''}`,
    () => CatalogService.category(slug.value, childSlug.value),
    { watch: [slug, childSlug] },
)

// A failed request keeps its own status; only an empty response is a 404.
if (error.value) {
    throwCatalogError(error.value, 'Category')
}

if (!data.value?.category) {
    throw createError({ statusCode: 404, statusMessage: 'Category not found', fatal: true })
}

const category = computed(() => data.value?.category ?? null)
const children = computed(() => category.value?.children ?? [])
const products = computed(() => data.value?.products ?? [])
const ancestors = computed(() => data.value?.breadcrumb ?? [])

/**
 * A child link is /categories/{parent}/{child}. When the current node is
 * already a child, its own children are one level deeper than the two-segment
 * route supports, so they are linked as roots of their own branch instead.
 */
const childLink = (target: string) =>
    childSlug.value ? `/categories/${target}` : `/categories/${slug.value}/${target}`

const crumbs = computed(() => {
    const items = [
        { title: 'Home', to: '/' },
        { title: 'Categories', to: '/categories' },
    ]

    ancestors.value.forEach((node) => {
        items.push({ title: node.name, to: `/categories/${node.slug}` })
    })

    items.push({ title: category.value?.name ?? '', to: route.path })

    return items
})

const origin = computed(() => String(config.public.siteUrl ?? ''))

usePageSeo({
    title: category.value?.meta_title || category.value?.name,
    description: category.value?.meta_description || category.value?.description,
    image: category.value?.logo ?? undefined,
    path: route.path,
    jsonLd: [
        buildBreadcrumbJsonLd(crumbs.value, origin.value),
        {
            '@context': 'https://schema.org',
            '@type': 'CollectionPage',
            name: category.value?.name,
            description: category.value?.description,
        },
    ],
})
</script>
