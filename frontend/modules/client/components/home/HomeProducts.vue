<template>
    <UiSection
        :tone="tone"
        :eyebrow="eyebrow"
        :title="title"
        :description="description">
        <template #actions>
            <UiButton :to="ctaTo" variant="link" size="sm" trailing-icon="chevron-right">
                {{ ctaLabel }}
            </UiButton>
        </template>

        <ProductGrid
            :products="products"
            :loading="loading"
            empty-title="No products yet"
            empty-description="Products for this section are on their way." />
    </UiSection>
</template>

<script setup lang="ts">
import type { ProductInterface } from '~/types/interfaces'

withDefaults(defineProps<{
    products: ProductInterface[]
    loading?: boolean
    eyebrow: string
    title: string
    description?: string
    ctaLabel?: string
    ctaTo?: string
    tone?: 'page' | 'surface' | 'muted'
}>(), {
    products: () => [],
    ctaLabel: 'View all',
    ctaTo: '/products',
    tone: 'page',
})
</script>
