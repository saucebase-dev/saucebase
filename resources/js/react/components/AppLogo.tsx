import { useSettings } from '@/hooks/useSettings';

type Size = 'sm' | 'md' | 'lg' | 'xl' | 'xxl';

interface AppLogoProps {
    size?: Size;
    variant?: 'logo' | 'icon';
}

const heights: Record<Size, string> = {
    sm: 'h-8',
    md: 'h-10',
    lg: 'h-16',
    xl: 'h-20',
    xxl: 'h-30',
};

const squares: Record<Size, string> = {
    sm: 'w-8',
    md: 'w-10',
    lg: 'w-16',
    xl: 'w-20',
    xxl: 'w-30',
};

/**
 * The application's brand, as artwork.
 *
 * Two variants, because two shapes of slot exist: the wide lockup, which carries the
 * name inside the artwork, and the square mark, for holes too narrow to read a name in —
 * a collapsed sidebar, a workspace row.
 *
 * Nothing here renders text. The name is in the image.
 */
export default function AppLogo({
    size = 'md',
    variant = 'logo',
}: AppLogoProps) {
    const brand = useSettings().general;
    const isIcon = variant === 'icon';

    // Wide artwork keeps its height and lets the width follow; a mark is square.
    const classes = isIcon
        ? `${heights[size]} ${squares[size]} object-contain`
        : `${heights[size]} w-auto max-w-full object-contain`;

    const onLight = isIcon
        ? brand.site_icon_on_light
        : brand.site_logo_on_light;
    const onDark = isIcon ? brand.site_icon_on_dark : brand.site_logo_on_dark;

    /*
     * Both variants render and CSS picks. Resolving the theme in JavaScript would flash
     * on hydration: `appearance` may be `system`, which the server cannot answer, so SSR
     * would embed the light artwork and swap it on the client. The inline script in
     * app.blade.php sets `.dark` before first paint, so CSS is right from frame one.
     */
    return (
        <>
            <img
                src={onLight}
                alt={brand.site_name}
                className={`${classes} dark:hidden`}
            />
            <img
                src={onDark}
                alt={brand.site_name}
                className={`${classes} not-dark:hidden`}
            />
        </>
    );
}
