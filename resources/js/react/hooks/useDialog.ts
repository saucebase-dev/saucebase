import { useCallback, useEffect, useState, type ComponentType } from 'react';

export interface ConfirmOptions {
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'default' | 'destructive';
    icon?: ComponentType<{ className?: string }>;
    align?: 'center' | 'left';
}

interface DialogState {
    isOpen: boolean;
    options: ConfirmOptions;
}

let state: DialogState = { isOpen: false, options: { title: '' } };
let resolveCallback: ((value: boolean) => void) | null = null;
const subscribers = new Set<(s: DialogState) => void>();

function setState(next: Partial<DialogState>) {
    state = { ...state, ...next };
    subscribers.forEach((cb) => cb(state));
}

export function useDialog() {
    const [localState, setLocalState] = useState<DialogState>(state);

    useEffect(() => {
        subscribers.add(setLocalState);
        return () => { subscribers.delete(setLocalState); };
    }, []);

    const confirm = useCallback((opts: ConfirmOptions): Promise<boolean> => {
        setState({ options: opts, isOpen: true });
        return new Promise<boolean>((resolve) => {
            resolveCallback = resolve;
        });
    }, []);

    const resolve = useCallback((confirmed: boolean) => {
        setState({ isOpen: false });
        resolveCallback?.(confirmed);
        resolveCallback = null;
    }, []);

    return { confirm, isOpen: localState.isOpen, options: localState.options, resolve };
}
