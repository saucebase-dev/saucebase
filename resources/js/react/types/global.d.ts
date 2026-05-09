import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { route as ziggyRoute } from 'ziggy-js';
import type { PageProps as AppPageProps } from './';

declare global {
    var route: typeof ziggyRoute;
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
