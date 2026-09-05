<template>
    <UiSection
        v-if="enabled && items.length"
        tone="surface"
        :eyebrow="eyebrow"
        :title="title">
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <li v-for="(item, index) in items" :key="index">
                <AppLink
                    :to="item.url || '#'"
                    class="group flex h-full flex-col overflow-hidden rounded-[var(--radius-base)] border border-border bg-page transition-colors hover:border-primary">
                    <UiImage
                        :src="item.image"
                        :alt="item.title"
                        ratio="video"
                        :rounded="false"
                        class="w-full" />

                    <div class="flex flex-1 flex-col p-4">
                        <p v-if="item.date" class="text-[0.625rem] uppercase tracking-wider text-muted">
                            {{ item.date }}
                        </p>

                        <h3 class="mt-1 line-clamp-2 text-sm font-bold leading-7 text-heading transition-colors group-hover:text-primary">
                            {{ item.title }}
                        </h3>

                        <p v-if="item.excerpt" class="mt-2 line-clamp-3 text-xs leading-6 text-muted">
                            {{ item.excerpt }}
                        </p>

                        <span class="mt-auto pt-3 text-[0.6875rem] font-medium text-primary">
                            Read more
                        </span>
                    </div>
                </AppLink>
            </li>
        </ul>
    </UiSection>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSiteSettings } from '~~/app/composables/useSiteSettings'

const { get } = useSiteSettings()

const enabled = computed(() => get('articles.enabled', true))
const eyebrow = computed(() => get('articles.eyebrow', 'Articles'))
const title = computed(() => get('articles.title', 'Latest articles'))
const items = computed(() => get<any[]>('articles.items', []).filter(item => item?.title))
</script>
