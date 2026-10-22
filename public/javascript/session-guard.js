/**
 * Session Guard - Prevents accessing pages after logout
 * Only blocks back button AFTER user logs out, allows normal navigation while logged in
 * Middleware handles actual authentication
 */

(function() {
    'use strict';

    const LOGIN_URL = '/login';

    /**
     * Check if user has logged out and block access if true
     */
    function checkLogoutStatus() {
        const loggedOut = sessionStorage.getItem('user_logged_out');

        if (loggedOut === 'true') {
            // User logged out, clear flag and redirect
            sessionStorage.removeItem('user_logged_out');
            window.location.replace(LOGIN_URL);
            return true;
        }
        return false;
    }

    /**
     * Handle page loaded from cache (bfcache) - only after logout
     */
    function handleCacheLoad() {
        window.addEventListener('pageshow', function(event) {
            // If loaded from bfcache, check if user had logged out
            if (event.persisted) {
                const loggedOut = sessionStorage.getItem('user_logged_out');
                if (loggedOut === 'true') {
                    sessionStorage.removeItem('user_logged_out');
                    window.location.replace(LOGIN_URL);
                }
            }
        });
    }

    /**
     * Initialize
     */
    function init() {
        // Check if user logged out
        if (checkLogoutStatus()) {
            return;
        }

        // Only handle bfcache, don't prevent normal back/forward navigation
        handleCacheLoad();
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
