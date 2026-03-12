import { precacheAndRoute, cleanupOutdatedCaches } from 'workbox-precaching';
import { registerRoute, NavigationRoute } from 'workbox-routing';
import { NetworkFirst, CacheFirst, StaleWhileRevalidate } from 'workbox-strategies';
import { ExpirationPlugin } from 'workbox-expiration';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';

// Precache assets from build
precacheAndRoute(self.__WB_MANIFEST);

// Cleanup old caches
cleanupOutdatedCaches();

// Cache name prefix
const CACHE_PREFIX = 'sim-tugas';

// Cache Google Fonts
registerRoute(
    ({ url }) => url.origin === 'https://fonts.googleapis.com' || url.origin === 'https://fonts.gstatic.com',
    new CacheFirst({
        cacheName: `${CACHE_PREFIX}-google-fonts`,
        plugins: [
            new CacheableResponsePlugin({ statuses: [0, 200] }),
            new ExpirationPlugin({ maxEntries: 30, maxAgeSeconds: 60 * 60 * 24 * 365 }),
        ],
    })
);

// Cache Bunny Fonts
registerRoute(
    ({ url }) => url.origin === 'https://fonts.bunny.net',
    new CacheFirst({
        cacheName: `${CACHE_PREFIX}-bunny-fonts`,
        plugins: [
            new CacheableResponsePlugin({ statuses: [0, 200] }),
            new ExpirationPlugin({ maxEntries: 30, maxAgeSeconds: 60 * 60 * 24 * 365 }),
        ],
    })
);

// Cache static assets (images, icons)
registerRoute(
    ({ request, url }) =>
        request.destination === 'image' ||
        url.pathname.startsWith('/icons/') ||
        url.pathname.startsWith('/images/'),
    new CacheFirst({
        cacheName: `${CACHE_PREFIX}-images`,
        plugins: [
            new CacheableResponsePlugin({ statuses: [0, 200] }),
            new ExpirationPlugin({ maxEntries: 100, maxAgeSeconds: 60 * 60 * 24 * 30 }),
        ],
    })
);

// Cache CSS and JS
registerRoute(
    ({ request }) =>
        request.destination === 'style' ||
        request.destination === 'script',
    new StaleWhileRevalidate({
        cacheName: `${CACHE_PREFIX}-static-resources`,
        plugins: [
            new CacheableResponsePlugin({ statuses: [0, 200] }),
        ],
    })
);

// Cache API requests with NetworkFirst
registerRoute(
    ({ url }) => url.pathname.startsWith('/api/'),
    new NetworkFirst({
        cacheName: `${CACHE_PREFIX}-api`,
        networkTimeoutSeconds: 10,
        plugins: [
            new CacheableResponsePlugin({ statuses: [0, 200] }),
            new ExpirationPlugin({ maxEntries: 100, maxAgeSeconds: 60 * 60 * 24 }),
        ],
    })
);

// Cache page navigations with NetworkFirst, fallback to offline page
registerRoute(
    ({ request }) => request.mode === 'navigate',
    new NetworkFirst({
        cacheName: `${CACHE_PREFIX}-pages`,
        networkTimeoutSeconds: 5,
        plugins: [
            new CacheableResponsePlugin({ statuses: [0, 200] }),
            new ExpirationPlugin({ maxEntries: 50, maxAgeSeconds: 60 * 60 * 24 * 7 }),
        ],
    })
);

// Offline fallback
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(`${CACHE_PREFIX}-offline`).then((cache) => {
            return cache.addAll(['/offline.html', '/icons/icon-192x192.png']);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

// Handle fetch failures for navigation - show offline page
self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((response) => {
                    // Clone and cache successful responses
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(`${CACHE_PREFIX}-pages`).then((cache) => {
                            cache.put(event.request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Try to get from cache first
                    return caches.match(event.request).then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Fallback to offline page
                        return caches.match('/offline.html');
                    });
                })
        );
    }
});
