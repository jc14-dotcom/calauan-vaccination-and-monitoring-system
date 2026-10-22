/**
 * Logout Helper - Marks logout and clears data
 * Works with session-guard.js to prevent back button access after logout
 */

(function() {
    'use strict';
    
    // Set logout marker that session-guard.js will check
    window.markUserLoggedOut = function() {
        sessionStorage.setItem('user_logged_out', 'true');
    };
    
    // Clear logout marker on login page
    if (window.location.pathname === '/' || window.location.pathname === '/login') {
        sessionStorage.removeItem('user_logged_out');
    }
})();
