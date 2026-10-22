/**
 * Service Worker for Infant Vaccination System
 * Comprehensive caching strategy with Firebase Cloud Messaging support
 * 
 * CACHING STRATEGY:
 * - TIER 1 (Cache First): Versioned static assets (CSS, JS, Fonts, Icons) - Long cache
 * - TIER 2 (Stale While Revalidate): Images and other media - Medium cache
 * - TIER 3 (Network First): Dynamic content with cache fallback
 * - TIER 4 (Network Only): Auth routes, form submissions
 * 
 * OFFLINE DETECTION:
 * - Distinguishes between "network offline" and "server unavailable"
 * - Provides helpful error context to users
 */

// ============================================
// CACHE CONFIGURATION
// ============================================

const CACHE_VERSION = 'v5';
const STATIC_CACHE = `ivs-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `ivs-dynamic-${CACHE_VERSION}`;
const IMAGE_CACHE = `ivs-images-${CACHE_VERSION}`;
const FONT_CACHE = `ivs-fonts-${CACHE_VERSION}`;

// Maximum items in dynamic cache (prevents unbounded growth)
const MAX_DYNAMIC_CACHE_ITEMS = 50;
const MAX_IMAGE_CACHE_ITEMS = 100;

// Cache expiration times (in milliseconds)
const CACHE_EXPIRATION = {
    STATIC: 7 * 24 * 60 * 60 * 1000,   // 7 days for CSS, JS (Tailwind, etc.)
    IMAGES: 24 * 60 * 60 * 1000,        // 24 hours for images
    DYNAMIC: 1 * 60 * 60 * 1000,        // 1 hour for dynamic content
    FONTS: 30 * 24 * 60 * 60 * 1000,    // 30 days for fonts
};

// Error types for better offline messaging
const ERROR_TYPES = {
    NETWORK_OFFLINE: 'network_offline',
    SERVER_UNAVAILABLE: 'server_unavailable',
    TIMEOUT: 'timeout',
    UNKNOWN: 'unknown'
};

// Debug mode - set to false for production
const DEBUG_MODE = false; // Set to true during development for detailed logging

// Conditional logging function
function logDebug(message, data = null) {
    if (DEBUG_MODE) {
        if (data) {
            console.log('[SW]', message, data);
        } else {
            console.log('[SW]', message);
        }
    }
}

// TIER 1: Static assets to precache on install (Cache Forever)
// These are critical for app shell and must be available offline
const PRECACHE_ASSETS = [
    // Core CSS
    '/css/tailwind-full.css',
    '/css/fontawesome-all.min.css',
    '/css/app.css',
    '/css/dashboard.css',
    
    // Core JavaScript
    '/javascript/pwa.js',
    '/javascript/notifications.js',
    '/javascript/session-guard.js',
    '/javascript/logout-helper.js',
    
    // PWA Icons (all sizes for different devices)
    '/images/icon-72x72.png',
    '/images/icon-96x96.png',
    '/images/icon-128x128.png',
    '/images/icon-144x144.png',
    '/images/icon-152x152.png',
    '/images/icon-192x192.png',
    '/images/icon-384x384.png',
    '/images/icon-512x512.png',
    
    // Common UI images
    '/images/systembg.jpg',
    '/images/calauanlogo.png',
    '/images/doh-logo.png',
    
    // Manifest
    '/manifest.json',
    
    // Offline fallback
    '/offline.html'
];

// Font files to cache (WOFF2 for best compression)
const FONT_ASSETS = [
    '/webfonts/fa-solid-900.woff2',
    '/webfonts/fa-regular-400.woff2',
    '/webfonts/fa-brands-400.woff2'
];

// URL patterns that should NEVER be cached
const NEVER_CACHE_PATTERNS = [
    /\/api\//,                    // API endpoints
    /\/login/,                    // Auth pages
    /\/logout/,                   // Logout
    /\/register/,                 // Registration
    /\/password/,                 // Password reset
    /\/sanctum/,                  // Sanctum CSRF
    /\/_debugbar/,                // Debug bar
    /\/horizon/,                  // Horizon
    /livewire/,                   // Livewire
    /\.php$/,                     // Direct PHP files
    /\?_token=/,                  // CSRF tokens in URL
];

// URL patterns for stale-while-revalidate (images, media)
const STALE_WHILE_REVALIDATE_PATTERNS = [
    /\/images\//,
    /\/storage\//,
    /\.jpg$/i,
    /\.jpeg$/i,
    /\.png$/i,
    /\.gif$/i,
    /\.svg$/i,
    /\.webp$/i,
    /\.ico$/i,
];

// URL patterns for cache-first (static assets)
const CACHE_FIRST_PATTERNS = [
    /\/css\//,
    /\/javascript\//,
    /\/js\//,
    /\/webfonts\//,
    /\/fonts\//,
    /\/build\/assets\//,
    /\/vendor\//,
    /\.css$/i,
    /\.js$/i,
    /\.woff2?$/i,
    /\.ttf$/i,
    /\.eot$/i,
];

// ============================================
// FIREBASE INITIALIZATION
// ============================================

importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "REPLACE_WITH_FIREBASE_API_KEY",
    authDomain: "REPLACE_WITH_FIREBASE_AUTH_DOMAIN",
    projectId: "REPLACE_WITH_FIREBASE_PROJECT_ID",
    storageBucket: "REPLACE_WITH_FIREBASE_STORAGE_BUCKET",
    messagingSenderId: "REPLACE_WITH_FIREBASE_SENDER_ID",
    appId: "REPLACE_WITH_FIREBASE_APP_ID"
});

const messaging = firebase.messaging();

// Handle background FCM messages
messaging.onBackgroundMessage(async (payload) => {
    const clientList = await self.clients.matchAll({
        type: 'window',
        includeUncontrolled: true
    });
    
    const hasVisibleClient = clientList.some(client => client.visibilityState === 'visible');
    
    if (hasVisibleClient) {
        return; // Foreground handler will manage this
    }
    
    const title = payload.data?.title || 'Notification';
    const body = payload.data?.body || '';
    const icon = payload.data?.icon || '/images/icon-192x192.png';
    const clickAction = payload.data?.click_action || '/parents/parentdashboard';
    
    return self.registration.showNotification(title, {
        body: body,
        icon: icon,
        badge: '/images/icon-192x192.png',
        data: { click_action: clickAction },
        tag: 'fcm-notification',
        requireInteraction: false
    });
});

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Check if URL matches any pattern in the given array
 */
function matchesPattern(url, patterns) {
    return patterns.some(pattern => pattern.test(url));
}

/**
 * Limit cache size by removing oldest entries
 */
async function limitCacheSize(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length > maxItems) {
        // Remove oldest entries (FIFO)
        const deleteCount = keys.length - maxItems;
        for (let i = 0; i < deleteCount; i++) {
            await cache.delete(keys[i]);
        }
    }
}

/**
 * Determine the type of network error for better user messaging
 */
function categorizeError(error) {
    // Check if browser reports offline
    if (!navigator.onLine) {
        return ERROR_TYPES.NETWORK_OFFLINE;
    }
    
    // Check error message for clues
    const errorMessage = error?.message?.toLowerCase() || '';
    
    if (errorMessage.includes('failed to fetch') || 
        errorMessage.includes('networkerror') ||
        errorMessage.includes('network request failed')) {
        // Could be either server down or network issue
        // Since navigator.onLine is true, likely server issue
        return ERROR_TYPES.SERVER_UNAVAILABLE;
    }
    
    if (errorMessage.includes('timeout') || 
        errorMessage.includes('aborted')) {
        return ERROR_TYPES.TIMEOUT;
    }
    
    return ERROR_TYPES.SERVER_UNAVAILABLE;
}

/**
 * Create an enhanced offline response with error context
 */
async function createOfflineResponse(request, errorType) {
    // For navigation requests, return the offline page with error context
    if (request.mode === 'navigate') {
        const offlinePage = await caches.match('/offline.html');
        if (offlinePage) {
            // Clone and modify the response to include error type
            const html = await offlinePage.text();
            const modifiedHtml = html.replace(
                '</head>',
                `<script>window.OFFLINE_ERROR_TYPE = '${errorType}';</script></head>`
            );
            return new Response(modifiedHtml, {
                status: 503,
                statusText: 'Service Unavailable',
                headers: {
                    'Content-Type': 'text/html; charset=utf-8',
                    'X-Offline-Reason': errorType
                }
            });
        }
    }
    
    // For other requests, return a JSON error
    return new Response(JSON.stringify({
        error: true,
        type: errorType,
        message: errorType === ERROR_TYPES.NETWORK_OFFLINE 
            ? 'No internet connection' 
            : 'Server is currently unavailable'
    }), { 
        status: 503, 
        statusText: 'Service Unavailable',
        headers: {
            'Content-Type': 'application/json',
            'X-Offline-Reason': errorType
        }
    });
}

/**
 * Cache-first strategy: Check cache, fallback to network
 * Used for static assets like Tailwind CSS, JavaScript, fonts
 */
async function cacheFirst(request, cacheName) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
        // For static assets, optionally refresh in background if cache is old
        // But always return cached version immediately for performance
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(cacheName);
            // Store with timestamp for potential expiration checks
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        if (DEBUG_MODE) console.error('[SW] Cache-first fetch failed:', error);
        const errorType = categorizeError(error);
        return createOfflineResponse(request, errorType);
    }
}

/**
 * Network-first strategy: Try network, fallback to cache
 * Used for dynamic content where freshness is important
 */
async function networkFirst(request, cacheName) {
    try {
        // Add timeout to prevent hanging requests
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        const networkResponse = await fetch(request, { signal: controller.signal });
        clearTimeout(timeoutId);
        
        if (networkResponse.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        if (DEBUG_MODE) console.warn('[SW] Network-first fetch failed:', error.message);
        
        // Try to return cached version
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            logDebug('Returning cached response for:', request.url);
            return cachedResponse;
        }
        
        // No cache available, return enhanced error response
        const errorType = categorizeError(error);
        return createOfflineResponse(request, errorType);
    }
}

/**
 * Stale-while-revalidate: Return cache immediately, update in background
 * Used for images and media where immediate response is important
 */
async function staleWhileRevalidate(request, cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const cachedResponse = await cache.match(request);
    
    // Fetch fresh version in background (with timeout)
    const fetchPromise = (async () => {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout for images
            
            const networkResponse = await fetch(request, { signal: controller.signal });
            clearTimeout(timeoutId);
            
            if (networkResponse.ok) {
                cache.put(request, networkResponse.clone());
                limitCacheSize(cacheName, maxItems);
            }
            return networkResponse;
        } catch (error) {
            if (DEBUG_MODE) console.warn('[SW] Background revalidation failed:', request.url);
            return null;
        }
    })();
    
    // Return cached version immediately if available
    if (cachedResponse) {
        return cachedResponse;
    }
    
    // No cache, wait for network
    const networkResponse = await fetchPromise;
    if (networkResponse) {
        return networkResponse;
    }
    
    // Both failed, return error
    return createOfflineResponse(request, ERROR_TYPES.SERVER_UNAVAILABLE);
}

// ============================================
// SERVICE WORKER LIFECYCLE EVENTS
// ============================================

/**
 * Install event: Precache critical assets
 */
self.addEventListener('install', (event) => {
    logDebug('Installing with cache version:', CACHE_VERSION);
    
    event.waitUntil(
        Promise.all([
            // Cache static assets
            caches.open(STATIC_CACHE).then(cache => {
                logDebug('Precaching static assets...');
                return cache.addAll(PRECACHE_ASSETS.map(url => 
                    new Request(url, { cache: 'reload' })
                )).catch(err => {
                    if (DEBUG_MODE) console.warn('[SW] Some precache assets failed:', err);
                    // Continue anyway - non-critical
                });
            }),
            // Cache fonts separately (they rarely change)
            caches.open(FONT_CACHE).then(cache => {
                logDebug('Precaching fonts...');
                return cache.addAll(FONT_ASSETS.map(url => 
                    new Request(url, { cache: 'reload' })
                )).catch(err => {
                    if (DEBUG_MODE) console.warn('[SW] Some font assets failed:', err);
                });
            })
        ])
    );
    
    // Activate immediately
    self.skipWaiting();
});

/**
 * Activate event: Clean up old caches
 */
self.addEventListener('activate', (event) => {
    logDebug('Activating...');
    
    const currentCaches = [STATIC_CACHE, DYNAMIC_CACHE, IMAGE_CACHE, FONT_CACHE];
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(cacheName => !currentCaches.includes(cacheName))
                    .map(cacheName => {
                        logDebug('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    })
            );
        }).then(() => {
            logDebug('Claiming clients...');
            return self.clients.claim();
        })
    );
});

/**
 * Fetch event: Implement caching strategies
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = request.url;
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip non-HTTP(S) requests
    if (!url.startsWith('http')) {
        return;
    }
    
    // BYPASS: Skip all caching for localhost/development
    // This prevents offline page from showing during local development
    const requestUrl = new URL(url);
    if (requestUrl.hostname === 'localhost' || 
        requestUrl.hostname === '127.0.0.1' ||
        requestUrl.hostname.endsWith('.test') ||
        requestUrl.hostname.endsWith('.local')) {
        return; // Let browser handle request normally
    }
    
    // Skip cross-origin requests (except fonts from CDNs)
    if (requestUrl.origin !== location.origin) {
        // Allow Google Fonts and other trusted CDNs
        if (!url.includes('fonts.googleapis.com') && 
            !url.includes('fonts.gstatic.com') &&
            !url.includes('cdnjs.cloudflare.com')) {
            return;
        }
    }
    
    // NEVER cache these (auth, API, etc.)
    if (matchesPattern(url, NEVER_CACHE_PATTERNS)) {
        return;
    }
    
    // Determine caching strategy based on URL pattern
    event.respondWith(
        (async () => {
            // TIER 1: Cache-first for static assets (CSS, JS, fonts)
            if (matchesPattern(url, CACHE_FIRST_PATTERNS)) {
                return cacheFirst(request, STATIC_CACHE);
            }
            
            // TIER 2: Stale-while-revalidate for images
            if (matchesPattern(url, STALE_WHILE_REVALIDATE_PATTERNS)) {
                return staleWhileRevalidate(request, IMAGE_CACHE, MAX_IMAGE_CACHE_ITEMS);
            }
            
            // TIER 3: Network-first for everything else (HTML pages)
            // This ensures users always get fresh content when online
            try {
                // Add timeout to prevent hanging requests
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
                
                const response = await fetch(request, { signal: controller.signal });
                clearTimeout(timeoutId);
                
                return response;
            } catch (error) {
                if (DEBUG_MODE) console.warn('[SW] Navigation fetch failed:', error.message);
                
                // Determine the type of error for better user messaging
                const errorType = categorizeError(error);
                
                // Try to return cached version first
                const cachedResponse = await caches.match(request);
                if (cachedResponse) {
                    logDebug('Returning cached page for:', request.url);
                    return cachedResponse;
                }
                
                // Return enhanced offline page with error context
                return createOfflineResponse(request, errorType);
            }
        })()
    );
});

// ============================================
// PUSH NOTIFICATION EVENTS
// ============================================

self.addEventListener('push', (event) => {
    let notificationData = {
        title: 'Bagong Abiso',
        body: 'May bagong notification para sa iyo',
        icon: '/images/icon-192x192.png',
        badge: '/images/icon-192x192.png',
        vibrate: [200, 100, 200],
        tag: 'notification',
        requireInteraction: false,
        data: { url: '/' }
    };
    
    if (event.data) {
        try {
            const payload = event.data.json();
            logDebug('Processing push notification:', payload.title);
            notificationData = {
                title: payload.title || notificationData.title,
                body: payload.message || payload.body || notificationData.body,
                icon: payload.icon || notificationData.icon,
                badge: payload.badge || notificationData.badge,
                vibrate: payload.vibrate || notificationData.vibrate,
                tag: payload.type || payload.tag || notificationData.tag,
                requireInteraction: payload.requireInteraction || false,
                data: {
                    url: payload.action_url || payload.url || '/',
                    notification_id: payload.notification_id || null,
                    type: payload.type || 'general'
                },
                actions: payload.actions || [],
                timestamp: Date.now()
            };
        } catch (error) {
            if (DEBUG_MODE) console.error('[SW] Error parsing push data:', error);
        }
    }
    
    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    const urlToOpen = event.notification.data?.url || '/';
    const notificationId = event.notification.data?.notification_id;
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                for (let client of clientList) {
                    if (client.url.includes(urlToOpen) && 'focus' in client) {
                        return client.focus();
                    }
                }
                
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen).then(client => {
                        if (notificationId && client) {
                            client.postMessage({
                                type: 'MARK_NOTIFICATION_READ',
                                notificationId: notificationId
                            });
                        }
                        return client;
                    });
                }
            })
    );
});

self.addEventListener('notificationclose', (event) => {
    const notificationId = event.notification.data?.notification_id;
    if (notificationId) {
        fetch('/api/notifications/dismissed', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                notification_id: notificationId,
                dismissed_at: Date.now()
            })
        }).catch(() => {});
    }
});

// ============================================
// MESSAGE HANDLING
// ============================================

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data?.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then(cacheNames => 
                Promise.all(cacheNames.map(cacheName => caches.delete(cacheName)))
            ).then(() => {
                // Notify all clients that cache was cleared
                self.clients.matchAll().then(clients => {
                    clients.forEach(client => {
                        client.postMessage({ type: 'CACHE_CLEARED' });
                    });
                });
            })
        );
    }
    
    if (event.data?.type === 'GET_CACHE_STATUS') {
        event.waitUntil(
            (async () => {
                const cacheNames = await caches.keys();
                const cacheStatus = {};
                
                for (const cacheName of cacheNames) {
                    const cache = await caches.open(cacheName);
                    const keys = await cache.keys();
                    cacheStatus[cacheName] = keys.length;
                }
                
                event.source.postMessage({
                    type: 'CACHE_STATUS',
                    data: cacheStatus,
                    version: CACHE_VERSION
                });
            })()
        );
    }
});

// ============================================
// BACKGROUND SYNC
// ============================================

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-notifications') {
        event.waitUntil(
            fetch('/api/notifications/check')
                .then(response => response.json())
                .catch(error => {
                    if (DEBUG_MODE) console.error('[SW] Sync failed:', error);
                })
        );
    }
});

logDebug('Service Worker loaded - Cache version:', CACHE_VERSION);
