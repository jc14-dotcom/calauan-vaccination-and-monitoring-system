<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaccineStockResource extends JsonResource
{
    /**
     * Transform the vaccine stock resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vaccine' => $this->vaccine_name,
            'stock' => $this->stocks ?? 0,
            'unit' => 'doses',
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'status' => $this->getStockStatus(),
        ];
    }

    /**
     * Get stock status based on current stock level
     */
    private function getStockStatus(): string
    {
        $stock = $this->stocks ?? 0;
        
        if ($stock === 0) {
            return 'out_of_stock';
        } elseif ($stock < 10) {
            return 'low';
        } elseif ($stock < 50) {
            return 'medium';
        } else {
            return 'high';
        }
    }
}
