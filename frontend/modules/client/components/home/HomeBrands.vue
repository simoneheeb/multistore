<template>
    <UiSection
        v-if="brands.length"
        tone="surface"
        eyebrow="Brands"
        title="Featured brands"
        description="We stock the most trusted brands on the market.">
        <template #actions>
            <UiButton to="/brands" variant="link" size="sm" trailing-icon="chevron-right">
                All brands
            </UiButton>
        </template>

        <UiSlider>
            <NuxtLink
                v-for="brand in brands"
                :key="brand.slug"
                :to="`/brands/${brand.slug}`"
                class="group flex w-36 shrink-0 snap-start flex-col items-center gap-2 rounded-[var(--radius-base)] border border-border bg-page p-4 transition-colors hover:border-primary sm:w-44">
                <UiImage
                    :src="brand.logo"
                    :alt="brand.name"
                    fit="contain"
                    ratio="square"
                    class="h-16 w-full" />

                <p class="w-full truncate text-center text-xs font-medium text-heading transition-colors group-hover:text-primary">
                    {{ brand.name }}
                </p>
                <p class="w-full truncate text-center text-[0.625rem] uppercase tracking-wider text-muted">
                    {{ brand.name }}
                </p>
            </NuxtLink>
        </UiSlider>
    </UiSection>
</template>

<script setup lang="ts">
import type { BrandInterface } from '~/types/interfaces'

withDefaults(defineProps<{ brands: BrandInterface[] }>(), {
    brands: () => [],
})
</script>
