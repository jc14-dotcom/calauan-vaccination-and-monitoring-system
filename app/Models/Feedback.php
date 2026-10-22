<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'parent_id',
        'vaccination_schedule_id',
        'barangay',
        'content',
        'submitted_at',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    
    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }
    
    public function vaccinationSchedule()
    {
        return $this->belongsTo(VaccinationSchedule::class, 'vaccination_schedule_id');
    }
}
