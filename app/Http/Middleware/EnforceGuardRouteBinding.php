<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnforceGuardRouteBinding Middleware
 * 
 * This middleware ensures that the authenticated user's guard type matches
 * the route they're trying to access. This prevents:
 * 
 * 1. URL hijacking - parent user can't access health_worker routes by pasting URL
 * 2. Cross-guard access - logged in as one type but accessing another type's pages
 * 3. Privilege escalation - prevents accessing admin features without admin login
 * 
 * How it works:
 * - Each route group specifies which guard is required (parents or health_worker)
 * - This middleware checks if the CORRECT guard is authenticated
 * - If wrong guard is active: Redirect to appropriate dashboard or login
 * 
 * Usage in routes:
 * Route::middleware(['enforce.guard:parents'])->group(function () { ... });
 * Route::middleware(['enforce.guard:health_worker'])->group(function () { ... });
 * 
 * @package App\Http\Middleware
 */
class EnforceGuardRouteBinding
{
    /**
     * Guard to dashboard route mapping
     */
    protected array $guardDashboards = [
        'parents' => 'parent.dashboard',
        'health_worker' => 'health_worker.dashboard',
    ];
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $requiredGuard  The guard required for this route
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $requiredGuard): Response
    {
        // Check if the required guard is authenticated
        if (!Auth::guard($requiredGuard)->check()) {
            // Required guard is NOT authenticated
            
            // Check if another guard is authenticated (wrong user type)
            $activeGuard = $this->getActiveGuard();
            
            if ($activeGuard) {
                // User is logged in but with wrong account type
                // Redirect to their correct dashboard
                return $this->redirectToCorrectDashboard($request, $activeGuard, $requiredGuard);
            }
            
            // No guard is authenticated, redirect to login
            return $this->redirectToLogin($request);
        }
        
        // Double-check: Make sure no OTHER guard is also authenticated
        // This handles edge case where multiple guards are somehow active
        $this->ensureSingleGuard($requiredGuard);
        
        // Correct guard is authenticated, proceed
        return $next($request);
    }
    
    /**
     * Get the currently active guard
     *
     * @return string|null
     */
    protected function getActiveGuard(): ?string
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
     * Ensure only one guard is active by logging out others
     *
     * @param  string  $keepGuard  The guard to keep authenticated
     * @return void
     */
    protected function ensureSingleGuard(string $keepGuard): void
    {
        $guards = ['parents', 'health_worker', 'web'];
        
        foreach ($guards as $guard) {
            if ($guard !== $keepGuard && Auth::guard($guard)->check()) {
                // Another guard is active, log it out
                Auth::guard($guard)->logout();
            }
        }
    }
    
    /**
     * Redirect user to their correct dashboard
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $activeGuard  The guard that is actually authenticated
     * @param  string  $requiredGuard  The guard that was required for the route
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function redirectToCorrectDashboard(Request $request, string $activeGuard, string $requiredGuard): Response
    {
        // Get appropriate dashboard route
        $dashboardRoute = $this->guardDashboards[$activeGuard] ?? 'login';
        
        // Create user-friendly message
        $activeType = $this->getGuardDisplayName($activeGuard);
        $requiredType = $this->getGuardDisplayName($requiredGuard);
        
        $message = "Access denied. You are logged in as {$activeType} but tried to access a {$requiredType} page.";
        
        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error' => true,
                'message' => $message,
                'redirect' => route($dashboardRoute),
            ], 403);
        }
        
        // Redirect with message
        return redirect()->route($dashboardRoute)
            ->with('warning', $message)
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }
    
    /**
     * Redirect to login page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function redirectToLogin(Request $request): Response
    {
        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'authenticated' => false,
                'message' => 'Please log in to access this page.',
                'redirect' => route('login'),
            ], 401);
        }
        
        return redirect()->route('login')
            ->with('error', 'Please log in to access this page.')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }
    
    /**
     * Get display name for a guard
     *
     * @param  string  $guard
     * @return string
     */
    protected function getGuardDisplayName(string $guard): string
    {
        return match ($guard) {
            'parents' => 'Parent',
            'health_worker' => 'Health Worker/Admin',
            'web' => 'User',
            default => ucfirst(str_replace('_', ' ', $guard)),
        };
    }
}
