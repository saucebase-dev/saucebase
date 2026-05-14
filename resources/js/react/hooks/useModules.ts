import { usePage } from '@inertiajs/react';

export function useModules() {
    const page = usePage();

    return {
        has(name: string): boolean {
            const modules = (page.props.modules as Record<string, string>) ?? {};
            const lower = name.toLowerCase();
            return (
                Object.keys(modules).some((k) => k.toLowerCase() === lower) ||
                Object.values(modules).some((v) => v.toLowerCase() === lower)
            );
        },

        all(): string[] {
            return Object.values((page.props.modules as Record<string, string>) ?? {});
        },
    };
}

export const modules = useModules;
