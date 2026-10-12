import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            ssr: 'resources/js/ssr.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(import.meta.dirname, 'resources/js'),
            '~corporate': path.resolve(import.meta.dirname, 'resources/js/Components/corporate'),
            '~ecommerce': path.resolve(import.meta.dirname, 'resources/js/Components/ecommerce'),
            '~shared': path.resolve(import.meta.dirname, 'resources/js/Components/shared'),
        },
    },
    server: {
        host: '127.0.0.1',
        hmr: { host: '127.0.0.1' },
    },
});
