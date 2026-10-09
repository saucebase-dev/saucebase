import { router, usePage } from '@inertiajs/react';
import { useModalStack } from '@inertiaui/modal-react';
import type { MouseEvent } from 'react';

/**
 * Opens sign-in and sign-up the way the site is set up: a modal over the current
 * page when modal sign-in is on, the page itself when it is off or no auth module
 * is installed.
 *
 * Call it from a button, or from a link's click with the event:
 * `<a href={route('login')} onClick={login}>`. The link keeps its href, so a
 * new-tab click or a page without JavaScript still reaches the page.
 *
 * For clicks only. A server redirect, such as the `auth` middleware turning a
 * guest away, always lands on the page.
 */
export function useAuth() {
    const page = usePage();
    const { visitModal } = useModalStack();
    const modalEnabled =
        (page.props.auth as { modal_enabled?: boolean } | undefined)
            ?.modal_enabled === true;

    function open(name: string, event?: MouseEvent) {
        if (event && opensElsewhere(event)) {
            return;
        }

        event?.preventDefault();

        if (!modalEnabled) {
            router.visit(route(name));

            return;
        }

        // The modal marks `#app` aria-hidden as it opens; focus left on the
        // trigger would sit inside an aria-hidden subtree until the panel loads.
        (document.activeElement as HTMLElement | null)?.blur();

        // `navigate` puts the route in the address bar while the modal is open,
        // so Back closes it and a refresh or shared link lands on the page.
        visitModal(route(name), { navigate: true });
    }

    return {
        login: (event?: MouseEvent) => open('login', event),
        signup: (event?: MouseEvent) => open('register', event),
    };
}

/** A click the browser should handle itself: new tab, new window, download. */
function opensElsewhere(event: MouseEvent): boolean {
    return (
        event.button !== 0 ||
        event.metaKey ||
        event.ctrlKey ||
        event.shiftKey ||
        event.altKey
    );
}
