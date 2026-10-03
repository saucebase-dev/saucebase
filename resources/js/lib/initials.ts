/**
 * An avatar's fallback: the first letter of a name's first two words.
 *
 * `Array.from` splits by character, so an emoji or astral script keeps its whole
 * glyph instead of half a surrogate pair.
 */
export function getInitials(name: string | null | undefined): string {
    return (name ?? '')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => Array.from(word)[0].toUpperCase())
        .join('');
}
