import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

// All assets are bundled into public/build and served from this server.
// Nothing here may reference an external CDN (jsDelivr, unpkg, cdnjs,
// Google Fonts, Bunny Fonts, ...) — see docs/ARCHITECTURE.md.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // Leave asset URLs to Laravel/Vite; do not rewrite absolute URLs.
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            // Runtime-only build: templates are compiled at build time, so
            // the full compiler is never shipped to the browser.
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
