import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            // adicionar aqui todos os CSS/JS usados pelo Blade via @vite
            input: ['resources/css/app.css', 'resources/css/main.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
