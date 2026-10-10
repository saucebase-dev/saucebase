import { usePage } from '@inertiajs/react';
import type { Settings } from '@saucebase/core/lib/settings';

export function useSettings(): Settings {
    return usePage().props.settings;
}
