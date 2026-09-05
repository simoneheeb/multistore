import { defineStore } from 'pinia'
import { AdminService } from '../services/AdminService'
import type { SettingsKey, SiteSettings } from '~/types/settings'

interface SettingsState {
    settings: Partial<SiteSettings>
    /** Working copy the editor mutates; committed on save. */
    draft: Partial<SiteSettings>
    loading: boolean
    saving: boolean
}

/**
 * Settings editor state.
 *
 * The panel edits a draft rather than the live object, so an unsaved change
 * can be discarded and a failed save leaves the last known-good values in
 * place instead of a half-applied tab.
 */
export const useAdminSettingsStore = defineStore('admin-settings', {
    state: (): SettingsState => ({
        settings: {},
        draft: {},
        loading: false,
        saving: false,
    }),

    getters: {
        /** True when the draft differs from what was last loaded. */
        isDirty: state => JSON.stringify(state.draft) !== JSON.stringify(state.settings),
    },

    actions: {
        async fetch(force = false) {
            if (this.loading) return
            if (!force && Object.keys(this.settings).length) return

            this.loading = true
            try {
                const data = await AdminService.settings.all()
                if (data) {
                    this.settings = data
                    // Deep clone so editing the draft never mutates the
                    // baseline the dirty check compares against.
                    this.draft = structuredClone(data)
                }
            } finally {
                this.loading = false
            }
        },

        /** Saves the groups belonging to one tab. */
        async saveGroups(keys: SettingsKey[]) {
            this.saving = true
            try {
                const payload: Partial<SiteSettings> = {}
                keys.forEach((key) => {
                    if (this.draft[key] !== undefined) {
                        (payload as any)[key] = this.draft[key]
                    }
                })

                const response = await AdminService.settings.saveMany(payload)

                if (response) {
                    // Re-read rather than trusting the local copy: the backend
                    // rewrites inline images into stored URLs, so the saved
                    // value is not identical to what was sent.
                    await this.fetch(true)
                    return true
                }

                return false
            } finally {
                this.saving = false
            }
        },

        async resetGroup(key: SettingsKey) {
            const response = await AdminService.settings.reset(key)
            if (response) await this.fetch(true)
            return Boolean(response)
        },

        /** Throws away unsaved edits. */
        discard() {
            this.draft = structuredClone(this.settings as SiteSettings)
        },
    },
})
