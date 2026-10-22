<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateSessionToken Middleware
 * 
 * This middleware validates that the user's session token matches the token
 * stored in the database. This prevents:
 * 
 * 1. Session hijacking - stolen session IDs won't work without valid token
 * 2. Multiple active sessions - new login invalidates old token
 * 3. URL manipulation attacks - copying URLs after logout won't work
 * 
 * How it works:
 * - On login: A unique token is generated and stored in both session and database
 * - On each request: This middleware compares session token with database token
 * - If tokens don't match: User is logged out and redirected to login
 * - On logout: Token is cleared from database
 * 
 * @package App\Http\Middleware
 */
class ValidateSessionToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard  The guard to validate token for
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        // Determine which guard to check
        $activeGuard = $guard ?? $this->detectActiveGuard();
        
        if (!$activeGuard) {
            // No active guard, let other middleware handle this
            return $next($request);
        }
        
        // Get the authenticated user
        $user = Auth::guard($activeGuard)->user();
        
        if (!$user) {
            return $this->handleInvalidSession($request, 'No authenticated user found.');
        }
        
        // Get session token from session
        $sessionToken = session('session_token');
        
        // Get session token from database
        $dbToken = $user->session_token;
        
        // Validate tokens - detect specific reason for failure
        if (empty($sessionToken) || empty($dbToken)) {
            // Session token is empty - this is a simple session timeout
            // The session data was cleared by Laravel when it expired
            return $this->handleInvalidSession($request, 'session_expired', $activeGuard);
        }
        
        if (!hash_equals($dbToken, $sessionToken)) {
            // Tokens don't match - user logged in from another location
            // The database token was updated when they logged in elsewhere
            return $this->handleInvalidSession($request, 'logged_in_elsewhere', $activeGuard);
        }
        
        // Token is valid, proceed with request
        return $next($request);
    }
    
    /**
     * Detect which guard is currently active
     *
     * @return string|null
     */
    protected function detectActiveGuard(): ?string
    {
        if (Auth::guard('parents')->check()) {
            return 'parents';
        }
        
        if (Auth::guard('health_worker')->check()) {
            return 'health_worker';
        }
        
        return null;
    }
    
    /**
     * Handle invalid session - logout user and redirect
     * 
     * NOTE: We intentionally do NOT clear the session_token from the database here.
     * This is because the token in the DB belongs to the legitimate session (e.g., Browser B).
     * We only clear the local session data for THIS request (Browser A with old token).
     * 
     * If we cleared the DB token here, it would invalidate ALL sessions including
     * the legitimate one that just logged in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $reason  The reason for invalid session: 'session_expired' or 'logged_in_elsewhere'
     * @param  string|null  $guard
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvalidSession(Request $request, string $reason, ?string $guard = null): Response
    {
        // Logout from the specific guard if provided (local session only)
        if ($guard) {
            // DO NOT clear the session token in database!
            // The DB token belongs to the legitimate session (newer login)
            // We only logout this local session
            Auth::guard($guard)->logout();
        }
        
        // Clear all LOCAL session data (does not affect database token)
        $request->session()->flush();
        $request->session()->regenerateToken();
        $request->session()->regenerate();
        
        // Determine the appropriate message based on the reason
        $userMessage = match($reason) {
            'logged_in_elsewhere' => 'You have been logged out because your account was accessed from another location.',
            'session_expired' => 'Your session has expired. Please log in again.',
            default => 'Your session has expired. Please log in again.',
        };
        
        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'authenticated' => false,
                'message' => $userMessage,
                'reason' => $reason,
                'redirect' => route('login'),
            ], 401);
        }
        
        // Redirect to login with error message
        return redirect()->route('login')
            ->with('error', $userMessage)
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }
}
