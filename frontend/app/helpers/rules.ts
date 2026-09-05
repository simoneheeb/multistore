/* -------------------------------------------------------------------------
 * Form validation rules.
 *
 * Each rule returns `true` when valid or an error message, which is exactly
 * what the UiInput/UiTextarea `rules` prop expects.
 * ---------------------------------------------------------------------- */

export type ValidationRule = (value: unknown) => true | string

export const required = (label = 'This field'): ValidationRule => (value) => {
    if (Array.isArray(value)) return value.length > 0 || `${label} is required.`
    if (typeof value === 'string') return value.trim().length > 0 || `${label} is required.`
    return (value !== null && value !== undefined && value !== '') || `${label} is required.`
}

export const minLength = (min: number, label = 'This field'): ValidationRule => (value) =>
    String(value ?? '').length >= min || `${label} must be at least ${min} characters.`

export const maxLength = (max: number, label = 'This field'): ValidationRule => (value) =>
    String(value ?? '').length <= max || `${label} may not exceed ${max} characters.`

export const email = (): ValidationRule => (value) => {
    const text = String(value ?? '').trim()
    if (!text) return true
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(text) || 'Enter a valid email address.'
}

export const url = (): ValidationRule => (value) => {
    const text = String(value ?? '').trim()
    if (!text) return true
    // A root-relative path is a legitimate value for every link field in the
    // settings editor, so it is accepted alongside absolute URLs.
    if (text.startsWith('/')) return true
    return /^https?:\/\/.+/.test(text) || 'Enter a valid URL.'
}

export const numeric = (label = 'This field'): ValidationRule => (value) => {
    if (value === '' || value === null || value === undefined) return true
    return !Number.isNaN(Number(value)) || `${label} must be a number.`
}

export const hexColor = (): ValidationRule => (value) => {
    const text = String(value ?? '').trim()
    if (!text) return true
    return /^#[0-9a-fA-F]{6}$/.test(text) || 'Use a hex colour in the form #RRGGBB.'
}

/** Runs a rule list and returns the first failure, or null when all pass. */
export function firstError(value: unknown, rules: ValidationRule[] = []): string | null {
    for (const rule of rules) {
        const result = rule(value)
        if (result !== true) return result
    }
    return null
}
