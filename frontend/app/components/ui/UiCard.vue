<template>
    <component :is="to ? NuxtLink : 'div'" :to="to" :class="classes">
        <slot />
    </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { NuxtLink } from '#components'

const props = withDefaults(defineProps<{
    to?: string | Record<string, unknown>
    padded?: boolean
    hoverable?: boolean
}>(), { padded: true })

const classes = computed(() => [
    'card-surface block overflow-hidden',
    props.padded ? 'p-4' : '',
    props.hoverable || props.to
        ? 'transition-colors duration-150 hover:border-border-strong'
        : '',
])
</script>
