<template>
    <div :class="wrapperClass" class="bg-transparent border-0">
        <img
            v-if="resolvedSrc && !failed"
            ref="imgRef"
            :src="resolvedSrc"
            :alt="alt"
            :width="width"
            :height="height"
            :loading="eager ? 'eager' : 'lazy'"
            :fetchpriority="eager ? 'high' : 'auto'"
            decoding="async"
            :class="imageClass"
            @error="failed = true" />

        <!-- Placeholder. Rendering a neutral box instead of a broken image keeps the grid aligned when a product has no photo yet. -->
        <div v-else class="flex h-full w-full items-center justify-center bg-surface-muted text-muted">
            <UiIcon name="image" :size="placeholderIconSize" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'

const props = withDefaults(defineProps<{
    src?: string | null
    alt?: string
    width?: number | string
    height?: number | string
    /** contain suits logos, cover suits photography. */
    fit?: 'cover' | 'contain'
    ratio?: 'square' | 'video' | 'portrait' | 'wide' | 'none'
    rounded?: boolean
    /** Skips lazy loading for above-the-fold images such as the hero. */
    eager?: boolean
    placeholderIconSize?: number
}>(), {
    alt: '',
    fit: 'cover',
    ratio: 'square',
    rounded: true,
    placeholderIconSize: 28,
})

const failed = ref(false)
const imgRef = ref<HTMLImageElement | null>(null)

// A new src deserves a fresh attempt, otherwise one broken URL would keep the
// placeholder pinned for the life of the component.
watch(() => props.src, () => { failed.value = false })

/**
 * A server-rendered image can finish loading - or fail - before Vue hydrates
 * and attaches the error handler, in which case @error never fires and a
 * broken image icon stays on the page. Re-check the element once on mount.
 */
onMounted(() => {
    const el = imgRef.value
    if (el && el.complete && el.naturalWidth === 0) {
        failed.value = true
    }
})

const resolvedSrc = computed(() => (props.src ? String(props.src) : ''))

const ratios = {
    square: 'aspect-square',
    video: 'aspect-video',
    portrait: 'aspect-[3/4]',
    wide: 'aspect-[21/9]',
    none: '',
}

/**
 * No `relative` here on purpose. Tailwind emits `.relative` after
 * `.absolute`, so hardcoding it would silently beat an `absolute` class
 * passed in by a caller - which is exactly what a full-bleed hero image
 * needs. Nothing inside this component is absolutely positioned, so the
 * wrapper does not need a positioning context of its own.
 */
const wrapperClass = computed(() => [
    'overflow-hidden bg-surface-muted',
    ratios[props.ratio],
    props.rounded ? 'rounded-[var(--radius-base)]' : '',
])

const imageClass = computed(() => [
    'max-h-full w-full',
    props.fit === 'contain' ? 'object-contain' : 'object-cover',
])
</script>

