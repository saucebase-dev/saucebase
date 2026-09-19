type PutConfig = (key: string, value: unknown) => void;

/**
 * The Inertia modal package's setup, shared by both stacks.
 *
 * `putConfig` is passed in because each stack imports it from its own build of
 * the package; everything it is given is the same either way.
 */
export function initializeModals(putConfig: PutConfig): void {
    // A native `<dialog>` sits in the top layer, where it would cover the
    // app-level confirm dialog and make it unclickable from inside a modal.
    putConfig('useNativeDialog', false);

    // The package defaults the panel to a hardcoded `bg-white`, which breaks
    // dark mode. Set key by key so its other defaults stay in place.
    putConfig(
        'modal.panelClasses',
        'bg-background overflow-hidden rounded-lg border shadow-lg',
    );
    putConfig(
        'slideover.panelClasses',
        'bg-background min-h-screen overflow-hidden border-l shadow-lg',
    );

    releaseCoveredModalFocus();
}

/**
 * Stop the modal's focus trap from fighting overlays stacked above it.
 *
 * With `useNativeDialog` off the modal traps focus with a document-level
 * `focusin` listener that pulls focus back into its wrapper. A dialog, menu or
 * popover portalled to the body sits outside that wrapper and traps focus the
 * same way, so the two pull against each other on one event until the stack
 * overflows.
 *
 * An overlay marks everything it covered with `data-aria-hidden` when it opens,
 * so a covered modal has an overlay above it and no business holding focus.
 * Swallowing the event in the capture phase keeps it from reaching either trap.
 */
function releaseCoveredModalFocus(): void {
    document.addEventListener(
        'focusin',
        (event) => {
            const covered = document.querySelector(
                '[data-aria-hidden] .im-modal-wrapper',
            );

            if (
                covered &&
                event.target instanceof Node &&
                !covered.contains(event.target)
            ) {
                event.stopPropagation();
            }
        },
        true,
    );
}
