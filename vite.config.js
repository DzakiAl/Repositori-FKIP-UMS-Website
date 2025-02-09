import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/dashboard.css',
                'resources/css/file_manager.css',
                'resources/css/login.css',
                'resources/css/navbar.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
