<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Patient;
use App\Services\Parent\ContactUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Profile Controller (Parent)
 * 
 * Handles parent profile viewing and updating.
 */
class ProfileController extends Controller
{
    /**
     * Contact update service instance
     */
    protected ContactUpdateService $contactUpdateService;
    
    /**
     * Create a new controller instance.
     */
    public function __construct(ContactUpdateService $contactUpdateService)
    {
        $this->contactUpdateService = $contactUpdateService;
    }
    
    /**
     * Display the parent profile page
     * 
     * @return View|RedirectResponse
     */
    public function show(): View|RedirectResponse
    {
        $parent = Auth::guard('parents')->user();
        
        if (!$parent) {
            return redirect()->back()->withErrors(['error' => 'No parent logged in.']);
        }
        
        $patient = Patient::where('parent_id', $parent->id)->first();
        
        if (!$patient) {
            return redirect()->back()->withErrors(['error' => 'Patient record not found.']);
        }
        
        return view('parents.profile', compact('patient'));
    }
    
    /**
     * Update parent profile with cascade to children
     * 
     * Updates the parent's contact number, email, address, and barangay.
     * Automatically cascades the contact number update to all associated children.
     * 
     * @param UpdateProfileRequest $request Validated request with profile data
     * @return JsonResponse
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        /** @var Parents $parent */
        $parent = auth('parents')->user();
        
        if (!$parent) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in first.'
            ], 401);
        }
        
        // Use service to update profile with cascade
        $result = $this->contactUpdateService->updateProfile($parent, [
            'contact_no' => $request->contact_no,
            'email' => $request->email,
            'address' => $request->address,
            'barangay' => $request->barangay,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully. Contact number updated for all children.',
            'patients_updated' => $result['patients_updated'],
        ]);
    }
}
