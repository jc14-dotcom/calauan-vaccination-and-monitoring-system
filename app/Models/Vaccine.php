<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\CapturesActivityCauser;

class Vaccine extends Model
{
    use HasFactory, LogsActivity, CapturesActivityCauser;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('vaccine')
            ->logOnly(['vaccine_name', 'doses_description', 'total_doses', 'target_age_group', 'acronym', 'is_standard', 'is_active_in_reports', 'sort_order'])
            ->logOnlyDirty();
    }

    protected $fillable = [
        'vaccine_name',
        'doses_description',
        'stocks',
        'available_bottles',
        'doses_per_bottle',
        'doses_used_from_current_bottles',
        // Phase 1 — config columns
        'total_doses',
        'target_age_group',
        'acronym',
        'is_standard',
        'is_active_in_reports',
        'sort_order',
    ];

    protected $casts = [
        'stocks'                          => 'integer',
        'available_bottles'               => 'integer',
        'doses_per_bottle'                => 'integer',
        'doses_used_from_current_bottles' => 'integer',
        // Phase 1 — config columns
        'total_doses'                     => 'integer',
        'sort_order'                      => 'integer',
        'is_standard'                     => 'boolean',
        'is_active_in_reports'            => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Bust the vaccine config cache whenever a vaccine record is saved or deleted
        static::saved(function () {
            Cache::forget('vaccine_config');
        });

        static::deleted(function () {
            Cache::forget('vaccine_config');
        });
    }

    // Relationship with PatientVaccineRecord
    public function vaccineRecords()
    {
        return $this->hasMany(PatientVaccineRecord::class, 'vaccine_id');
    }

    // Phase 1 — dose config rows
    public function doseConfigs(): HasMany
    {
        return $this->hasMany(VaccineDoseConfig::class)->orderBy('dose_number');
    }

    public function getTotalDosesCount(): int
    {
        return max(1, min(10, (int) $this->total_doses));
    }
}
