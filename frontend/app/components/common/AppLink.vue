<template>
    <a v-if="external" :href="to ?? undefined" :target="target" rel="noopener noreferrer" :class="$attrs.class">
        <slot />
    </a>

    <NuxtLink v-else :to="to || '/'" :class="$attrs.class">
        <slot />
    </NuxtLink>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { isExternalLink } from '~/utils/format'

/**
 * Renders a settings-supplied link correctly whether the admin typed a path
 * or a full URL, so every navigation link in the site can be driven from the
 * admin panel without the template caring which kind it got.
 */
defineOptions({ inheritAttrs: false })

const props = defineProps<{ to?: string | null }>()

const external = computed(() => isExternalLink(props.to))

// mailto:/tel: must open in the same tab; only real web links get a new one.
const target = computed(() =>
    props.to?.startsWith('mailto:') || props.to?.startsWith('tel:') ? undefined : '_blank',
)
</script>
