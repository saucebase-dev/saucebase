import { useLocalStorage } from '@vueuse/core';
import type { Ref } from 'vue';

const SIDEBAR_STATE_KEY = 'sidebar:state';

/**
 * Composable to manage sidebar state with localStorage persistence.
 *
 * This ensures the sidebar state persists across Inertia navigation
 * and page reloads.
 */
export function useSidebarState() {
    const isOpen = useLocalStorage<boolean>(
        SIDEBAR_STATE_KEY,
        true,
    ) as Ref<boolean>;

    return {
        isOpen,
    };
}
