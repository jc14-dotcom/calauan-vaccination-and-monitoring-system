<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vaccine;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\VaccineStockResource;
use App\Http\Resources\VaccineStockCollection;

class VaccineStockController extends Controller
{
    /**
     * Get all vaccine stocks for real-time display
     * Uses Redis cache with 30-second TTL
     * Protected by Laravel's default rate limiting (6 requests/minute)
     * Returns data in API Resource format with metadata
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $cacheKey = 'vaccine_stocks_list';
            $cacheTTL = 30; // 30 seconds - matches frontend refresh rate
            
            // Check if cache exists BEFORE fetching
            $cacheHit = Cache::has($cacheKey);

            // Try to get data from cache
            $vaccines = Cache::remember($cacheKey, $cacheTTL, function () {
                // Fetch all vaccines with their stocks
                return Vaccine::select('vaccine_name', 'stocks', 'updated_at')
                    ->orderBy('vaccine_name', 'asc')
                    ->get();
            });

            // Create resource collection with metadata
            $collection = new VaccineStockCollection($vaccines);
            $collection->setCacheHit($cacheHit)->setCacheTTL($cacheTTL);

            // Return response with cache headers
            return $collection->toResponse(request())
                ->header('X-Cache-Hit', $cacheHit ? 'true' : 'false')
                ->header('X-Cache-TTL', $cacheTTL)
                ->header('Cache-Control', 'public, max-age=' . $cacheTTL);

        } catch (\Exception $e) {
            // Log error for debugging
            Log::error('Vaccine Stock API Error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Unable to fetch vaccine stocks',
                'message' => config('app.debug') ? $e->getMessage() : 'Server error'
            ], 500);
        }
    }
}
