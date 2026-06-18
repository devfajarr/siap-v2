import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            // Alias lowercase → uppercase Components untuk kompatibilitas Linux Docker.
            // Di Windows (case-insensitive), '@/components' dan '@/Components' keduanya
            // bekerja. Di Linux Docker (case-sensitive), hanya '@/Components' yang valid
            // karena direktori aslinya bernama 'Components' (C kapital).
            // Alias ini memastikan 68 import lowercase tetap bekerja di Docker.
            '@/components': path.resolve(__dirname, './resources/js/Components'),
        },
    },
    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
        },
    },
});
