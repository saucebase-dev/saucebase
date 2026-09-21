/**
 * A day, in the app's language, as the same text on the server and in the
 * browser.
 *
 * Pages render on the server first, and the server and the reader rarely share a
 * time zone, so formatting in the reader's zone prints one day in the HTML and
 * another once the page hydrates. UTC is the one zone both agree on. It also
 * keeps a date-only value such as `2025-04-19`, which the browser reads as UTC
 * midnight, from sliding back a day for anyone west of Greenwich.
 */
export function formatDate(
    value: string | Date | null | undefined,
    locale: string | undefined,
    options: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    },
): string {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleDateString(locale, {
        ...options,
        timeZone: 'UTC',
    });
}

/**
 * A moment, with its time of day, in the reader's own zone.
 *
 * Only for pages that never render on the server: a time of day in UTC would be
 * wrong for almost everyone, and one in the reader's zone cannot match the
 * server's copy.
 */
export function formatDateTime(
    value: string | Date | null | undefined,
    locale: string | undefined,
): string {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleString(locale, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
