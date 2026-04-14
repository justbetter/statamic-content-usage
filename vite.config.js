import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/statamic-content-usage.js',
                'resources/css/statamic-content-usage.css'
            ],
            publicDirectory: 'resources/dist',
        }),
        vue(),
    ],
    server: {
        cors: true,
        host: 'localhost',
        port: 5175,
        strictPort: true,
        https: false,
        hmr: {
            host: 'localhost',
        },
    },
});
