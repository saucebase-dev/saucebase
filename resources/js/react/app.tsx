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
    setup({ el, App, props }) {
        executeModuleSetups(moduleSetups).then(() => {
            createRoot(el).render(<App {...props} />);
        });
    },
    progress: { color: '#4B5563' },
});
