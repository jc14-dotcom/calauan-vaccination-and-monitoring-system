<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Dashboard Controller (Parent)
 * 
 * Handles the parent dashboard display and related functionality.
 */
class DashboardController extends Controller
{
    /**
     * Display the parent dashboard
     * 
     * Shows the authenticated parent's information and their children's data.
     * Middleware ensures user is authenticated before reaching this method.
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get authenticated parent (middleware ensures this exists)
        $user = Auth::guard('parents')->user();
        
        // Fetch all patients (children) for this parent
        $patients = Patient::where('parent_id', $user->id)->get();
        
        return view('parents.parentdashboard', compact('user', 'patients'));
    }

    /**
     * Upload profile image for a child/patient
     * 
     * Handles image upload, compression, and storage.
     * Old images are deleted to save storage space.
     * 
     * @param Request $request
     * @param int $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadChildPhoto(Request $request, int $patientId)
    {
        try {
            // Validate request
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,jpg,png|max:5120', // 5MB max
            ]);

            // Get authenticated parent
            $user = Auth::guard('parents')->user();
            
            // Find patient and verify ownership
            $patient = Patient::where('id', $patientId)
                ->where('parent_id', $user->id)
                ->first();
            
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found or access denied.'
                ], 404);
            }

            // Delete old image if exists
            if ($patient->profile_image) {
                $oldPath = 'children/' . $patient->profile_image;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                    Log::info("Deleted old profile image: {$oldPath}");
                }
            }

            // Create directory if not exists
            if (!Storage::disk('public')->exists('children')) {
                Storage::disk('public')->makeDirectory('children');
            }

            // Generate unique filename
            $filename = $patientId . '_' . time() . '.jpg';
            $path = 'children/' . $filename;

            // Process and compress image using Intervention Image
            $image = Image::read($request->file('photo'));
            
            // Cover crop to 150x150 (maintains aspect ratio and fills the square)
            $image->cover(150, 150);
            
            // Encode to JPEG with 80% quality (~50KB)
            $encoded = $image->toJpeg(80);
            
            // Save to storage
            Storage::disk('public')->put($path, $encoded);

            // Update patient record
            $patient->profile_image = $filename;
            $patient->save();

            Log::info("Profile image uploaded for patient {$patientId}: {$filename}");

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully!',
                'image_url' => asset('storage/children/' . $filename) . '?v=' . time(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file. Please upload a JPG or PNG image (max 5MB).',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error uploading profile image for patient {$patientId}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading the photo. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete profile image for a child/patient
     * 
     * @param int $patientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteChildPhoto(int $patientId)
    {
        try {
            // Get authenticated parent
            $user = Auth::guard('parents')->user();
            
            // Find patient and verify ownership
            $patient = Patient::where('id', $patientId)
                ->where('parent_id', $user->id)
                ->first();
            
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found or access denied.'
                ], 404);
            }

            // Delete image if exists
            if ($patient->profile_image) {
                $path = 'children/' . $patient->profile_image;
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
                
                $patient->profile_image = null;
                $patient->save();
                
                Log::info("Profile image deleted for patient {$patientId}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully!',
                'initials' => $patient->initials
            ]);

        } catch (\Exception $e) {
            Log::error("Error deleting profile image for patient {$patientId}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the photo.'
            ], 500);
        }
    }
}
