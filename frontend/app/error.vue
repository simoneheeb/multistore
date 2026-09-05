<template>
    <div class="flex min-h-screen flex-col bg-page">
        <AppHeader />

        <main class="flex flex-1 items-center">
            <div class="site-container py-20 text-center">
                <p class="text-6xl font-bold text-primary md:text-7xl">
                    {{ error?.statusCode ?? 500 }}
                </p>

                <h1 class="mt-4 text-xl font-bold text-heading md:text-2xl">{{ title }}</h1>
                <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-muted">{{ description }}</p>

                <div class="mt-7 flex flex-wrap items-center justify-center gap-2">
                    <UiButton to="/" icon="home" @click="clear">Back to home</UiButton>
                    <UiButton to="/products" variant="outline">Browse products</UiButton>
                </div>
            </div>
        </main>

        <AppFooter />
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { clearError, type NuxtError } from 'nuxt/app'

const props = defineProps<{ error: NuxtError }>()

// Settings drive the header and footer here too, so an error page still looks
// like the rest of the site rather than a bare fallback.
useSiteSettings()
useSeoDefaults()

const title = computed(() =>
    props.error?.statusCode === 404
        ? 'We could not find that page'
        : 'Something went wrong',
)

const description = computed(() =>
    props.error?.statusCode === 404
        ? 'The page may have been removed or its address changed.'
        : 'This page could not be displayed. Please try again shortly.',
)

const clear = () => clearError({ redirect: '/' })
</script>
