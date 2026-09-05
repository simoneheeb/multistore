<template>
    <UiSection
        v-if="categories.length"
        tone="surface"
        eyebrow="Categories"
        title="Categories"
        description="Browse the catalogue by category.">
        <template #actions>
            <UiButton to="/categories" variant="link" size="sm" trailing-icon="chevron-right">
                All categories
            </UiButton>
        </template>

        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <li v-for="category in categories" :key="category.slug">
                <NuxtLink
                    :to="`/categories/${category.slug}`"
                    class="group flex h-full flex-col items-center gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4 text-center transition-colors hover:border-primary">
                    <UiImage
                        :src="category.logo"
                        :alt="category.name"
                        fit="contain"
                        ratio="square"
                        class="h-16 w-16" />

                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-heading transition-colors group-hover:text-primary">
                            {{ category.name }}
                        </p>
                        <p class="mt-0.5 truncate text-[0.625rem] uppercase tracking-wider text-muted">
                            {{ category.name }}
                        </p>
                    </div>

                    <span v-if="category.products_length" class="text-[0.625rem] text-muted">
                        {{ formatNumber(category.products_length) }} products
                    </span>
                </NuxtLink>
            </li>
        </ul>
    </UiSection>
</template>

<script setup lang="ts">
import { formatNumber } from '~/utils/format'
import type { CategoryInterface } from '~/types/interfaces'

withDefaults(defineProps<{ categories: CategoryInterface[] }>(), {
    categories: () => [],
})
</script>
