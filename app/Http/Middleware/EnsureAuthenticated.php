<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    /**
     * Handle an incoming request.
     * Verify that the user is still authenticated and session is valid.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $guard = null): Response
    {
        // Check if the specified guard is authenticated
        if (!Auth::guard($guard)->check()) {
            // If it's an AJAX request, return JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'authenticated' => false,
                    'message' => 'Session expired'
                ], 401);
            }
            
            // Redirect to login page (not welcome)
            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please log in again.');
        }
        
        // User is authenticated, proceed with request
        $response = $next($request);
        
        // Add aggressive no-cache headers to prevent browser caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0, private, no-transform');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        $response->headers->set('X-Accel-Expires', '0');
        
        return $response;
    }
}
