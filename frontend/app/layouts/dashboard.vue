<template>
    <div class="flex h-screen bg-page">
        <NuxtLoadingIndicator color="var(--color-primary)" :height="2" />

        <!-- Backdrop for the mobile drawer -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            @click="sidebarOpen = false" />

        <DashboardSidebar :open="sidebarOpen" @close="sidebarOpen = false" />

        <div class="flex min-w-0 flex-1 flex-col overflow-y-auto">
            <DashboardHeader :title="pageTitle" @toggle="sidebarOpen = !sidebarOpen" />

            <main class="flex-1">
                <slot />
            </main>
        </div>

        <UiToaster />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const sidebarOpen = ref(false)

// Any navigation closes the mobile drawer.
watch(() => route.path, () => { sidebarOpen.value = false })

/**
 * Header title derived from the route, so each page does not have to set it
 * and the two can never disagree.
 */
const titles: Record<string, string> = {
    '/admin': 'Dashboard',
    '/admin/brands': 'Manage brands',
    '/admin/categories': 'Manage categories',
    '/admin/products': 'Manage products',
    '/admin/settings': 'Site settings',
}

/**
 * An exact match wins; otherwise the longest matching prefix does, so a
 * detail route such as /admin/products/42 still reads "Manage products".
 * "/admin" is excluded from the prefix pass because it matches everything.
 */
const pageTitle = computed(() => {
    const exact = titles[route.path]
    if (exact) return exact

    const prefix = Object.keys(titles)
        .filter(path => path !== '/admin' && route.path.startsWith(path))
        .sort((a, b) => b.length - a.length)[0]

    return prefix ? titles[prefix]! : 'Admin panel'
})
</script>
