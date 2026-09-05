<template>
    <div>
        <!-- Cover -->
        <section class="relative">
            <UiImage :src="about.cover" :alt="about.title" ratio="wide" :rounded="false" eager class="h-56 w-full md:h-72" />

            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/25" />

            <div class="site-container absolute inset-0 flex flex-col justify-end pb-8">
                <h1 class="mt-1 text-3xl font-bold text-white md:text-4xl">{{ about.title }}</h1>
                <p v-if="about.subtitle" class="mt-2 text-sm text-white/85">{{ about.subtitle }}</p>
            </div>
        </section>

        <div class="site-container py-8 md:py-12">
            <UiBreadcrumbs :items="crumbs" class="mb-6" />

            <div class="grid gap-10 lg:grid-cols-3">
                <article class="prose-content lg:col-span-2">
                    <!-- Body copy is plain text from the settings editor and is
                         rendered as text, never as HTML, so an edit can never
                         inject markup into the page. -->
                    <p v-for="(paragraph, index) in paragraphs" :key="index" class="text-sm leading-8 text-body">
                        {{ paragraph }}
                    </p>

                    <section v-for="(block, index) in sections" :key="`section-${index}`" class="mt-10">
                        <UiTitle :title="block.title" size="sm" rule />

                        <div class="mt-4 grid gap-5" :class="block.image ? 'md:grid-cols-2' : ''">
                            <p class="text-sm leading-8 text-muted">{{ block.text }}</p>

                            <UiImage
                                v-if="block.image"
                                :src="block.image"
                                :alt="block.title"
                                ratio="video"
                                class="w-full" />
                        </div>
                    </section>
                </article>

                <aside class="flex flex-col gap-6">
                    <dl v-if="stats.length" class="grid grid-cols-1 gap-3">
                        <div
                            v-for="(stat, index) in stats"
                            :key="index"
                            class="rounded-[var(--radius-base)] border border-border bg-surface p-4">
                            <dt class="text-2xl font-bold text-primary">{{ stat.value }}</dt>
                            <dd class="mt-1 text-xs text-muted">
                                {{ stat.label }}
                            </dd>
                        </div>
                    </dl>

                    <div class="rounded-[var(--radius-base)] border border-border bg-surface p-5">
                        <p class="text-sm font-bold text-heading">Have a question?</p>
                        <p class="mt-1 text-xs leading-6 text-muted">
                            Our team is ready to help.
                        </p>

                        <UiButton to="/contact" block class="mt-4">Contact us</UiButton>
                    </div>
                </aside>
            </div>
        </div>

        <HomeFeatures />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRuntimeConfig } from 'nuxt/app'
import { buildBreadcrumbJsonLd } from '~/composables/useSeoDefaults'

const { get } = useSiteSettings()
const config = useRuntimeConfig()

const about = computed(() => get('about_page', {} as Record<string, any>))
const stats = computed(() => get<any[]>('about_page.stats', []))
const sections = computed(() => get<any[]>('about_page.sections', []).filter(item => item?.title || item?.text))

// Blank lines in the editor become paragraph breaks on the page.
const paragraphs = computed(() =>
    String(get('about_page.body', ''))
        .split(/\n{2,}/)
        .map(part => part.trim())
        .filter(Boolean),
)

const crumbs = [
    { title: 'Home', to: '/' },
    { title: 'About us', to: '/about' },
]

usePageSeo({
    title: () => get('about_page.meta_title', '') || get('about_page.title', 'About us'),
    description: () => get('about_page.meta_description', '') || get('about_page.subtitle', ''),
    image: () => get('about_page.cover', ''),
    path: '/about',
    jsonLd: () => [buildBreadcrumbJsonLd(crumbs, String(config.public.siteUrl ?? ''))],
})
</script>
