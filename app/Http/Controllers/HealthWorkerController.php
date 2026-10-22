<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Models\VaccinationTransaction;
use App\Models\HealthWorker;
use App\Models\Barangay;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\VaccinationSchedule;
use Carbon\Carbon;

class HealthWorkerController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    public function vaccinationStatus(Request $request)
    {
        $healthWorker = $this->getHealthWorker();
        
        // Auto-activate today's schedules
        VaccinationSchedule::today()
            ->where('status', 'scheduled')
            ->update(['status' => 'active']);
        
        // Get today's active schedules (filtered by health worker's barangay)
        $todaySchedulesQuery = VaccinationSchedule::today()->where('status', 'active');
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            $barangayName = $healthWorker->getAssignedBarangayName();
            $todaySchedulesQuery->where(function($q) use ($barangayName) {
                $q->where('barangay', $barangayName)
                  ->orWhere('barangay', 'RHU/All Barangays');
            });
        }
        $todaySchedules = $todaySchedulesQuery->get();
        
        // Generate session ID if today has active vaccination schedule
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $nowPHT = now('Asia/Manila');
        if ($todaySchedules->count() > 0 && !session('vaccination_session_id')) {
            $sessionId = 'vax_' . $nowPHT->format('Y-m-d') . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
            session(['vaccination_session_id' => $sessionId]);
            session(['vaccination_day' => $nowPHT->toDateString()]);
        }
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // if ($todaySchedules->count() > 0 && !session('vaccination_session_id')) {
        //     $sessionId = 'vax_' . now()->format('Y-m-d') . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
        //     session(['vaccination_session_id' => $sessionId]);
        //     session(['vaccination_day' => now()->toDateString()]);
        // }
        
        // Check if vaccination_day is set and if more than 24 hours have passed
        $vaccinationDay = session('vaccination_day');
        if ($vaccinationDay) {
            // SERVER-SIDE TIMEZONE (Production)
            $now = now('Asia/Manila');
            // LOCAL/DEFAULT TIMEZONE (Testing)
            // $now = now();
            $day = \Carbon\Carbon::parse($vaccinationDay)->setTimezone('Asia/Manila');
            if ($now->diffInHours($day) >= 24) {
                // Reset statuses after 24 hours
                session(['missed_patients' => [], 'vaccinated_patients' => [], 'vaccination_day' => null]);
            }
        }
        
        // Get session arrays
        $vaccinatedPatients = session('vaccinated_patients', []);
        $missedPatients = session('missed_patients', []);
        
        // Build query with health worker's barangay filter
        $query = Patient::forHealthWorker($healthWorker);
        
        // Do not pre-filter patients by vaccine records here.
        // Patients without any vaccine records should still appear
        // in the status page as incomplete / not started.
        
        // Apply search filter (case-insensitive)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('contact_no', 'LIKE', '%' . $searchTerm . '%');
            });
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'vaccinated' && !empty($vaccinatedPatients)) {
                $query->whereIn('id', $vaccinatedPatients);
            } elseif ($status === 'missed' && !empty($missedPatients)) {
                $query->whereIn('id', $missedPatients);
            } elseif ($status === 'not_done') {
                // Not done = not in vaccinated AND not in missed
                if (!empty($vaccinatedPatients)) {
                    $query->whereNotIn('id', $vaccinatedPatients);
                }
                if (!empty($missedPatients)) {
                    $query->whereNotIn('id', $missedPatients);
                }
            }
            // If status is set but no matching patients, return empty collection
            if (($status === 'vaccinated' && empty($vaccinatedPatients)) || 
                ($status === 'missed' && empty($missedPatients))) {
                $patients = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 30);
                $filters = [
                    'search' => $request->get('search', ''),
                    'status' => $request->get('status', '')
                ];
                return view('health_worker.vaccination_status', compact('patients', 'filters', 'vaccinatedPatients', 'missedPatients', 'todaySchedules'));
            }
        }
        
        // Order alphabetically by last name, first name, then middle initial
        // Use separate name fields for accurate sorting (handles suffixes correctly)
        $query->orderByRaw('LOWER(COALESCE(last_name, SUBSTRING_INDEX(name, " ", -1))) ASC')
              ->orderByRaw('LOWER(COALESCE(first_name, SUBSTRING_INDEX(name, " ", 1))) ASC')
              ->orderByRaw('LOWER(COALESCE(middle_initial, "")) ASC');
        
        // Get all results first, then filter and paginate
        $allPatients = $query->get();
        
        // Filter out fully immunized patients
        $incompletePatients = $allPatients->filter(function($patient) {
            return $patient->hasIncompleteVaccines();
        });
        
        // Separate patients by today's active schedules
        $todayScheduleBarangays = $todaySchedules->pluck('barangay')->toArray();
        $hasRHUSchedule = in_array('RHU/All Barangays', $todayScheduleBarangays);
        
        // Patients from today's targeted barangays (priority section)
        $priorityPatients = collect();
        if (!empty($todayScheduleBarangays) && !$hasRHUSchedule) {
            $priorityPatients = $incompletePatients->filter(function($patient) use ($todayScheduleBarangays) {
                return in_array($patient->barangay, $todayScheduleBarangays);
            });
        } elseif ($hasRHUSchedule) {
            // If RHU schedule, all patients are priority
            $priorityPatients = $incompletePatients;
        }
        
        // Other patients (not in today's schedule)
        $otherPatients = $incompletePatients->diff($priorityPatients);
        
        // Manually paginate the filtered collection
        $perPage = 30;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $currentPageItems = $incompletePatients->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $patients = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $incompletePatients->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        
        // Add formatted name, age, and status to each patient
        // Check if there's an active schedule for today
        $hasActiveSchedule = $todaySchedules->count() > 0;
        
        foreach ($patients as $patient) {
            $patient->display_age = $patient->formatted_age;
            $patient->display_name = $patient->formatted_name;
            
            // Determine status based on vaccination transactions created TODAY
            // Only check for "vaccinated" status if there's an active schedule for today
            // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
            $todayDatePHT = now('Asia/Manila')->toDateString();
            
            // Check if patient has a vaccination transaction today
            $hasTransactionToday = VaccinationTransaction::where('patient_id', $patient->id)
                ->whereDate('created_at', $todayDatePHT)
                ->exists();
            
            // Determine patient status
            if (in_array($patient->id, $missedPatients)) {
                $patient->status = 'missed';
            } elseif ($hasTransactionToday && $hasActiveSchedule) {
                // Only show "vaccinated" if there's an active schedule today
                // This prevents showing "vaccinated" from previous transactions 
                // when there's no vaccination day
                $patient->status = 'vaccinated';
            } elseif ($hasTransactionToday && !$hasActiveSchedule) {
                // Transaction exists but no active schedule today
                // This could be a walk-in or backdated entry - show as not_done
                $patient->status = 'not_done';
            } else {
                $patient->status = 'not_done';
            }
            
            // Mark if patient is in today's priority group
            $patient->is_priority = $priorityPatients->contains('id', $patient->id);
        }
        
        // Keep filter values for form
        $filters = [
            'search' => $request->get('search', ''),
            'status' => $request->get('status', '')
        ];
        
        return view('health_worker.vaccination_status', compact('patients', 'filters', 'vaccinatedPatients', 'missedPatients', 'todaySchedules', 'priorityPatients', 'otherPatients'));
    }

    /**
     * Get paginated vaccination status data with filtering options (AJAX endpoint)
     */
    public function getVaccinationStatus(Request $request)
    {
        try {
            $healthWorker = $this->getHealthWorker();
            
            // Get session arrays (for backward compatibility if still used)
            $vaccinatedPatients = session('vaccinated_patients', []);
            $missedPatients = session('missed_patients', []);
            
            // Build query with health worker's barangay filter
            $query = Patient::forHealthWorker($healthWorker);
            
            // Apply search filter (case-insensitive)
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('contact_no', 'LIKE', '%' . $searchTerm . '%');
                });
            }
            
            // Apply status filter based on vaccination transactions created today
            // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
            $todayPHT = now('Asia/Manila')->toDateString();
            if ($request->filled('status')) {
                $status = $request->status;
                
                if ($status === 'vaccinated') {
                    // Patient has vaccination transaction created today
                    $query->whereHas('vaccinationTransactions', function($q) use ($todayPHT) {
                        $q->whereDate('created_at', $todayPHT);
                    });
                    // LOCAL/DEFAULT TIMEZONE (Testing)
                    // $query->whereHas('vaccinationTransactions', function($q) {
                    //     $q->whereDate('created_at', now()->toDateString());
                    // });
                } elseif ($status === 'missed') {
                    // Use session data for missed patients
                    if (!empty($missedPatients)) {
                        $query->whereIn('id', $missedPatients);
                    } else {
                        return response()->json([
                            'patients' => [],
                            'has_more' => false,
                            'total' => 0,
                            'current_page' => 1,
                            'per_page' => 30
                        ]);
                    }
                } elseif ($status === 'not_done') {
                    // Patient has NO transactions created today
                    $query->whereDoesntHave('vaccinationTransactions', function($q) use ($todayPHT) {
                        $q->whereDate('created_at', $todayPHT);
                    });
                    // LOCAL/DEFAULT TIMEZONE (Testing)
                    // $query->whereDoesntHave('vaccinationTransactions', function($q) {
                    //     $q->whereDate('created_at', now()->toDateString());
                    // });
                }
            }
            
            // Order alphabetically by last name, first name, then middle initial
            // Use separate name fields for accurate sorting (handles suffixes correctly)
            $query->orderByRaw('LOWER(COALESCE(last_name, SUBSTRING_INDEX(name, " ", -1))) ASC')
                  ->orderByRaw('LOWER(COALESCE(first_name, SUBSTRING_INDEX(name, " ", 1))) ASC')
                  ->orderByRaw('LOWER(COALESCE(middle_initial, "")) ASC');
            
            // Paginate results (30 per page)
            $perPage = min((int)$request->get('size', 30), 50);
            $patients = $query->paginate($perPage);
            
            // Transform data to include status, age, and formatted name
            $transformedPatients = [];
            foreach ($patients->items() as $patient) {
                $patientArray = $patient->toArray();
                
                // Determine status based on vaccination transactions created TODAY
                // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
                $hasTransactionToday = VaccinationTransaction::where('patient_id', $patient->id)
                    ->whereDate('created_at', $todayPHT)
                    ->exists();
                // LOCAL/DEFAULT TIMEZONE (Testing)
                // $hasTransactionToday = VaccinationTransaction::where('patient_id', $patient->id)
                //     ->whereDate('created_at', now()->toDateString())
                //     ->exists();
                
                if (in_array($patient->id, $missedPatients)) {
                    $patientArray['status'] = 'missed';
                } elseif ($hasTransactionToday) {
                    $patientArray['status'] = 'vaccinated';
                } else {
                    $patientArray['status'] = 'not_done';
                }
                
                // Add formatted age and name
                $patientArray['display_age'] = $patient->formatted_age;
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
            \Log::error('Error in vaccination status API: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load vaccination status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Add this method for the vaccination day button
    public function setVaccinationDay(Request $request)
    {
        $healthWorker = $this->getHealthWorker();
        
        // Generate unique vaccination session ID
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $sessionId = 'vax_' . now('Asia/Manila')->format('Y-m-d') . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
        // LOCAL/DEFAULT TIMEZONE (Testing)
        // $sessionId = 'vax_' . now()->format('Y-m-d') . '_' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        // Store vaccination session ID in session
        session(['vaccination_session_id' => $sessionId]);
        
        // Set the timezone to Asia/Manila (PHT)
        $nowPHT = now('Asia/Manila');
        session(['vaccination_day' => $nowPHT->toDateString()]);

        /*
        // --- CLIENT-SIDE DEMO LOGIC (for defense/testing) ---
        // Uncomment this section and comment out the server-side logic below to use local machine time
        session(['vaccination_day' => now()->toDateString()]);
        if ($request->input('after_six_pm') == '1') {
            // Get patients filtered by health worker's barangay
            $patients = Patient::forHealthWorker($healthWorker)->get();
            $vaccinatedPatients = session('vaccinated_patients', []);
            $missedPatients = session('missed_patients', []);
            foreach ($patients as $patient) {
                if (!in_array($patient->id, $vaccinatedPatients)) {
                    $missedPatients[] = $patient->id;
                }
            }
            // Remove duplicates
            $missedPatients = array_unique($missedPatients);
            session(['missed_patients' => $missedPatients]);
        } else {
            // If before 6 PM, reset missed/vaccinated
            session(['missed_patients' => [], 'vaccinated_patients' => []]);
        }
        */

        // --- SERVER-SIDE LOGIC (Production) ---
        // Uses server time in Asia/Manila timezone for 6PM cutoff
        if ($nowPHT->hour >= 18) {
            // Get patients filtered by health worker's barangay
            $patients = Patient::forHealthWorker($healthWorker)->get();
            $vaccinatedPatients = session('vaccinated_patients', []);
            $missedPatients = session('missed_patients', []);
            foreach ($patients as $patient) {
                if (!in_array($patient->id, $vaccinatedPatients)) {
                    $missedPatients[] = $patient->id;
                }
            }
            $missedPatients = array_unique($missedPatients);
            session(['missed_patients' => $missedPatients]);
        } else {
            session(['missed_patients' => [], 'vaccinated_patients' => []]);
        }

        return back();
    }

    /**
     * Show the change password form for health workers (admin).
     */
    public function showChangePasswordForm()
    {
        return view('health_worker.change-password-healthworker');
    }

    /**
     * Update the health worker's (admin's) password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*()_+\-=\[\]{}|;:,.<>?]).+$/'
            ],
        ]);

        $user = Auth::guard('health_worker')->user();

        if (!$user || !Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password has been updated successfully.');
    }

    /**
     * Update the health worker's email address.
     */
    public function updateEmail(Request $request)
    {
        $request->validate([
            'new_email' => ['required', 'string', 'email', 'max:255', 'unique:health_workers,email'],
            'password' => ['required', 'string'],
        ]);

        $user = Auth::guard('health_worker')->user();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        $user->email = $request->new_email;
        $user->save();

        return back()->with('success', 'Email has been updated successfully.');
    }
}