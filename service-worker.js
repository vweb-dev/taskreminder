/**
 * Service Worker for Task Reminder PWA
 * Handles offline functionality and caching
 */

const CACHE_NAME = 'task-reminder-v1';
const RUNTIME_CACHE = 'task-reminder-runtime-v1';

// Assets to cache on install
const STATIC_ASSETS = [
    '/task-reminder/',
    '/task-reminder/index.php',
    '/task-reminder/login.php',
    '/task-reminder/register.php',
    '/task-reminder/assets/css/style.css',
    '/task-reminder/assets/css/auth.css',
    '/task-reminder/assets/js/main.js',
    '/task-reminder/assets/js/tasks.js',
    '/task-reminder/assets/js/websocket.js',
    '/task-reminder/assets/js/notifications.js',
    '/task-reminder/assets/js/auth.js',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://code.jquery.com/jquery-3.7.1.min.js'
];

// Install event - Cache static assets
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching static assets');
                return cache.addAll(STATIC_ASSETS.map(url => {
                    try {
                        return new Request(url, { mode: 'no-cors' });
                    } catch (e) {
                        return url;
                    }
                })).catch(err => {
                    console.log('[Service Worker] Cache addAll error:', err);
                    // Continue even if some assets fail to cache
                    return Promise.resolve();
                });
            })
    );
    
    // Force activation of new service worker
    self.skipWaiting();
});

// Activate event - Clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    
    // Take control of all pages immediately
    return self.clients.claim();
});

// Fetch event - Serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip WebSocket connections
    if (url.protocol === 'ws:' || url.protocol === 'wss:') {
        return;
    }
    
    // Skip non-GET requests (POST, PUT, DELETE for API calls)
    if (request.method !== 'GET') {
        event.respondWith(fetch(request));
        return;
    }
    
    // Handle API requests - Network first, then cache
    if (url.pathname.includes('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Clone the response
                    const responseToCache = response.clone();
                    
                    // Cache successful API responses
                    if (response.status === 200) {
                        caches.open(RUNTIME_CACHE).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    
                    return response;
                })
                .catch(() => {
                    // Network failed, try cache
                    return caches.match(request).then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Return offline response for API
                        return new Response(
                            JSON.stringify({ 
                                success: false, 
                                message: 'You are offline. Please check your connection.' 
                            }),
                            {
                                headers: { 'Content-Type': 'application/json' },
                                status: 503
                            }
                        );
                    });
                })
        );
        return;
    }
    
    // Handle static assets - Cache first, then network
    event.respondWith(
        caches.match(request)
            .then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                
                // Not in cache, fetch from network
                return fetch(request)
                    .then((response) => {
                        // Don't cache non-successful responses
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }
                        
                        // Clone the response
                        const responseToCache = response.clone();
                        
                        caches.open(RUNTIME_CACHE).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                        
                        return response;
                    })
                    .catch(() => {
                        // Network failed and not in cache
                        // Return offline page for navigation requests
                        if (request.mode === 'navigate') {
                            return caches.match('/task-reminder/index.php') || 
                                   caches.match('/task-reminder/');
                        }
                        return new Response('Offline', { status: 503 });
                    });
            })
    );
});

// Background sync for offline task creation
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-tasks') {
        event.waitUntil(syncTasks());
    }
});

// Push notifications for reminders
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push notification received');
    
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Task Reminder';
    const options = {
        body: data.body || 'You have a task reminder',
        icon: '/task-reminder/assets/icons/icon-192x192.png',
        badge: '/task-reminder/assets/icons/icon-96x96.png',
        tag: data.tag || 'task-reminder',
        requireInteraction: true,
        vibrate: [200, 100, 200],
        data: data
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow('/task-reminder/')
    );
});

// Sync tasks function (for background sync)
async function syncTasks() {
    // This would sync offline tasks when connection is restored
    console.log('[Service Worker] Syncing tasks...');
}

