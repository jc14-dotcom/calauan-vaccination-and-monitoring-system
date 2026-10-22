<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Models\PatientGrowthRecord;
use App\Models\Feedback;
use App\Models\Vaccine;
use App\Models\Barangay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\PatientVaccinationService;
use App\Services\VaccineAgeFilterService;
use App\Models\VaccinationSchedule;

class PatientController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    public function index()
    {
        $healthWorker = $this->getHealthWorker();
        
        // Get patients filtered by health worker's barangay access
        $patients = Patient::forHealthWorker($healthWorker)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate total patients (filtered by barangay)
        $totalPatients = $patients->count();
        
        // Calculate vaccinated patients (patients with at least one vaccine dose recorded)
        // For barangay workers, only count patients in their barangay
        $vaccinatedQuery = PatientVaccineRecord::distinct('patient_id')
            ->where(function($query) {
                $query->whereNotNull('dose_1_date')
                      ->orWhereNotNull('dose_2_date')
                      ->orWhereNotNull('dose_3_date')
                      ->orWhereNotNull('dose_4_date')
                      ->orWhereNotNull('dose_5_date')
                      ->orWhereNotNull('dose_6_date')
                      ->orWhereNotNull('dose_7_date')
                      ->orWhereNotNull('dose_8_date')
                      ->orWhereNotNull('dose_9_date')
                      ->orWhereNotNull('dose_10_date');
            });

        if ($healthWorker && !$healthWorker->isRHU()) {
            $patientIds = Patient::forHealthWorker($healthWorker)->pluck('id');
            $vaccinatedQuery->whereIn('patient_id', $patientIds);
        }
        $vaccinated = $vaccinatedQuery->count('patient_id');
        
        // Count feedback submissions (filtered by barangay for barangay workers)
        if ($healthWorker && !$healthWorker->isRHU()) {
            $feedbackCount = Feedback::where('barangay', $healthWorker->getAssignedBarangayName())->count();
        } else {
            $feedbackCount = Feedback::count();
        }
        
        // Get upcoming scheduled vaccination days (next 5, including today)
        // For barangay workers, only show their barangay's schedules
        $schedulesQuery = VaccinationSchedule::where('vaccination_date', '>=', Carbon::today())
            ->whereIn('status', ['scheduled', 'active']);
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            $barangayName = $healthWorker->getAssignedBarangayName();
            $schedulesQuery->where(function($q) use ($barangayName) {
                $q->where('barangay', $barangayName)
                  ->orWhere('barangay', 'RHU/All Barangays');
            });
        }
        
        $upcomingVaccinations = $schedulesQuery
            ->orderBy('vaccination_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function($schedule) {
                // Check if vaccination is today (ongoing)
                $isToday = $schedule->vaccination_date->isToday();
                $statusText = $isToday ? 'Ongoing' : $schedule->daysUntil();
                
                return (object)[
                    'patient_name' => $schedule->barangay,
                    'vaccine_name' => $statusText,
                    'scheduled_date' => $schedule->vaccination_date->format('M d, Y'),
                    'patient_id' => null, // No patient ID for schedule
                    'is_ongoing' => $isToday, // Flag for styling
                ];
            });
        
        // Monthly vaccination data for the current year (Jan to Dec)
        // For barangay workers, only count their barangay's patients
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $currentYear = Carbon::now('Asia/Manila')->year;
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $currentYear = Carbon::now()->year;
        $monthlyData = [];
        
        // Count unique patients who received at least one vaccine dose per month
        if ($healthWorker && !$healthWorker->isRHU()) {
            $patientIds = Patient::forHealthWorker($healthWorker)->pluck('id');
            for ($month = 1; $month <= 12; $month++) {
                $count = PatientVaccineRecord::whereIn('patient_id', $patientIds)
                    ->where(function($query) use ($currentYear, $month) {
                        $query->whereYear('dose_1_date', $currentYear)->whereMonth('dose_1_date', $month)
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_2_date', $currentYear)->whereMonth('dose_2_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_3_date', $currentYear)->whereMonth('dose_3_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_4_date', $currentYear)->whereMonth('dose_4_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_5_date', $currentYear)->whereMonth('dose_5_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_6_date', $currentYear)->whereMonth('dose_6_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_7_date', $currentYear)->whereMonth('dose_7_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_8_date', $currentYear)->whereMonth('dose_8_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_9_date', $currentYear)->whereMonth('dose_9_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_10_date', $currentYear)->whereMonth('dose_10_date', $month);
                              });
                    })
                    ->distinct('patient_id')
                    ->count('patient_id');
                $monthlyData[] = $count;
            }
        } else {
            for ($month = 1; $month <= 12; $month++) {
                $count = PatientVaccineRecord::where(function($query) use ($currentYear, $month) {
                        $query->whereYear('dose_1_date', $currentYear)->whereMonth('dose_1_date', $month)
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_2_date', $currentYear)->whereMonth('dose_2_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_3_date', $currentYear)->whereMonth('dose_3_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_4_date', $currentYear)->whereMonth('dose_4_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_5_date', $currentYear)->whereMonth('dose_5_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_6_date', $currentYear)->whereMonth('dose_6_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_7_date', $currentYear)->whereMonth('dose_7_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_8_date', $currentYear)->whereMonth('dose_8_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_9_date', $currentYear)->whereMonth('dose_9_date', $month);
                              })
                              ->orWhere(function($query) use ($currentYear, $month) {
                                  $query->whereYear('dose_10_date', $currentYear)->whereMonth('dose_10_date', $month);
                              });
                    })
                    ->distinct('patient_id')
                    ->count('patient_id');
                $monthlyData[] = $count;
            }
        }
        
        // Vaccination status data (Complete, Partial, Not Started)
        $filteredPatients = Patient::forHealthWorker($healthWorker)->get();
        $totalPatientCount = $filteredPatients->count();
        
        // Complete: Patients who have FULLY COMPLETED all vaccines (all required doses)
        $completeCount = $filteredPatients->filter(function($patient) {
            return $patient->isFullyImmunized();
        })->count();
        
        // Partial: Patients with some vaccines but not all completed
        $partialCount = $filteredPatients->filter(function($patient) {
            $hasVaccines = $patient->vaccineRecords()->where(function($query) {
                $query->whereNotNull('dose_1_date')
                      ->orWhereNotNull('dose_2_date')
                      ->orWhereNotNull('dose_3_date')
                      ->orWhereNotNull('dose_4_date')
                      ->orWhereNotNull('dose_5_date')
                      ->orWhereNotNull('dose_6_date')
                      ->orWhereNotNull('dose_7_date')
                      ->orWhereNotNull('dose_8_date')
                      ->orWhereNotNull('dose_9_date')
                      ->orWhereNotNull('dose_10_date');
            })->exists();
            return $hasVaccines && !$patient->isFullyImmunized();
        })->count();
        
        // Not Started: Patients with no vaccine records at all
        $notStartedCount = $totalPatientCount - $completeCount - $partialCount;
        
        $vaccineStatusData = [$completeCount, $partialCount, $notStartedCount];
        
        // Vaccination distribution by vaccine type (BCG, HepB, Penta, OPV, IPV, PCV, Measles)
        $vaccineNames = ['BCG', 'Hepatitis B', 'Pentavalent', 'OPV', 'IPV', 'PCV', 'Measles'];
        $vaccineDistData = [];
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            $patientIds = Patient::forHealthWorker($healthWorker)->pluck('id');
            foreach ($vaccineNames as $vaccineName) {
                $count = PatientVaccineRecord::whereIn('patient_id', $patientIds)
                    ->whereHas('vaccine', function($query) use ($vaccineName) {
                        $query->where('vaccine_name', 'LIKE', '%' . $vaccineName . '%');
                    })->where(function($query) {
                        $query->whereNotNull('dose_1_date')
                              ->orWhereNotNull('dose_2_date')
                              ->orWhereNotNull('dose_3_date')
                              ->orWhereNotNull('dose_4_date')
                              ->orWhereNotNull('dose_5_date')
                              ->orWhereNotNull('dose_6_date')
                              ->orWhereNotNull('dose_7_date')
                              ->orWhereNotNull('dose_8_date')
                              ->orWhereNotNull('dose_9_date')
                              ->orWhereNotNull('dose_10_date');
                    })->count();
                
                $vaccineDistData[] = $count;
            }
        } else {
            foreach ($vaccineNames as $vaccineName) {
                $count = PatientVaccineRecord::whereHas('vaccine', function($query) use ($vaccineName) {
                    $query->where('vaccine_name', 'LIKE', '%' . $vaccineName . '%');
                })->where(function($query) {
                    $query->whereNotNull('dose_1_date')
                          ->orWhereNotNull('dose_2_date')
                          ->orWhereNotNull('dose_3_date')
                          ->orWhereNotNull('dose_4_date')
                          ->orWhereNotNull('dose_5_date')
                          ->orWhereNotNull('dose_6_date')
                          ->orWhereNotNull('dose_7_date')
                          ->orWhereNotNull('dose_8_date')
                          ->orWhereNotNull('dose_9_date')
                          ->orWhereNotNull('dose_10_date');
                })->count();
                
                $vaccineDistData[] = $count;
            }
        }
        
        // Pass all statistics to the dashboard view
        return view('health_worker.dashboard', compact(
            'patients', 
            'totalPatients', 
            'vaccinated', 
            'feedbackCount', 
            'upcomingVaccinations',
            'monthlyData',
            'vaccineStatusData',
            'vaccineDistData'
        ));
    }

    public function show($id)
    {
        $healthWorker = $this->getHealthWorker();
        $patient = Patient::with('latestGrowthRecord')->findOrFail($id);
        
        // Verify health worker can access this patient's barangay
        if ($healthWorker && !$healthWorker->canAccessBarangay($patient->barangay)) {
            abort(403, 'You do not have permission to view this patient.');
        }

        // Ensure standard vaccination records exist for this patient
        try {
            $svc = new PatientVaccinationService();
            $svc->createStandardVaccinationRecords($patient);
        } catch (\Throwable $e) {
            // Continue even if seeding fails; view will still render
        }

        $vaccinations = PatientVaccineRecord::with('vaccine')->where('patient_id', $id)->get();
        $vaccinations = $this->appendNonStandardVaccinePlaceholders($patient, $vaccinations);

        // Get all vaccines with their stock information for warnings (empty - removed per evaluator feedback)
        $vaccineStocks = collect([]);

        // Get vaccine age eligibility data
        $ageFilterService = new VaccineAgeFilterService();
        $vaccineEligibility = $ageFilterService->getVaccinationsWithEligibility($patient, $vaccinations);
        $patientAgeFormatted = $ageFilterService->getFormattedPatientAge($patient);
        
        // Check if "Show All" toggle is enabled
        $showAllVaccines = request()->query('show_all') === '1';

        return view('health_worker.patient_card_tailwind', compact(
            'patient', 
            'vaccinations', 
            'vaccineStocks',
            'vaccineEligibility',
            'patientAgeFormatted',
            'showAllVaccines'
        ));
    }

    public function showParentVaccinationCard(Request $request, $id)
    {
        // Get the currently authenticated parent
        $parent = auth('parents')->user();

        // Find the patient and make sure it belongs to the authenticated parent
        $patient = Patient::where('id', $id)
            ->where('parent_id', $parent->id)
            ->firstOrFail(); // Get ALL fields, not just id and name

        // Load the relationships
        $patient->load([
            'latestGrowthRecord', 
            'growthRecords',
            'parent'
        ]);

        // Ensure standard vaccination records exist for this patient
        try {
            $svc = new PatientVaccinationService();
            $svc->createStandardVaccinationRecords($patient);
        } catch (\Throwable $e) {
            // Continue even if seeding fails; view will still render
        }

        // Get vaccination records
        $vaccinations = PatientVaccineRecord::with('vaccine')
            ->where('patient_id', $id)
            ->get();

        // Determine return URL for back button - default to parent dashboard for parents
        $returnUrl = $request->query('return', route('parent.dashboard'));

        $vaccinations = $this->appendNonStandardVaccinePlaceholders($patient, $vaccinations);

        return view('parents.infantsRecord', compact('patient', 'vaccinations', 'returnUrl'));
    }

    /**
     * Show read-only patient vaccination card for health workers (from patient list)
     */
    public function showHealthWorkerPatientView(Request $request, $id)
    {
        $healthWorker = $this->getHealthWorker();
        
        // Find the patient (health worker can view any patient in their barangay)
        $patient = Patient::findOrFail($id);
        
        // Verify health worker can access this patient's barangay
        if ($healthWorker && !$healthWorker->canAccessBarangay($patient->barangay)) {
            abort(403, 'You do not have permission to view this patient.');
        }

        // Load the relationships
        $patient->load([
            'latestGrowthRecord', 
            'growthRecords',
            'parent'
        ]);

        // Ensure standard vaccination records exist for this patient
        try {
            $svc = new PatientVaccinationService();
            $svc->createStandardVaccinationRecords($patient);
        } catch (\Throwable $e) {
            // Continue even if seeding fails; view will still render
        }

        // Get vaccination records
        $vaccinations = PatientVaccineRecord::with('vaccine')
            ->where('patient_id', $id)
            ->get();

        $vaccinations = $this->appendNonStandardVaccinePlaceholders($patient, $vaccinations);

        // Determine return URL for back button
        $returnUrl = $request->query('return', route('health_worker.patients'));

        return view('health_worker.patient_card_readonly', compact('patient', 'vaccinations', 'returnUrl'));
    }

    /**
     * Show patient list page with initial data
     */
    public function showPatientList(Request $request)
    {
        $healthWorker = $this->getHealthWorker();
        
        // Build query with efficient SQL filtering (fields are now decrypted)
        // Apply health worker's barangay filter first
        $query = Patient::forHealthWorker($healthWorker);
        
        // Apply search filter (case-insensitive)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('contact_no', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        // Apply additional barangay filter (for RHU admin selecting specific barangay)
        // Barangay workers will only see their barangay anyway
        if ($request->filled('barangay') && $healthWorker && $healthWorker->isRHU()) {
            $query->where('barangay', $request->barangay);
        }
        
        // Get paginated results (30 per page) - ordered alphabetically by last name, first name, then middle initial
        // Use separate name fields for accurate sorting (handles suffixes correctly)
        $patients = $query
            ->orderByRaw('LOWER(COALESCE(last_name, SUBSTRING_INDEX(name, " ", -1))) ASC')
            ->orderByRaw('LOWER(COALESCE(first_name, SUBSTRING_INDEX(name, " ", 1))) ASC')
            ->orderByRaw('LOWER(COALESCE(middle_initial, "")) ASC')
            ->paginate(30);
        
        // Add age and formatted name to each patient (modify after pagination)
        foreach ($patients as $patient) {
            $patient->display_age = $patient->formatted_age;
            $patient->display_name = $patient->formatted_name;
        }
        
        // Keep filter values for form
        $filters = [
            'search' => $request->get('search', ''),
            'barangay' => $request->get('barangay', '')
        ];
        
        // Get accessible barangays for dropdown (RHU sees all, barangay worker sees only theirs)
        $accessibleBarangays = $healthWorker ? $healthWorker->getAccessibleBarangays() : Barangay::getActiveNames();
        
        return view('health_worker.patients', compact('patients', 'filters', 'accessibleBarangays', 'healthWorker'));
    }

    /**
     * Get paginated patients data with filtering options (AJAX endpoint)
     */
    public function getPatients(Request $request)
    {
        try {
            $healthWorker = $this->getHealthWorker();
            
            // DEBUG: Uncomment for debugging
            // Log::info('getPatients called', [
            //     'search' => $request->get('search'),
            //     'barangay' => $request->get('barangay'),
            //     'page' => $request->get('page'),
            //     'health_worker_barangay' => $healthWorker ? $healthWorker->getAssignedBarangayName() : 'RHU'
            // ]);

            // Build query with health worker's barangay filter
            $query = Patient::forHealthWorker($healthWorker);
            
            // Apply search filter (case-insensitive)
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('contact_no', 'LIKE', '%' . $searchTerm . '%');
                });
                // DEBUG: Log::info('Applied search filter', ['term' => $searchTerm]);
            }
            
            // Apply additional barangay filter (for RHU admin selecting specific barangay)
            if ($request->filled('barangay') && $healthWorker && $healthWorker->isRHU()) {
                $query->where('barangay', $request->barangay);
                // DEBUG: Log::info('Applied barangay filter', ['barangay' => $request->barangay]);
            }
            
            // Order by created_at with newest first
            $query->orderBy('created_at', 'desc');
            
            // Paginate results (30 per page, max 50)
            $perPage = min((int)$request->get('size', 30), 50);
            $patients = $query->paginate($perPage);
            
            // DEBUG: Log::info('Query results', ['total' => $patients->total()]);
            
            // Transform data to include age and formatted name
            $transformedPatients = [];
            foreach ($patients->items() as $patient) {
                $patientArray = $patient->toArray();
                
                // Add formatted age (e.g., "2 months old", "3 years old")
                $patientArray['display_age'] = $patient->formatted_age;
                
                // Add formatted name
                $patientArray['display_name'] = $patient->formatted_name;
                
                $transformedPatients[] = $patientArray;
            }
            
            return response()->json([
                'patients' => $transformedPatients,
                'has_more' => $patients->hasMorePages(),
                'total' => $patients->total(),
                'current_page' => $patients->currentPage(),
                'per_page' => $patients->perPage()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in patients API: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load patients',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new growth record for a patient
     */
    public function storeGrowthRecord(Request $request, $id)
    {
        // Ensure JSON response for AJAX requests
        if (!$request->expectsJson()) {
            $request->headers->set('Accept', 'application/json');
        }
        
        $healthWorker = $this->getHealthWorker();
        
        $patient = Patient::findOrFail($id);
        
        if ($healthWorker && !$healthWorker->canAccessBarangay($patient->barangay)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update this patient.'
            ], 403);
        }
        
        // Get the values, treating empty strings as null
        $weight = $request->input('weight');
        $height = $request->input('height');
        
        // Convert empty strings to null
        $weight = ($weight === '' || $weight === null) ? null : (float)$weight;
        $height = ($height === '' || $height === null) ? null : (float)$height;
        
        // Check if both are empty/null
        if ($weight === null && $height === null) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide at least weight or height.'
            ], 422);
        }
        
        // Basic validation - just check if numeric and reasonable
        if ($weight !== null && ($weight < 0.1 || $weight > 100)) {
            return response()->json([
                'success' => false,
                'message' => 'Weight must be between 0.1 and 100 kg.'
            ], 422);
        }
        
        if ($height !== null && ($height < 1 || $height > 250)) {
            return response()->json([
                'success' => false,
                'message' => 'Height must be between 1 and 250 cm.'
            ], 422);
        }
        
        try {
            // First, clean up any duplicate records for today (keep only the latest one)
            $todaysRecords = PatientGrowthRecord::where('patient_id', $patient->id)
                ->whereDate('recorded_date', Carbon::today())
                ->orderBy('id', 'desc')
                ->get();
            
            if ($todaysRecords->count() > 1) {
                // Keep the first (latest by ID), delete the rest
                $todaysRecords->skip(1)->each(function ($record) {
                    $record->delete();
                });
            }
            
            // Now update or create the record for today
            $existingRecord = PatientGrowthRecord::where('patient_id', $patient->id)
                ->whereDate('recorded_date', Carbon::today())
                ->first();
            
            if ($existingRecord) {
                // Update existing record, merging with existing values
                $existingRecord->update([
                    'weight' => $weight !== null ? $weight : $existingRecord->weight,
                    'height' => $height !== null ? $height : $existingRecord->height,
                    'recorded_by' => $healthWorker ? $healthWorker->id : $existingRecord->recorded_by,
                ]);
                $growthRecord = $existingRecord->fresh();
            } else {
                // Create new record
                $growthRecord = PatientGrowthRecord::create([
                    'patient_id' => $patient->id,
                    'weight' => $weight,
                    'height' => $height,
                    'recorded_date' => Carbon::today(),
                    'recorded_by' => $healthWorker ? $healthWorker->id : null,
                    'measurement_type' => 'routine_checkup',
                    'notes' => 'Recorded during vaccination visit'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Growth record saved successfully.',
                'data' => [
                    'weight' => $growthRecord->weight,
                    'height' => $growthRecord->height,
                    'recorded_date' => $growthRecord->recorded_date->format('M d, Y')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save growth record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save growth record. Please try again.'
            ], 500);
        }
    }

    /**
     * Append non-standard vaccine records that already exist for the patient.
     *
     * This is read-only — it does NOT create new PatientVaccineRecord rows.
     * Non-standard vaccine records are created only when the user saves the
     * vaccination form (AuthController::saveRecord).
     */
    private function appendNonStandardVaccinePlaceholders(Patient $patient, $vaccinations)
    {
        $existingVaccineIds = $vaccinations->pluck('vaccine_id')->filter()->unique()->values()->all();

        // Fetch existing non-standard vaccine records for this patient (no new rows created)
        $nonStandardRecords = PatientVaccineRecord::with('vaccine')
            ->where('patient_id', $patient->id)
            ->whereHas('vaccine', fn($q) => $q->where('is_standard', false))
            ->get();

        foreach ($nonStandardRecords as $record) {
            if (!in_array($record->vaccine_id, $existingVaccineIds, true)) {
                $vaccinations->push($record);
            }
        }

        return $vaccinations->sortBy(function ($record) {
            return [
                optional($record->vaccine)->is_standard ? 0 : 1,
                optional($record->vaccine)->sort_order ?? 999,
                optional($record->vaccine)->id ?? 0,
            ];
        })->values();
    }
}
