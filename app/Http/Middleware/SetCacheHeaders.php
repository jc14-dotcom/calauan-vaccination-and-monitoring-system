<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Middleware to set appropriate cache headers for different types of responses
 * 
 * This middleware implements a tiered caching strategy:
 * - Static assets (CSS, JS, fonts, images): Long-term caching with immutable flag
 * - Dynamic HTML pages: No caching (always fresh)
 * - API responses: No caching
 */
class SetCacheHeaders
{
    /**
     * Predefined cache presets for common use cases
     */
    protected array $presets = [
        'immutable' => [
            'public' => true,
            'max_age' => 31536000,
            'immutable' => true,
        ],
        'static' => [
            'public' => true,
            'max_age' => 604800,
            'must_revalidate' => true,
        ],
        'images' => [
            'public' => true,
            'max_age' => 2592000,
        ],
        'fonts' => [
            'public' => true,
            'max_age' => 31536000,
            'immutable' => true,
        ],
        'none' => [
            'no_cache' => true,
            'no_store' => true,
            'must_revalidate' => true,
            'private' => true,
            'max_age' => 0,
        ],
        'short' => [
            'public' => true,
            'max_age' => 300,
            'must_revalidate' => true,
        ],
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$options): Response
    {
        $response = $next($request);
        
        if (!$response->isSuccessful()) {
            return $response;
        }
        
        if ($response->headers->has('Cache-Control') && 
            !str_contains($response->headers->get('Cache-Control'), 'no-cache')) {
            return $response;
        }
        
        $cacheOptions = $this->parseOptions($options);
        $cacheControl = $this->buildCacheControlHeader($cacheOptions);
        
        $response->headers->set('Cache-Control', $cacheControl);
        
        if (!$response->headers->has('ETag') && $response instanceof BinaryFileResponse === false) {
            $content = $response->getContent();
            if ($content) {
                $etag = md5($content);
                $response->headers->set('ETag', '"' . $etag . '"');
            }
        }
        
        if (!$response->headers->has('Vary')) {
            $response->headers->set('Vary', 'Accept-Encoding');
        }
        
        return $response;
    }

    /**
     * Parse the options passed to the middleware
     */
    protected function parseOptions(array $options): array
    {
        if (count($options) === 1 && isset($this->presets[$options[0]])) {
            return $this->presets[$options[0]];
        }
        
        $parsed = [];
        foreach ($options as $option) {
            if (str_contains($option, '=')) {
                [$key, $value] = explode('=', $option, 2);
                $parsed[str_replace('-', '_', $key)] = is_numeric($value) ? (int)$value : $value;
            } else {
                $parsed[str_replace('-', '_', $option)] = true;
            }
        }
        
        return $parsed;
    }

    /**
     * Build the Cache-Control header value from options
     */
    protected function buildCacheControlHeader(array $options): string
    {
        $directives = [];
        
        if (!empty($options['public'])) {
            $directives[] = 'public';
        } elseif (!empty($options['private'])) {
            $directives[] = 'private';
        }
        
        if (isset($options['max_age'])) {
            $directives[] = 'max-age=' . $options['max_age'];
        }
        
        if (isset($options['s_maxage'])) {
            $directives[] = 's-maxage=' . $options['s_maxage'];
        }
        
        if (!empty($options['immutable'])) {
            $directives[] = 'immutable';
        }
        
        if (!empty($options['must_revalidate'])) {
            $directives[] = 'must-revalidate';
        }
        
        if (!empty($options['no_cache'])) {
            $directives[] = 'no-cache';
        }
        
        if (!empty($options['no_store'])) {
            $directives[] = 'no-store';
        }
        
        if (!empty($options['no_transform'])) {
            $directives[] = 'no-transform';
        }
        
        if (isset($options['stale_while_revalidate'])) {
            $directives[] = 'stale-while-revalidate=' . $options['stale_while_revalidate'];
        }
        
        if (isset($options['stale_if_error'])) {
            $directives[] = 'stale-if-error=' . $options['stale_if_error'];
        }
        
        return implode(', ', $directives);
    }
}
