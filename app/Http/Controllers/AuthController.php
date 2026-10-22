<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HealthWorker; 
use App\Models\Parents; 
use App\Models\Patient; 
use App\Models\Vaccine; 
use App\Models\PatientVaccineRecord; 
use App\Models\Feedback;
use App\Models\Barangay;
use App\Services\PatientVaccinationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

/**
 * Auth Controller
 * 
 * NOTE: This controller is being refactored for better separation of concerns.
 * The following methods have been moved to dedicated controllers:
 * - showProfile(), update() -> App\Http\Controllers\Parent\ProfileController
 * - showChangePasswordForm(), changePassword(), firstLoginChangePassword() -> App\Http\Controllers\Auth\PasswordController
 * - acceptPrivacyPolicy() -> App\Http\Controllers\Auth\PrivacyController
 * 
 * Remaining methods in this controller are still in use by the application.
 * Future refactoring may move additional methods to more specific controllers.
 */
class AuthController extends Controller
{
// DEPRECATED: Moved to Parent\ProfileController::show()
// Kept temporarily for backward compatibility
public function showProfile()
{
    $parent = Auth::guard('parents')->user(); // Fetch logged-in parent

    if (!$parent) {
        return redirect()->back()->withErrors(['error' => 'No parent logged in.']);
    }

    $patient = Patient::where('parent_id', $parent->id)->first(); // Fetch associated patient

    if (!$patient) {
        return redirect()->back()->withErrors(['error' => 'Patient record not found.']);
    }

    return view('parents.profile', compact('patient'));
}
    
public function update(Request $request)
{
    // Check if the user is authenticated
    $parent = auth('parents')->user();
    if (!$parent) {
        return response()->json(['success' => false, 'message' => 'Please log in first.'], 401);
    }

    // Validate the input fields
    $request->validate([
        'contact_no' => 'required|regex:/^09\d{9}$/',
        'email' => 'required|email',
        'address' => 'required|string|max:255',
        'barangay' => 'required|string|max:255',
    ]);

    // Update the parent's contact number, address, barangay, and email
    $parent->update([
        'contact_number' => $request->contact_no,
        'address' => $request->address,
        'barangay' => $request->barangay,
        'email' => $request->email,
    ]);

    // CASCADE UPDATE: Update contact number for all patients under this parent
    Patient::where('parent_id', $parent->id)->update([
        'contact_no' => $request->contact_no,
    ]);

    // Return JSON response
    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully. Contact number updated for all children.'
    ]);
}
public function showChangePasswordForm()
    {
        return view('parents.change-password');
    }
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*()_+\-=\[\]{}|;:,.<>?]).+$/'
            ],
        ], [
            'current_password.required' => 'The current password is required.',
            'new_password.required' => 'The new password is required.',
            'new_password.min' => 'The new password must be at least 8 characters.',
            'new_password.confirmed' => 'The new password and confirmation do not match.',
            'new_password.regex' => 'The new password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@#$%^&*()_+-=[]{}|;:,.<>?).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::guard('parents')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->route('parent.dashboard')->with('success', 'Password changed successfully.');
    }

    public function firstLoginChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#$%^&*()_+\-=\[\]{}|;:,.<>?]).+$/'
            ],
        ], [
            'current_password.required' => 'The current password is required.',
            'new_password.required' => 'The new password is required.',
            'new_password.min' => 'The new password must be at least 8 characters.',
            'new_password.confirmed' => 'The new password and confirmation do not match.',
            'new_password.regex' => 'The new password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@#$%^&*()_+-=[]{}|;:,.<>?).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::guard('parents')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        // Update password and mark as changed
        $user->password = Hash::make($request->new_password);
        $user->password_changed = true;
        $user->save();

        // After password change, check if privacy policy needs to be accepted
        if (!$user->privacy_policy_accepted) {
            return redirect()->route('parent.privacy.consent')
                ->with('success', 'Password changed successfully! Please review and accept the Privacy Policy to continue.');
        }

        return redirect()->route('parent.dashboard')->with('success', 'Password changed successfully! Welcome to your dashboard.');
    }

    public function acceptPrivacyPolicy(Request $request)
    {
        $request->validate([
            'privacy_consent' => 'required|accepted',
        ]);

        $parent = Auth::guard('parents')->user();
        
        if (!$parent) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        // Update parent record with privacy policy acceptance
        $parent->update([
            'privacy_policy_accepted' => true,
            'privacy_policy_accepted_at' => now(),
            'privacy_policy_version' => '1.0',
        ]);

        return redirect()->route('parent.dashboard')
            ->with('success', 'Salamat sa pagtanggap ng Patakaran sa Privacy ng Data. Maligayang pagdating!');
    }



