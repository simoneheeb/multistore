<template>
    <div>
        <!-- Skeletons keep the grid at its final height while data loads, so
             the page below does not jump when the products arrive. -->
        <ul v-if="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <li v-for="index in skeletonCount" :key="index" class="overflow-hidden rounded-[var(--radius-base)] border border-border bg-surface">
                <UiSkeleton height="0" class="aspect-square w-full" rounded="sm" />
                <div class="flex flex-col gap-2 p-3">
                    <UiSkeleton height="0.6rem" width="40%" />
                    <UiSkeleton height="0.9rem" />
                    <UiSkeleton height="0.7rem" width="60%" />
                </div>
            </li>
        </ul>

        <UiEmpty
            v-else-if="!products.length"
            :title="emptyTitle"
            :description="emptyDescription"
            icon="box" />

        <ul v-else class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <li v-for="product in products" :key="product.slug">
                <ProductCard :product="product" />
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import type { ProductInterface } from '~/types/interfaces'

withDefaults(defineProps<{
    products: ProductInterface[]
    loading?: boolean
    skeletonCount?: number
    emptyTitle?: string
    emptyDescription?: string
}>(), {
    products: () => [],
    skeletonCount: 8,
    emptyTitle: 'No products found',
    emptyDescription: 'Try adjusting the filters or your search term.',
})
</script>
