import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        react()
    ],
    server: {
        host: 'localhost',
        port: 5175,
        hmr: {
            host: 'localhost',
            port: 5175
        }
    },
    resolve: {
        alias: {
            '@': '/resources/js'
        },
        extensions: ['.js', '.jsx', '.ts', '.tsx']
    }
});
