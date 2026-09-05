<template>
    <UiSection v-if="enabled && items.length" tone="surface" compact>
        <ul class="grid gap-x-6 gap-y-7 sm:grid-cols-2 lg:grid-cols-4">
            <li v-for="(item, index) in items" :key="index" class="flex items-start gap-3 border border-border p-2 h-25 rounded">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                    <UiIcon :name="item.icon || 'star'" :size="21" />
                </span>

                <div class="min-w-0">
                    <p class="text-sm font-bold text-heading">{{ item.title }}</p>
                    <p v-if="item.description" class="mt-1 text-xs leading-6 text-muted">
                        {{ item.description }}
                    </p>
                </div>
            </li>
        </ul>
    </UiSection>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSiteSettings } from '~~/app/composables/useSiteSettings';

const { get } = useSiteSettings()

const enabled = computed(() => get('features.enabled', true))
const items = computed(() => get<any[]>('features.items', []))
</script>
