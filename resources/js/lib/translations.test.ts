import { afterEach, describe, expect, it } from 'vitest';
import { setTranslations, trans } from './translations';

describe('trans', () => {
    afterEach(() => setTranslations({}));

    it('returns the key when nothing is loaded', () => {
        expect(trans('Log out')).toBe('Log out');
    });

    it('reads whatever translations are current when called', () => {
        setTranslations({ 'Log out': 'Sair' });
        expect(trans('Log out')).toBe('Sair');

        setTranslations({ 'Log out': 'Abmelden' });
        expect(trans('Log out')).toBe('Abmelden');
    });

    it('fills every occurrence of a placeholder', () => {
        setTranslations({ ':name meets :name': ':name conhece :name' });
        expect(trans(':name meets :name', { name: 'Ada' })).toBe(
            'Ada conhece Ada',
        );
    });
});
