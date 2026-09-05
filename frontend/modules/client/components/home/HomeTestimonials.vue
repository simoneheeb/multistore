<template>
    <UiSection v-if="enabled && items.length" tone="surface" :eyebrow="eyebrow" :title="title" align="center">
        <UiSlider :show-arrows="items.length > 3">

            <figure v-for="(item, index) in items" :key="index"
                class="flex w-[18rem] shrink-0 snap-start flex-col rounded-[var(--radius-base)] border border-border bg-surface p-5 sm:w-[22rem]">
                <div v-if="item.rating" class="mb-3 flex items-center gap-0.5" :aria-label="`${item.rating} out of 5`">

                    <UiIcon v-for="star in 5" :key="star" name="star" :size="15" :class="star <= item.rating ? 'text-accent' : 'text-border-strong'" />

                </div>

                <blockquote class="flex-1 text-sm leading-8 text-body">
                    {{ item.text }}
                </blockquote>

                <figcaption class="mt-4 flex items-center gap-3 border-t border-border pt-4">

                    <UiImage v-if="item.avatar" :src="item.avatar" :alt="item.name" ratio="square" class="h-10 w-10 rounded-full" />

                    <span v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-surface-muted text-muted">
                        <UiIcon name="user" :size="18" />
                    </span>

                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-heading">{{ item.name }}</span>
                        <span v-if="item.role" class="block truncate text-[0.6875rem] text-muted">{{ item.role }}</span>
                    </span>
                </figcaption>
            </figure>
        </UiSlider>
    </UiSection>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSiteSettings } from '~~/app/composables/useSiteSettings'

const { get } = useSiteSettings()

const enabled = computed(() => get('testimonials.enabled', true))
const eyebrow = computed(() => get('testimonials.eyebrow', 'Reviews'))
const title = computed(() => get('testimonials.title', 'Customer reviews'))
const items = computed(() => get<any[]>('testimonials.items', []).filter(item => item?.text))
</script>
