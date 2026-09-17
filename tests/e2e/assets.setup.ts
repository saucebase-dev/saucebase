/// <reference types="node" />
import { execSync } from 'node:child_process';
import { existsSync, rmSync } from 'node:fs';

/**
 * Build the frontend before the suite runs, and drop `public/hot` so the app
 * serves the build rather than pointing at a dev server that is not running.
 */
export default function globalSetup() {
    execSync('npm run build', { stdio: 'inherit' });

    if (existsSync('public/hot')) {
        rmSync('public/hot');
    }
}
