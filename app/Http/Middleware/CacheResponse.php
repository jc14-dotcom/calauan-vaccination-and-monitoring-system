<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    /**
     * Handle an incoming request and cache the response
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $minutes = 10): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }
        
        // Don't cache inventory pages (need real-time stock data)
        if ($request->is('health_worker/inventory') || $request->is('inventory') || $request->is('inventory/*')) {
            return $next($request);
        }
        
        // Don't cache if user is authenticated as admin (for real-time data)
        // You can customize this logic based on your needs
        
        // Generate cache key based on URL and query parameters
        $cacheKey = $this->getCacheKey($request);
        
        // Try to get cached response
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse !== null) {
            // Return cached response with header indicating it's from cache
            return response($cachedResponse['content'])
                ->withHeaders($cachedResponse['headers'])
                ->header('X-Cache-Hit', 'true')
                ->header('X-Cache-Key', $cacheKey);
        }
        
        // Process request
        $response = $next($request);
        
        // Only cache successful responses
        if ($response->isSuccessful() && $response->getStatusCode() === 200) {
            // Cache the response content and headers
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'headers' => $this->getCacheableHeaders($response)
            ], now()->addMinutes($minutes));
            
            // Add header to indicate this is a fresh response
            $response->headers->set('X-Cache-Hit', 'false');
        }
        
        return $response;
    }
    
    /**
     * Generate cache key from request
     */
    protected function getCacheKey(Request $request): string
    {
        // Include URL path and sorted query parameters
        $queryParams = $request->query();
        ksort($queryParams);
        
        return 'response_cache:' . md5(
            $request->path() . '?' . http_build_query($queryParams)
        );
    }
    
    /**
     * Get headers that should be cached
     */
    protected function getCacheableHeaders(Response $response): array
    {
        $headers = [];
        $cacheableHeaders = ['Content-Type', 'Content-Language'];
        
        foreach ($cacheableHeaders as $header) {
            if ($response->headers->has($header)) {
                $headers[$header] = $response->headers->get($header);
            }
        }
        
        return $headers;
    }
}
