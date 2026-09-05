<template>
    <header class="sticky top-0 z-40 flex h-16 items-center justify-between gap-3 border-b border-border bg-surface/95 px-4 backdrop-blur">
        <div class="flex min-w-0 items-center gap-2">
            <button
                type="button"
                class="rounded-[var(--radius-base)] p-2 text-muted transition-colors hover:bg-surface-muted lg:hidden"
                aria-label="Open menu"
                @click="$emit('toggle')">
                <UiIcon name="menu" :size="20" />
            </button>

            <div class="min-w-0">
                <h1 class="truncate text-sm font-bold text-heading">{{ title }}</h1>
                <p v-if="eyebrow" class="truncate text-[0.625rem] uppercase tracking-wider text-muted">
                    {{ eyebrow }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-1">
            <button
                type="button"
                class="flex h-9 w-9 items-center justify-center rounded-[var(--radius-base)] text-muted transition-colors hover:bg-surface-muted"
                :aria-label="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
                @click="toggle">
                <UiIcon :name="isDark ? 'sun' : 'moon'" :size="18" />
            </button>

            <div class="hidden items-center gap-2 rounded-[var(--radius-base)] border border-border px-3 py-1.5 sm:flex">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-surface-muted text-muted">
                    <UiIcon name="user" :size="13" />
                </span>
                <span class="max-w-[10rem] truncate text-xs text-body">
                    {{ auth.user?.email ?? '—' }}
                </span>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
import { useAuthStore } from '~~/modules/client/store/authStore'

defineProps<{ title: string; eyebrow?: string }>()
defineEmits<{ toggle: [] }>()

const auth = useAuthStore()
const { isDark, toggle } = useTheme()
</script>
