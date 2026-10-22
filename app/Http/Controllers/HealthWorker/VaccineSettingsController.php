<?php

namespace App\Http\Controllers\HealthWorker;

use App\Http\Controllers\Controller;
use App\Models\PatientVaccineRecord;
use App\Models\Vaccine;
use App\Models\VaccineDoseConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Facades\Activity;

class VaccineSettingsController extends Controller
{
    private function requireRHU()
    {
        $user = Auth::guard('health_worker')->user();
        if (!$user || !$user->isRHU()) {
            abort(403, 'Access restricted to RHU administrators.');
        }
        return $user;
    }

    public function index()
    {
        $this->requireRHU();

        $vaccines = Vaccine::with(['doseConfigs'])
            ->orderBy('sort_order')
            ->orderBy('vaccine_name')
            ->get();

        return view('health_worker.vaccine-settings.index', compact('vaccines'));
    }

    public function edit(Request $request, $id)
    {
        $this->requireRHU();

        $vaccine = Vaccine::with(['doseConfigs'])->findOrFail($id);

        $ageGroupOptions = [
            'under_1_year' => 'Under 1 Year (Infants)',
            '0_12_months'  => '0–12 Months',
            'grade_1'      => 'Grade 1 (6–7 years old)',
            'grade_7'      => 'Grade 7 (12–13 years old)',
        ];

        $returnTo = $request->query('return', 'settings'); // 'inventory' or 'settings'

        return view('health_worker.vaccine-settings.edit', compact('vaccine', 'ageGroupOptions', 'returnTo'));
    }

    public function update(Request $request, $id)
    {
        $this->requireRHU();

        $vaccine = Vaccine::findOrFail($id);

        $validated = $request->validate([
            'vaccine_name'          => 'required|string|max:255',
            'acronym'               => 'nullable|string|max:50',
            'total_doses'           => 'required|integer|min:1|max:10',
            'target_age_group'      => 'required|string|in:under_1_year,0_12_months,grade_1,grade_7',
            'sort_order'            => 'required|integer|min:0|max:9999',
            'is_standard'           => 'boolean',
            'is_active_in_reports'  => 'boolean',
            'dose_min'              => 'nullable|array',
            'dose_min.*'            => 'nullable|numeric|min:0',
            'dose_max'              => 'nullable|array',
            'dose_max.*'            => 'nullable|numeric|min:0',
        ]);

        $totalDoses = (int) $validated['total_doses'];

        DB::transaction(function () use ($vaccine, $validated, $totalDoses, $request) {
            $vaccine->update([
                'vaccine_name'         => $validated['vaccine_name'],
                'acronym'              => $validated['acronym'] ?? null,
                'total_doses'          => $totalDoses,
                'target_age_group'     => $validated['target_age_group'],
                'sort_order'           => (int) $validated['sort_order'],
                'is_standard'          => $request->boolean('is_standard'),
                'is_active_in_reports' => $request->boolean('is_active_in_reports'),
            ]);

            // Rebuild dose config rows: remove extras, upsert actives
            VaccineDoseConfig::where('vaccine_id', $vaccine->id)
                ->where('dose_number', '>', $totalDoses)
                ->delete();

            $minValues = $request->input('dose_min', []);
            $maxValues = $request->input('dose_max', []);

            for ($dose = 1; $dose <= $totalDoses; $dose++) {
                VaccineDoseConfig::updateOrCreate(
                    ['vaccine_id' => $vaccine->id, 'dose_number' => $dose],
                    [
                        'min_age_months' => isset($minValues[$dose]) ? (float) $minValues[$dose] : 0,
                        'max_age_months' => isset($maxValues[$dose]) ? (float) $maxValues[$dose] : 0,
                    ]
                );
            }
        });

        // Cache is already busted by the Vaccine::saved() event, but be explicit
        Cache::forget('vaccine_config');

        activity('vaccine_settings')->event('vaccine_settings_updated')
            ->performedOn($vaccine)
            ->withProperties([
                'vaccine_name' => $vaccine->vaccine_name,
                'total_doses' => $totalDoses,
                'is_standard' => $request->boolean('is_standard'),
                'is_active_in_reports' => $request->boolean('is_active_in_reports'),
            ])
            ->log("Updated vaccine settings for {$vaccine->vaccine_name}");

        $returnTo = $request->input('return_to', 'settings');
        if ($returnTo === 'inventory') {
            return redirect()
                ->route('health_worker.inventory')
                ->with('success', "Vaccine settings for \"{$vaccine->vaccine_name}\" saved successfully.");
        }

        return redirect()
            ->route('health_worker.vaccine-settings.index')
            ->with('success', "Vaccine settings for \"{$vaccine->vaccine_name}\" saved successfully.");
    }

    protected function getVaccineById($id)
    {
        return Vaccine::findOrFail($id);
    }

    protected function hasRecordedDoseData($id): bool
    {
        return PatientVaccineRecord::where('vaccine_id', $id)
            ->where(function ($q) {
                $q->whereNotNull('dose_1_date')
                  ->orWhereNotNull('dose_2_date')
                  ->orWhereNotNull('dose_3_date')
                  ->orWhereNotNull('dose_4_date')
                  ->orWhereNotNull('dose_5_date')
                  ->orWhereNotNull('dose_6_date')
                  ->orWhereNotNull('dose_7_date')
                  ->orWhereNotNull('dose_8_date')
                  ->orWhereNotNull('dose_9_date')
                  ->orWhereNotNull('dose_10_date');
            })
            ->exists();
    }

    protected function deletePatientVaccineRecords($id): void
    {
        PatientVaccineRecord::where('vaccine_id', $id)->delete();
    }

    protected function deleteDoseConfigs($id): void
    {
        VaccineDoseConfig::where('vaccine_id', $id)->delete();
    }

    public function destroy(Request $request, $id)
    {
        $this->requireRHU();

        $vaccine = $this->getVaccineById($id);

        $hasData = $this->hasRecordedDoseData($id);

        if ($hasData && !$request->boolean('force_delete')) {
            return response()->json([
                'success'  => false,
                'has_data' => true,
                'message'  => 'This vaccine has recorded dose data. Confirm deletion with the checkbox.',
            ], 422);
        }

        $name = $vaccine->vaccine_name;

        activity('vaccine_settings')->event('vaccine_deleted')
            ->performedOn($vaccine)
            ->withProperties([
                'vaccine_name' => $vaccine->vaccine_name,
                'force_delete' => $request->boolean('force_delete'),
            ])
            ->log("Deleted vaccine: {$name}");

        $this->deletePatientVaccineRecords($id);
        $this->deleteDoseConfigs($id);
        $vaccine->delete();

        Cache::forget('vaccine_config');

        return response()->json([
            'success' => true,
            'message' => "\"{$name}\" has been deleted.",
        ]);
    }
}
