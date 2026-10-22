<?php

namespace App\Models;

use App\Traits\CapturesActivityCauser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PatientGrowthRecord extends Model
{
    use HasFactory, LogsActivity, CapturesActivityCauser;

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['patient_id', 'height', 'weight', 'recorded_date', 'measurement_type'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Growth record {$eventName}")
            ->useLogName('growth_record');
    }

    protected $fillable = [
        'patient_id', 
        'height', 
        'weight', 
        'recorded_date', 
        'recorded_by', 
        'measurement_type', 
        'notes'
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(HealthWorker::class, 'recorded_by');
    }
}
