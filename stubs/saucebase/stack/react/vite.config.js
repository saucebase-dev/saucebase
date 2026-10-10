import { saucebase } from './vendor/saucebase/core/resources/js/react/vite.ts';
import fs from 'node:fs';
import { defineConfig } from 'vite';

const sslKey = 'docker/ssl/app.key.pem';
const sslCert = 'docker/ssl/app.pem';

export default defineConfig({
    plugins: [saucebase()],
    server: fs.existsSync(sslCert)
        ? { https: { key: fs.readFileSync(sslKey), cert: fs.readFileSync(sslCert) } }
        : {},
});
