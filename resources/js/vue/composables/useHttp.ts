import type { FormDataType, UseHttpSubmitOptions } from '@inertiajs/core';
import { useHttp as useInertiaHttp } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { toast } from 'vue-sonner';

const verbs = ['get', 'post', 'put', 'patch', 'delete'] as const;

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
    const say = (message: string) =>
        notify ? notify(message, http) : toast.error(trans(message));
    const handlers = {
        onHttpException: (response: { status: number }) => {
            say(failureMessage(response.status));
        },
        onNetworkError: () => {
            say(failureMessage());
        },
    };

    for (const verb of verbs) {
        const send = http[verb];

        (http as unknown as Record<string, Send<TForm, TResponse>>)[verb] = (
            url,
            options = {},
        ) => send(url, { ...handlers, ...options }).catch(() => undefined);
    }

    return http as typeof http & {
        [V in (typeof verbs)[number]]: Send<TForm, TResponse>;
    };
}
