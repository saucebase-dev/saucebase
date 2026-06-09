import type { Announcement } from '.';

declare module '@inertiajs/core' {
    interface PageProps {
        announcement?: Announcement | null;
    }
}

export {};
