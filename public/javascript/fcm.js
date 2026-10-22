/**
 * Firebase Cloud Messaging (FCM) Integration
 * This script handles FCM token registration and push notifications
 * Replaces VAPID with Firebase for better shared hosting support
 */

class FcmNotificationManager {
    constructor() {
        this.fcmConfig = null;
        this.messaging = null;
        this.token = null;
        this.isSupported = false;
    }

    /**
     * Check if push notifications are supported
     */
    checkSupport() {
        // Check basic requirements
        if (!('serviceWorker' in navigator)) {
            return { supported: false, reason: 'Service workers not supported' };
        }
        if (!('PushManager' in window)) {
            return { supported: false, reason: 'Push notifications not supported' };
        }
        if (!('Notification' in window)) {
            return { supported: false, reason: 'Notifications API not supported' };
        }
        // Check if running in secure context (HTTPS or localhost)
        if (!window.isSecureContext) {
            return { supported: false, reason: 'Not a secure context (HTTPS required)' };
        }
        return { supported: true };
    }

    /**
     * Initialize FCM
     */
    async init() {
        try {
            // Check if push notifications are supported
            const support = this.checkSupport();
            if (!support.supported) {
                // Silently skip initialization on unsupported browsers
                return;
            }
            
            // Fetch FCM config from server
            await this.fetchConfig();
            
            this.isSupported = true;
            
            // Register service worker FIRST (before Firebase)
            this.swRegistration = await this.registerServiceWorker();
            
            // Load Firebase scripts
            await this.loadFirebaseScripts();
            
            // Initialize Firebase app
            firebase.initializeApp(this.fcmConfig);
            
            // Get messaging instance
            this.messaging = firebase.messaging();
            
            // Request permission and get token
            await this.requestPermission();
            
            // Listen for foreground messages
            this.listenForMessages();
            
        } catch (error) {
            // Silently handle unsupported browsers or permission issues
            // Only critical errors should reach user
        }
    }

    /**
     * Fetch FCM configuration from server
     */
    async fetchConfig() {
        try {
            const response = await fetch('/api/fcm/config');
            const data = await response.json();
            
            this.fcmConfig = {
                apiKey: data.apiKey,
                authDomain: data.authDomain,
                projectId: data.projectId,
                storageBucket: data.storageBucket,
                messagingSenderId: data.messagingSenderId,
                appId: data.appId
            };
            
            // Store VAPID key separately
            this.vapidKey = data.vapidKey;
            
            // DEBUG: console.log('FCM config fetched:', this.fcmConfig.projectId);
            
        } catch (error) {
            // Config fetch failed - FCM unavailable
            throw error;
        }
    }

    /**
     * Load Firebase SDK scripts dynamically
     */
    async loadFirebaseScripts() {
        return new Promise((resolve, reject) => {
            // Check if already loaded
            if (typeof firebase !== 'undefined') {
                resolve();
                return;
            }
            
            // Load Firebase App
            const appScript = document.createElement('script');
            appScript.src = 'https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js';
            appScript.onload = () => {
                // Load Firebase Messaging
                const messagingScript = document.createElement('script');
                messagingScript.src = 'https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js';
                messagingScript.onload = resolve;
                messagingScript.onerror = reject;
                document.head.appendChild(messagingScript);
            };
            appScript.onerror = reject;
            document.head.appendChild(appScript);
        });
    }

    /**
     * Register service worker for FCM
     */
    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            // DEBUG: console.log('Service Worker registered for FCM');
            await navigator.serviceWorker.ready;
            return registration;
        } catch (error) {
            // Service worker registration failed
            throw error;
        }
    }

    /**
     * Request notification permission and get FCM token
     */
    async requestPermission() {
        try {
            // Request permission
            const permission = await Notification.requestPermission();
            
            if (permission !== 'granted') {
                // User declined, silently exit
                return;
            }
            
            // Get FCM token with our service worker registration
            this.token = await this.messaging.getToken({
                vapidKey: this.vapidKey,
                serviceWorkerRegistration: this.swRegistration
            });
            
            if (this.token) {
                await this.sendTokenToServer(this.token);
            }
            
        } catch (error) {
            // Handle specific FCM errors gracefully
            if (error.code === 'messaging/permission-blocked' || 
                error.code === 'messaging/notifications-blocked') {
                // User blocked notifications - this is expected behavior
                return;
            }
            if (error.code === 'messaging/unsupported-browser') {
                // Browser doesn't support push - silently ignore
                return;
            }
            // Silent handling for other errors
        }
    }

    /**
     * Send FCM token to server
     */
    async sendTokenToServer(token) {
        try {
            const response = await fetch('/api/fcm/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ token })
            });
            
            const data = await response.json();
            // Token saved or failed silently
        } catch (error) {
            // Server communication failed - silent handling
        }
    }

    /**
     * Listen for foreground messages
     */
    listenForMessages() {
        if (this.messageHandlerRegistered) {
            return;
        }
        this.messageHandlerRegistered = true;
        
        this.messaging.onMessage((payload) => {
            // DEBUG: console.log('[Foreground] FCM message received');
            
            // Get notification content from DATA payload
            const title = payload.data?.title || 'Notification';
            const body = payload.data?.body || '';
            const icon = payload.data?.icon || '/images/icon-192x192.png';
            
            // Show notification
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, {
                    body: body,
                    icon: icon,
                    tag: 'fcm-notification'
                });
            }
            
            // Update bell icon
            if (typeof window.notificationHandler !== 'undefined') {
                window.notificationHandler.loadNotifications();
            }
            
            // Mark that foreground handled this message (for service worker to check)
            localStorage.setItem('fcm_foreground_handled', Date.now().toString());
        });
    }

    /**
     * Unsubscribe from FCM
     */
    async unsubscribe() {
        try {
            // Delete token from Firebase
            await this.messaging.deleteToken();
            
            // Remove token from server
            await fetch('/api/fcm/unsubscribe', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            // DEBUG: console.log('FCM unsubscribed successfully');
            this.token = null;
            
        } catch (error) {
            // Unsubscribe failed - silent handling
        }
    }

    /**
     * Refresh FCM token
     */
    async refreshToken() {
        try {
            await this.messaging.deleteToken();
            await this.requestPermission();
        } catch (error) {
            // Token refresh failed - silent handling
        }
    }
}

// Initialize FCM when DOM is ready (only once)
if (!window.fcmManager) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.fcmManager) {
                window.fcmManager = new FcmNotificationManager();
                window.fcmManager.init();
            }
        });
    } else {
        window.fcmManager = new FcmNotificationManager();
        window.fcmManager.init();
    }
}
