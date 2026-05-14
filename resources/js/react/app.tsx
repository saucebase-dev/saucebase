import App from '@/components/App';
import { I18nProvider } from '@/i18n';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { discoverModuleSetups, executeModuleSetups } from './lib/moduleSetup';
import { resolveModularPageComponent } from './lib/utils';
import '@css/app.css';

const appName = import.meta.env.VITE_APP_NAME || 'Saucebase';
const moduleSetups = discoverModuleSetups();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: resolveModularPageComponent,
    setup({ el, App: InertiaApp, props }) {
        const locale = (props.initialPage.props?.locale as string) || 'en';

        executeModuleSetups(moduleSetups).then(() => {
            createRoot(el).render(
                <I18nProvider initialLocale={locale}>
                    <App>
                        <InertiaApp {...props} />
                    </App>
                </I18nProvider>,
            );
        });
    },
    progress: {
        color:
            getComputedStyle(document.documentElement)
                .getPropertyValue('--primary')
                .trim() || '#4B5563',
    },
});
