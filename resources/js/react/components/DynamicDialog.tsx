import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { buttonVariants } from '@/components/ui/button';
import { useT } from '@/i18n';
import { useDialog } from '@/hooks/useDialog';
import { cn } from '@/lib/utils';

export default function DynamicDialog() {
    const t = useT();
    const { isOpen, options, resolve } = useDialog();

    const isCentered = !options.align || options.align === 'center';
    const Icon = options.icon;

    const iconContainerClass = options.variant === 'destructive'
        ? 'bg-destructive/10 text-destructive'
        : 'bg-primary/10 text-primary';

    const iconClass = cn(
        'flex size-14 items-center justify-center rounded-xl',
        isCentered ? 'mb-4' : 'shrink-0',
        iconContainerClass,
    );

    const contentClass = isCentered
        ? 'flex flex-col items-center text-center'
        : 'flex flex-row items-center gap-4';

    const headerClass = isCentered && Icon ? 'sm:text-center' : '';

    return (
        <AlertDialog open={isOpen}>
            <AlertDialogContent className="overflow-hidden p-0 sm:max-w-sm">
                <div data-testid="confirm-dialog">
                    <div className={cn('bg-background p-6', Icon ? contentClass : '')}>
                        {Icon && (
                            <div className={iconClass}>
                                <Icon className="size-7" />
                            </div>
                        )}
                        <AlertDialogHeader className={headerClass}>
                            <AlertDialogTitle>{options.title}</AlertDialogTitle>
                            {options.description && (
                                <AlertDialogDescription>{options.description}</AlertDialogDescription>
                            )}
                        </AlertDialogHeader>
                    </div>
                    <div className="bg-muted/50 border-t p-4">
                        <div className="grid grid-cols-2 gap-4">
                            <AlertDialogCancel
                                className="dark:hover:bg-accent dark:hover:text-accent-foreground mt-0 w-full"
                                data-testid="confirm-dialog-cancel"
                                onClick={() => resolve(false)}
                            >
                                {options.cancelLabel ?? t('Cancel')}
                            </AlertDialogCancel>
                            <AlertDialogAction
                                className={cn(buttonVariants({ variant: options.variant ?? 'default' }), 'w-full')}
                                data-testid="confirm-dialog-confirm"
                                onClick={() => resolve(true)}
                            >
                                {options.confirmLabel ?? t('Confirm')}
                            </AlertDialogAction>
                        </div>
                    </div>
                </div>
            </AlertDialogContent>
        </AlertDialog>
    );
}
