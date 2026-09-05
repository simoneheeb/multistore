<template>
    <nav v-if="lastPage > 1" class="flex flex-wrap items-center justify-center gap-1.5" aria-label="Pagination">
        <UiButton
            variant="outline"
            size="sm"
            icon="chevron-left"
            :disabled="currentPage <= 1"
            @click="go(currentPage - 1)">
            Previous
        </UiButton>

        <button
            v-for="(page, index) in pages"
            :key="`${page}-${index}`"
            type="button"
            :disabled="page === '…'"
            :aria-current="page === currentPage ? 'page' : undefined"
            :class="[
                'h-8 min-w-8 rounded-[var(--radius-base)] border px-2 text-xs font-medium transition-colors',
                page === currentPage
                    ? 'border-primary bg-primary text-on-primary'
                    : page === '…'
                        ? 'cursor-default border-transparent text-muted'
                        : 'border-border bg-surface text-body hover:border-primary hover:text-primary',
            ]"
            @click="typeof page === 'number' && go(page)">
            {{ page }}
        </button>

        <UiButton
            variant="outline"
            size="sm"
            trailing-icon="chevron-right"
            :disabled="currentPage >= lastPage"
            @click="go(currentPage + 1)">
            Next
        </UiButton>
    </nav>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    currentPage: number
    lastPage: number
}>()

const emit = defineEmits<{ change: [number] }>()

/**
 * Window of page numbers around the current one, with ellipses standing in
 * for the gaps. Keeps the control a fixed width on a catalogue of any size.
 */
const pages = computed<(number | '…')[]>(() => {
    const total = props.lastPage
    const current = props.currentPage

    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)

    const result: (number | '…')[] = [1]

    const start = Math.max(2, current - 1)
    const end = Math.min(total - 1, current + 1)

    if (start > 2) result.push('…')
    for (let page = start; page <= end; page++) result.push(page)
    if (end < total - 1) result.push('…')

    result.push(total)

    return result
})

const go = (page: number) => {
    if (page >= 1 && page <= props.lastPage && page !== props.currentPage) {
        emit('change', page)
    }
}
</script>
