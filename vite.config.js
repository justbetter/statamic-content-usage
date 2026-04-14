import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import statamic from '@statamic/cms/vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/statamic-content-usage.js',
                'resources/css/statamic-content-usage.css'
            ],
            publicDirectory: 'resources/dist',
        }),
        statamic(),
    ],
});
