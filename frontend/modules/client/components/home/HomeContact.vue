<template>
    <UiSection
        :eyebrow="eyebrow"
        :title="title"
        description="Get in touch to place an order or ask us anything.">
        <template #actions>
            <UiButton to="/contact" variant="link" size="sm" trailing-icon="chevron-right">
                Contact page
            </UiButton>
        </template>

        <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <li v-if="address" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                    <UiIcon name="map-pin" :size="19" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-heading">Address</p>
                    <p class="mt-0.5 text-xs leading-6 text-muted">{{ address }}</p>
                </div>
            </li>

            <li v-for="phone in phones" :key="phone" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                    <UiIcon name="phone" :size="19" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-heading">Phone</p>
                    <a :href="`tel:${phone}`" class="mt-0.5 block text-xs text-muted transition-colors hover:text-primary">
                        {{ phone }}
                    </a>
                </div>
            </li>

            <li v-for="mail in emails" :key="mail" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                    <UiIcon name="mail" :size="19" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-heading">Email</p>
                    <a :href="`mailto:${mail}`" class="mt-0.5 block truncate text-xs text-muted transition-colors hover:text-primary">
                        {{ mail }}
                    </a>
                </div>
            </li>

            <li v-if="workingHours" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                    <UiIcon name="clock" :size="19" />
                </span>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-heading">Opening hours</p>
                    <p class="mt-0.5 text-xs leading-6 text-muted">{{ workingHours }}</p>
                </div>
            </li>
        </ul>
    </UiSection>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const { get } = useSiteSettings()

const eyebrow = 'Contact'
const title = computed(() => get('contact_page.title', 'Contact us'))
const address = computed(() => get('contact_page.address', ''))
const workingHours = computed(() => get('contact_page.working_hours', ''))

// Empty slots left behind in the settings editor must not render blank cards.
const phones = computed(() => get<string[]>('contact_page.phones', []).filter(Boolean).slice(0, 2))
const emails = computed(() => get<string[]>('contact_page.emails', []).filter(Boolean).slice(0, 1))
</script>
