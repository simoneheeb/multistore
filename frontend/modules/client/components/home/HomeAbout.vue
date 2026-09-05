<template>
    <UiSection tone="surface">
        <div class="grid items-center gap-8 lg:grid-cols-2">
            <div>
                <UiTitle eyebrow="About us" :title="about.title" :description="about.subtitle" size="lg" rule />

                <p v-if="excerpt" class="mt-5 text-sm leading-8 text-muted">
                    {{ excerpt }}
                </p>

                <dl v-if="stats.length" class="mt-7 grid grid-cols-3 gap-4">
                    <div v-for="(stat, index) in stats" :key="index" class="border-s-2 border-primary ps-3">
                        <dt class="text-xl font-bold text-heading md:text-2xl">{{ stat.value }}</dt>
                        <dd class="mt-0.5 text-[0.6875rem] leading-5 text-muted">
                            {{ stat.label }}
                        </dd>
                    </div>
                </dl>

                <UiButton to="/about" variant="outline" trailing-icon="chevron-right" class="mt-7">
                    Learn more about us
                </UiButton>
            </div>

            <UiImage :src="about.cover" :alt="about.title" ratio="video" class="w-full" />
        </div>
    </UiSection>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { truncate } from '~/utils/format'
import { useSiteSettings } from '~~/app/composables/useSiteSettings'

const { get } = useSiteSettings()

const about = computed(() => get('about_page', {} as Record<string, string>))
const stats = computed(() => get<any[]>('about_page.stats', []).slice(0, 3))

// The landing page shows a teaser; the full body lives on /about.
const excerpt = computed(() => truncate(get('about_page.body', ''), 320))
</script>
