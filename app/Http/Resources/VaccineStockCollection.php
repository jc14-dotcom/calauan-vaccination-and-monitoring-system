<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VaccineStockCollection extends ResourceCollection
{
    /**
     * Indicates if cache was hit
     */
    public $cacheHit = false;

    /**
     * Cache TTL in seconds
     */
    public $cacheTTL = 30;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'cached' => $this->cacheHit,
                'cache_expires_in' => $this->cacheHit ? $this->cacheTTL . ' seconds' : null,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * Set cache hit status
     */
    public function setCacheHit(bool $hit): self
    {
        $this->cacheHit = $hit;
        return $this;
    }

    /**
     * Set cache TTL
     */
    public function setCacheTTL(int $ttl): self
    {
        $this->cacheTTL = $ttl;
        return $this;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'status' => 'success',
        ];
    }
}
