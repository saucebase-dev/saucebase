import tailwindcss from '@tailwindcss/vite';
import inertia from '@inertiajs/vite';
import react from '@vitejs/plugin-react';
import fs from 'fs';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';
import { collectModuleLangPaths } from './module-loader.js';

async function createConfig() {
    const sslKeyPath = 'docker/ssl/app.key.pem';
    const sslCertPath = 'docker/ssl/app.pem';
    const hasSSL = fs.existsSync(sslKeyPath) && fs.existsSync(sslCertPath);

    const moduleLangPaths = await collectModuleLangPaths();

    return defineConfig({
        server: hasSSL
            ? {
                  https: {
                      key: fs.readFileSync(sslKeyPath),
                      cert: fs.readFileSync(sslCertPath),
                  },
              }
            : {},
        plugins: [
            tailwindcss(),
            laravel({
                input: [
                    'resources/js/react/app.tsx',
                    'resources/css/filament/admin/theme.css',
                ],
                refresh: true,
            }),
            inertia(),
            react(),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js/react'),
                '@css': path.resolve(__dirname, 'resources/css'),
                '@modules': path.resolve(__dirname, 'modules'),
                'ziggy-js': path.resolve(__dirname, 'vendor/tightenco/ziggy'),
            },
        },
    });
}

export default createConfig();
