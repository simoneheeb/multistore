<template>
    <div class="py-8 md:py-10">
        <div class="site-container">
            <UiBreadcrumbs :items="crumbs" class="mb-4" />

            <UiTitle
                eyebrow="Categories"
                title="Categories"
                description="The full category structure of the catalogue."
                size="lg"
                rule
                tag="h1" />

            <div v-if="pending" class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <UiSkeleton v-for="index in 6" :key="index" height="11rem" />
            </div>

            <UiEmpty
                v-else-if="!tree.length"
                title="No categories yet"
                class="mt-8" />

            <!--
                Two levels are rendered as a card per root with its children
                listed inside. Deeper nodes are reachable from each branch's
                own page, which keeps this overview scannable.
            -->
            <ul v-else class="mt-8 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <li v-for="node in tree" :key="node.slug">
                    <div class="flex h-full flex-col rounded-[var(--radius-base)] border border-border bg-surface p-5">
                        <NuxtLink :to="`/categories/${node.slug}`" class="group border-b border-border pb-3">
                            <span class="block text-[0.625rem] uppercase tracking-[0.16em] text-muted">
                                {{ node.name }}
                            </span>
                            <span class="mt-0.5 flex items-center justify-between gap-2">
                                <span class="text-base font-bold text-heading transition-colors group-hover:text-primary">
                                    {{ node.name }}
                                </span>
                                <span v-if="node.products_length" class="shrink-0 text-[0.6875rem] text-muted">
                                    {{ formatNumber(node.products_length) }} products
                                </span>
                            </span>
                        </NuxtLink>

                        <ul v-if="node.children?.length" class="mt-3 flex flex-col gap-1">
                            <li v-for="child in node.children" :key="child.slug">
                                <NuxtLink
                                    :to="`/categories/${node.slug}/${child.slug}`"
                                    class="group flex items-baseline justify-between gap-2 rounded px-2 py-1.5 transition-colors hover:bg-surface-muted">
                                    <span class="min-w-0">
                                        <span class="block truncate text-[0.8125rem] text-body transition-colors group-hover:text-primary">
                                            {{ child.name }}
                                        </span>
                                        <span class="block truncate text-[0.625rem] text-muted">
                                            {{ child.name }}
                                        </span>
                                    </span>

                                    <span v-if="child.children?.length" class="shrink-0 text-[0.625rem] text-muted">
                                        {{ formatNumber(child.children.length) }} subcategories
                                    </span>
                                </NuxtLink>
                            </li>
                        </ul>

                        <p v-else class="mt-3 text-xs text-muted">No subcategories.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useAsyncData } from 'nuxt/app'
import { CatalogService } from '~~/modules/client/services/CatalogService'
import { formatNumber } from '~/utils/format'
import { usePageSeo } from '~~/app/composables/useSeoDefaults'

const { data, pending } = useAsyncData('categories-tree-page', () => CatalogService.categoryTree())

const tree = computed(() => data.value ?? [])

const crumbs = [
    { title: 'Home', to: '/' },
    { title: 'Categories', to: '/categories' },
]

usePageSeo({
    title: 'Categories',
    description: 'Browse every product category in the store at a glance.',
    path: '/categories',
})
</script>
