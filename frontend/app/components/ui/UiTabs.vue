<template>
    <div class="w-full">
        <div class="flex gap-1 overflow-x-auto border-b border-border [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="tablist">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                role="tab"
                :aria-selected="tab.value === modelValue"
                :class="[
                    'relative flex shrink-0 items-center gap-1.5 whitespace-nowrap px-3.5 py-2.5 text-sm transition-colors',
                    tab.value === modelValue
                        ? 'font-medium text-primary'
                        : 'text-muted hover:text-heading',
                ]"
                @click="$emit('update:modelValue', tab.value)">
                <UiIcon v-if="tab.icon" :name="tab.icon" :size="16" />
                {{ tab.label }}

                <span
                    v-if="tab.value === modelValue"
                    class="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-primary" />
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
export interface TabItem {
    value: string
    label: string
    icon?: string
}

defineProps<{
    tabs: TabItem[]
    modelValue: string
}>()

defineEmits<{ 'update:modelValue': [string] }>()
</script>
