<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Vaccine;
use App\Models\VaccinationSchedule;
use App\Services\PatientVaccinationService;
use App\Services\VaccineAgeFilterService;
use Illuminate\Support\Facades\DB;

class ShowVaccinationFormController extends Controller
{
    public function show(Request $request, $id)
    {
        $patient = Patient::with('latestGrowthRecord')->findOrFail($id);
        
        // Check if there's an active vaccination schedule
        // Note: Check for both 'RHU/All Barangays' and 'RHU (Health Center)' values
        $activeSchedule = VaccinationSchedule::where('status', 'active')
            ->where(function($query) use ($patient) {
                $query->where('barangay', $patient->barangay)
                      ->orWhere('barangay', 'RHU/All Barangays')
                      ->orWhere('barangay', 'RHU (Health Center)');
            })
            ->first();
        
        // Allow bypassing schedule check via query parameter (for emergencies)
        $bypassScheduleCheck = $request->query('bypass_schedule') === '1';
        
        if (!$activeSchedule && !$bypassScheduleCheck) {
            return redirect()->route('health_worker.vaccination_status')
                ->with('error', 'No active vaccination schedule found for this patient\'s barangay. Vaccinations can only be recorded during scheduled vaccination days to prevent vaccine waste.');
        }
        
        // Determine return URL based on referrer or query parameter
        $returnUrl = $request->query('return');
        if (!$returnUrl) {
            $referer = $request->headers->get('referer');
            if ($referer && str_contains($referer, '/patients')) {
                $returnUrl = route('health_worker.patients');
            } else {
                $returnUrl = route('health_worker.vaccination_status');
            }
        }
        
        // Ensure standard vaccination records exist for this patient
        try {
            $svc = new PatientVaccinationService();
            $svc->createStandardVaccinationRecords($patient);
        } catch (\Throwable $e) {
            // Continue even if seeding fails; view will still render
        }
        
        // Get vaccination records
        $vaccinations = \App\Models\PatientVaccineRecord::with('vaccine')
            ->where('patient_id', $id)
            ->get();
        
        // Force fresh database query - bypass any query caching
        DB::reconnect();
        
        // Get all vaccines with their stock information for warnings
        // Query fresh data directly from database
        $allVaccines = Vaccine::all();
        $vaccineStocks = [];
        
        foreach ($allVaccines as $vaccine) {
            // Reload from database to ensure fresh data
            $vaccine->refresh();
            
            $availableDoses = max(0, (int) ($vaccine->stocks ?? 0));
            
            if ($availableDoses <= 0) {
                $status = 'out';
            } elseif ($availableDoses < 10) {
                $status = 'low';
            } elseif ($availableDoses < 50) {
                $status = 'medium';
            } else {
                $status = 'high';
            }
            
            $vaccineStocks[$vaccine->id] = [
                'available_doses' => $availableDoses,
                'available_bottles' => (int) ($vaccine->available_bottles ?? 0),
                'doses_per_bottle' => (int) ($vaccine->doses_per_bottle ?? 10),
                'status' => $status,
                'name' => $vaccine->vaccine_name ?? 'Unknown',
            ];
        }
        
        // Get vaccine age eligibility data
        $ageFilterService = new VaccineAgeFilterService();
        $vaccineEligibility = $ageFilterService->getVaccinationsWithEligibility($patient, $vaccinations);
        $patientAgeFormatted = $ageFilterService->getFormattedPatientAge($patient);
        
        // Check if "Show All" toggle is enabled
        $showAllVaccines = $request->query('show_all') === '1';
        
        return view('health_worker.patient_card_tailwind', compact(
            'patient', 
            'vaccinations', 
            'vaccineStocks', 
            'returnUrl',
            'vaccineEligibility',
            'patientAgeFormatted',
            'showAllVaccines'
        ));
    }
}