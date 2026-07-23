import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/chatbot.js',
                'resources/js/charts.js',
            ],
            refresh: true,
            fonts: [
                bunny('Inter', {
                    weights: [300, 400, 500, 600, 700, 800],
                }),
            ],
        }),

        tailwindcss(),
    ],

    server: {
        watch: {
            ignored: [
                '**/storage/framework/views/**',
            ],
        },
    },
});
