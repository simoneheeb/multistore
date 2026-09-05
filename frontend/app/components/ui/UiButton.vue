<template>
    <component
        :is="tag"
        v-bind="linkProps"
        :type="tag === 'button' ? type : undefined"
        :disabled="isDisabled"
        :aria-busy="loading || undefined"
        :class="classes">
        <UiSpinner v-if="loading" :size="16" />
        <UiIcon v-else-if="icon" :name="icon" :size="iconSize" />
        <span v-if="$slots.default" class="truncate"><slot /></span>
        <UiIcon v-if="trailingIcon && !loading" :name="trailingIcon" :size="iconSize" />
    </component>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { NuxtLink } from '#components'

type Variant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'link'
type Size = 'sm' | 'md' | 'lg'

const props = withDefaults(defineProps<{
    variant?: Variant
    size?: Size
    type?: 'button' | 'submit' | 'reset'
    to?: string | Record<string, unknown>
    href?: string
    disabled?: boolean
    loading?: boolean
    block?: boolean
    icon?: string
    trailingIcon?: string
}>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
})

/** Renders as a router link, an anchor, or a button depending on the props. */
const tag = computed(() => (props.to ? NuxtLink : props.href ? 'a' : 'button'))

const linkProps = computed(() => {
    if (props.to) return { to: props.to }
    if (props.href) return { href: props.href, rel: 'noopener', target: '_blank' }
    return {}
})

const isDisabled = computed(() => props.disabled || props.loading || undefined)

const iconSize = computed(() => (props.size === 'sm' ? 15 : props.size === 'lg' ? 20 : 17))

const variants: Record<Variant, string> = {
    primary: 'bg-primary text-on-primary border border-primary hover:opacity-90',
    secondary: 'bg-surface-muted text-heading border border-border hover:border-border-strong',
    outline: 'bg-transparent text-heading border border-border-strong hover:border-primary hover:text-primary',
    ghost: 'bg-transparent text-body border border-transparent hover:bg-surface-muted',
    danger: 'bg-danger text-white border border-danger hover:opacity-90',
    link: 'bg-transparent text-primary border border-transparent underline-offset-4 hover:underline p-0',
}

const sizes: Record<Size, string> = {
    sm: 'h-8 px-3 text-[0.8125rem] gap-1.5',
    md: 'h-10 px-4 text-sm gap-2',
    lg: 'h-12 px-6 text-base gap-2',
}

const classes = computed(() => [
    'inline-flex items-center justify-center rounded-[var(--radius-base)] font-medium',
    'transition-colors duration-150 select-none',
    'disabled:opacity-50 disabled:cursor-not-allowed',
    variants[props.variant],
    props.variant === 'link' ? '' : sizes[props.size],
    props.block ? 'w-full' : '',
])
</script>
