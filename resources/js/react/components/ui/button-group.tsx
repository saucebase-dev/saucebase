import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { type VariantProps, cva } from 'class-variance-authority';
import type { HTMLAttributes } from 'react';

export const buttonGroupVariants = cva(
    "flex w-fit items-stretch [&>*]:focus-visible:z-10 [&>*]:focus-visible:relative [&>[data-slot=select-trigger]:not([class*='w-'])]:w-fit [&>input]:flex-1 has-[>[data-slot=button-group]]:gap-2",
    {
        variants: {
            orientation: {
                horizontal:
                    '[&>*:not(:first-child)]:rounded-l-none [&>*:not(:first-child)]:border-l-0 [&>*:not(:last-child)]:rounded-r-none',
                vertical:
                    'flex-col [&>*:not(:first-child)]:rounded-t-none [&>*:not(:first-child)]:border-t-0 [&>*:not(:last-child)]:rounded-b-none',
            },
        },
        defaultVariants: { orientation: 'horizontal' },
    },
);

export type ButtonGroupVariants = VariantProps<typeof buttonGroupVariants>;

interface ButtonGroupProps extends HTMLAttributes<HTMLDivElement> {
    orientation?: ButtonGroupVariants['orientation'];
}

export function ButtonGroup({
    className,
    orientation,
    ...props
}: ButtonGroupProps) {
    return (
        <div
            role="group"
            data-slot="button-group"
            data-orientation={orientation}
            className={cn(buttonGroupVariants({ orientation }), className)}
            {...props}
        />
    );
}

export function ButtonGroupSeparator({
    className,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <Separator
            data-slot="button-group-separator"
            orientation="vertical"
            className={cn('bg-input relative !m-0 self-stretch', className)}
            {...props}
        />
    );
}

export function ButtonGroupText({
    className,
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            role="group"
            data-slot="button-group"
            className={cn(
                "bg-muted flex items-center gap-2 rounded-md border px-4 text-sm font-medium shadow-xs [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4",
                className,
            )}
            {...props}
        />
    );
}
