<?php

namespace App\Models;

use App\Traits\CapturesActivityCauser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VaccinationTransaction extends Model
{
    use HasFactory, LogsActivity, CapturesActivityCauser;

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['patient_id', 'vaccine_id', 'dose_number', 'doses_deducted', 'vaccinated_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Vaccination transaction {$eventName}")
            ->useLogName('vaccination');
    }

    protected $table = 'vaccination_transactions';

    protected $fillable = [
        'vaccination_session_id',
        'vaccination_schedule_id',
        'patient_id',
        'vaccine_id',
        'patient_vaccine_record_id',
        'dose_number',
        'doses_deducted',
        'vaccinated_at',
        'vaccinated_by',
        'notes',
        'administered_elsewhere',
        'stock_override',
    ];

    protected $casts = [
        'vaccinated_at' => 'date',
        'dose_number' => 'integer',
        'doses_deducted' => 'integer',
        'administered_elsewhere' => 'boolean',
        'stock_override' => 'boolean',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function vaccine()
    {
        return $this->belongsTo(Vaccine::class, 'vaccine_id');
    }

    public function patientVaccineRecord()
    {
        return $this->belongsTo(PatientVaccineRecord::class, 'patient_vaccine_record_id');
    }

    public function vaccinator()
    {
        return $this->belongsTo(HealthWorker::class, 'vaccinated_by');
    }

    public function vaccinationSchedule()
    {
        return $this->belongsTo(VaccinationSchedule::class, 'vaccination_schedule_id');
    }

    /**
     * Get dose label for display (Dose 1, Dose 2, Dose 3)
     */
    public function getDoseLabelAttribute()
    {
        return 'Dose ' . $this->dose_number;
    }

    /**
     * Scope to get transactions for a specific vaccine
     */
    public function scopeForVaccine($query, $vaccineId)
    {
        return $query->where('vaccine_id', $vaccineId);
    }

    /**
     * Scope to get transactions for a specific patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to get transactions within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('vaccinated_at', [$startDate, $endDate]);
    }

    /**
     * Prevent updates to transaction records (immutable audit log)
     * 
     * @return bool
     */
    public function isImmutable()
    {
        return true; // Transactions should not be modified after creation
    }

    /**
     * Override update to prevent modifications
     */
    public function update(array $attributes = [], array $options = [])
    {
        // Allow update only if it's a new record (not yet saved)
        if ($this->exists && $this->isImmutable()) {
            \Log::warning('Attempted to modify immutable vaccination transaction', [
                'transaction_id' => $this->id,
                'patient_id' => $this->patient_id,
                'vaccine_id' => $this->vaccine_id,
            ]);
            return false;
        }
        
        return parent::update($attributes, $options);
    }
}
