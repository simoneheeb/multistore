<template>
    <nav aria-label="Breadcrumb" class="w-full">
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-muted">
            <li v-for="(item, index) in items" :key="index" class="flex items-center gap-1.5">
                <NuxtLink
                    v-if="item.to && index < items.length - 1"
                    :to="item.to"
                    class="transition-colors hover:text-primary">
                    {{ item.title }}
                </NuxtLink>

                <span v-else class="font-medium text-heading" aria-current="page">
                    {{ item.title }}
                </span>

                <!-- Points along the trail, in reading order. -->
                <UiIcon
                    v-if="index < items.length - 1"
                    name="chevron-right"
                    :size="13"
                    class="text-border-strong" />
            </li>
        </ol>
    </nav>
</template>

<script setup lang="ts">
import type { BreadcrumbItem } from '~/types/interfaces'

defineProps<{ items: BreadcrumbItem[] }>()
</script>
