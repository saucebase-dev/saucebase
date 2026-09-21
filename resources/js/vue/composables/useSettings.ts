import { usePage } from '@inertiajs/vue3';
import type { Settings } from '@js/lib/settings';
import { computed, type ComputedRef } from 'vue';

export function useSettings(): ComputedRef<Settings> {
    const page = usePage();

    return computed(() => page.props.settings);
}
