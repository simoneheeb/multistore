<template>
    <div>
        <div
            class="flex items-center gap-2 rounded-[var(--radius-base)] py-1.5 pe-2 transition-colors hover:bg-surface-muted"
            :style="{ paddingInlineStart: `${depth * 1.25 + 0.5}rem` }">
            <button
                v-if="node.children?.length"
                type="button"
                class="rounded p-0.5 text-muted transition-colors hover:text-primary"
                :aria-expanded="expanded"
                :aria-label="expanded ? 'Close' : 'Expand'"
                @click="expanded = !expanded">
                <UiIcon name="chevron-down" :size="15" :class="['transition-transform', !expanded && '-rotate-90']" />
            </button>

            <!-- Spacer keeps leaf rows aligned with their expandable siblings. -->
            <span v-else class="inline-block w-[1.375rem]" />

            <UiIcon :name="node.children?.length ? 'layers' : 'tag'" :size="15" class="shrink-0 text-muted" />

            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-medium text-heading">{{ node.name }}</p>
                <p class="truncate text-[0.625rem] text-muted">{{ node.name }}</p>
            </div>

            <UiBadge v-if="!node.is_active" tone="neutral">Inactive</UiBadge>

            <span v-if="node.products_length" class="shrink-0 text-[0.625rem] text-muted">
                {{ node.products_length }}
            </span>

            <div class="flex shrink-0 items-center gap-0.5">
                <button
                    type="button"
                    class="rounded p-1.5 text-muted transition-colors hover:bg-surface hover:text-primary"
                    aria-label="Add subcategory"
                    @click="$emit('addChild', node)">
                    <UiIcon name="plus" :size="15" />
                </button>

                <button
                    type="button"
                    class="rounded p-1.5 text-muted transition-colors hover:bg-surface hover:text-primary"
                    aria-label="Edit"
                    @click="$emit('edit', node)">
                    <UiIcon name="edit" :size="15" />
                </button>

                <button
                    type="button"
                    class="rounded p-1.5 text-danger transition-colors hover:bg-danger/10"
                    aria-label="Delete"
                    @click="$emit('remove', node)">
                    <UiIcon name="trash" :size="15" />
                </button>
            </div>
        </div>

        <!-- Recursive: the component renders its own children, so the tree
             works at any depth without the page knowing how deep it goes. -->
        <div v-if="expanded && node.children?.length" class="border-s border-border" :style="{ marginInlineStart: `${depth * 1.25 + 1.1}rem` }">
            <CategoryTreeNodeRow
                v-for="child in node.children"
                :key="child.id ?? child.slug"
                :node="child"
                :depth="depth + 1"
                @edit="$emit('edit', $event)"
                @add-child="$emit('addChild', $event)"
                @remove="$emit('remove', $event)" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import type { CategoryTreeNode } from '~/types/interfaces'

defineOptions({ name: 'CategoryTreeNodeRow' })

const props = withDefaults(defineProps<{
    node: CategoryTreeNode
    depth?: number
}>(), { depth: 0 })

defineEmits<{
    edit: [CategoryTreeNode]
    addChild: [CategoryTreeNode]
    remove: [CategoryTreeNode]
}>()

// Top two levels start open; deeper branches stay collapsed so a large tree
// does not fill the screen on load.
const expanded = ref(props.depth < 1)
</script>
