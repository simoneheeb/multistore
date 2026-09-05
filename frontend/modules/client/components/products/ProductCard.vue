<template>
    <article class="group flex h-full flex-col overflow-hidden rounded-[var(--radius-base)] border border-border bg-surface transition-colors hover:border-primary relative">

        <!-- brand logo -->
        <div class="absolute top-0 left-0 z-10 flex h-12 w-auto p-1 items-center">
            <UiImage :src="product.brand?.logo" :alt="product.brand?.name" class="h-full w-full self-start" />
        </div>

        <NuxtLink :to="`/products/${product.slug}`" class="relative block">
            <UiImage :src="product.featured_img" :alt="product.name" ratio="square" :rounded="false" class="h-full w-full transition-transform duration-300 group-hover:scale-[1.02]" />

            <UiBadge v-if="product.is_new" tone="primary" class="absolute top-2 start-2">
                New
            </UiBadge>
        </NuxtLink>

        <div class="flex flex-1 flex-col p-3 text-center">
            
            <h3 class="text-xl text-wrap font-medium leading-6 text-heading transition-colors group-hover:text-primary mb-2">
                {{ product.name }}
            </h3>
            <p class="text-xs uppercase tracking-[0.18em] text-primary">
                {{ product.name }}
            </p>

            <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                <span v-if="product.category" class="truncate text-[0.6875rem] text-muted">
                    {{ product.category.name }}
                </span>

                <span v-if="product.pid" class="shrink-0 text-[0.625rem] text-border-strong">
                    {{ product.pid }}
                </span>
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import type { ProductInterface } from '~/types/interfaces'

defineProps<{ product: ProductInterface }>()
</script>
