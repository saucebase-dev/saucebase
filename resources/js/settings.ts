import type { Page } from '@inertiajs/core';

/** One section of the settings modal, contributed by core or by a module. */
export type SettingsSection = {
    slug: string;
    title: string;
    icon: string | null;
    component: string;
};

export type Settings = {
    sections: SettingsSection[];
    general: {
        site_name: string;
        site_tagline: string | null;
        site_description: string | null;
        /**
         * Always a URL, never null: these fall back to the artwork core ships, so
         * nothing rendering them needs an "unconfigured" branch.
         *
         * The suffix names the background the asset sits on, not the colour of its
         * ink — `on_dark` is the light-coloured artwork.
         */
        site_logo_on_light: string;
        site_logo_on_dark: string;
        site_icon_on_light: string;
        site_icon_on_dark: string;
    };
    [domain: string]: unknown;
};

/**
 * Global Inertia title callback: suffixes each page title with the site name.
 *
 * Pages without a title fall back to the site name alone, so the document
 * never renders a bare separator or an empty <title>.
 */
export function siteTitle(title: string, page: Page): string {
    const siteName = page.props.settings?.general?.site_name ?? '';

    if (!title) {
        return siteName;
    }

    return siteName ? `${title} - ${siteName}` : title;
}
