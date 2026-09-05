<template>
    <div class="py-8 md:py-10">
        <div class="site-container">
            <UiBreadcrumbs :items="crumbs" class="mb-4" />

            <UiTitle eyebrow="Brands" title="Brands" description="The brands carried by this store." size="lg" rule tag="h1" />

            <div v-if="pending" class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <UiSkeleton v-for="index in 10" :key="index" height="9rem" />
            </div>

            <UiEmpty v-else-if="!brands.length" title="No brands yet" class="mt-8" />

            <ul v-else class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <li v-for="brand in brands" :key="brand.slug">

                    <NuxtLink :to="`/brands/${brand.slug}`"
                        class="group flex h-full flex-col items-center gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4 text-center transition-colors hover:border-primary">

                        <UiImage :src="brand.logo" :alt="brand.name" fit="contain" ratio="square" class="h-20 w-full" />

                        <div class="min-w-0">
                            <p class="truncate text-2xl font-medium text-heading transition-colors group-hover:text-primary">
                                {{ brand.name }}
                            </p>
                            <p class="mt-0.5 truncate text-[0.625rem] uppercase tracking-wider text-muted">
                                {{ brand.name }}
                            </p>
                        </div>

                        <span v-if="brand.categories_length" class="text-[0.625rem] text-muted">
                            {{ formatNumber(brand.categories_length) }} categories
                        </span>
                    </NuxtLink>
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

const { data, pending } = useAsyncData('brands-page', () => CatalogService.brands())

const brands = computed(() => data.value ?? [])

const crumbs = [
    { title: 'Home', to: '/' },
    { title: 'Brands', to: '/brands' },
]

usePageSeo({
    title: 'Brands',
    description: 'Every brand we stock, with the categories each one covers.',
    path: '/brands',
})
</script>
