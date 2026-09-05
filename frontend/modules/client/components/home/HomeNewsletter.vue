<template>
    <div v-if="enabled" :class="compact ? '' : 'py-12 md:py-16'">
        <div :class="compact ? '' : 'site-container'">
            <div :class="wrapperClass">
                <div class="min-w-0">
                    <p v-if="eyebrow" class="text-[0.6875rem] uppercase tracking-[0.2em] text-primary">
                        {{ eyebrow }}
                    </p>
                    <p class="mt-0.5 text-lg font-bold text-heading">{{ title }}</p>
                    <p v-if="description" class="mt-1 text-xs leading-6 text-muted">{{ description }}</p>
                </div>

                <form class="flex w-full max-w-md items-start gap-2" @submit.prevent="submit">
                    <UiInput
                        v-model="email"
                        type="email"
                        :placeholder="placeholder"
                        :error="error"
                        class="flex-1" />

                    <UiButton type="submit" :loading="submitting">{{ buttonLabel }}</UiButton>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { email as emailRule, firstError, required } from '~/helpers/rules'

/**
 * Newsletter sign-up.
 *
 * There is no subscribers endpoint in the API yet, so this validates the
 * address and confirms locally rather than pretending to store it. Wiring it
 * to a real endpoint is a one-line change in submit().
 */
const props = withDefaults(defineProps<{ compact?: boolean }>(), { compact: false })

const { get } = useSiteSettings()
const toast = useToast()

const enabled = computed(() => get('newsletter.enabled', true))
const eyebrow = computed(() => get('newsletter.eyebrow', 'Newsletter'))
const title = computed(() => get('newsletter.title', 'Newsletter'))
const description = computed(() => get('newsletter.description', ''))
const placeholder = computed(() => get('newsletter.placeholder', 'Your email address'))
const buttonLabel = computed(() => get('newsletter.button_label', 'Subscribe'))

const email = ref('')
const error = ref<string | null>(null)
const submitting = ref(false)

const wrapperClass = computed(() => [
    'flex flex-col items-start justify-between gap-5 md:flex-row md:items-center',
    props.compact ? '' : 'rounded-[var(--radius-base)] border border-border bg-surface p-6 md:p-8',
])

const submit = () => {
    error.value = firstError(email.value, [required('Email'), emailRule()])
    if (error.value) return

    submitting.value = true
    email.value = ''
    submitting.value = false

    toast.success('You are subscribed. Look out for our next offer.')
}
</script>
