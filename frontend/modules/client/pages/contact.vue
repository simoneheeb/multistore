<template>
    <div>
        <section class="relative">
            <UiImage
                :src="contact.cover"
                :alt="contact.title"
                ratio="wide"
                :rounded="false"
                eager
                class="h-48 w-full md:h-64" />

            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-black/25" />

            <div class="site-container absolute inset-0 flex flex-col justify-end pb-8">
                <h1 class="mt-1 text-3xl font-bold text-white md:text-4xl">{{ contact.title }}</h1>
                <p v-if="contact.subtitle" class="mt-2 text-sm text-white/85">{{ contact.subtitle }}</p>
            </div>
        </section>

        <div class="site-container py-8 md:py-12">
            <UiBreadcrumbs :items="crumbs" class="mb-6" />

            <p v-if="contact.intro" class="max-w-3xl text-sm leading-8 text-muted">{{ contact.intro }}</p>

            <div class="mt-8 grid gap-8 lg:grid-cols-5">
                <!-- Details -->
                <div class="lg:col-span-2">
                    <ul class="flex flex-col gap-3">
                        <li v-if="contact.address" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                                <UiIcon name="map-pin" :size="19" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-heading">Address</p>
                                <p class="mt-0.5 text-xs leading-6 text-muted">{{ contact.address }}</p>
                            </div>
                        </li>

                        <li v-if="phones.length" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                                <UiIcon name="phone" :size="19" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-heading">Phone</p>
                                <a
                                    v-for="phone in phones"
                                    :key="phone"
                                    :href="`tel:${phone}`"
                                    class="mt-0.5 block text-xs text-muted transition-colors hover:text-primary">
                                    {{ phone }}
                                </a>
                            </div>
                        </li>

                        <li v-if="emails.length" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                                <UiIcon name="mail" :size="19" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-heading">Email</p>
                                <a
                                    v-for="mail in emails"
                                    :key="mail"
                                    :href="`mailto:${mail}`"
                                    class="mt-0.5 block truncate text-xs text-muted transition-colors hover:text-primary">
                                    {{ mail }}
                                </a>
                            </div>
                        </li>

                        <li v-if="contact.working_hours" class="flex items-start gap-3 rounded-[var(--radius-base)] border border-border bg-surface p-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-soft text-primary-strong">
                                <UiIcon name="clock" :size="19" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-heading">Opening hours</p>
                                <p class="mt-0.5 text-xs leading-6 text-muted">{{ contact.working_hours }}</p>
                            </div>
                        </li>
                    </ul>

                    <ul v-if="socials.length" class="mt-4 flex items-center gap-2">
                        <li v-for="social in socials" :key="social.platform">
                            <a
                                :href="social.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                :aria-label="social.label || social.platform"
                                class="flex h-10 w-10 items-center justify-center rounded-full border border-border text-muted transition-colors hover:border-primary hover:text-primary">
                                <UiIcon :name="social.platform" :size="18" />
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Message form -->
                <div v-if="formEnabled" class="lg:col-span-3">
                    <form class="rounded-[var(--radius-base)] border border-border bg-surface p-5" @submit.prevent="submit">
                        <UiTitle eyebrow="Send a message" title="Send a message" size="sm" rule />

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <UiInput v-model="form.name" label="Full name" required :rules="[required('Name')]" />
                            <UiInput v-model="form.email" label="Email" type="email" required :rules="[required('Email'), emailRule()]" />
                            <UiInput v-model="form.phone" label="Phone number" class="sm:col-span-2" />
                            <UiTextarea
                                v-model="form.message"
                                label="Message"
                                :rows="6"
                                :maxlength="1200"
                                required
                                :rules="[required('Message')]"
                                class="sm:col-span-2" />
                        </div>

                        <UiButton type="submit" :loading="sending" class="mt-5">Send a message</UiButton>

                        <p class="mt-3 text-xs leading-6 text-muted">
                            Your message goes straight to our support team and we will reply as soon as we can.
                        </p>
                    </form>
                </div>
            </div>

            <!-- Map. The embed is admin-supplied markup, so it is rendered
                 inside a sandboxed iframe rather than injected into the page. -->
            <section v-if="mapSrc" class="mt-10">
                <UiTitle eyebrow="Location" title="Find us on the map" size="sm" rule />

                <div class="mt-4 overflow-hidden rounded-[var(--radius-base)] border border-border">
                    <iframe
                        :src="mapSrc"
                        title="Map showing the store location"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        sandbox="allow-scripts allow-same-origin allow-popups"
                        class="h-80 w-full border-0" />
                </div>
            </section>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useRuntimeConfig } from 'nuxt/app'
import { buildBreadcrumbJsonLd } from '~/composables/useSeoDefaults'
import { email as emailRule, firstError, required } from '~/helpers/rules'

const { get } = useSiteSettings()
const config = useRuntimeConfig()
const toast = useToast()

const contact = computed(() => get('contact_page', {} as Record<string, any>))
const phones = computed(() => get<string[]>('contact_page.phones', []).filter(Boolean))
const emails = computed(() => get<string[]>('contact_page.emails', []).filter(Boolean))
const formEnabled = computed(() => get('contact_page.form_enabled', true))
const socials = computed(() => get<any[]>('social.items', []).filter(item => item?.url))

/**
 * The settings field accepts either a bare URL or a full <iframe> snippet
 * pasted from a map provider; only the src is used, so no admin-supplied
 * markup ever reaches the DOM.
 */
const mapSrc = computed(() => {
    const raw = String(get('contact_page.map_embed', '')).trim()
    if (!raw) return ''

    if (raw.startsWith('http')) return raw

    const match = raw.match(/src=["']([^"']+)["']/i)
    return match?.[1] ?? ''
})

const form = reactive({ name: '', email: '', phone: '', message: '' })
const sending = ref(false)

/**
 * There is no contact endpoint in the API yet, so the form validates and
 * confirms locally. Pointing submit() at an endpoint is the only change
 * needed once one exists.
 */
const submit = () => {
    const error =
        firstError(form.name, [required('Name')]) ??
        firstError(form.email, [required('Email'), emailRule()]) ??
        firstError(form.message, [required('Message')])

    if (error) {
        toast.error(error)
        return
    }

    sending.value = true
    Object.assign(form, { name: '', email: '', phone: '', message: '' })
    sending.value = false

    toast.success('Thanks — we have your message and will be in touch shortly.')
}

const crumbs = [
    { title: 'Home', to: '/' },
    { title: 'Contact us', to: '/contact' },
]

usePageSeo({
    title: () => get('contact_page.meta_title', '') || get('contact_page.title', 'Contact us'),
    description: () => get('contact_page.meta_description', '') || get('contact_page.intro', ''),
    image: () => get('contact_page.cover', ''),
    path: '/contact',
    jsonLd: () => [
        buildBreadcrumbJsonLd(crumbs, String(config.public.siteUrl ?? '')),
        {
            '@context': 'https://schema.org',
            '@type': 'ContactPage',
            name: get('contact_page.title', ''),
        },
    ],
})
</script>
