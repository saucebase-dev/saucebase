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
        setup({ App, props }) {
            return <App {...props} />;
        },
    }),
);
