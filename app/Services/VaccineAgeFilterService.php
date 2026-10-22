<?php

namespace App\Services;

use App\Models\Patient;
use Carbon\Carbon;

/**
 * Service to filter vaccines based on patient age.
 * 
 * Business Rules:
 * - Vaccine for YOUNGER age (past/missed): ENABLED - Can update (patient may have missed it)
 * - Vaccine for CURRENT age (due now): ENABLED - Can update
 * - Vaccine for OLDER age (future): DISABLED - Cannot update until age-appropriate
 */
class VaccineAgeFilterService
{
    /**
     * Age mapping for vaccine doses_description to age ranges in months.
     * 
     * Structure: 'doses_description_pattern' => ['min_months' => X, 'max_months' => Y]
     * - min_months: Minimum age when vaccine becomes due
     * - max_months: Maximum recommended age for the vaccine (for "on time" status)
     */
    protected array $ageMapping = [
        // Birth vaccines
        'Pagkapanganak' => ['min_months' => 0, 'max_months' => 1],
        
        // Infant vaccines (multi-dose)
        '1 1/2, 2 1/2, 3 1/2 Buwan' => ['min_months' => 1.5, 'max_months' => 12],
        '3 1/2 & 9 Buwan' => ['min_months' => 3.5, 'max_months' => 12],
        
        // 9-month vaccines
        '9 Buwan & 1 Taon' => ['min_months' => 9, 'max_months' => 18],
        '9 na buwan' => ['min_months' => 9, 'max_months' => 12],
        
        // 1-year vaccines
        '1 Taon' => ['min_months' => 12, 'max_months' => 18],
        
        // School-aged vaccines
        'Grade 1' => ['min_months' => 72, 'max_months' => 96],        // ~6-8 years
        'Grade 7' => ['min_months' => 144, 'max_months' => 168],      // ~12-14 years
        'Grade 1 & 7' => ['min_months' => 72, 'max_months' => 168],   // Grade 1 to 7
        'Grade 4 - (Babae) 9-14 Taon Gulang' => ['min_months' => 108, 'max_months' => 168], // HPV: 9-14 years
    ];

    /**
     * Calculate patient's age in months.
     *
     * @param Patient $patient
     * @return float
     */
    public function getPatientAgeInMonths(Patient $patient): float
    {
        $dob = Carbon::parse($patient->date_of_birth);
        return $dob->diffInMonths(Carbon::now(), false) + ($dob->diffInDays(Carbon::now()) % 30) / 30;
    }

    /**
     * Get age range for a vaccine based on its doses_description.
     *
     * @param string|null $dosesDescription
     * @return array ['min_months' => float, 'max_months' => float]
     */
    public function getVaccineAgeRange(?string $dosesDescription): array
    {
        if (empty($dosesDescription)) {
            return ['min_months' => 0, 'max_months' => 999]; // Default: always eligible
        }

        // Direct match
        if (isset($this->ageMapping[$dosesDescription])) {
            return $this->ageMapping[$dosesDescription];
        }

        // Partial match for flexibility
        foreach ($this->ageMapping as $pattern => $range) {
            if (stripos($dosesDescription, $pattern) !== false || stripos($pattern, $dosesDescription) !== false) {
                return $range;
            }
        }

        // Default: always eligible if no match found
        return ['min_months' => 0, 'max_months' => 999];
    }

    /**
     * Check if a vaccine is eligible for a patient based on age.
     * 
     * A vaccine is eligible if:
     * - Patient age >= vaccine min_months (they've reached the age OR passed it)
     * 
     * This allows:
     * - Catching up on missed vaccines (past due)
     * - Administering on-time vaccines (current)
     * - But NOT future vaccines (patient too young)
     *
     * @param float $patientAgeMonths
     * @param string|null $dosesDescription
     * @return bool
     */
    public function isVaccineEligible(float $patientAgeMonths, ?string $dosesDescription): bool
    {
        $ageRange = $this->getVaccineAgeRange($dosesDescription);
        
        // Eligible if patient has reached the minimum age for this vaccine
        return $patientAgeMonths >= $ageRange['min_months'];
    }