// AuthController
public function logout(Request $request)
{
    // SECURITY: Clear session token from database BEFORE logout
    // This invalidates the session token immediately, preventing reuse
    
    // Check which guard is logged in and clear their token
    if (Auth::guard('parents')->check()) {
        $user = Auth::guard('parents')->user();
        if ($user) {
            $user->update([
                'session_token' => null,
                'session_created_at' => null,
            ]);
        }
        // Clear remember token to prevent "remember me" bypass
        Auth::guard('parents')->logout();
    }
    
    if (Auth::guard('health_worker')->check()) {
        $user = Auth::guard('health_worker')->user();
        if ($user) {
            $user->update([
                'session_token' => null,
                'session_created_at' => null,
            ]);
        }
        Auth::guard('health_worker')->logout();
    }
    
    if (Auth::guard('web')->check()) {
        Auth::guard('web')->logout();
    }
    
    // SECURITY: Complete session destruction
    // Flush first to clear all data including tokens
    $request->session()->flush();
    
    // Then invalidate to destroy the session completely  
    $request->session()->invalidate();
    
    // Generate new CSRF token for security
    $request->session()->regenerateToken();
    
    // Generate completely new session ID (creates new cookie)
    // Pass true to destroy the old session completely
    $request->session()->regenerate(true);
    
    // Force save the new empty session
    $request->session()->save();
    
    // Redirect to login page with success message and no-cache headers
    return redirect()->route('login')
        ->with('success', 'You have been logged out successfully.')
        ->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
}

public function showInfantRecords()
{
    $parent = Auth::guard('parents')->user();  // Explicitly use the 'parents' guard
    if (!$parent) {
        return redirect()->route('parents.login')->withErrors(['error' => 'Please log in first.']);
    }

    $patient = Patient::where('parent_id', $parent->id)->first(); // Fetch associated patient
    
    if (!$patient) {
        return redirect()->back()->withErrors(['error' => 'No patient record found.']);
    }

    // Fetch vaccination records for the patient
    $vaccinations = PatientVaccineRecord::with('vaccine')
        ->where('patient_id', $patient->id)
        ->get();

    return view('parents.infantsRecord', compact('vaccinations', 'patient'));
}

/**
 * Show the vaccination form for adding new patients.
 * 
 * @return \Illuminate\View\View
 */
public function showVaccinationForm()
{
    $healthWorker = Auth::guard('health_worker')->user();

    $accessibleBarangays = $healthWorker ? $healthWorker->getAccessibleBarangays() : Barangay::getActiveNames();

    // Get standard vaccines first, then non-standard
    $standardVaccines = Vaccine::where('is_standard', true)->orderBy('sort_order')->orderBy('id')->get();
    $nonStandardVaccines = Vaccine::where('is_standard', false)->orderBy('sort_order')->orderBy('id')->get();
    $vaccines = $standardVaccines->concat($nonStandardVaccines);

    return view('health_worker.vaccination_form', compact('healthWorker', 'accessibleBarangays', 'vaccines')); 
}

