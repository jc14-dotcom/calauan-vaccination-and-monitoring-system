<?php

namespace App\Http\Controllers\HealthWorker;

use App\Http\Controllers\Controller;
use App\Models\HealthWorker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get the authenticated health worker.
     */
    private function getUser(): HealthWorker
    {
        /** @var HealthWorker $user */
        $user = Auth::guard('health_worker')->user();
        return $user;
    }

    /**
     * Show the profile settings page.
     */
    public function show()
    {
        return view('health_worker.profile-settings', [
            'user' => $this->getUser(),
        ]);
    }

    /**
     * Update profile information (name, contact number).
     */
    public function updateProfile(Request $request)
    {
        $user = $this->getUser();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('health_worker.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update email address.
     */
    public function updateEmail(Request $request)
    {
        $user = $this->getUser();
        
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:health_workers,email,' . $user->id,
            'current_password' => 'required',
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['email' => $validated['email']]);

        return redirect()->route('health_worker.profile')
            ->with('success', 'Email updated successfully.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $user = $this->getUser();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('health_worker.profile')
            ->with('success', 'Password updated successfully.');
    }
}