    /**
     * Get vaccine eligibility status for display.
     *
     * @param float $patientAgeMonths
     * @param string|null $dosesDescription
     * @return array ['eligible' => bool, 'status' => string, 'message' => string]
     */
    public function getVaccineEligibilityStatus(float $patientAgeMonths, ?string $dosesDescription): array
    {
        $ageRange = $this->getVaccineAgeRange($dosesDescription);
        
        if ($patientAgeMonths < $ageRange['min_months']) {
            // Patient is too young
            $monthsUntilDue = $ageRange['min_months'] - $patientAgeMonths;
            return [
                'eligible' => false,
                'status' => 'future',
                'message' => $this->formatTimeUntilDue($monthsUntilDue),
            ];
        }
        
        if ($patientAgeMonths <= $ageRange['max_months']) {
            // Patient is in the recommended age window
            return [
                'eligible' => true,
                'status' => 'due',
                'message' => 'Due now',
            ];
        }
        
        // Patient has passed the recommended window but can still catch up
        return [
            'eligible' => true,
            'status' => 'overdue',
            'message' => 'Catch-up',
        ];
    }

    /**
     * Format time until vaccine is due.
     *
     * @param float $months
     * @return string
     */
    protected function formatTimeUntilDue(float $months): string
    {
        if ($months < 1) {
            $weeks = round($months * 4);
            return "Due in {$weeks} week" . ($weeks != 1 ? 's' : '');
        }
        
        if ($months < 12) {
            $m = round($months);
            return "Due in {$m} month" . ($m != 1 ? 's' : '');
        }
        
        $years = round($months / 12, 1);
        return "Due in {$years} year" . ($years != 1 ? 's' : '');
    }

    /**
     * Get all vaccines with eligibility status for a patient.
     *
     * @param Patient $patient
     * @param \Illuminate\Support\Collection $vaccinations
     * @return array Keyed by vaccine_id for easy lookup in views
     */
    public function getVaccinationsWithEligibility(Patient $patient, $vaccinations): array
    {
        $patientAgeMonths = $this->getPatientAgeInMonths($patient);
        $result = [];

        foreach ($vaccinations as $vaccination) {
            $vaccineId = optional($vaccination->vaccine)->id;
            if (!$vaccineId) continue;
            
            $dosesDescription = optional($vaccination->vaccine)->doses_description;
            $eligibility = $this->getVaccineEligibilityStatus($patientAgeMonths, $dosesDescription);
            
            // Map status to simplified eligibility for view
            $status = 'eligible';
            $reason = '';
            
            if (!$eligibility['eligible']) {
                $status = 'not_eligible';
                $reason = $eligibility['message'];
            } elseif ($eligibility['status'] === 'overdue') {
                $status = 'missed';
                $reason = 'Overdue - catch-up recommended';
            }
            
            // Key by vaccine_id for easy lookup in blade template
            $result[$vaccineId] = [
                'vaccination_id' => $vaccination->id,
                'vaccine_id' => $vaccineId,
                'vaccine_name' => optional($vaccination->vaccine)->vaccine_name,
                'doses_description' => $dosesDescription,
                'status' => $status,
                'reason' => $reason,
            ];
        }

        return $result;
    }

    /**
     * Get patient age formatted for display.
     *
     * @param Patient $patient
     * @return string
     */
    public function getFormattedPatientAge(Patient $patient): string
    {
        if (empty($patient->date_of_birth)) {
            return 'N/A';
        }
        
        $dob = Carbon::parse($patient->date_of_birth);
        $now = Carbon::now();
        
        // Use integer casting to get whole numbers
        $years = (int) $dob->diffInYears($now);
        $totalMonths = (int) $dob->diffInMonths($now);
        $months = $totalMonths % 12;
        
        if ($years > 0) {
            $result = "{$years} year" . ($years != 1 ? 's' : '');
            if ($months > 0) {
                $result .= ", {$months} month" . ($months != 1 ? 's' : '');
            }
            return $result;
        }
        
        if ($totalMonths > 0) {
            return "{$totalMonths} month" . ($totalMonths != 1 ? 's' : '');
        }
        
        $days = (int) $dob->diffInDays($now);
        return "{$days} day" . ($days != 1 ? 's' : '');
    }
}
