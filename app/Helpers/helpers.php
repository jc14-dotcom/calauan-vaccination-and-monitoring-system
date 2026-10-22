<?php

if (!function_exists('versioned_asset')) {
    /**
     * Generate a versioned asset URL based on file modification time.
     * This allows for long-term browser caching while ensuring updates are served immediately.
     * 
     * Benefits:
     * - Browser can cache assets indefinitely (1 year)
     * - When file changes, new version is automatically served
     * - No manual version bumping required
     * - Works with service worker caching
     * 
     * @param string $path The asset path (e.g., 'css/tailwind-full.css')
     * @return string The asset URL with version query parameter
     */
    function versioned_asset(string $path): string
    {
        $fullPath = public_path($path);
        
        if (file_exists($fullPath)) {
            $version = filemtime($fullPath);
            return asset($path) . '?v=' . $version;
        }
        
        // Fallback to unversioned if file doesn't exist
        return asset($path);
    }
}

if (!function_exists('cache_busted_asset')) {
    /**
     * Generate an asset URL that always bypasses cache.
     * Use this sparingly - only for assets that must never be cached.
     * 
     * @param string $path The asset path
     * @return string The asset URL with timestamp query parameter
     */
    function cache_busted_asset(string $path): string
    {
        return asset($path) . '?t=' . time();
    }
}

if (!function_exists('get_asset_version')) {
    /**
     * Get the version string for a specific asset.
     * Useful when you need the version separately.
     * 
     * @param string $path The asset path
     * @return string The version string (file modification time or current time)
     */
    function get_asset_version(string $path): string
    {
        $fullPath = public_path($path);
        
        if (file_exists($fullPath)) {
            return (string) filemtime($fullPath);
        }
        
        return (string) time();
    }
}

if (!function_exists('static_version')) {
    /**
     * Get a static version number that can be updated manually during deployments.
     * This is useful for invalidating all cached assets at once.
     * 
     * Set ASSET_VERSION in .env to manually control the version.
     * 
     * @return string The static version string
     */
    function static_version(): string
    {
        return config('app.asset_version', '1.0.0');
    }
}

if (!function_exists('formatProperName')) {
    /**
     * Format a name with proper title case capitalization.
     * Each word's first letter is capitalized, rest lowercase.
     * Handles special cases like hyphenated names and apostrophes.
     * 
     * Examples:
     * - "jhon lloyd" -> "Jhon Lloyd"
     * - "dela cruz" -> "Dela Cruz"
     * - "o'brien" -> "O'Brien"
     * - "anne-marie" -> "Anne-Marie"
     * 
     * @param string|null $name The name to format
     * @return string|null The formatted name
     */
    function formatProperName(?string $name): ?string
    {
        if (empty($name)) {
            return $name;
        }
        
        $name = trim($name);
        
        // Convert to lowercase first for consistent processing
        $name = mb_strtolower($name, 'UTF-8');
        
        // Capitalize first letter of each word (handles spaces)
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
        
        // Handle apostrophes (e.g., O'brien -> O'Brien)
        $name = preg_replace_callback(
            "/('|\x{2019})([a-z])/u",
            function ($matches) {
                return $matches[1] . mb_strtoupper($matches[2], 'UTF-8');
            },
            $name
        );
        
        // Handle hyphenated names (e.g., Anne-marie -> Anne-Marie)
        $name = preg_replace_callback(
            "/-([a-z])/u",
            function ($matches) {
                return '-' . mb_strtoupper($matches[1], 'UTF-8');
            },
            $name
        );
        
        return $name;
    }
}

if (!function_exists('formatMiddleInitial')) {
    /**
     * Format a middle initial to uppercase.
     * Ensures the middle initial is always capitalized.
     * 
     * Examples:
     * - "g" -> "G"
     * - "g." -> "G"
     * - "G." -> "G"
     * 
     * @param string|null $initial The middle initial to format
     * @return string|null The formatted middle initial (without period)
     */
    function formatMiddleInitial(?string $initial): ?string
    {
        if (empty($initial)) {
            return $initial;
        }
        
        // Remove any periods and trim
        $initial = trim(str_replace('.', '', $initial));
        
        // Take only the first character and uppercase it
        if (strlen($initial) > 0) {
            return mb_strtoupper(mb_substr($initial, 0, 1, 'UTF-8'), 'UTF-8');
        }
        
        return $initial;
    }
}
