<template>
    <div>
        <!-- NuxtPage must be nested inside NuxtLayout: it is what fills the
             layout's default slot. Without it every layout renders with an
             empty <main>. -->
        <NuxtLayout>
            <NuxtPage />
        </NuxtLayout>
    </div>
</template>

<script setup lang="ts">
import { useHead } from 'nuxt/app'
import { watchEffect } from 'vue'

/**
 * Root shell.
 *
 * The theme attribute and the admin-configurable brand colours are applied to
 * <html> here so they are present in the server-rendered markup - doing it in
 * a component would paint the default palette first and swap after hydration.
 */
const { mode } = useTheme()
const { get } = useSiteSettings()

const radiusScale: Record<string, string> = {
    none: '0px',
    sm: '0.375rem',
    md: '0.625rem',
    lg: '1rem',
}

const containerScale: Record<string, string> = {
    narrow: '1080px',
    wide: '1280px',
    full: '100%',
}

useHead(() => ({
    htmlAttrs: {
        'data-theme': mode.value,
    },
    style: [
        {
            // Overrides the tokens defined in main.css with the values an
            // admin picked in the theme settings group.
            key: 'theme-tokens',
            innerHTML: `:root{
                --color-primary:${get('theme.primary', '#0f766e')};
                --color-accent:${get('theme.accent', '#b45309')};
                --radius-base:${radiusScale[get('theme.radius', 'sm')] ?? '0.375rem'};
                --container-max:${containerScale[get('theme.container', 'wide')] ?? '1280px'};
            }`,
        },
    ],
}))

// Keep the DOM attribute in step with client-side theme toggles.
watchEffect(() => {
    if (import.meta.client) {
        document.documentElement.setAttribute('data-theme', mode.value)
    }
})
</script>
