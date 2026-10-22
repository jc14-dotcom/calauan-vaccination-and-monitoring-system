<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Models\Vaccine;
use App\Models\VaccinationTransaction;
use App\Models\VaccinationSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class VaccinationController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    public function update(Request $request, $id)
    {
        $healthWorker = $this->getHealthWorker();
        $patient = Patient::findOrFail($id);
        
        // Verify health worker can access this patient's barangay
        if ($healthWorker && !$healthWorker->canAccessBarangay($patient->barangay)) {
            return redirect()->back()
                ->with('error', 'You do not have permission to update vaccinations for this patient.');
        }
        
        // Validate active vaccination schedule exists before processing
        $bypassScheduleCheck = $request->input('bypass_schedule') === '1';
        if (!$bypassScheduleCheck) {
            $activeSchedule = VaccinationSchedule::where('status', 'active')
                ->where(function($query) use ($patient) {
                    $query->where('barangay', $patient->barangay)
                          ->orWhere('barangay', 'RHU/All Barangays')
                          ->orWhere('barangay', 'RHU (Health Center)');
                })
                ->first();
            
            if (!$activeSchedule) {
                return redirect()->back()
                    ->with('error', 'Cannot record vaccinations - No active vaccination schedule found for this patient\'s barangay.');
            }
        }
        
        // Check if this is a backdate entry (only check NEWLY CHANGED dates)
        // Also check for FUTURE dates which are NOT ALLOWED
        $isBackdated = false;
        $hasFutureDate = false;
        $backdateType = $request->input('backdate_type'); // 'calauan' or 'external'
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $today = now('Asia/Manila')->startOfDay()->toDateString(); // Ensure we get just the date part
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $today = now()->startOfDay()->toDateString();
        
        if ($request->has('vaccinations')) {
            foreach ($request->vaccinations as $vaccinationId => $data) {
                $existingVaccination = PatientVaccineRecord::find($vaccinationId);
                if (!$existingVaccination) continue;

                $doseCount = optional($existingVaccination->vaccine)->getTotalDosesCount() ?? 1;
                for ($dose = 1; $dose <= $doseCount; $dose++) {
                    $field = "dose_{$dose}_date";
                    if ($hasFutureDate || empty($data[$field] ?? null)) {
                        continue;
                    }

                    $newDoseDate = date('Y-m-d', strtotime($data[$field]));
                    $oldDoseDate = $existingVaccination->{$field};

                    if ($newDoseDate > $today) {
                        $hasFutureDate = true;
                        break 2;
                    }

                    if ((!$oldDoseDate || $oldDoseDate !== $newDoseDate) && $newDoseDate < $today) {
                        $isBackdated = true;
                    }
                }
            }
        }
        
        // Block future dates - vaccinations can only be recorded for today or past dates
        if ($hasFutureDate) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'Future dates are not allowed. Vaccination dates must be today or earlier.')
                ->withInput();
        }
        
        // Validate dose order - can't give dose 2 without dose 1, can't give dose 3 without dose 2
        $doseOrderErrors = [];
        if ($request->has('vaccinations')) {
            foreach ($request->vaccinations as $vaccinationId => $data) {
                $existingVaccination = PatientVaccineRecord::find($vaccinationId);
                if (!$existingVaccination) continue;

                $vaccine = $existingVaccination->vaccine;
                $doseCount = optional($vaccine)->getTotalDosesCount() ?? 1;
                $finalDoseDates = [];

                for ($dose = 1; $dose <= $doseCount; $dose++) {
                    $field = "dose_{$dose}_date";
                    $finalDoseDates[$dose] = !empty($data[$field] ?? null)
                        ? date('Y-m-d', strtotime($data[$field]))
                        : $existingVaccination->{$field};
                }

                for ($dose = 2; $dose <= $doseCount; $dose++) {
                    $field = "dose_{$dose}_date";
                    if (empty($data[$field] ?? null)) {
                        continue;
                    }

                    if (empty($finalDoseDates[$dose - 1])) {
                        $doseOrderErrors[] = "{$vaccine->vaccine_name}: Cannot record dose {$dose} without dose " . ($dose - 1) . ".";
                    }
                }
            }
        }

        if (count($doseOrderErrors) > 0) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'Dose order validation failed: ' . implode(' ', $doseOrderErrors))
                ->withInput();
        }
        
        // Validate that no vaccine dose is before the patient's birth date
        // A vaccine cannot be given before the baby is born!
        $birthDateErrors = [];
        $patientBirthDate = $patient->date_of_birth;
        if ($patientBirthDate && $request->has('vaccinations')) {
            $birthDateFormatted = date('Y-m-d', strtotime($patientBirthDate));
            foreach ($request->vaccinations as $vaccinationId => $data) {
                $existingVaccination = PatientVaccineRecord::find($vaccinationId);
                if (!$existingVaccination) continue;

                $vaccine = $existingVaccination->vaccine;
                $doseCount = optional($vaccine)->getTotalDosesCount() ?? 1;

                for ($dose = 1; $dose <= $doseCount; $dose++) {
                    $field = "dose_{$dose}_date";
                    if (empty($data[$field] ?? null)) {
                        continue;
                    }

                    $doseDate = date('Y-m-d', strtotime($data[$field]));
                    if ($doseDate < $birthDateFormatted) {
                        $birthDateErrors[] = "{$vaccine->vaccine_name} Dose {$dose}: Cannot be before birth date (" . date('M d, Y', strtotime($patientBirthDate)) . ").";
                    }
                }
            }
        }
        
        if (count($birthDateErrors) > 0) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'Invalid vaccination date: ' . implode(' ', $birthDateErrors))
                ->withInput();
        }
        
        // If backdated but user hasn't chosen type yet, return error
        if ($isBackdated && !$backdateType) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'Backdated entry detected. Please specify where the vaccination was administered.')
                ->withInput();
        }
        
        $missedPatients = session('missed_patients', []);
        $vaccinatedPatients = session('vaccinated_patients', []);
        $anyVaccinationChanged = false;
        $stockErrors = [];

        if ($request->has('vaccinations')) {
            foreach ($request->vaccinations as $vaccinationId => $data) {
                $vaccination = PatientVaccineRecord::findOrFail($vaccinationId);
                $vaccine = $vaccination->vaccine;
                $doseCount = optional($vaccine)->getTotalDosesCount() ?? 1;

                // Track old dates to detect new vaccinations
                $oldDoseDates = [];
                for ($dose = 1; $dose <= $doseCount; $dose++) {
                    $oldDoseDates[$dose] = $vaccination->{"dose_{$dose}_date"};
                }

                // Count how many NEW doses are being administered
                $newDosesAdministered = 0;
                for ($dose = 1; $dose <= $doseCount; $dose++) {
                    $field = "dose_{$dose}_date";
                    if (empty($oldDoseDates[$dose]) && !empty($data[$field] ?? null)) {
                        $newDosesAdministered++;
                    }
                }

                // VALIDATE STOCK BEFORE ALLOWING VACCINATION (only for non-backdated entries)
                if ($newDosesAdministered > 0 && !$isBackdated) {
                    $availableStock = (int) ($vaccine->stocks ?? 0);
                    if ($availableStock < $newDosesAdministered) {
                        $stockErrors[] = "{$vaccine->vaccine_name} - Insufficient stock! Available: {$availableStock} doses, Required: {$newDosesAdministered} doses";
                        continue; // Skip this vaccination
                    }
                }

                // Check if any of the dates have changed
                $doseDateChanged = false;
                for ($dose = 1; $dose <= $doseCount; $dose++) {
                    $field = "dose_{$dose}_date";
                    if (array_key_exists($field, $data) && $vaccination->{$field} !== ($data[$field] ?? null)) {
                        $doseDateChanged = true;
                        break;
                    }
                }

                // Check if remarks changed
                $remarksChanged = isset($data['remarks']) && $vaccination->remarks !== $data['remarks'];

                // Only update if any field changed
                if ($doseDateChanged || $remarksChanged) {
                    $updateData = ['remarks' => $data['remarks'] ?? null];
                    for ($dose = 1; $dose <= $doseCount; $dose++) {
                        $field = "dose_{$dose}_date";
                        $updateData[$field] = $data[$field] ?? null;
                    }
                    $vaccination->update($updateData);

                    // AUTO-DEDUCT INVENTORY: Only for non-backdated entries
                    if ($doseDateChanged && $newDosesAdministered > 0 && !$isBackdated) {
                        $this->deductVaccineStock($vaccine, $newDosesAdministered);
                    }

                    // CREATE VACCINATION TRANSACTIONS for new doses
                    if ($doseDateChanged) {
                        $this->createVaccinationTransactions(
                            $patient,
                            $vaccination,
                            $vaccine,
                            $data,
                            $oldDoseDates,
                            $doseCount,
                            $isBackdated,
                            $backdateType
                        );
                    }
                }

                // Only mark as vaccinated if a dose date changed
                if ($doseDateChanged) {
                    $anyVaccinationChanged = true;
                    $hasAnyDose = false;
                    for ($dose = 1; $dose <= $doseCount; $dose++) {
                        if (!empty($data["dose_{$dose}_date"] ?? null)) {
                            $hasAnyDose = true;
                            break;
                        }
                    }

                    if ($hasAnyDose && !in_array($patient->id, $vaccinatedPatients)) {
                        $vaccinatedPatients[] = $patient->id;
                        if (in_array($patient->id, $missedPatients)) {
                            $missedPatients = array_diff($missedPatients, [$patient->id]);
                        }
                    }
                }
            }
        }
        
        session([
            'missed_patients' => $missedPatients,
            'vaccinated_patients' => $vaccinatedPatients
        ]);

        // If there were stock errors, redirect back to patient card with error messages
        if (count($stockErrors) > 0) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'Vaccination failed due to insufficient stock:')
                ->with('stock_errors', $stockErrors);
        }

        // Check if any data was actually submitted and updated
        $hasVaccinations = $request->has('vaccinations') && count($request->vaccinations) > 0;
        if (!$hasVaccinations) {
            return redirect()->route('vaccination.form', $id)
                ->with('error', 'No vaccination data was submitted. Please try again.');
        }

        // Stay on the patient card page with success message
        return redirect()->route('vaccination.form', $id)
            ->with('success', 'Vaccination records updated successfully.')
            ->with('vaccination_changed', $anyVaccinationChanged);
    }

    /**
     * Determine how many doses a vaccine has based on its description
     * 
     * @param string $dosesDescription
     * @return int
     */
    private function getVaccineDoseCount($dosesDescription)
    {
        // Count the number of doses based on the description
        if (strpos($dosesDescription, '&') !== false || strpos($dosesDescription, ',') !== false) {
            // If description contains "&" or "," it likely has multiple doses
            return substr_count($dosesDescription, '&') + substr_count($dosesDescription, ',') + 1;
        } else if (strpos($dosesDescription, 'Pagkapanganak') !== false || 
                   strpos($dosesDescription, 'Birth') !== false || 
                   strpos($dosesDescription, 'Grade') !== false) {
            // Common patterns for single dose vaccines
            return 1;
        }
        
        // Count the number of months/dates mentioned
        preg_match_all('/\d+\s*(Buwan|Month|Year|Taon)/', $dosesDescription, $matches);
        $count = count($matches[0]);
        
        return $count > 0 ? $count : 1; // Return at least 1
    }

    /**
     * Deduct vaccine stock from inventory when doses are administered
     * 
     * @param Vaccine $vaccine
     * @param int $dosesAdministered
     * @return void
     */
    private function deductVaccineStock(Vaccine $vaccine, int $dosesAdministered)
    {
        DB::beginTransaction();

        try {
            // Reload the vaccine to get fresh data
            $vaccine->refresh();

            // Deduct doses from stock
            $vaccine->stocks -= $dosesAdministered;

            // Update doses used counter
            $vaccine->doses_used_from_current_bottles += $dosesAdministered;

            // Check if we've consumed a full bottle
            $dosesPerBottle = max(1, (int) $vaccine->doses_per_bottle);
            
            while ($vaccine->doses_used_from_current_bottles >= $dosesPerBottle && $vaccine->available_bottles > 0) {
                // Deduct one bottle
                $vaccine->available_bottles -= 1;
                // Reset counter (subtract the bottle's worth of doses)
                $vaccine->doses_used_from_current_bottles -= $dosesPerBottle;
            }

            // Save the updated vaccine
            $vaccine->save();

            // Clear cache to ensure fresh inventory data
            Cache::forget('vaccine_stocks_list');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to deduct vaccine stock for {$vaccine->vaccine_name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create vaccination transaction records for newly administered doses
     * 
     * @param Patient $patient
     * @param PatientVaccineRecord $vaccination
     * @param Vaccine $vaccine
     * @param array $data
     * @param string|null $oldDose1
     * @param string|null $oldDose2
     * @param string|null $oldDose3
     * @param int $doseCount
     * @param bool $isBackdated
     * @param string|null $backdateType
     * @return void
     */
    private function createVaccinationTransactions(
        Patient $patient,
        PatientVaccineRecord $vaccination,
        Vaccine $vaccine,
        array $data,
        array $oldDoseDates,
        int $doseCount,
        bool $isBackdated = false,
        $backdateType = null
    ) {
        $vaccinationSessionId = session('vaccination_session_id');
        $healthWorkerId = Auth::guard('health_worker')->id();

        // Determine flags based on backdate type
        $administeredElsewhere = ($isBackdated && $backdateType === 'external');
        $stockOverride = ($isBackdated && $backdateType === 'calauan');

        // Auto-detect active vaccination schedule based on patient's barangay
        // Set to NULL if external vaccination
        $vaccinationScheduleId = $administeredElsewhere ? null : $this->getActiveScheduleForPatient($patient);

        for ($dose = 1; $dose <= $doseCount; $dose++) {
            $field = "dose_{$dose}_date";
            if (empty($data[$field] ?? null) || !empty($oldDoseDates[$dose])) {
                continue;
            }

            VaccinationTransaction::create([
                'vaccination_session_id' => $vaccinationSessionId,
                'vaccination_schedule_id' => $vaccinationScheduleId,
                'patient_id' => $patient->id,
                'vaccine_id' => $vaccine->id,
                'patient_vaccine_record_id' => $vaccination->id,
                'dose_number' => $dose,
                'doses_deducted' => $isBackdated ? 0 : 1,
                'vaccinated_at' => $data[$field],
                'vaccinated_by' => $healthWorkerId,
                'notes' => $data['remarks'] ?? null,
                'administered_elsewhere' => $administeredElsewhere,
                'stock_override' => $stockOverride,
            ]);
        }
    }

    /**
     * Get active vaccination schedule for a patient based on their barangay
     * Priority: Patient's specific barangay schedule > RHU/All Barangays schedule
     * 
     * @param Patient $patient
     * @return int|null
     */
    private function getActiveScheduleForPatient(Patient $patient)
    {
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $today = now('Asia/Manila')->toDateString();
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $today = now()->toDateString();
        
        // First, try to find a schedule specifically for the patient's barangay
        $schedule = \App\Models\VaccinationSchedule::where('vaccination_date', $today)
            ->where('barangay', $patient->barangay)
            ->where('status', 'active')
            ->first();
        
        // If no specific barangay schedule, check for RHU/All Barangays schedule
        if (!$schedule) {
            $schedule = \App\Models\VaccinationSchedule::where('vaccination_date', $today)
                ->where('barangay', 'RHU/All Barangays')
                ->where('status', 'active')
                ->first();
        }
        
        return $schedule ? $schedule->id : null;
    }
}
