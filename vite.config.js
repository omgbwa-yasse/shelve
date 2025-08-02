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
    build: {
        // Optimisation de la construction
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['react', 'react-dom'],
                    bootstrap: ['bootstrap'],
                    axios: ['axios']
                }
            }
        },
        // Compression et minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true
            }
        },
        // Améliore les performances de build
        chunkSizeWarningLimit: 1000
    },
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
