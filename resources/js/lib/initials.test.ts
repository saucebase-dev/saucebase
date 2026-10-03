import { describe, expect, it } from 'vitest';
import { getInitials } from './initials';

describe('getInitials', () => {
    it('takes the first letter of the first two words, upper-cased', () => {
        expect(getInitials('ada lovelace byron')).toBe('AL');
    });

    it('ignores extra whitespace', () => {
        expect(getInitials('  Ada   Lovelace ')).toBe('AL');
    });

    it('keeps a whole emoji or astral character', () => {
        expect(getInitials('😀 Smile')).toBe('😀S');
    });

    it('is empty for an empty name', () => {
        expect(getInitials('')).toBe('');
        expect(getInitials(null)).toBe('');
    });
});