public function saveRecord(Request $request)
{
    $request->validate([
        'first_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\'\-]+$/'],
        'middle_initial' => ['nullable', 'string', 'max:2', 'regex:/^[a-zA-Z]\.?$/'],
        'last_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\'\-]+$/'],
        'suffix' => ['nullable', 'string', 'max:10', 'in:Jr.,Sr.,II,III,IV,V,'],
        'mothers_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s,.\'\-]+$/'],
        'date_of_birth' => 'required|date|before_or_equal:today',
        'fathers_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s,.\'\-]+$/'],
        'place_of_birth' => 'required|string|max:255',
        'birth_height' => 'required|numeric|min:20|max:70',
        'birth_weight' => 'required|numeric|min:0.5|max:10',
        'barangay' => 'required|string',
        'address' => 'required|string|max:255',
        'contact_no' => ['required', 'regex:/^09\d{9}$/'],
        'sex' => 'required|string|in:Male,Female',
        'vaccines.*.vaccine_name' => 'nullable|string|max:255',
        'vaccines.*.dose_1_date' => 'nullable|date',
        'vaccines.*.dose_2_date' => 'nullable|date',
        'vaccines.*.dose_3_date' => 'nullable|date',
        'vaccines.*.dose_4_date' => 'nullable|date',
        'vaccines.*.dose_5_date' => 'nullable|date',
        'vaccines.*.dose_6_date' => 'nullable|date',
        'vaccines.*.dose_7_date' => 'nullable|date',
        'vaccines.*.dose_8_date' => 'nullable|date',
        'vaccines.*.dose_9_date' => 'nullable|date',
        'vaccines.*.dose_10_date' => 'nullable|date',
        'vaccines.*.remarks' => 'nullable|string|max:255',
    ], [
        'first_name.required' => 'First name is required.',
        'first_name.regex' => 'First name can only contain letters, spaces, apostrophes, and hyphens.',
        'middle_initial.regex' => 'Middle initial should be a single letter (e.g., T or T.).',
        'last_name.required' => 'Last name is required.',
        'last_name.regex' => 'Last name can only contain letters, spaces, apostrophes, and hyphens.',
        'mothers_name.regex' => 'Mother\'s name can only contain letters, spaces, commas, periods, apostrophes, and hyphens.',
        'fathers_name.regex' => 'Father\'s name can only contain letters, spaces, commas, periods, apostrophes, and hyphens.',
        'date_of_birth.before_or_equal' => 'Date of birth cannot be a future date.',
        'contact_no.regex' => 'Contact number must be a valid Philippine mobile number starting with 09 (11 digits).',
        'birth_weight.min' => 'Birth weight must be at least 0.5 kg.',
        'birth_weight.max' => 'Birth weight cannot exceed 10 kg.',
        'birth_height.min' => 'Birth height must be at least 20 cm.',
        'birth_height.max' => 'Birth height cannot exceed 70 cm.',
    ]);

    // Build full name from separate fields for backward compatibility
    $firstName = trim($request->input('first_name'));
    $middleInitial = trim($request->input('middle_initial', ''));
    $lastName = trim($request->input('last_name'));
    $suffix = trim($request->input('suffix', ''));
    
    // Build the full name string
    $fullNameParts = [$firstName];
    if (!empty($middleInitial)) {
        $fullNameParts[] = rtrim($middleInitial, '.') . '.';
    }
    $fullNameParts[] = $lastName;
    if (!empty($suffix)) {
        $fullNameParts[] = $suffix;
    }
    $fullName = implode(' ', $fullNameParts);

    // Normalize mother's name for username generation
    $mothersName = $request->input('mothers_name');
    $contactNumber = $request->input('contact_no');
    $fathersName = $request->input('fathers_name');
    
    // STEP 1: Smart matching to detect if parent account already exists (for siblings)
    // Uses 2-out-of-3 matching: Mother Name (required) + Contact Number + Father Name
    // Requires at least 2 fields to match to prevent wrong family linking
    
    // Fast database query - mother_name, father_name, contact_no are NOT encrypted
    $candidates = Patient::where('mother_name', $mothersName)
        ->with('parent')
        ->get();
    
    $bestMatch = null;
    $highestScore = 0;
    
    foreach ($candidates as $candidate) {
        $score = 1; // Mother name already matches (from query)
        
        // Check contact number match
        if (!empty($contactNumber) && !empty($candidate->contact_no)) {
            if (trim($candidate->contact_no) === trim($contactNumber)) {
                $score++;
            }
        }
        
        // Check father name match (case-insensitive, trimmed)
        if (!empty($fathersName) && !empty($candidate->father_name)) {
            if (strtolower(trim($candidate->father_name)) === strtolower(trim($fathersName))) {
                $score++;
            }
        }
        
        // Need at least 2 matches to consider it the same family
        // This prevents false matches while being tolerant of field changes
        if ($score >= 2 && $score > $highestScore) {
            $highestScore = $score;
            $bestMatch = $candidate;
        }
    }
    
    $existingPatient = $bestMatch;
    
    if ($existingPatient && $existingPatient->parent) {
        // Parent account already exists - check for duplicate patient
        $parent = $existingPatient->parent;
        
        // DUPLICATE CHECK: Check if this exact patient already exists
        // Use the built full name for comparison
        $dateOfBirth = $request->input('date_of_birth');
        
        $duplicateCheck = Patient::where('parent_id', $parent->id)
            ->where('name', $fullName)
            ->where('date_of_birth', $dateOfBirth)
            ->first();
        
        if ($duplicateCheck) {
            // Exact duplicate found (same name + same birthday + same parent)
            return redirect()->back()->with([
                'error' => "Patient already exists! A child named '{$fullName}' with the same birthday is already registered under account '{$parent->username}'."
            ])->withInput();
        }
        
        // Check for same name but different birthday (warning case)
        $sameNameCheck = Patient::where('parent_id', $parent->id)
            ->where('name', $fullName)
            ->where('date_of_birth', '!=', $dateOfBirth)
            ->first();
        
        if ($sameNameCheck) {
            // Same name but different birthday - could be twins or different child with same name
            // Allow it but add a note in the message
            $message = "Successfully added to the account of {$parent->username}. Note: Another child with the same name ('{$fullName}') but different birthday is already registered under this account.";
        } else {
            // Normal sibling addition
            $message = "Successfully added to the account of {$parent->username}";
        }
    } else {
        // No existing parent found - create new account
        
        // Extract first initial + last name (or use full name if single word)
        $nameParts = array_filter(explode(' ', trim($mothersName)));
        
        if (count($nameParts) >= 2) {
            // Multiple words: use first initial + last name
            $firstInitial = strtolower(substr($nameParts[0], 0, 1));
            $lastName = strtolower(end($nameParts));
            $baseUsername = $firstInitial . $lastName;
        } else {
            // Single word: use it directly
            $baseUsername = strtolower(trim($mothersName));
        }
        
        // Remove any special characters, keep only letters and numbers
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
        
        // Generate unique username with counter
        $counter = 1;
        $username = $baseUsername . str_pad($counter, 3, '0', STR_PAD_LEFT);
        
        while (Parents::where('username', $username)->exists()) {
            $counter++;
            $username = $baseUsername . str_pad($counter, 3, '0', STR_PAD_LEFT);
        }
        
        // Create new parent account with random password
        // Note: Use $passwordSuffix to avoid collision with patient name $suffix variable
        $passwordSuffix = strtoupper(\Illuminate\Support\Str::random(5));
        $rawPassword = 'RHUKC-' . $passwordSuffix;
        $hashedPassword = Hash::make($rawPassword);

        $parent = Parents::create([
            'username' => $username,
            'password' => $hashedPassword,
            'password_changed' => false,
            'barangay' => $request->input('barangay'),
            'address' => $request->input('address'),
            'contact_number' => $request->input('contact_no'),
        ]);
        
        $message = "Successfully created account! Username: {$parent->username} Password: $rawPassword";
    }

    // Create the patient record with separate name fields
    $patient = $parent->patients()->create([
        'name' => $fullName,
        'first_name' => $firstName,
        'middle_initial' => !empty($middleInitial) ? rtrim($middleInitial, '.') : null,
        'last_name' => $lastName,
        'suffix' => !empty($suffix) ? $suffix : null,
        'mother_name' => $request->input('mothers_name'),
        'father_name' => $request->input('fathers_name'),
        'date_of_birth' => $request->input('date_of_birth'),
        'place_of_birth' => $request->input('place_of_birth'),
        'birth_height' => $request->input('birth_height'),
        'birth_weight' => $request->input('birth_weight'),
        'barangay' => $request->input('barangay'),
        'address' => $request->input('address'),
        'contact_no' => $request->input('contact_no'),
        'sex' => $request->input('sex'),
    ]);

    // Initialize PatientVaccinationService and create standard vaccination records
    $vaccinationService = new PatientVaccinationService();
    $vaccinationService->createStandardVaccinationRecords($patient);

    // Process any additional vaccine records provided in the form
    foreach ($request->input('vaccines', []) as $vaccineData) {
        if (empty($vaccineData['vaccine_name'])) {
            continue;
        }

        // Find the existing vaccine record or create a new one if it doesn't match standard vaccines
        $vaccine = Vaccine::where('vaccine_name', $vaccineData['vaccine_name'])->first();
        
        if ($vaccine) {
            // Check if a record for this vaccine already exists for this patient
            $existingRecord = PatientVaccineRecord::where('patient_id', $patient->id)
                ->where('vaccine_id', $vaccine->id)
                ->first();
                
            if ($existingRecord) {
                // Update the existing record with form data
                $updateData = ['remarks' => $vaccineData['remarks'] ?? $existingRecord->remarks];
                for ($dose = 1; $dose <= 6; $dose++) {
                    $field = "dose_{$dose}_date";
                    $updateData[$field] = $vaccineData[$field] ?? $existingRecord->{$field};
                }
                $existingRecord->update($updateData);
            } else {
                // Create new record for non-standard vaccines
                $createData = [
                    'patient_id' => $patient->id,
                    'vaccine_id' => $vaccine->id,
                    'remarks' => $vaccineData['remarks'] ?? null,
                ];
                for ($dose = 1; $dose <= 6; $dose++) {
                    $field = "dose_{$dose}_date";
                    $createData[$field] = $vaccineData[$field] ?? null;
                }
                PatientVaccineRecord::create($createData);
            }
        }
    }

    return redirect()->back()->with('success', $message);
}

