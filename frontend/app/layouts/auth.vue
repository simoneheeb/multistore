<template>
    <div class="flex min-h-screen items-center justify-center bg-page px-4 py-10">
        <NuxtLoadingIndicator color="var(--color-primary)" :height="2" />

        <div class="w-full max-w-sm">
            <NuxtLink to="/" class="mb-6 flex flex-col items-center gap-2 text-center">
                <UiImage
                    :src="logo"
                    :alt="siteName"
                    ratio="none"
                    fit="contain"
                    :rounded="false"
                    class="h-11 w-auto max-w-[11rem]" />
            </NuxtLink>

            <slot />

            <p class="mt-6 text-center text-xs text-muted">
                <NuxtLink to="/" class="transition-colors hover:text-primary">Back to the store</NuxtLink>
            </p>
        </div>

        <UiToaster />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const { get } = useSiteSettings()

const logo = computed(() => get('identity.logo', '/assets/images/logo-header.png'))
const siteName = computed(() => get('identity.site_name', ''))
</script>
