import { useT } from '@/i18n';
import type { FormDataType, UseHttpSubmitOptions } from '@inertiajs/core';
import { useHttp as useInertiaHttp } from '@inertiajs/react';
import { toast } from 'sonner';

type Send<TForm, TResponse> = (
    url: string,
    options?: UseHttpSubmitOptions<TResponse, TForm>,
) => Promise<TResponse | undefined>;

/**
 * What to tell the user. No response is not always offline: the server may be down, or a
 * callback threw, and only the browser knows.
 */
function failureMessage(status?: number): string {
    if (status === 419) {
        return 'Your session expired. Refresh the page and try again.';
    }

    if (status === undefined && globalThis.navigator?.onLine === false) {
        return "You're offline. Check your connection and try again.";
    }

    return 'Something went wrong. Try again.';
}

/**
 * Inertia's `useHttp`, telling the user when a request fails. Its `onError` covers a 422
 * only; anything else (an expired session, a server error, no connection) would roll back
 * an optimistic change and reject with nothing shown. This shows the failure through
 * `notify` (a toast unless given; it gets the form too) and resolves `undefined` instead
 * of rejecting. A call's own `onHttpException` or `onNetworkError` replaces it.
 */
export function useHttp<TForm extends FormDataType<TForm>, TResponse = unknown>(
    data: TForm | (() => TForm) = {} as TForm,
    notify?: (
        message: string,
        http: ReturnType<typeof useInertiaHttp<TForm, TResponse>>,
    ) => void,
) {
    const http = useInertiaHttp<TForm, TResponse>(data);
    const t = useT();
    const say = (message: string) =>
        notify ? notify(message, http) : toast.error(t(message));
    const handlers = {
        onHttpException: (response: { status: number }) => {
            say(failureMessage(response.status));
        },
        onNetworkError: () => {
            say(failureMessage());
        },
    };

    const wrap =
        (send: (typeof http)['post']): Send<TForm, TResponse> =>
        (url, options = {}) =>
            send(url, { ...handlers, ...options }).catch(() => undefined);

    return {
        ...http,
        get: wrap(http.get),
        post: wrap(http.post),
        put: wrap(http.put),
        patch: wrap(http.patch),
        delete: wrap(http.delete),
    };
}
