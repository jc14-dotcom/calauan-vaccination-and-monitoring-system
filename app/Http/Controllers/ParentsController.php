<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\PatientGrowthRecord;
use App\Models\PatientVaccineRecord; // Add this line
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ParentsController extends Controller
{
    // ...existing methods...

    public function infantsRecord($id)
    {
        // Check if we have an authenticated parent and verify patient belongs to them
        if (auth('parents')->check()) {
            $parent = auth('parents')->user();
            $patient = Patient::where('id', $id)
                ->where('parent_id', $parent->id)
                ->firstOrFail(); // Get ALL fields, not just select()
        } else {
            // For admin/health worker access, get patient directly
            $patient = Patient::findOrFail($id);
        }
        
        // Test decryption manually
        try {
            $testName = $patient->name;
            $testSex = $patient->sex;
            $testDOB = $patient->date_of_birth;
            $testBH = $patient->birth_height;
            $testBW = $patient->birth_weight;
            
            Log::info('Manual decryption test:', [
                'name_success' => !empty($testName),
                'sex_success' => !empty($testSex),
                'dob_success' => !empty($testDOB),
                'bh_success' => !empty($testBH),
                'bw_success' => !empty($testBW),
                'authenticated_as' => auth('parents')->check() ? 'parent' : 'other',
                'app_key_exists' => !empty(config('app.key')),
                'app_key_length' => strlen(config('app.key'))
            ]);
        } catch (\Exception $e) {
            Log::error('Decryption error: ' . $e->getMessage());
        }
        
        // Load the relationships
        $patient->load([
            'latestGrowthRecord', 
            'growthRecords',
            'parent'
        ]);
        
        // Load vaccination records - Force fresh query to bypass caching
        $vaccinations = DB::select("
            SELECT pvr.*, v.vaccine_name, v.doses_description, v.stocks, v.is_standard
            FROM patient_vaccine_records pvr 
            JOIN vaccines v ON pvr.vaccine_id = v.id 
            WHERE pvr.patient_id = ?
            ORDER BY pvr.id
        ", [$id]);
        
        // Convert to collection for easier use in view
        $vaccinations = collect($vaccinations)->map(function($item) {
            $vaccination = new PatientVaccineRecord();
            $vaccination->id = $item->id;
            $vaccination->patient_id = $item->patient_id;
            $vaccination->vaccine_id = $item->vaccine_id;
            $vaccination->dose_1_date = $item->dose_1_date;
            $vaccination->dose_2_date = $item->dose_2_date;
            $vaccination->dose_3_date = $item->dose_3_date;
            $vaccination->dose_4_date = $item->dose_4_date;
            $vaccination->dose_5_date = $item->dose_5_date;
            $vaccination->dose_6_date = $item->dose_6_date;
            $vaccination->dose_7_date = $item->dose_7_date;
            $vaccination->dose_8_date = $item->dose_8_date;
            $vaccination->dose_9_date = $item->dose_9_date;
            $vaccination->dose_10_date = $item->dose_10_date;
            $vaccination->remarks = $item->remarks;
            
            // Create vaccine object
            $vaccine = new \App\Models\Vaccine();
            $vaccine->id = $item->vaccine_id;
            $vaccine->vaccine_name = $item->vaccine_name;
            $vaccine->doses_description = $item->doses_description;
            $vaccine->stocks = $item->stocks;
            $vaccine->is_standard = (bool) ($item->is_standard ?? false);
            
            $vaccination->setRelation('vaccine', $vaccine);
            return $vaccination;
        });
        
        return view('parents.infantsRecord', compact('patient', 'vaccinations'));
    }

    // ...existing methods...
}