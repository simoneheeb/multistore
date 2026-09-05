<template>
    <footer class="mt-auto border-t border-border bg-surface">
        <!-- Newsletter strip, shown only when both the footer and the
             newsletter settings groups allow it. -->
        <div v-if="showNewsletter" class="border-b border-border bg-surface-muted">
            <div class="site-container py-8">
                <HomeNewsletter compact />
            </div>
        </div>

        <div class="site-container grid gap-8 py-12 md:grid-cols-2 lg:grid-cols-4">
            <!-- Brand column -->
            <div class="lg:col-span-1 flex flex-col">
                <NuxtLink to="/" class="mb-4 flex mx-auto">
                    <UiImage :src="logo" :alt="siteName" ratio="none" fit="contain" :rounded="false" class="h-30 w-auto max-w-[15rem]" />
                </NuxtLink>
                <p class="mt-1 text-sm leading-7 text-muted">{{ about.text }}</p>

                <ul v-if="socials.length" class="mt-4 flex items-center gap-2">
                    <li v-for="social in socials" :key="social.platform">
                        <a :href="social.url" target="_blank" rel="noopener noreferrer" :aria-label="social.label || social.platform"
                            class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-muted transition-colors hover:border-primary hover:text-primary">

                            <UiIcon :name="social.platform" :size="17" />
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Link columns -->
            <nav v-for="(column, index) in columns" :key="index" aria-label="Footer links">
                <p class="mb-3 mt-0.5 text-sm font-bold text-heading">{{ column.title }}</p>

                <ul class="flex flex-col gap-1.5">
                    <li v-for="link in column.links" :key="link.url ?? link.label">
                        <AppLink :to="link.url" class="flex flex-col text-[0.8125rem] text-muted transition-colors hover:text-primary">
                            <span>{{ link.label }}</span>
                        </AppLink>
                    </li>
                </ul>
            </nav>

            <!-- Contact column -->
            <div>
                <p class="mb-3 mt-0.5 text-sm font-bold text-heading">{{ contact.title }}</p>

                <ul class="flex flex-col gap-2.5 text-[0.8125rem] text-muted">
                    <li v-if="contact.address" class="flex items-start gap-2">
                        <UiIcon name="map-pin" :size="16" class="mt-1 shrink-0" />
                        <span class="leading-6">{{ contact.address }}</span>
                    </li>

                    <li v-if="contact.phone" class="flex items-center gap-2">
                        <UiIcon name="phone" :size="16" class="shrink-0" />
                        <a :href="`tel:${contact.phone}`" class="transition-colors hover:text-primary">
                            {{ contact.phone }}
                        </a>
                    </li>

                    <li v-if="contact.email" class="flex items-center gap-2">
                        <UiIcon name="mail" :size="16" class="shrink-0" />
                        <a :href="`mailto:${contact.email}`" class="transition-colors hover:text-primary">
                            {{ contact.email }}
                        </a>
                    </li>

                    <li v-if="contact.working_hours" class="flex items-center gap-2">
                        <UiIcon name="clock" :size="16" class="shrink-0" />
                        <span>{{ contact.working_hours }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-border">
            <div class="site-container flex flex-wrap items-center justify-between gap-3 py-4 text-xs text-muted">
                <p>{{ copyright }}</p>

                <p class="text-[0.6875rem] uppercase tracking-wider">
                    {{ siteName }}
                </p>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useSiteSettings } from '~~/app/composables/useSiteSettings'
import { useTheme } from '~~/app/composables/useTheme'

const { get } = useSiteSettings()
const { isDark } = useTheme()

const about = computed(() => get('footer.about', { title: '', text: '' }))
const columns = computed(() => get<any[]>('footer.columns', []))
const contact = computed(() => get('footer.contact', {} as Record<string, string>))
const copyright = computed(() => get('footer.copyright', ''))
const siteName = computed(() => get('identity.site_name', ''))

const showNewsletter = computed(
    () => get('footer.show_newsletter', true) && get('newsletter.enabled', true),
)

// The footer logo falls back to the header one when no dedicated footer mark
// has been uploaded.
const logo = computed(() =>
    isDark.value
        ? get('identity.logo_dark', get('identity.logo', '/assets/images/logo-header.png'))
        : get('identity.logo', '/assets/images/logo-header.png'),
)

// Only social entries with a URL are rendered - an empty slot in the settings
// should not produce a dead icon.
const socials = computed(() =>
    get<any[]>('social.items', []).filter(item => item?.url),
)
</script>
