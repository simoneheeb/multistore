<template>
    <div class="relative">
        <div
            ref="track"
            class="flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-smooth pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <slot />
        </div>

        <template v-if="showArrows">
            <button
                type="button"
                class="absolute -start-3 top-1/2 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-border bg-surface text-heading shadow-sm transition-colors hover:border-primary hover:text-primary md:flex"
                aria-label="Previous"
                @click="scrollBy(-1)">
                <UiIcon name="chevron-left" :size="18" />
            </button>

            <button
                type="button"
                class="absolute -end-3 top-1/2 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-border bg-surface text-heading shadow-sm transition-colors hover:border-primary hover:text-primary md:flex"
                aria-label="Next"
                @click="scrollBy(1)">
                <UiIcon name="chevron-right" :size="18" />
            </button>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

/**
 * Horizontal scroll rail used by the brand strip and the product carousels.
 *
 * Built on native overflow scrolling rather than a carousel library: it works
 * without JavaScript, follows the page direction automatically, and keeps
 * touch momentum on mobile.
 */
withDefaults(defineProps<{ showArrows?: boolean }>(), { showArrows: true })

const track = ref<HTMLElement | null>(null)

const scrollBy = (direction: number) => {
    const element = track.value
    if (!element) return

    // In RTL the scroll axis is inverted, so the sign is flipped to keep the
    // arrows pointing where the content actually moves.
    const isRtl = getComputedStyle(element).direction === 'rtl'
    const amount = element.clientWidth * 0.8 * direction * (isRtl ? -1 : 1)

    element.scrollBy({ left: amount, behavior: 'smooth' })
}
</script>