public function showPatients()
{
    $patients = Patient::all();
    return view('health_worker.patients', compact('patients'));
}

public function store(Request $request)
{
    $user = Auth::guard('parents')->user();

    if (!$user) {
        return response()->json(['error' => 'You must be logged in to submit feedback.'], 401);
    }

    $request->validate([
        'content' => 'required|string',
        'vaccination_schedule_id' => 'required|exists:vaccination_schedules,id',
    ]);

    try {
        $scheduleId = $request->input('vaccination_schedule_id');
        
        // Get the vaccination schedule
        $schedule = \App\Models\VaccinationSchedule::find($scheduleId);
        
        if (!$schedule) {
            return response()->json(['error' => 'Vaccination schedule not found.'], 404);
        }
        
        // Check if the schedule is for the parent's barangay
        // RHU schedules (RHU (Health Center) or RHU/All Barangays) are accessible by all barangays
        $isRHUSchedule = in_array($schedule->barangay, ['RHU (Health Center)', 'RHU/All Barangays']);
        if (!$isRHUSchedule && $schedule->barangay !== $user->barangay) {
            return response()->json(['error' => 'This vaccination schedule is not for your barangay.'], 403);
        }
        
        // Check if the vaccination day has passed
        // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
        $nowPHT = \Carbon\Carbon::now('Asia/Manila');
        $todayPHT = \Carbon\Carbon::today('Asia/Manila');
        $scheduleDatePHT = \Carbon\Carbon::parse($schedule->vaccination_date)->setTimezone('Asia/Manila');
        
        if ($scheduleDatePHT->isAfter($todayPHT)) {
            return response()->json(['error' => 'You can only submit feedback after the vaccination day.'], 403);
        }
        
        // Check if within 24-hour window
        $vaccinationDate = $scheduleDatePHT;
        if ($nowPHT->diffInHours($vaccinationDate) > 24) {
            return response()->json(['error' => 'The 24-hour feedback window has expired.'], 403);
        }
        
        // LOCAL/DEFAULT TIMEZONE (Testing) - Uses server default timezone
        // if (\Carbon\Carbon::parse($schedule->vaccination_date)->isFuture()) {
        //     return response()->json(['error' => 'You can only submit feedback after the vaccination day.'], 403);
        // }
        // $vaccinationDate = \Carbon\Carbon::parse($schedule->vaccination_date);
        // $now = \Carbon\Carbon::now();
        // if ($now->diffInHours($vaccinationDate) > 24) {
        //     return response()->json(['error' => 'The 24-hour feedback window has expired.'], 403);
        // }
        
        // Check if parent already submitted feedback for this schedule
        $existingFeedback = Feedback::where('parent_id', $user->id)
            ->where('vaccination_schedule_id', $scheduleId)
            ->first();
            
        if ($existingFeedback) {
            return response()->json(['error' => 'You have already submitted feedback for this vaccination schedule.'], 409);
        }
        
        // Create the feedback
        Feedback::create([
            'parent_id' => $user->id,
            'vaccination_schedule_id' => $scheduleId,
            'barangay' => $user->barangay,
            'content' => $request->input('content'),
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Evaluation submitted successfully!']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error processing evaluation: ' . $e->getMessage()], 500);
    }
}

public function showFeedback(Request $request)
{
    // Get the current health worker and their accessible barangays
    $healthWorker = Auth::guard('health_worker')->user();
    
    // Get accessible barangays for the dropdown
    $accessibleBarangays = $healthWorker ? $healthWorker->getAccessibleBarangays() : \App\Models\Barangay::getActiveNames();
    
    // Return the feedback analysis view with accessible barangays
    return view('health_worker.feedback-analysis', compact('accessibleBarangays', 'healthWorker'));
}

}
