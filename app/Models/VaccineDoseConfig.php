<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccineDoseConfig extends Model
{
    protected $fillable = [
        'vaccine_id',
        'dose_number',
        'min_age_months',
        'max_age_months',
    ];

    protected $casts = [
        'dose_number'     => 'integer',
        'min_age_months'  => 'decimal:2',
        'max_age_months'  => 'decimal:2',
    ];

    public function vaccine(): BelongsTo
    {
        return $this->belongsTo(Vaccine::class);
    }
}
