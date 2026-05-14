import { useEffect, useState } from 'react';

interface AppLogoProps {
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'xxl';
    showText?: boolean;
    centered?: boolean;
    variant?: 'brand' | 'light';
    showSubtitle?: boolean;
    subtitleSize?: 'xs' | 'sm' | 'md' | 'xl' | 'xxl';
}

const sizeClasses = {
    sm: 'h-8 w-8',
    md: 'h-12 w-12',
    lg: 'h-16 w-16',
    xl: 'h-20 w-20',
    xxl: 'h-30 w-30',
};

const textSizeClasses = {
    sm: 'text-xl',
    md: 'text-2xl',
    lg: 'text-3xl',
    xl: 'text-4xl',
    xxl: 'text-6xl',
};

const subtitleSizeClasses: Record<string, string> = {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg',
    xl: 'text-xl',
    xxl: 'text-2xl',
};

export default function AppLogo({
    size = 'md',
    showText = false,
    centered = false,
    variant = 'brand',
    showSubtitle = false,
    subtitleSize,
}: AppLogoProps) {
    const [isDark, setIsDark] = useState(() =>
        document.documentElement.classList.contains('dark'),
    );

    useEffect(() => {
        const observer = new MutationObserver(() => {
            setIsDark(document.documentElement.classList.contains('dark'));
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        return () => observer.disconnect();
    }, []);

    const primaryFill = isDark ? 'url(#logo-dark-bottom-grad)' : 'url(#logo-primary-grad)';
    const secondaryFill = isDark ? 'url(#logo-dark-top-grad)' : 'url(#logo-secondary-grad)';

    return (
        <div className={centered ? 'flex flex-col items-center gap-1' : 'flex items-center gap-1'}>
            <div className={`relative ${sizeClasses[size]}`}>
                <svg
                    className="h-full w-full"
                    viewBox="0 0 568 568"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-label="Saucebase logo"
                    role="img"
                    style={{ fillRule: 'evenodd', clipRule: 'evenodd' }}
                >
                    <defs>
                        <linearGradient id="logo-secondary-grad" x1="0" y1="0" x2="1" y2="0" gradientUnits="userSpaceOnUse" gradientTransform="matrix(376.07,135.39,-135.39,376.07,231.46,875.253)">
                            <stop offset="0" style={{ stopColor: 'color-mix(in oklch, var(--secondary) 96%, black)' }} />
                            <stop offset="1" style={{ stopColor: 'color-mix(in oklch, var(--secondary) 98%, white)' }} />
                        </linearGradient>
                        <linearGradient id="logo-primary-grad" x1="0" y1="0" x2="1" y2="0" gradientUnits="userSpaceOnUse" gradientTransform="matrix(-481.156,-26.6311,26.6311,-481.156,753.144,1025.92)">
                            <stop offset="0" style={{ stopColor: 'color-mix(in oklch, var(--primary) 96%, black)' }} />
                            <stop offset="1" style={{ stopColor: 'color-mix(in oklch, var(--primary) 98%, white)' }} />
                        </linearGradient>
                        <linearGradient id="logo-dark-top-grad" gradientUnits="objectBoundingBox" x1="0.5" y1="0" x2="0.5" y2="1">
                            <stop offset="0" style={{ stopColor: 'color-mix(in oklch, var(--primary) 45%, white)' }} />
                            <stop offset="1" style={{ stopColor: 'color-mix(in oklch, var(--primary) 65%, white)' }} />
                        </linearGradient>
                        <linearGradient id="logo-dark-bottom-grad" gradientUnits="objectBoundingBox" x1="0.5" y1="0" x2="0.5" y2="1">
                            <stop offset="0" style={{ stopColor: 'color-mix(in oklch, var(--primary) 88%, white)' }} />
                            <stop offset="1" style={{ stopColor: 'color-mix(in oklch, var(--primary) 75%, black)' }} />
                        </linearGradient>
                    </defs>
                    <g transform="matrix(1,0,0,1,-923,-1301)">
                        <g transform="matrix(0.373135,0,0,0.373135,759.596,1101.55)">
                            <g transform="matrix(1,0,0,1,26.7094,46.0787)">
                                <g transform="matrix(-1.01323,-1.01323,1.01323,-1.01323,789.359,3040.11)">
                                    <path
                                        d="M796.834,683.998L796.834,1297.69C796.834,1340.02 762.461,1374.4 720.123,1374.4L683.357,1374.4C471.667,1374.4 299.801,1202.53 299.801,990.842C299.801,779.152 471.667,607.287 683.357,607.287L720.123,607.287C762.461,607.287 796.834,641.66 796.834,683.998Z"
                                        fill={variant === 'light' ? 'rgba(255,255,255,0.7)' : primaryFill}
                                    />
                                </g>
                                <g transform="matrix(1.01323,1.01323,-1.01323,1.01323,1557.32,-541.47)">
                                    <path
                                        d="M796.834,683.998L796.834,1297.69C796.834,1340.02 762.461,1374.4 720.123,1374.4L683.357,1374.4C471.667,1374.4 299.801,1202.53 299.801,990.842C299.801,779.152 471.667,607.287 683.357,607.287L720.123,607.287C762.461,607.287 796.834,641.66 796.834,683.998Z"
                                        fill={variant === 'light' ? 'white' : secondaryFill}
                                    />
                                </g>
                            </g>
                        </g>
                    </g>
                </svg>
            </div>

            {showText && (
                <div className={centered ? 'flex flex-col items-center text-center' : 'flex flex-col'}>
                    <h1 className={`${textSizeClasses[size]} ${variant === 'light' ? 'leading-none font-bold text-white' : 'leading-none font-bold text-gray-900 dark:text-white'}`}>
                        <span className="text-secondary dark:text-muted-foreground">sauce</span>
                        <span className="text-primary dark:text-foreground">base</span>
                    </h1>
                    {showSubtitle && (
                        <p className={`${subtitleSizeClasses[subtitleSize ?? size]} leading-tight ${variant === 'light' ? 'text-white/80' : 'text-muted-foreground'}`}>
                            the recipe that works
                        </p>
                    )}
                </div>
            )}
        </div>
    );
}
