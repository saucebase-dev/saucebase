import type { ComponentType, ReactNode } from 'react';

/**
 * The banner at the top of a public page: an icon, a title, a short
 * description, and optional actions on the right.
 *
 * The gradient is built from opacities of the primary token rather than a
 * numbered shade, because the theme only defines the base colours.
 */
// Written out rather than built from the prop: Tailwind only keeps classes it
// can read in the source.
const WIDTHS = {
    '3xl': 'max-w-3xl',
    '5xl': 'max-w-5xl',
    '6xl': 'max-w-6xl',
} as const;

export default function PageHero({
    title,
    description,
    icon: Icon,
    actions,
    testId = 'page-hero',
    width = '6xl',
}: {
    title: string;
    description?: string;
    icon?: ComponentType<{ className?: string }>;
    actions?: ReactNode;
    testId?: string;
    /** Match the width of the content below it. */
    width?: keyof typeof WIDTHS;
}) {
    return (
        <section
            data-testid={testId}
            className="from-primary/15 dark:from-primary-900/70 text-foreground bg-linear-to-b to-transparent pt-8"
        >
            <div
                className={`mx-auto flex w-full ${WIDTHS[width]} flex-col items-start gap-6 px-6 pt-20 pb-6 sm:flex-row sm:items-center sm:justify-between`}
            >
                <div className="flex items-center gap-5">
                    {Icon && (
                        <div className="bg-primary dark:bg-foreground/5 dark:text-foreground rounded-full p-7 text-white backdrop-blur-sm">
                            <Icon className="size-14" />
                        </div>
                    )}

                    <div>
                        <h1 className="text-primary dark:text-foreground text-4xl font-bold tracking-tight">
                            {title}
                        </h1>
                        {description && (
                            <p className="text-muted-foreground mt-2 max-w-2xl">
                                {description}
                            </p>
                        )}
                    </div>
                </div>

                {actions && (
                    <div className="w-full shrink-0 sm:w-auto">{actions}</div>
                )}
            </div>
        </section>
    );
}
