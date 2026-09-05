<template>
    <div class="relative" @mouseenter="open = true" @mouseleave="open = false">
        <button type="button"
            class="flex items-center gap-1 rounded-[var(--radius-base)] px-3 py-2 text-sm text-body transition-colors hover:bg-surface-muted hover:text-primary" :aria-expanded="open" @click="open = !open">
            <ul>
                <li class="font-bold">
                    {{ label }}
                </li>
                <li class="text-[10px] text-left">
                    {{ labelEn }}
                </li>
            </ul>
            <UiIcon name="chevron-down" :size="15" :class="['transition-transform', open && 'rotate-180']" />
        </button>

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            leave-active-class="transition duration-100 ease-in"
            leave-to-class="opacity-0">
            <div
                v-if="open"
                class="absolute top-full z-50 pt-2"
                :style="panelPosition">
                <div class="w-[min(56rem,90vw)] rounded-[var(--radius-base)] border border-border bg-surface p-5 shadow-xl">
                    <div class="grid gap-x-6 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="(column, index) in columns" :key="index" class="min-w-0">
                            <NuxtLink
                                v-if="column.title"
                                :to="column.to"
                                class="mb-2 block border-b border-border pb-1.5">
                                <span class="text-sm font-bold text-heading transition-colors hover:text-primary">
                                    {{ column.title }}
                                </span>
                            </NuxtLink>

                            <ul class="flex flex-col gap-0.5">
                                <li v-for="link in column.links" :key="link.to">
                                    <NuxtLink
                                        :to="link.to"
                                        class="group flex items-baseline justify-between gap-2 rounded px-2 py-1 transition-colors hover:bg-surface-muted">
                                        <span class="min-w-0">
                                            <span class="block truncate text-[0.8125rem] text-body transition-colors group-hover:text-primary">
                                                {{ link.title }}
                                            </span>
                                        </span>

                                        <span v-if="link.count" class="shrink-0 text-[0.625rem] text-muted">
                                            {{ link.count }}
                                        </span>
                                    </NuxtLink>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div v-if="ctaTo" class="mt-4 border-t border-border pt-3">
                        <UiButton :to="ctaTo" variant="link" size="sm" trailing-icon="chevron-right">
                            {{ ctaLabel }}
                        </UiButton>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

export interface MegaMenuLink {
    title: string
    to: string
    count?: number
}

export interface MegaMenuColumn {
    title: string
    to?: string
    links: MegaMenuLink[]
}

defineProps<{
    label: string
    labelEn?: string
    columns: MegaMenuColumn[]
    ctaLabel?: string
    ctaTo?: string
}>()

const open = ref(false)

// The panel is far wider than its trigger, so it is anchored to the inline
// start edge and allowed to run toward the centre of the viewport. Using
// logical properties keeps that correct in an RTL layout.
const panelPosition = { insetInlineStart: '50%', transform: 'translateX(50%)' }
</script>
