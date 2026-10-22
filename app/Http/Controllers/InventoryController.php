<?php
//InventoryController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vaccine;
use App\Models\VaccineDoseConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

class InventoryController extends Controller
{
    /**
     * Get the current health worker from the session.
     */
    private function getHealthWorker()
    {
        return Auth::guard('health_worker')->user();
    }

    /**
     * Check if current health worker is RHU admin (can modify inventory).
     * Returns error response if not authorized.
     */
    private function checkRHUAccess()
    {
        $healthWorker = $this->getHealthWorker();
        
        if ($healthWorker && !$healthWorker->isRHU()) {
            return response()->json([
                'success' => false,
                'message' => 'Only RHU administrators can modify inventory.'
            ], 403);
        }
        
        return null; // No error, access granted
    }

    // Method to display the inventory form
    public function index()
    {
        $healthWorker = $this->getHealthWorker();
        
        // Clear API cache to ensure fresh data
        Cache::forget('vaccine_stocks_list');
        
        // Force fresh database connection to bypass any connection-level caching
        DB::reconnect();
        
        // Pull all vaccines ordered by ID (database sequence)
        $vaccines = Vaccine::orderBy('id')->get();

        $inventorySummary = $vaccines->map(function ($vaccine) {
            $availableDoses = max(0, (int) ($vaccine->stocks ?? 0));
            $availableBottles = max(0, (int) ($vaccine->available_bottles ?? 0));
            $dosesPerBottle = max(1, (int) ($vaccine->doses_per_bottle ?? 10));

            if ($availableDoses <= 0) {
                $status = 'out';
            } elseif ($availableDoses < 10) {
                $status = 'low';
            } elseif ($availableDoses < 50) {
                $status = 'medium';
            } else {
                $status = 'high';
            }

            return [
                'vaccine' => $vaccine->fresh(),
                'available_doses' => $availableDoses,
                'available_bottles' => $availableBottles,
                'doses_per_bottle' => $dosesPerBottle,
                'status' => $status,
            ];
        });

        // Prevent ALL caching - force fresh data every time
        return response()
            ->view('health_worker.inventory', compact('inventorySummary', 'healthWorker'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT')
            ->header('ETag', '')
            ->header('Vary', '*');
    }

    // Method to store inventory data
    // RHU ONLY - Barangay workers cannot store inventory
    public function store(Request $request)
    {
        // Check RHU access
        $accessError = $this->checkRHUAccess();
        if ($accessError) return $accessError;
        
        // Validate the request data
        $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'stocks' => 'required|integer',
        ]);

        // Store the vaccine data
        $vaccine = Vaccine::create([
            'vaccine_name' => $request->vaccine_name,
            'stocks' => $request->stocks,
            'doses_description' => '',
        ]);

        activity('vaccine')->event('vaccine_stored')
            ->performedOn($vaccine)
            ->withProperties([
                'vaccine_name' => $request->vaccine_name,
                'stocks' => $request->stocks,
            ])
            ->log("Stored vaccine: {$request->vaccine_name}");

        // Clear the vaccine stocks cache
        Cache::forget('vaccine_stocks_list');

        // Redirect back to the inventory page with a success message
        return redirect()->route('inventory.index')->with('success', 'Vaccine added successfully.');
    }

