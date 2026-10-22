/**
 * PWA Install Prompt Handler
 * This script handles:
 * - PWA install prompt detection and triggering
 * - App update detection
 * 
 * Note: Service worker is registered by fcm.js - this script uses the existing registration
 * Note: Push notifications are handled separately by FCM (fcm.js)
 */

class PWAManager {
    constructor() {
        this.registration = null;
        this.deferredPrompt = null;
    }

    /**
     * Initialize the PWA system
     */
    async init() {
        // Check if service workers are supported
        if (!('serviceWorker' in navigator)) {
            // console.log('Service Workers are not supported in this browser');
            return;
        }

        // Setup install prompt listener FIRST (before SW registration completes)
        // This is critical - beforeinstallprompt can fire early
        this.setupInstallPrompt();

        // Wait for existing service worker registration (fcm.js registers it)
        await this.waitForServiceWorker();
    }

    /**
     * Wait for existing service worker registration
     * fcm.js already registers sw.js, so we just wait for it
     */
    async waitForServiceWorker() {
        try {
            // Wait for service worker to be ready (registered by fcm.js)
            this.registration = await navigator.serviceWorker.ready;
            // console.log('Service Worker ready for PWA:', this.registration.scope);

            // Listen for service worker updates
            if (this.registration) {
                this.registration.addEventListener('updatefound', () => {
                    const newWorker = this.registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                this.showUpdateNotification();
                            }
                        });
                    }
                });
            }

        } catch (error) {
            // Service worker setup failed - silent handling for production
        }
    }

    /**
     * Setup install prompt listener for PWA
     */
    setupInstallPrompt() {
        // Listen for the beforeinstallprompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later
            this.deferredPrompt = e;
            // Show install button if it exists
            this.showInstallButton();
        });

        // Listen for app installed event
        window.addEventListener('appinstalled', () => {
            // console.log('PWA was installed');
            this.deferredPrompt = null;
            this.hideInstallButton();
        });
    }

    /**
     * Show the install button in UI
     */
    showInstallButton() {
        const installButton = document.getElementById('pwa-install-button');
        if (installButton) {
            installButton.classList.remove('hidden');
        }
    }

    /**
     * Hide the install button in UI
     */
    hideInstallButton() {
        const installButton = document.getElementById('pwa-install-button');
        if (installButton) {
            installButton.classList.add('hidden');
        }
    }

    /**
     * Trigger the install prompt
     */
    async promptInstall() {
        if (!this.deferredPrompt) {
            // console.log('No install prompt available');
            return false;
        }

        // Show the install prompt
        this.deferredPrompt.prompt();

        // Wait for the user to respond to the prompt
        const { outcome } = await this.deferredPrompt.userChoice;
        
        // console.log(`User response to the install prompt: ${outcome}`);

        // Clear the deferredPrompt
        this.deferredPrompt = null;

        return outcome === 'accepted';
    }

    /**
     * Show update notification when new service worker is available
     */
    showUpdateNotification() {
        // Check if there's an update notification container
        const updateContainer = document.getElementById('sw-update-notification');
        
        if (updateContainer) {
            updateContainer.classList.remove('hidden');
        } else {
            // Create a simple notification
            const notification = document.createElement('div');
            notification.id = 'sw-update-notification';
            notification.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-blue-600 text-white p-4 rounded-lg shadow-lg z-50 flex items-center justify-between';
            notification.innerHTML = `
                <span class="text-sm font-medium">A new version is available!</span>
                <button onclick="window.location.reload()" class="ml-4 px-3 py-1 bg-white text-blue-600 rounded text-sm font-semibold hover:bg-blue-50">
                    Refresh
                </button>
            `;
            document.body.appendChild(notification);
        }
    }

    /**
     * Check if app is installed
     */
    isInstalled() {
        // Check if running in standalone mode (installed PWA)
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true;
    }
}

// Initialize PWA Manager when DOM is ready
let pwaManager;

document.addEventListener('DOMContentLoaded', () => {
    pwaManager = new PWAManager();
    pwaManager.init();
});

// Export for use in other scripts
window.pwaManager = pwaManager;

// Helper function to trigger install from anywhere
window.installPWA = async function() {
    if (pwaManager) {
        return await pwaManager.promptInstall();
    }
    return false;
};
