<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;

/**
 * Session Service
 * 
 * Handles multi-guard session verification and authentication status checks.
 * This service encapsulates the business logic for determining which guard
 * is currently authenticated and providing debug information for troubleshooting.
 */
class SessionService
{
    /**
     * Check authentication status across all guards
     * 
     * @return array ['authenticated' => bool, 'guard' => string|null, 'debug' => array]
     */
    public function checkAuthentication(): array
    {
        $authenticated = false;
        $guard = null;
        $debug = [];
        
        // Check parents guard first
        if (Auth::guard('parents')->check()) {
            $authenticated = true;
            $guard = 'parents';
            $debug['parents_user_id'] = Auth::guard('parents')->id();
        } 
        // Check health_worker guard
        elseif (Auth::guard('health_worker')->check()) {
            $authenticated = true;
            $guard = 'health_worker';
            $debug['health_worker_user_id'] = Auth::guard('health_worker')->id();
        }
        // Check default web guard
        elseif (Auth::guard('web')->check()) {
            $debug['web_guard_active'] = true;
            $debug['web_user_id'] = Auth::guard('web')->id();
        }
        
        // Add session information
        $debug['session_id'] = session()->getId();
        $debug['has_session'] = session()->isStarted();
        
        return [
            'authenticated' => $authenticated,
            'guard' => $guard,
            'debug' => $debug,
        ];
    }
    
    /**
     * Get the currently active guard
     * 
     * @return string|null The name of the active guard or null if none
     */
    public function getActiveGuard(): ?string
    {
        if (Auth::guard('parents')->check()) {
            return 'parents';
        }
        
        if (Auth::guard('health_worker')->check()) {
            return 'health_worker';
        }
        
        if (Auth::guard('web')->check()) {
            return 'web';
        }
        
        return null;
    }
    
    /**
     * Check if a specific guard is authenticated
     * 
     * @param string $guard The guard name to check
     * @return bool
     */
    public function isAuthenticated(string $guard): bool
    {
        return Auth::guard($guard)->check();
    }
    
    /**
     * Get debug information about the current session
     * 
     * @return array
     */
    public function getDebugInfo(): array
    {
        return [
            'session_id' => session()->getId(),
            'session_started' => session()->isStarted(),
            'parents_authenticated' => Auth::guard('parents')->check(),
            'health_worker_authenticated' => Auth::guard('health_worker')->check(),
            'web_authenticated' => Auth::guard('web')->check(),
            'active_guard' => $this->getActiveGuard(),
        ];
    }
}
