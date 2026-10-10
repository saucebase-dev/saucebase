import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Settings } from '@saucebase/core/lib/settings';
import type { Toast } from '@saucebase/react/lib/toastActions';

export interface User {
    id: number;
    name: string;
    email: string;
    avatar: string;
    last_login_at: string | null;
    role?: string;
}

export interface Breadcrumb {
    title: string;
    url?: string;
    active?: boolean;
    attributes?: {
        label?: string;
        [key: string]: any;
    };
    children?: any[];
    depth?: number;
}

export type { Toast };

/**
 * A page's own props on top of every shared prop.
 *
 * Built from the augmented Inertia interface rather than a second copy of the
 * list below, so a module that contributes a shared prop (auth's `auth`, for
 * one) is covered here without core having to know the module exists.
 */
export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & InertiaPageProps;

declare module '@inertiajs/core' {
    interface PageProps {
        locale?: string;
        locales?: Record<string, string>;
        modules?: Record<string, string>;
        navigation?: Record<string, any>;
        breadcrumbs?: Breadcrumb[];
        toast?: Toast;
        settings: Settings;
    }
}
