import App from '@/components/App';
import { I18nProvider } from '@/i18n';
import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import ReactDOMServer from 'react-dom/server';
import { resolveModularPageComponent } from './lib/utils';

const appName = import.meta.env.VITE_APP_NAME || 'Saucebase';

createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: resolveModularPageComponent,
        setup({ App: InertiaApp, props }: { App: React.ComponentType<any>; props: Record<string, any> }) {
            const locale = (page.props?.locale as string) || 'en';

            return (
                <I18nProvider initialLocale={locale}>
                    <App>
                        <InertiaApp {...props} />
                    </App>
                </I18nProvider>
            );
        },
    }),
);
