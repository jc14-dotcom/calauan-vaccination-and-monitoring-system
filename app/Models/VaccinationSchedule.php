<?php

namespace App\Models;

use App\Traits\CapturesActivityCauser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VaccinationSchedule extends Model
{
    use HasFactory, LogsActivity, CapturesActivityCauser;

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['vaccination_date', 'barangay', 'status', 'cancelled_at', 'cancellation_reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Vaccination schedule {$eventName}")
            ->useLogName('schedule');
    }

    protected $fillable = [
        'vaccination_date',
        'barangay',
        'status',
        'notes',
        'created_by',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by'
    ];

    protected $casts = [
        'vaccination_date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Relationship: Schedule belongs to a health worker (creator)
     */
    public function healthWorker()
    {
        return $this->belongsTo(HealthWorker::class, 'created_by');
    }

    /**
     * Relationship: Schedule cancelled by a health worker
     */
    public function cancelledBy()
    {
        return $this->belongsTo(HealthWorker::class, 'cancelled_by');
    }

    /**
     * Scope: Get only scheduled (upcoming) vaccination days
     */
    public function scopeUpcoming($query)
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        return $query->where('status', 'scheduled')
                     ->where('vaccination_date', '>=', Carbon::today('Asia/Manila'));
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // return $query->where('status', 'scheduled')
        //              ->where('vaccination_date', '>=', Carbon::today());
    }

    /**
     * Scope: Get today's schedules
     */
    public function scopeToday($query)
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        return $query->whereDate('vaccination_date', Carbon::today('Asia/Manila'));
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // return $query->whereDate('vaccination_date', Carbon::today());
    }

    /**
     * Scope: Get active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if schedule is for today
     */
    public function isToday()
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $todayPHT = Carbon::today('Asia/Manila');
        $vaccDate = Carbon::parse($this->vaccination_date);
        return $vaccDate->isSameDay($todayPHT);
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // return $this->vaccination_date->isToday();
    }

    /**
     * Check if schedule is upcoming (future date)
     */
    public function isUpcoming()
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $todayPHT = Carbon::today('Asia/Manila');
        $vaccDate = Carbon::parse($this->vaccination_date);
        return $vaccDate->isAfter($todayPHT);
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // return $this->vaccination_date->isFuture();
    }

    /**
     * Check if schedule is past
     */
    public function isPast()
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $todayPHT = Carbon::today('Asia/Manila');
        $vaccDate = Carbon::parse($this->vaccination_date);
        return $vaccDate->isBefore($todayPHT);
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // return $this->vaccination_date->isPast() && !$this->vaccination_date->isToday();
    }

    /**
     * Check if schedule is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if schedule can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['scheduled', 'active']) && !$this->isCancelled();
    }

    /**
     * Get days until vaccination
     */
    public function daysUntil()
    {
        if ($this->isToday()) {
            return 'Today';
        }
        
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $days = Carbon::today('Asia/Manila')->diffInDays($this->vaccination_date, false);
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // $days = Carbon::today()->diffInDays($this->vaccination_date, false);
        
        if ($days < 0) {
            return 'Past';
        }
        
        return $days == 1 ? 'Tomorrow' : $days . ' days';
    }
}
