<template>
    <UiCarousel
        v-if="slides.length"
        :count="slides.length"
        :autoplay="autoplay"
        :interval="interval"
        class="h-[60vh] min-h-[22rem] w-full md:h-[70vh]">
        <template #slide="{ index }">
            <!-- Dark base under the photo: the copy is always white, so it
                 must stay readable even when the image is missing or has not
                 loaded yet. -->
            <article class="relative h-full w-full bg-neutral-900">
                <UiImage
                    :src="slides[index]?.image"
                    :alt="slides[index]?.title ?? ''"
                    ratio="none"
                    :rounded="false"
                    :eager="index === 0"
                    class="absolute inset-0 h-full w-full" />

                <!-- Scrim. Without it the copy is unreadable over a light
                     photograph, and the contrast ratio fails outright. -->
                <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/55 to-black/35" />

                <div class="site-container relative flex h-full items-center">
                    <div class="max-w-xl text-white">
                        <p v-if="slides[index]?.eyebrow" class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">
                            {{ slides[index]?.eyebrow }}
                        </p>

                        <h1 class="mt-2 text-3xl font-bold leading-snug text-white md:text-5xl">
                            {{ slides[index]?.title }}
                        </h1>

                        <p v-if="slides[index]?.description" class="mt-3 max-w-lg text-sm leading-8 text-white/75">
                            {{ slides[index]?.description }}
                        </p>

                        <UiButton
                            v-if="slides[index]?.cta_label"
                            :to="slides[index]?.cta_url || '/products'"
                            size="lg"
                            trailing-icon="chevron-right"
                            class="mt-6">
                            {{ slides[index]?.cta_label }}
                        </UiButton>
                    </div>
                </div>
            </article>
        </template>
    </UiCarousel>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HeroSlide } from '~/types/settings'
import { useSiteSettings } from '~~/app/composables/useSiteSettings'

/**
 * Landing hero. Slides, autoplay and timing all come from the "hero" settings
 * group, so an admin controls the whole thing without a deploy.
 */
const { get } = useSiteSettings()

const slides = computed(() => get<HeroSlide[]>('hero.slides', []).filter(slide => slide?.title || slide?.image))
const autoplay = computed(() => get('hero.autoplay', true))
const interval = computed(() => get('hero.interval', 6000))
</script>
