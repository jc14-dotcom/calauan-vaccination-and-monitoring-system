<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     * Adds headers to prevent browser caching of authenticated pages.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Add aggressive headers to prevent all forms of caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0, private, no-transform');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        
        // Prevent back-forward cache (bfcache)
        $response->headers->set('X-Accel-Expires', '0');
        
        return $response;
    }
}
