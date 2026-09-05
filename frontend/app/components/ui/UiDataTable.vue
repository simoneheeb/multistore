<template>
    <div class="card-surface overflow-hidden">
        <header v-if="title || $slots.toolbar" class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-4 py-3">
            <div class="min-w-0">
                <h2 v-if="title" class="text-sm font-bold text-heading">{{ title }}</h2>
                <p v-if="subtitle" class="text-xs text-muted">{{ subtitle }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <slot name="toolbar" />
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[42rem] border-collapse text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface-muted/60">
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            scope="col"
                            :style="column.width ? { width: column.width } : undefined"
                            :class="[
                                'whitespace-nowrap px-4 py-2.5 text-xs font-medium text-muted',
                                alignments[column.align ?? 'start'],
                            ]">
                            {{ column.title }}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <!-- Loading: skeleton rows rather than a spinner, so the
                         table does not collapse and jump when data lands. -->
                    <tr v-if="loading" v-for="row in skeletonRows" :key="`skeleton-${row}`" class="border-b border-border last:border-0">
                        <td v-for="column in columns" :key="column.key" class="px-4 py-3">
                            <UiSkeleton height="0.85rem" />
                        </td>
                    </tr>

                    <tr v-else-if="!items.length">
                        <td :colspan="columns.length" class="px-4 py-10">
                            <UiEmpty :title="emptyTitle" :description="emptyDescription" />
                        </td>
                    </tr>

                    <tr
                        v-else
                        v-for="(item, index) in items"
                        :key="rowKey(item, index)"
                        class="border-b border-border transition-colors last:border-0 hover:bg-surface-muted/40">
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            :class="['px-4 py-3 align-middle', alignments[column.align ?? 'start']]">
                            <slot :name="`cell-${column.key}`" :item="item" :index="index" :value="(item as any)[column.key]">
                                <span class="text-body">{{ (item as any)[column.key] ?? '—' }}</span>
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <footer v-if="meta && meta.last_page > 1" class="border-t border-border px-4 py-3">
            <UiPagination
                :current-page="meta.current_page"
                :last-page="meta.last_page"
                @change="$emit('page', $event)" />
        </footer>
    </div>
</template>

<script setup lang="ts">
import type { DataTableColumn, PageMetaInterface } from '~/types/interfaces'

const props = withDefaults(defineProps<{
    columns: DataTableColumn[]
    items: Record<string, unknown>[]
    meta?: PageMetaInterface | null
    loading?: boolean
    title?: string
    subtitle?: string
    emptyTitle?: string
    emptyDescription?: string
    /** Field used as the row key; falls back to the array index. */
    itemKey?: string
    skeletonRows?: number
}>(), {
    emptyTitle: 'Nothing to show yet',
    itemKey: 'id',
    skeletonRows: 5,
})

defineEmits<{ page: [number] }>()

const alignments = {
    start: 'text-start',
    center: 'text-center',
    end: 'text-end',
}

const rowKey = (item: Record<string, unknown>, index: number) =>
    String(item[props.itemKey] ?? index)
</script>
