<template>
    <div class="w-full">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-sm font-medium text-heading">{{ label }}</p>
                <p v-if="hint" class="text-xs text-muted">{{ hint }}</p>
            </div>

            <UiButton variant="outline" size="sm" icon="plus" :disabled="atMax" @click="add">
                {{ addLabel }}
            </UiButton>
        </div>

        <div v-if="!modelValue.length" class="rounded-[var(--radius-base)] border border-dashed border-border px-4 py-6 text-center text-xs text-muted">
            {{ emptyLabel }}
        </div>

        <ul v-else class="flex flex-col gap-3">
            <li
                v-for="(item, index) in modelValue"
                :key="index"
                class="rounded-[var(--radius-base)] border border-border bg-surface p-3">
                <div class="mb-2.5 flex items-center justify-between gap-2">
                    <span class="text-[0.6875rem] font-medium uppercase tracking-wider text-muted">
                        #{{ index + 1 }}
                    </span>

                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            class="rounded p-1 text-muted transition-colors hover:bg-surface-muted hover:text-heading disabled:opacity-40"
                            aria-label="Move up"
                            :disabled="index === 0"
                            @click="move(index, -1)">
                            <UiIcon name="chevron-up" :size="15" />
                        </button>

                        <button
                            type="button"
                            class="rounded p-1 text-muted transition-colors hover:bg-surface-muted hover:text-heading disabled:opacity-40"
                            aria-label="Move down"
                            :disabled="index === modelValue.length - 1"
                            @click="move(index, 1)">
                            <UiIcon name="chevron-down" :size="15" />
                        </button>

                        <button
                            type="button"
                            class="rounded p-1 text-danger transition-colors hover:bg-danger/10"
                            aria-label="Remove"
                            @click="remove(index)">
                            <UiIcon name="trash" :size="15" />
                        </button>
                    </div>
                </div>

                <slot :item="item" :index="index" :update="(patch: Record<string, unknown>) => update(index, patch)" />
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

/**
 * Generic list editor behind every repeatable settings block: menu links,
 * footer columns, hero slides, features, testimonials, product attributes.
 *
 * It owns add/remove/reorder; the caller renders the fields of one row
 * through the default slot and writes back with the `update` callback.
 */
const props = withDefaults(defineProps<{
    modelValue: Record<string, any>[]
    label: string
    hint?: string
    addLabel?: string
    emptyLabel?: string
    /** Shape of a newly added row. */
    factory: () => Record<string, any>
    max?: number
}>(), {
    addLabel: 'Add',
    emptyLabel: 'Nothing added yet.',
    max: 50,
})

const emit = defineEmits<{ 'update:modelValue': [Record<string, any>[]] }>()

const atMax = computed(() => props.modelValue.length >= props.max)

// Every mutation replaces the array rather than mutating in place, so the
// parent's watchers and v-model see a genuinely new value.
const commit = (next: Record<string, any>[]) => emit('update:modelValue', next)

const add = () => {
    if (atMax.value) return
    commit([...props.modelValue, props.factory()])
}

const remove = (index: number) => {
    commit(props.modelValue.filter((_, i) => i !== index))
}

const move = (index: number, direction: number) => {
    const target = index + direction
    if (target < 0 || target >= props.modelValue.length) return

    const next = [...props.modelValue]
    const [row] = next.splice(index, 1)
    next.splice(target, 0, row!)
    commit(next)
}

const update = (index: number, patch: Record<string, unknown>) => {
    commit(props.modelValue.map((row, i) => (i === index ? { ...row, ...patch } : row)))
}
</script>
