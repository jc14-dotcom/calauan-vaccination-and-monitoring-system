<?php

namespace App\Models;

use App\Traits\CapturesActivityCauser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Patient extends Model
{
    use HasFactory, LogsActivity, CapturesActivityCauser;

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate QR UUID when creating a new patient
        static::creating(function ($patient) {
            if (empty($patient->qr_uuid)) {
                $patient->qr_uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Configure activity logging options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'date_of_birth', 'barangay', 'sex', 'parent_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Patient {$eventName}")
            ->useLogName('patient');
    }

    protected $table = 'patients'; // Name of the table
    protected $fillable = [
        'parent_id', 
        'name',
        'first_name',
        'middle_initial',
        'last_name',
        'suffix',
        'date_of_birth',
        'place_of_birth',
        'birth_height',
        'birth_weight',
        'barangay',
        'address',
        'mother_name',
        'father_name',
        'contact_no',
        'sex',
        'profile_image',
        'qr_uuid',
    ];

    protected $casts = [
        // All fields decrypted for performance and analytics except address
        // 'name' => 'encrypted',
        // 'date_of_birth' => 'encrypted',
        // 'barangay' => 'encrypted',
        // 'contact_no' => 'encrypted',
        // 'mother_name' => 'encrypted',
        // 'father_name' => 'encrypted',
        // 'place_of_birth' => 'encrypted',  // Decrypted for birth location reports
        // 'birth_height' => 'encrypted',    // Decrypted for growth analytics
        // 'birth_weight' => 'encrypted',    // Decrypted for nutrition monitoring
        
        // Address encryption handled manually via accessor to catch decryption errors
        // 'address' => 'encrypted',
    ];

    /**
     * =====================================================
     * NAME FORMATTING MUTATORS
     * Auto-capitalize names when saving to database
     * Format: First letter of each word capitalized
     * =====================================================
     */

    /**
     * Set the first_name attribute with proper capitalization.
     * Example: "jhon lloyd" -> "Jhon Lloyd"
     */
    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = formatProperName($value);
    }

    /**
     * Set the last_name attribute with proper capitalization.
     * Example: "dela cruz" -> "Dela Cruz"
     */
    public function setLastNameAttribute($value): void
    {
        $this->attributes['last_name'] = formatProperName($value);
    }

    /**
     * Set the middle_initial attribute with uppercase.
     * Example: "g" -> "G"
     */
    public function setMiddleInitialAttribute($value): void
    {
        $this->attributes['middle_initial'] = formatMiddleInitial($value);
    }

    /**
     * Set the mother_name attribute with proper capitalization.
     * Example: "maria santos" -> "Maria Santos"
     */
    public function setMotherNameAttribute($value): void
    {
        $this->attributes['mother_name'] = formatProperName($value);
    }

    /**
     * Set the father_name attribute with proper capitalization.
     * Example: "juan dela cruz" -> "Juan Dela Cruz"
     */
    public function setFatherNameAttribute($value): void
    {
        $this->attributes['father_name'] = formatProperName($value);
    }

    /**
     * Set the name attribute with proper capitalization.
     * This is the legacy full name field.
     * Example: "jhon lloyd g. cruz" -> "Jhon Lloyd G. Cruz"
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = formatProperName($value);
    }

    /**
     * =====================================================
     * END NAME FORMATTING MUTATORS
     * =====================================================
     */

    /**
     * Get the address attribute with safe decryption.
     * Returns empty string if decryption fails (corrupted data or wrong APP_KEY).
     *
     * @param mixed $value
     * @return string
     */
    public function getAddressAttribute($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Return empty string if decryption fails (corrupted or plain text)
            // Try returning as-is in case it's stored unencrypted
            return is_string($value) ? $value : '';
        }
    }

    /**
     * Set the address attribute with encryption.
     *
     * @param string|null $value
     * @return void
     */
    public function setAddressAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['address'] = null;
            return;
        }

        $this->attributes['address'] = \Illuminate\Support\Facades\Crypt::encryptString($value);
    }

    /**
     * Get the URL for the patient's profile image.
     * Returns null if no image exists.
     *
     * @return string|null
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image) {
            return null;
        }
        
        return asset('storage/children/' . $this->profile_image);
    }

    /**
     * Get the initials for the patient's name (fallback when no photo).
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        $nameParts = explode(' ', $this->name ?? '');
        $initials = '';
        
        // Get first letter of first name
        if (isset($nameParts[0]) && strlen($nameParts[0]) > 0) {
            $initials .= strtoupper(substr($nameParts[0], 0, 1));
        }
        
        // Get first letter of last name (or second name)
        if (count($nameParts) > 1) {
            $lastName = end($nameParts);
            if (strlen($lastName) > 0) {
                $initials .= strtoupper(substr($lastName, 0, 1));
            }
        }
        
        return $initials ?: '??';
    }

    /**
     * Check if patient has a profile image.
     *
     * @return bool
     */
    public function hasProfileImage(): bool
    {
        return !empty($this->profile_image) && Storage::disk('public')->exists('children/' . $this->profile_image);
    }

    /**
     * Scope to filter patients by the current health worker's assigned barangay.
     * - RHU admin (barangay_id = NULL): sees all patients
     * - Barangay worker: sees only patients in their assigned barangay
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\HealthWorker|null $healthWorker Optional health worker (defaults to current auth user)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForHealthWorker($query, $healthWorker = null)
    {
        $healthWorker = $healthWorker ?? Auth::guard('health_worker')->user();
        
        // No health worker or RHU admin = no filter (see all)
        if (!$healthWorker || $healthWorker->isRHU()) {
            return $query;
        }
        
        // Barangay worker = filter by their assigned barangay
        $barangayName = $healthWorker->getAssignedBarangayName();
        if ($barangayName) {
            return $query->where('barangay', $barangayName);
        }
        
        return $query;
    }

    /**
     * Scope to filter patients by a specific barangay name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $barangayName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInBarangay($query, ?string $barangayName)
    {
        if (empty($barangayName)) {
            return $query;
        }
        return $query->where('barangay', $barangayName);
    }

    // Define the relationship with the Parents model
    public function parent()
    {
        return $this->belongsTo(Parents::class, 'parent_id');
    }

    // Define the relationship with the PatientVaccineRecord model
    public function vaccines()
    {
        return $this->hasMany(PatientVaccineRecord::class, 'patient_id', 'id');
    }

    // Add these relationships at the end of the class, before the closing brace
    public function growthRecords()
    {
        return $this->hasMany(PatientGrowthRecord::class)->orderBy('recorded_date', 'desc')->orderBy('id', 'desc');
    }

    public function latestGrowthRecord()
    {
        return $this->hasOne(PatientGrowthRecord::class)->orderBy('recorded_date', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Get unique growth records (one per day, keeping the latest entry for each day)
     * This prevents showing multiple records from the same day in the history
     */
    public function getUniqueGrowthRecordsAttribute()
    {
        return $this->growthRecords()
            ->get()
            ->groupBy(function ($record) {
                return \Carbon\Carbon::parse($record->recorded_date)->format('Y-m-d');
            })
            ->map(function ($records) {
                return $records->first(); // Get the latest record for each day (already sorted by id desc)
            })
            ->values();
    }

    // Helper method to get current height with fallback to birth height
    public function getCurrentHeight()
    {
        $latest = $this->latestGrowthRecord;
        return $latest ? $latest->height : $this->birth_height;
    }

    // Helper method to get current weight with fallback to birth weight
    public function getCurrentWeight()
    {
        $latest = $this->latestGrowthRecord;
        return $latest ? $latest->weight : $this->birth_weight;
    }

    // Helper method to get the date of current measurement
    public function getCurrentMeasurementDate()
    {
        $latest = $this->latestGrowthRecord;
        return $latest ? $latest->recorded_date : null;
    }

    // Helper method to get formatted age for infants/children
    public function getFormattedAgeAttribute()
    {
        if (!$this->date_of_birth) {
            return 'N/A';
        }
        
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $now = \Carbon\Carbon::now('Asia/Manila');
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $now = \Carbon\Carbon::now();
        
        // Use floor() to get whole numbers only (no decimals)
        $ageInYears = (int) $birthDate->diffInYears($now);
        $ageInMonths = (int) $birthDate->diffInMonths($now);
        $ageInDays = (int) $birthDate->diffInDays($now);
        
        // For children 2 years and older, show years
        if ($ageInYears >= 2) {
            return $ageInYears . ' ' . ($ageInYears == 1 ? 'year' : 'years') . ' old';
        }
        
        // For children 1-23 months, show months
        if ($ageInMonths >= 1) {
            return $ageInMonths . ' ' . ($ageInMonths == 1 ? 'month' : 'months') . ' old';
        }
        
        // For newborns less than 1 month
        if ($ageInDays < 7) {
            return $ageInDays . ' ' . ($ageInDays == 1 ? 'day' : 'days') . ' old';
        } else {
            $ageInWeeks = (int) floor($ageInDays / 7);
            return $ageInWeeks . ' ' . ($ageInWeeks == 1 ? 'week' : 'weeks') . ' old';
        }
    }

    // Add this relationship if it's missing:
    public function vaccineRecords()
    {
        return $this->hasMany(PatientVaccineRecord::class);
    }

    // Relationship to vaccination transactions
    public function vaccinationTransactions()
    {
        return $this->hasMany(VaccinationTransaction::class);
    }

    /**
     * Build the full name from separate fields.
     * Format: "FirstName M.I. LastName Suffix"
     * 
     * @return string
     */
    public function buildFullName(): string
    {
        $parts = [];
        
        if (!empty($this->first_name)) {
            $parts[] = trim($this->first_name);
        }
        
        if (!empty($this->middle_initial)) {
            // Ensure M.I. has a period
            $mi = trim($this->middle_initial);
            $mi = rtrim($mi, '.');
            $parts[] = $mi . '.';
        }
        
        if (!empty($this->last_name)) {
            $parts[] = trim($this->last_name);
        }
        
        if (!empty($this->suffix)) {
            $parts[] = trim($this->suffix);
        }
        
        return implode(' ', $parts);
    }

    /**
     * Get formatted name as "Surname, First Name M.I. Suffix"
     * Uses separate name fields if available, otherwise falls back to parsing the 'name' field.
     * 
     * @return string
     */
    public function getFormattedNameAttribute(): string
    {
        // If we have the new separate fields, use them
        if (!empty($this->last_name) && !empty($this->first_name)) {
            $formatted = $this->last_name . ', ' . $this->first_name;
            
            if (!empty($this->middle_initial)) {
                $mi = trim($this->middle_initial);
                $mi = rtrim($mi, '.') . '.';
                $formatted .= ' ' . $mi;
            }
            
            if (!empty($this->suffix)) {
                $formatted .= ' ' . trim($this->suffix);
            }
            
            return $formatted;
        }
        
        // Fallback: Parse the legacy 'name' field
        if (!$this->name) {
            return 'N/A';
        }

        $parts = preg_split('/\s+/', trim($this->name));
        
        if (count($parts) < 2) {
            return $this->name; // Return as-is if format is unexpected
        }

        // Last word is the last name (unless it's a suffix)
        $suffixes = ['Jr.', 'Jr', 'Sr.', 'Sr', 'II', 'III', 'IV', 'V'];
        $suffix = '';
        $lastName = array_pop($parts);
        
        // Check if last word is a suffix
        if (in_array($lastName, $suffixes) && count($parts) >= 2) {
            $suffix = ' ' . $lastName;
            $lastName = array_pop($parts);
        }
        
        // Check if the now-last part is a middle initial
        $middleInitial = '';
        if (count($parts) > 1) {
            $potentialMI = end($parts);
            // Match: single letter OR letter with period (B, B., T, T.)
            if (preg_match('/^[A-Za-z]\.?$/', $potentialMI)) {
                $middleInitial = ' ' . rtrim(array_pop($parts), '.') . '.';
            }
        }
        
        // Everything remaining is first name(s)
        $firstName = implode(' ', $parts);
        
        return $lastName . ', ' . $firstName . $middleInitial . $suffix;
    }

    /**
     * Check if patient has the new separate name fields populated.
     * 
     * @return bool
     */
    public function hasNewNameFields(): bool
    {
        return !empty($this->first_name) && !empty($this->last_name);
    }

    /**
     * Check if patient is fully immunized (all vaccines completed)
     * A patient is considered fully immunized if all their vaccine records have all required doses filled
     */
    public function isFullyImmunized()
    {
        $vaccineRecords = $this->vaccines()->with('vaccine')->get();
        if ($vaccineRecords->isEmpty()) {
            return false;
        }

        foreach ($vaccineRecords as $record) {
            $vaccine = $record->vaccine;
            if (!$vaccine) {
                continue;
            }

            $requiredDoses = max(1, min(10, (int) ($vaccine->total_doses ?? 1)));

            for ($dose = 1; $dose <= $requiredDoses; $dose++) {
                $field = "dose_{$dose}_date";
                if (empty($record->{$field})) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if patient has any incomplete vaccines
     */
    public function hasIncompleteVaccines()
    {
        return !$this->isFullyImmunized();
    }

    /**
     * Get patient's age group at a specific target date
     * Used for determining eligible population categories
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return string (under_1_year, 0_12_months, 13_23_months, grade_1, grade_7, older)
     */
    public function getEligibleAgeGroup($targetDate = null)
    {
        if (!$this->date_of_birth) {
            return 'unknown';
        }

        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now('Asia/Manila');
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        $ageInMonths = $birthDate->diffInMonths($targetDate);

        // Determine age group
        if ($ageInMonths < 12) {
            return 'under_1_year';
        } elseif ($ageInMonths <= 12) {
            return '0_12_months';
        } elseif ($ageInMonths >= 13 && $ageInMonths <= 23) {
            return '13_23_months';
        } elseif ($ageInMonths >= 72 && $ageInMonths <= 84) { // 6-7 years
            return 'grade_1';
        } elseif ($ageInMonths >= 144 && $ageInMonths <= 156) { // 12-13 years
            return 'grade_7';
        } else {
            return 'older';
        }
    }

    /**
     * Check if patient is Fully Immunized Child (FIC) at target date
     * FIC = Child 0-12 months who completed: BCG, HepB, Pentavalent (3), OPV (3), MMR (2)
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return bool
     */
    public function isFIC($targetDate = null)
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now('Asia/Manila');
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        $ageInMonths = $birthDate->diffInMonths($targetDate);

        // Must be 0-12 months old at target date
        if ($ageInMonths > 12) {
            return false;
        }

        // Get required FIC vaccines
        $ficVaccines = \App\Config\VaccineConfig::getFICVaccines();

        // Check if patient has completed all required vaccines
        foreach ($ficVaccines as $vaccineName => $requiredDoses) {
            $vaccineRecord = $this->vaccines()
                ->whereHas('vaccine', function($query) use ($vaccineName) {
                    $query->where('vaccine_name', $vaccineName);
                })
                ->first();

            if (!$vaccineRecord) {
                return false; // Vaccine record doesn't exist
            }

            // Check if all required doses are completed
            for ($dose = 1; $dose <= $requiredDoses; $dose++) {
                $doseField = "dose_{$dose}_date";
                if (empty($vaccineRecord->$doseField)) {
                    return false; // Missing dose
                }
            }
        }

        return true; // All FIC vaccines completed
    }

    /**
     * Check if patient is Completely Immunized Child (CIC) at target date
     * CIC = Child 13-23 months who completed all FIC vaccines + school vaccines
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return bool
     */
    public function isCIC($targetDate = null)
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now('Asia/Manila');
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        $ageInMonths = $birthDate->diffInMonths($targetDate);

        // Must be 13-23 months old at target date
        if ($ageInMonths < 13 || $ageInMonths > 23) {
            return false;
        }

        // Get required CIC vaccines
        $cicVaccines = \App\Config\VaccineConfig::getCICVaccines();

        // Check if patient has completed all required vaccines
        foreach ($cicVaccines as $vaccineName => $requiredDoses) {
            $vaccineRecord = $this->vaccines()
                ->whereHas('vaccine', function($query) use ($vaccineName) {
                    $query->where('vaccine_name', $vaccineName);
                })
                ->first();

            if (!$vaccineRecord) {
                return false; // Vaccine record doesn't exist
            }

            // Check if all required doses are completed
            for ($dose = 1; $dose <= $requiredDoses; $dose++) {
                $doseField = "dose_{$dose}_date";
                if (empty($vaccineRecord->$doseField)) {
                    return false; // Missing dose
                }
            }
        }

        return true; // All CIC vaccines completed
    }

    /**
     * Get patient's age in months at a specific date
     * 
     * @param \Carbon\Carbon|string $targetDate
     * @return int
     */
    public function getAgeInMonths($targetDate = null)
    {
        if (!$this->date_of_birth) {
            return 0;
        }

        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now('Asia/Manila');
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $targetDate = $targetDate ? \Carbon\Carbon::parse($targetDate) : \Carbon\Carbon::now();
        $birthDate = \Carbon\Carbon::parse($this->date_of_birth);
        
        return (int) $birthDate->diffInMonths($targetDate);
    }
}
