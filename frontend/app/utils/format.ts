/**
 * Formats an integer with thousands separators, e.g. 1234567 -> 1,234,567.
 *
 * Returns an empty string for a missing value and passes non-numeric input
 * through unchanged, so a caller can hand it a raw field without guarding.
 */
export function formatNumber(value: number | string | null | undefined): string {
    if (value === null || value === undefined || value === '') return ''

    const number = Number(value)
    if (Number.isNaN(number)) return String(value)

    return number.toLocaleString('en-US')
}

/** Trims copy to a readable length without cutting a word in half. */
export function truncate(text: string | null | undefined, length = 120): string {
    const value = String(text ?? '').trim()
    if (value.length <= length) return value

    const cut = value.slice(0, length)
    const lastSpace = cut.lastIndexOf(' ')

    return `${lastSpace > length * 0.6 ? cut.slice(0, lastSpace) : cut}…`
}

/**
 * Resolves a link value from the settings editor.
 *
 * Admins type either a path ("/about") or a full URL; the two need different
 * markup (NuxtLink vs <a target="_blank">), and this tells them apart.
 */
export function isExternalLink(url: string | null | undefined): boolean {
    const value = String(url ?? '')
    return /^(https?:)?\/\//.test(value) || value.startsWith('mailto:') || value.startsWith('tel:')
}
