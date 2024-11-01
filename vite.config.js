import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css'],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            'vue': path.resolve(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js'),
        },
    },
    define: {
        '__VUE_PROD_HYDRATION_MISMATCH_DETAILS__': false,  // Disable detailed hydration mismatch warnings in production
    },
});
