    import { defineConfig } from 'vite'
    import react from '@vitejs/plugin-react'
    import tailwindcss from '@tailwindcss/vite'
    import path from 'path'
    import { VitePWA } from 'vite-plugin-pwa'

    export default defineConfig({
    plugins: [
        react(),
        tailwindcss(),
        VitePWA({
        registerType: 'autoUpdate',
        includeAssets: ['favicon.svg', 'pwa-192x192.png', 'pwa-512x512.png'],
        manifest: {
            name: 'Alnes Coffee and Venue Batu',
            short_name: 'Alnes Coffee',
            description: 'Smart Café Ordering System',
            theme_color: '#1C1008',
            background_color: '#FAF8F4',
            display: 'standalone',
            orientation: 'portrait',
            scope: '/',
            start_url: '/',
            icons: [
            {
                src: 'pwa-192x192.png',
                sizes: '192x192',
                type: 'image/png',
            },
            {
                src: 'pwa-512x512.png',
                sizes: '512x512',
                type: 'image/png',
            },
            {
                src: 'pwa-512x512.png',
                sizes: '512x512',
                type: 'image/png',
                purpose: 'any maskable',
            },
            ],
        },
        workbox: {
            globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
            runtimeCaching: [
            {
                urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                handler: 'CacheFirst',
                options: {
                cacheName: 'google-fonts-cache',
                expiration: { maxEntries: 10, maxAgeSeconds: 60 * 60 * 24 * 365 },
                cacheableResponse: { statuses: [0, 200] },
                },
            },
            {
                urlPattern: /^http:\/\/127\.0\.0\.1:8000\/api\/.*/i,
                handler: 'NetworkFirst',
                options: {
                cacheName: 'api-cache',
                expiration: { maxEntries: 50, maxAgeSeconds: 60 * 5 },
                cacheableResponse: { statuses: [0, 200] },
                },
            },
            ],
        },
        }),
    ],
    resolve: {
        alias: {
        "@": path.resolve(__dirname, "./src"),
        },
    },
    server: {
        allowedHosts: ['laundry-ideology-monstrous.ngrok-free.dev'],
    },
    })