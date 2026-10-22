<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Privacy Controller
 * 
 * Handles privacy policy consent for parent accounts.
 */
class PrivacyController extends Controller
{
    /**
     * Show the privacy policy consent page
     * 
     * @return View
     */
    public function show(): View
    {
        return view('parents.privacy-policy-consent');
    }
    
    /**
     * Handle privacy policy acceptance
     * 
     * Records the parent's acceptance of the privacy policy with timestamp
     * and version information.
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function accept(Request $request): RedirectResponse
    {
        $request->validate([
            'privacy_consent' => 'required|accepted',
        ]);
        
        $parent = Auth::guard('parents')->user();
        
        if (!$parent) {
            return redirect()->route('login')
                ->with('error', 'Please login first.');
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
}
