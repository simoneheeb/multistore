<template>
    <component :is="tag" :class="wrapperClass">
        <!--
            The house heading style: a small uppercase label above the title.
            The label names the section (Categories, Reviews, About) rather
            than translating the title, so it carries information instead of
            repeating what is already below it.
        -->
        <span v-if="eyebrow" class="eyebrow block">{{ eyebrow }}</span>

        <span :class="titleClass">
            <slot>{{ title }}</slot>
        </span>

        <span v-if="description" class="mt-2 block text-sm font-normal leading-6 text-muted">
            {{ description }}
        </span>
    </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
    /** Small uppercase label rendered above the title. */
    eyebrow?: string
    /** The heading itself. Ignored when the default slot is used. */
    title?: string
    description?: string
    tag?: string
    size?: 'sm' | 'md' | 'lg'
    align?: 'start' | 'center'
    /** Draws the short accent rule beneath the block. */
    rule?: boolean
}>(), {
    tag: 'h2',
    size: 'md',
    align: 'start',
    rule: false,
})

const sizes = {
    sm: 'text-base',
    md: 'text-xl md:text-2xl',
    lg: 'text-2xl md:text-3xl',
}

const wrapperClass = computed(() => [
    'block',
    props.align === 'center' ? 'text-center' : 'text-start',
    props.rule ? 'rule-under' : '',
    // A centred block needs its rule centred too; the default sits at the
    // inline start edge.
    props.rule && props.align === 'center'
        ? '[&::after]:inset-inline-auto [&::after]:left-1/2 [&::after]:-translate-x-1/2'
        : '',
])

const titleClass = computed(() => ['mt-1 block font-bold text-heading', sizes[props.size]])
</script>
