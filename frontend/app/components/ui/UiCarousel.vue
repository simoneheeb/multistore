<template>
    <section
        class="relative isolate overflow-hidden"
        :aria-roledescription="'carousel'"
        @mouseenter="pause"
        @mouseleave="resume">
        <div class="relative h-full w-full">
            <TransitionGroup
                enter-active-class="transition-opacity duration-700 ease-out"
                enter-from-class="opacity-0"
                leave-active-class="absolute inset-0 transition-opacity duration-700 ease-in"
                leave-to-class="opacity-0">
                <div v-for="(_, index) in count" v-show="index === active" :key="index" class="h-full w-full">
                    <slot name="slide" :index="index" :active="index === active" />
                </div>
            </TransitionGroup>
        </div>

        <template v-if="count > 1">
            <!-- Arrows sit at the inline edges, pointing in reading order.
                 They are hidden on narrow screens: there is no room for them
                 beside the slide copy, so they end up sitting on top of it,
                 and a touch device swipes instead. The dots below remain, so
                 the control is never lost. -->
            <button
                type="button"
                class="absolute inset-y-0 start-2 my-auto hidden h-10 w-10 items-center justify-center rounded-full bg-surface/80 text-heading backdrop-blur transition-colors hover:bg-surface sm:flex"
                aria-label="Previous slide"
                @click="prev">
                <UiIcon name="chevron-left" :size="20" />
            </button>

            <button
                type="button"
                class="absolute inset-y-0 end-2 my-auto hidden h-10 w-10 items-center justify-center rounded-full bg-surface/80 text-heading backdrop-blur transition-colors hover:bg-surface sm:flex"
                aria-label="Next slide"
                @click="next">
                <UiIcon name="chevron-right" :size="20" />
            </button>

            <div class="absolute inset-x-0 bottom-4 flex items-center justify-center gap-2">
                <button
                    v-for="(_, index) in count"
                    :key="index"
                    type="button"
                    :aria-label="`Slide ${index + 1}`"
                    :aria-current="index === active"
                    :class="[
                        'h-1.5 rounded-full transition-all duration-300',
                        index === active ? 'w-7 bg-primary' : 'w-1.5 bg-white/60 hover:bg-white',
                    ]"
                    @click="goTo(index)" />
            </div>
        </template>
    </section>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = withDefaults(defineProps<{
    count: number
    autoplay?: boolean
    interval?: number
}>(), {
    autoplay: true,
    interval: 6000,
})

const active = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

const goTo = (index: number) => {
    active.value = (index + props.count) % props.count
}

const next = () => goTo(active.value + 1)
const prev = () => goTo(active.value - 1)

const stop = () => {
    if (timer) {
        clearInterval(timer)
        timer = null
    }
}

const start = () => {
    stop()

    // Respect the user's motion preference: an auto-advancing hero is exactly
    // the kind of movement prefers-reduced-motion is meant to suppress.
    const reduced = import.meta.client
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches

    if (!props.autoplay || props.count < 2 || reduced) return

    timer = setInterval(next, Math.max(2000, props.interval))
}

const pause = () => stop()
const resume = () => start()

onMounted(start)
onBeforeUnmount(stop)

// Slides arrive asynchronously from the settings API, so the timer has to be
// (re)started once the real count is known.
watch(() => [props.count, props.autoplay, props.interval], start)

defineExpose({ next, prev, goTo, active })
</script>
