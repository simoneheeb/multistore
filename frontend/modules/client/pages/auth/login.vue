<template>
    <form class="rounded-[var(--radius-base)] border border-border bg-surface p-6" @submit.prevent="submit">
        <UiTitle eyebrow="Admin Login" title="Sign in to the admin panel" size="sm" align="center" />

        <div class="mt-6 flex flex-col gap-4">
            <UiInput
                v-model="form.email"
                label="Email"
                type="email"
                icon="mail"
                required
                :rules="[required('Email'), emailRule()]" />

            <UiInput
                v-model="form.password"
                label="Password"
                type="password"
                icon="lock"
                required
                :rules="[required('Password')]" />
        </div>

        <UiAlert v-if="auth.error" tone="danger" class="mt-4">
            {{ auth.error }}
        </UiAlert>

        <UiButton type="submit" block size="lg" :loading="auth.loading" class="mt-6">
            Sign in
        </UiButton>
    </form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { definePageMeta } from '#imports'
import { useAuthStore } from '~~/modules/client/store/authStore'
import { email as emailRule, firstError, required } from '~/helpers/rules'

definePageMeta({ layout: 'auth' })

const auth = useAuthStore()
const toast = useToast()

const form = reactive({ email: '', password: '' })

const submit = async () => {
    const error =
        firstError(form.email, [required('Email'), emailRule()]) ??
        firstError(form.password, [required('Password')])

    if (error) {
        toast.error(error)
        return
    }

    await auth.login({ email: form.email, password: form.password })
}

usePageSeo({
    title: 'Sign in',
    path: '/auth/login',
    // A login screen has nothing to offer a search result.
    noindex: true,
})
</script>
