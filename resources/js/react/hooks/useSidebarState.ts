import { useEffect, useState } from 'react';

const SIDEBAR_STATE_KEY = 'sidebar:state';

export function useSidebarState() {
    const [isOpen, setIsOpen] = useState<boolean>(() => {
        try {
            const stored = localStorage.getItem(SIDEBAR_STATE_KEY);
            return stored !== null ? JSON.parse(stored) : true;
        } catch {
            return true;
        }
    });

    useEffect(() => {
        try {
            localStorage.setItem(SIDEBAR_STATE_KEY, JSON.stringify(isOpen));
        } catch {
            // ignore storage errors
        }
    }, [isOpen]);

    return { isOpen, setIsOpen };
}
