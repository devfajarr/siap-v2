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
        alias: [
            // URUTAN PENTING: alias spesifik HARUS di atas alias general.
            // Vite/Rollup mencocokkan alias dari atas ke bawah dan berhenti di match pertama.
            //
            // Masalah: direktori di git adalah 'Components' (C kapital), tapi 68 file Vue
            // mengimport '@/components/...' (c kecil). Di Windows tidak masalah karena
            // file system-nya case-insensitive. Di Linux/Docker, case-sensitive → build gagal.
            //
            // '@/components' harus di atas '@' agar tidak kalah oleh catch-all '@'.
            {
                find: '@/components',
                replacement: path.resolve(__dirname, './resources/js/Components'),
            },
            {
                find: '@',
                replacement: path.resolve(__dirname, './resources/js'),
            },
        ],
    },
    server: {
        host: 'localhost',
        hmr: {
            host: 'localhost',
        },
    },
});
