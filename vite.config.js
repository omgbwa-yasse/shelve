import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/opac/index.jsx'],
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
            '@': '/resources/js/opac'
        },
        extensions: ['.js', '.jsx', '.ts', '.tsx']
    }
});
