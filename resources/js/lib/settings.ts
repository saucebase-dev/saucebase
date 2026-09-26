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
         * The uploaded asset's URL, or null when none was uploaded — render
         * `BRAND_FALLBACKS` in its place.
         *
         * The suffix names the background the asset sits on, not the colour of its
         * ink — `on_dark` is the light-coloured artwork.
         */
        site_logo_on_light: string | null;
        site_logo_on_dark: string | null;
        site_icon_on_light: string | null;
        site_icon_on_dark: string | null;
    };
    [domain: string]: unknown;
};

export type BrandAsset =
    | 'site_logo_on_light'
    | 'site_logo_on_dark'
    | 'site_icon_on_light'
    | 'site_icon_on_dark';

/**
 * The app's own artwork, shown for any brand asset nobody uploaded. The favicon
 * links in the root blade views repeat these paths.
 */
export const BRAND_FALLBACKS: Record<BrandAsset, string> = {
    site_logo_on_light: '/images/logo-on-light.svg',
    site_logo_on_dark: '/images/logo-on-dark.svg',
    site_icon_on_light: '/images/icon-on-light.svg',
    site_icon_on_dark: '/images/icon-on-dark.svg',
};

/** The URL to render for a brand asset: the upload, or the app's fallback. */
export function brandAssetUrl(
    general: Settings['general'],
    asset: BrandAsset,
): string {
    return general[asset] ?? BRAND_FALLBACKS[asset];
}

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
