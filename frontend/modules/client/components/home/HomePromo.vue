<template>
    <section v-if="enabled" class="py-12 md:py-16">
        <div class="site-container">
            <div class="grid items-center gap-0 overflow-hidden rounded-[var(--radius-base)] border border-border bg-surface md:grid-cols-2">
                <div class="order-2 p-6 md:order-1 md:p-10">
                    <UiBadge v-if="promo.badge" tone="accent">{{ promo.badge }}</UiBadge>

                    <h2 class="mt-1 text-2xl font-bold leading-snug text-heading md:text-3xl">
                        {{ promo.title }}
                    </h2>

                    <p v-if="promo.description" class="mt-3 text-sm leading-8 text-muted">
                        {{ promo.description }}
                    </p>

                    <UiButton
                        v-if="promo.cta_label"
                        :to="promo.cta_url || '/products'"
                        trailing-icon="chevron-right"
                        class="mt-6">
                        {{ promo.cta_label }}
                    </UiButton>
                </div>
                <UiImage
                    :src="promo.image"
                    :alt="promo.title"
                    ratio="video"
                    :rounded="false"
                    class="order-1 h-full w-full md:order-2" />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSiteSettings } from '~~/app/composables/useSiteSettings';

const { get } = useSiteSettings()

const promo = computed(() => get('promo', {} as Record<string, string>))
const enabled = computed(() => get('promo.enabled', true))
</script>
