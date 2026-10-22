<?php

namespace App\Models;

use App\Traits\CapturesActivityCauser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Barangay extends Model
{
    use HasFactory, LogsActivity, CapturesActivityCauser;

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'is_active', 'has_scheduled_vaccination'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Barangay {$eventName}")
            ->useLogName('barangay');
    }

    protected $table = 'barangays';

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'has_scheduled_vaccination',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_scheduled_vaccination' => 'boolean',
    ];

    /**
     * Get health workers assigned to this barangay.
     */
    public function healthWorkers()
    {
        return $this->hasMany(HealthWorker::class, 'barangay_id');
    }

    /**
     * Get patients in this barangay.
     * Note: Uses string matching since patients.barangay is a text field.
     */
    public function patients()
    {
        return $this->hasMany(Patient::class, 'barangay', 'name');
    }

    /**
     * Get parents in this barangay.
     */
    public function parents()
    {
        return $this->hasMany(Parents::class, 'barangay', 'name');
    }

    /**
     * Scope to get only active barangays.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get barangays that can have vaccination schedules.
     * Excludes Kanluran since RHU is located there.
     */
    public function scopeSchedulable($query)
    {
        return $query->where('has_scheduled_vaccination', true);
    }

    /**
     * Get all active barangay names as an array.
     * Useful for dropdowns and validation.
     */
    public static function getActiveNames()
    {
        return static::active()->orderBy('name')->pluck('name')->toArray();
    }

    /**
     * Get barangay names that can have vaccination schedules.
     */
    public static function getSchedulableNames()
    {
        return static::active()->schedulable()->orderBy('name')->pluck('name')->toArray();
    }

    /**
     * Find barangay by name (case-insensitive).
     */
    public static function findByName($name)
    {
        return static::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
    }
}
