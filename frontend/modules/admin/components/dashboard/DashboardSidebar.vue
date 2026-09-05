<template>
    <!--
        The sidebar is docked at the inline start edge, so off-canvas means
        translating it further toward that edge. The transform is scoped to
        max-lg deliberately: keeping it out of the desktop cascade entirely is
        what guarantees it can never sit on top of the page content.
    -->
    <aside
        :class="[
            'fixed inset-y-0 start-0 z-50 flex w-64 flex-col border-e border-border bg-surface transition-transform lg:static',
            open ? 'translate-x-0' : 'max-lg:-translate-x-full',
        ]">
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-border px-4">
            <NuxtLink to="/admin" class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-[var(--radius-base)] bg-primary text-on-primary">
                    <UiIcon name="dashboard" :size="17" />
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-sm font-bold text-heading">Admin panel</span>
                    <span class="block truncate text-[0.625rem] uppercase tracking-wider text-muted">Admin</span>
                </span>
            </NuxtLink>

            <button
                type="button"
                class="rounded p-1 text-muted transition-colors hover:bg-surface-muted lg:hidden"
                aria-label="Close menu"
                @click="$emit('close')">
                <UiIcon name="close" :size="18" />
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-3" aria-label="Admin navigation">
            <ul class="flex flex-col gap-0.5">
                <li v-for="item in links" :key="item.to">
                    <NuxtLink
                        :to="item.to"
                        :class="[
                            'flex items-center gap-2.5 rounded-[var(--radius-base)] px-3 py-2.5 text-sm transition-colors',
                            isActive(item.to)
                                ? 'bg-primary-soft font-medium text-primary-strong'
                                : 'text-body hover:bg-surface-muted',
                        ]">
                        <UiIcon :name="item.icon" :size="17" />
                        <span class="min-w-0 flex-1 truncate">{{ item.label }}</span>
                    </NuxtLink>
                </li>
            </ul>
        </nav>

        <div class="border-t border-border p-3">
            <NuxtLink
                to="/"
                class="flex items-center gap-2.5 rounded-[var(--radius-base)] px-3 py-2.5 text-sm text-body transition-colors hover:bg-surface-muted">
                <UiIcon name="home" :size="17" />
                View site
            </NuxtLink>

            <button
                type="button"
                class="mt-0.5 flex w-full items-center gap-2.5 rounded-[var(--radius-base)] px-3 py-2.5 text-sm text-danger transition-colors hover:bg-danger/10"
                @click="auth.logout()">
                <UiIcon name="logout" :size="17" />
                Sign out
            </button>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { useAuthStore } from '~~/modules/client/store/authStore'

defineProps<{ open: boolean }>()
defineEmits<{ close: [] }>()

const route = useRoute()
const auth = useAuthStore()

const links = [
    { to: '/admin', label: 'Dashboard', icon: 'dashboard' },
    { to: '/admin/brands', label: 'Brands', icon: 'tag' },
    { to: '/admin/categories', label: 'Categories', icon: 'layers' },
    { to: '/admin/products', label: 'Products', icon: 'box' },
    { to: '/admin/settings', label: 'Site settings', icon: 'settings' },
]

/**
 * The dashboard root would otherwise stay highlighted on every child route,
 * so it matches exactly while the rest match by prefix.
 */
const isActive = (path: string) =>
    path === '/admin' ? route.path === '/admin' : route.path.startsWith(path)
</script>
