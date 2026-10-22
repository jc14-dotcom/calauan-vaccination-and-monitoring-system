<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientVaccineRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'vaccine_id',
        'dose_1_date',
        'dose_2_date',
        'dose_3_date',
        'dose_4_date',
        'dose_5_date',
        'dose_6_date',
        'dose_7_date',
        'dose_8_date',
        'dose_9_date',
        'dose_10_date',
        'remarks',
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

    public function getDoseDate(int $dose): ?string
    {
        $dose = max(1, min(10, $dose));
        return $this->{"dose_{$dose}_date"} ?? null;
    }

    public function getDoseDates(int $maxDoses = 10): array
    {
        $maxDoses = max(1, min(10, $maxDoses));
        $dates = [];

        for ($dose = 1; $dose <= $maxDoses; $dose++) {
            $dates[$dose] = $this->getDoseDate($dose);
        }

        return $dates;
    }
}
