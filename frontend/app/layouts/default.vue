<template>
    <div class="flex min-h-screen flex-col bg-page">
        <NuxtLoadingIndicator color="var(--color-primary)" :height="2" />

        <AppHeader />

        <main id="main" class="flex-1">
            <slot />
        </main>

        <AppFooter />

        <UiToaster />
    </div>
</template>

<script setup lang="ts">
/**
 * Public site shell. Settings are requested once here so the header, footer
 * and every page section read from the same cached payload.
 */
useSiteSettings()
useSeoDefaults()
</script>
