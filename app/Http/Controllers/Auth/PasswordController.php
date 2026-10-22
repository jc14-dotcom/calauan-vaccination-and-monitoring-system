<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Password Controller
 * 
 * Handles password changes for parent accounts including first-login forced password changes.
 */
class PasswordController extends Controller
{
    /**
     * Show the regular password change form
     * 
     * @return View
     */
    public function showChangeForm(): View
    {
        return view('parents.change-password');
    }
    
    /**
     * Handle regular password change
     * 
     * @param ChangePasswordRequest $request Validated password change request
     * @return RedirectResponse
     */
    public function change(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::guard('parents')->user();
        
        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }
        
        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return redirect()->route('parent.dashboard')
            ->with('success', 'Password changed successfully.');
    }
    
    /**
     * Show the first-login password change form
     * 
     * @return View
     */
    public function showFirstLoginForm(): View
    {
        return view('parents.first-login-change-password');
    }
    
    /**
     * Handle first-login forced password change
     * 
     * After successful password change, redirects to privacy policy consent
     * if not yet accepted, otherwise redirects to dashboard.
     * 
     * @param ChangePasswordRequest $request Validated password change request
     * @return RedirectResponse
     */
    public function firstLoginChange(ChangePasswordRequest $request): RedirectResponse
    {
        $user = Auth::guard('parents')->user();
        
        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }
        
        // Update password and mark as changed
        $user->password = Hash::make($request->new_password);
        $user->password_changed = true;
        $user->save();
        
        // Check if privacy policy needs to be accepted
        if (!$user->privacy_policy_accepted) {
            return redirect()->route('parent.privacy.consent')
                ->with('success', 'Password changed successfully! Please review and accept the Privacy Policy to continue.');
        }
        
        return redirect()->route('parent.dashboard')
            ->with('success', 'Password changed successfully! Welcome to your dashboard.');
    }
}
