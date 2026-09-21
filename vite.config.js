import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

// All assets are bundled into public/build and served from this server.
// Nothing here may reference an external CDN (jsDelivr, unpkg, cdnjs,
// Google Fonts, Bunny Fonts, ...) — see docs/FRONTEND.md.
//
// RTL: the compiled CSS is flipped by RTLCSS via postcss.config.js.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js'],
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
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5.3 still uses @import and legacy colour functions;
                // silence Dart Sass deprecation noise from dependencies.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    resolve: {
        alias: {
            // Runtime-only Vue build: SFC templates are compiled at build time.
            vue: 'vue/dist/vue.runtime.esm-bundler.js',
        },
    },
    build: {
        // Vue and each widget are emitted as separate chunks and fetched only
        // when a page actually contains a [data-vue-component] target.
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/vue/') || id.includes('node_modules/@vue/')) {
                        return 'vue';
                    }
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