    // Method to update inventory data
    // RHU ONLY - Barangay workers cannot update inventory
    public function update(Request $request, $id)
    {
        // Check RHU access
        $accessError = $this->checkRHUAccess();
        if ($accessError) return $accessError;
        
        try {
            $validated = $request->validate([
                'available_bottles' => 'required|integer|min:0',
                'doses_per_bottle' => 'required|integer|min:1',
                'available_doses' => 'nullable|integer|min:0',
            ]);

            $vaccine = Vaccine::findOrFail($id);
            $oldStocks = (int) $vaccine->stocks;

            $newBottles = (int) ($validated['available_bottles'] ?? 0);
            $dosesPerBottle = (int) ($validated['doses_per_bottle'] ?? 10);
            $manualDoses = array_key_exists('available_doses', $validated)
                ? ($validated['available_doses'] ?? null)
                : null;

            $calculatedDoses = $newBottles * $dosesPerBottle;
            $newStocks = is_null($manualDoses) ? $calculatedDoses : (int) $manualDoses;

            $vaccine->stocks = $newStocks;
            $vaccine->available_bottles = $newBottles;
            $vaccine->doses_per_bottle = $dosesPerBottle;
            
            // Reset the tracking counter when user manually updates inventory
            $vaccine->doses_used_from_current_bottles = 0;
            
            $vaccine->save();

            $availableDoses = (int) ($vaccine->stocks ?? 0);
            $availableBottles = (int) ($vaccine->available_bottles ?? 0);
            $currentDosesPerBottle = (int) ($vaccine->doses_per_bottle ?? 10);

            if ($availableDoses <= 0) {
                $status = 'out';
            } elseif ($availableDoses < 10) {
                $status = 'low';
            } elseif ($availableDoses < 50) {
                $status = 'medium';
            } else {
                $status = 'high';
            }

            // Clear all caches to ensure fresh data on reload
            Cache::forget('vaccine_stocks_list');
            Cache::flush();

            activity('vaccine')->event('vaccine_updated')
                ->performedOn($vaccine)
                ->withProperties([
                    'vaccine_name' => $vaccine->vaccine_name,
                    'old_stocks' => $oldStocks,
                    'new_stocks' => $newStocks,
                    'available_bottles' => $newBottles,
                ])
                ->log("Updated inventory for {$vaccine->vaccine_name}");

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'old_stock' => $oldStocks,
                'new_stock' => $newStocks,
                'available_doses' => $availableDoses,
                'available_bottles' => $availableBottles,
                'doses_per_bottle' => $currentDosesPerBottle,
                'status' => $status,
                'vaccine_id' => $vaccine->id,
                'vaccine_name' => $vaccine->vaccine_name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Method to add a new vaccine to inventory
    // RHU ONLY - Barangay workers cannot add vaccines
    public function addVaccine(Request $request)
    {
        // Check RHU access
        $accessError = $this->checkRHUAccess();
        if ($accessError) return $accessError;

        try {
            // Validate the request data
            $request->validate([
                'vaccine_name'         => 'required|string|max:255|unique:vaccines,vaccine_name',
                'description'          => 'nullable|string|max:500',
                'stocks'               => 'required|integer|min:0',
                'acronym'              => 'nullable|string|max:50',
                'total_doses'          => 'nullable|integer|min:1|max:10',
                'target_age_group'     => 'nullable|string|in:under_1_year,0_12_months,grade_1,grade_7',
                'is_standard'          => 'nullable|boolean',
                'is_active_in_reports' => 'nullable|boolean',
                'dose_min'             => 'nullable|array',
                'dose_min.*'           => 'nullable|numeric|min:0',
                'dose_max'             => 'nullable|array',
                'dose_max.*'           => 'nullable|numeric|min:0',
            ]);

            $totalDosesSchedule = (int) $request->input('total_doses', 1);

            $vaccine = Vaccine::create([
                'vaccine_name'                    => $request->vaccine_name,
                'doses_description'               => $request->description ?? '',
                'stocks'                          => $request->stocks,
                'doses_used_from_current_bottles' => 0,
                'total_doses'                     => $totalDosesSchedule,
                'target_age_group'                => $request->input('target_age_group', 'under_1_year'),
                'acronym'                         => $request->input('acronym') ?: null,
                'is_standard'                     => (bool) $request->input('is_standard', false),
                'is_active_in_reports'            => (bool) $request->input('is_active_in_reports', false),
                'sort_order'                      => 99,
            ]);

            // Create dose config rows for each dose in the schedule
            $doseMin = $request->input('dose_min', []);
            $doseMax = $request->input('dose_max', []);
            for ($i = 1; $i <= $totalDosesSchedule; $i++) {
                VaccineDoseConfig::create([
                    'vaccine_id'     => $vaccine->id,
                    'dose_number'    => $i,
                    'min_age_months' => (float) ($doseMin[$i] ?? 0),
                    'max_age_months' => (float) ($doseMax[$i] ?? 0),
                ]);
            }

            // Clear the vaccine stocks cache
            Cache::forget('vaccine_stocks_list');

            activity('vaccine')->event('vaccine_created')
                ->performedOn($vaccine)
                ->withProperties([
                    'vaccine_name' => $vaccine->vaccine_name,
                    'total_doses' => $totalDosesSchedule,
                    'is_standard' => $vaccine->is_standard,
                    'is_active_in_reports' => $vaccine->is_active_in_reports,
                    'stocks' => $request->stocks,
                ])
                ->log("Added new vaccine: {$vaccine->vaccine_name}");

            return response()->json([
                'success' => true,
                'message' => 'Vaccine added successfully.',
                'vaccine' => $vaccine,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding the vaccine: ' . $e->getMessage(),
            ], 500);
        }
    }
}
