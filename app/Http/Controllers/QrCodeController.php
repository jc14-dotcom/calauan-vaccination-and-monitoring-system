<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Vaccine;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class QrCodeController extends Controller
{
    protected QrCodeService $qrService;

    public function __construct(QrCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Handle external QR code scan - redirect to patient view or return JSON.
     *
     * @param string $uuid
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function handleQrScan(string $uuid, Request $request)
    {
        $patient = $this->qrService->findPatientByUuid($uuid);

        if (!$patient) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found'
                ], 404);
            }

            return redirect()->route('login')->with('error', 'Invalid QR code or patient not found.');
        }

        // If a health worker is authenticated, redirect to the patient card
        if (Auth::guard('health_worker')->check()) {
            return redirect()->route('health_worker.patient_card', ['id' => $patient->id]);
        }

        // If an authenticated parent owns this child, redirect to their dashboard
        if (Auth::guard('parents')->check()) {
            $parent = Auth::guard('parents')->user();
            if ($patient->parent_id === $parent->id) {
                return redirect()->route('parent.dashboard');
            }
        }

        // Not authenticated or not authorized - redirect to login
        return redirect()->route('login')->with('info', 'Please log in to view patient information.');
    }

    /**
     * API endpoint for in-app QR scanner - returns patient quick-view data.
     * Optimized with caching for vaccine list.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function lookupPatient(string $uuid): JsonResponse
    {
        $patient = $this->qrService->findPatientByUuid($uuid);

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        // Load relationships for quick view (optimized eager loading)
        // Note: parent table uses 'username' not 'name', so we load full parent model
        $patient->load(['parent', 'vaccines:id,patient_id,vaccine_id,dose_1_date,dose_2_date,dose_3_date,dose_4_date,dose_5_date,dose_6_date,dose_7_date,dose_8_date,dose_9_date,dose_10_date']);

        // Calculate age (single unit: days, weeks, months, or years)
        $dob = Carbon::parse($patient->date_of_birth);
        $age = $this->calculateAge($dob);

        // Get all vaccines from cache (rarely changes, cache for 1 hour)
        $allVaccines = Cache::remember('vaccines_list', 3600, function () {
            return Vaccine::select('id', 'vaccine_name', 'total_doses', 'doses_description')->get();
        });
        
        // Create a keyed collection for O(1) lookups instead of O(n)
        $vaccinesById = $allVaccines->keyBy('id');
        
        // Get patient's vaccine records (already loaded)
        $patientVaccineRecords = $patient->vaccines;
        
        // Build taken vaccines list (vaccines with at least one dose administered)
        $takenVaccines = [];
        $takenVaccineIds = [];
        
        foreach ($patientVaccineRecords as $record) {
            // Use cached vaccine data instead of loading through relationship
            $vaccine = $vaccinesById->get($record->vaccine_id);
            if (!$vaccine) {
                continue;
            }

            $totalDoses = max(1, min(10, (int) $vaccine->total_doses));
            $dosesTaken = [];

            foreach ($record->getDoseDates($totalDoses) as $doseNumber => $date) {
                if (!empty($date)) {
                    $dosesTaken[] = $doseNumber;
                }
            }

            if (!empty($dosesTaken)) {
                $takenVaccines[] = [
                    'id' => $vaccine->id,
                    'name' => $vaccine->vaccine_name,
                    'doses_taken' => $dosesTaken,
                    'doses_taken_count' => count($dosesTaken),
                    'total_doses' => $totalDoses,
                    'doses_label' => $this->formatDosesLabel($dosesTaken, $totalDoses),
                ];
                $takenVaccineIds[] = $vaccine->id;
            }
        }
        
        // Build not taken vaccines list using array_diff for efficiency
        $notTakenVaccines = $allVaccines
            ->whereNotIn('id', $takenVaccineIds)
            ->map(fn($vaccine) => [
                'id' => $vaccine->id,
                'name' => $vaccine->vaccine_name,
                'doses_description' => $vaccine->doses_description,
            ])
            ->values()
            ->toArray();

        // Get parent/mother name (parent table uses 'username' not 'name')
        $motherName = $patient->mother_name ?? null;
        $parentUsername = $patient->parent->username ?? null;
        $displayParentName = $motherName ?: $parentUsername ?: 'N/A';

        // Get profile image URL
        $profileImageUrl = $patient->profile_image_url ?? null;

        return response()->json([
            'success' => true,
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'date_of_birth' => $patient->date_of_birth,
                'age' => $age,
                'sex' => $patient->sex,
                'barangay' => $patient->barangay,
                'mother_name' => $motherName ?: 'N/A',
                'parent_name' => $displayParentName,
                'parent_contact' => $patient->parent->contact_number ?? $patient->contact_no ?? 'N/A',
                'profile_image_url' => $profileImageUrl,
                'qr_uuid' => $patient->qr_uuid,
            ],
            'vaccination_summary' => [
                'taken_count' => count($takenVaccines),
                'not_taken_count' => count($notTakenVaccines),
                'taken' => $takenVaccines,
                'not_taken' => $notTakenVaccines,
            ],
            'links' => [
                // Read-only patient card (printable, under Patient List)
                'full_record' => route('health_worker.patient_view', ['id' => $patient->id]),
                // Editable patient card (under Vaccination Status)
                'update_vaccination' => route('health_worker.patient_card', ['id' => $patient->id]),
            ]
        ]);
    }

    /**
     * Generate QR code image for a patient.
     *
     * @param int $patientId
     * @return \Illuminate\Http\Response
     */
    public function generateQrImage(int $patientId)
    {
        $patient = Patient::findOrFail($patientId);

        // Ensure patient has QR UUID
        if (empty($patient->qr_uuid)) {
            $patient->qr_uuid = \Illuminate\Support\Str::uuid()->toString();
            $patient->save();
        }

        $svg = $this->qrService->generateSvg($patient, 200);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Get QR code as base64 for embedding (used in print templates).
     *
     * @param int $patientId
     * @return JsonResponse
     */
    public function getQrBase64(int $patientId): JsonResponse
    {
        $patient = Patient::findOrFail($patientId);

        // Ensure patient has QR UUID
        if (empty($patient->qr_uuid)) {
            $patient->qr_uuid = \Illuminate\Support\Str::uuid()->toString();
            $patient->save();
        }

        return response()->json([
            'success' => true,
            'qr_svg' => $this->qrService->generateBase64Svg($patient, 150),
            'qr_url' => $this->qrService->getQrUrl($patient),
        ]);
    }

    /**
     * Generate QR code image for a patient (Parent access - restricted to their children).
     *
     * @param int $patientId
     * @return \Illuminate\Http\Response
     */
    public function generateParentQrImage(int $patientId)
    {
        $parent = auth('parents')->user();
        
        // Find patient and verify it belongs to this parent
        $patient = Patient::where('id', $patientId)
            ->where('parent_id', $parent->id)
            ->firstOrFail();

        // Ensure patient has QR UUID
        if (empty($patient->qr_uuid)) {
            $patient->qr_uuid = \Illuminate\Support\Str::uuid()->toString();
            $patient->save();
        }

        $svg = $this->qrService->generateSvg($patient, 200);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * Calculate human-readable age from date of birth.
     * Uses single unit: days, weeks, months, or years (whichever is most appropriate)
     * No decimal places - whole numbers only
     *
     * @param Carbon $dob
     * @return string
     */
    private function calculateAge(Carbon $dob): string
    {
        $now = Carbon::now();
        $totalDays = (int) $dob->diffInDays($now);
        
        // If less than 7 days, show days
        if ($totalDays < 7) {
            return $totalDays . ' day' . ($totalDays != 1 ? 's' : '') . ' old';
        }
        
        // If less than 4 weeks (28 days), show weeks
        if ($totalDays < 28) {
            $weeks = (int) floor($totalDays / 7);
            return $weeks . ' week' . ($weeks != 1 ? 's' : '') . ' old';
        }
        
        // If less than 12 months, show months
        $months = (int) $dob->diffInMonths($now);
        if ($months < 12) {
            return $months . ' month' . ($months != 1 ? 's' : '') . ' old';
        }
        
        // Otherwise show years
        $years = (int) $dob->diffInYears($now);
        return $years . ' year' . ($years != 1 ? 's' : '') . ' old';
    }

    /**
     * Format doses label for display
     * Simple format: "X/Y" (e.g., "1/3" = 1 of 3 doses taken)
     *
     * @param array $dosesTaken Array of dose numbers taken (e.g., [1, 2])
     * @param int $totalDoses Total doses required
     * @return string
     */
    private function formatDosesLabel(array $dosesTaken, int $totalDoses): string
    {
        $count = count($dosesTaken);
        return "{$count}/{$totalDoses}";
    }

    /**
     * Determine total required doses from vaccine description.
     * Cached logic for performance.
     *
     * @param string $dosesDesc
     * @return int
     */
    private function getTotalDoses(string $dosesDesc): int
    {
        // Simple check: if description mentions multiple doses
        if (stripos($dosesDesc, '1 1/2') !== false && stripos($dosesDesc, '2 1/2') !== false && stripos($dosesDesc, '3 1/2') !== false) {
            return 3;
        }
        if (stripos($dosesDesc, 'Grade 1') !== false && stripos($dosesDesc, 'Grade 7') !== false) {
            return 2;
        }
        if ($dosesDesc === 'Pagkapanganak') {
            return 1;
        }
        
        // Pattern like "1 1/2, 2 1/2, 3 1/2 Buwan" = 3 doses (count commas + 1)
        $commaCount = substr_count($dosesDesc, ',');
        if ($commaCount > 0) {
            return $commaCount + 1;
        }
        
        return 1; // default single dose
    }
}
