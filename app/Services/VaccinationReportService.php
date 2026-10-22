<?php

namespace App\Services;

use App\Models\VaccinationReportSnapshot;
use App\Models\Vaccine;
use App\Models\Patient;
use App\Models\PatientVaccineRecord;
use App\Config\VaccineConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class VaccinationReportService
{
    /**
     * List of all barangays in Calauan, Laguna
     */
    private $barangays = [
        'Balayhangin',
        'Bangyas',
        'Dayap',
        'Hanggan',
        'Imok',
        'Kanluran',
        'Lamot 1',
        'Lamot 2',
        'Limao',
        'Mabacan',
        'Masiit',
        'Paliparan',
        'Perez',
        'Prinza',
        'San Isidro',
        'Santo Tomas',
        'Silangan'
    ];
    
    /**
     * Get LIVE report data - always calculates from patient records, never uses snapshots
     * Use this for the Current Report page to show real-time data
     * OPTIMIZED: Caches calculated result for 5 minutes
     */
    public function getCurrentReport($year, $quarterStart, $quarterEnd, $barangayFilter = null, $monthStart = null, $monthEnd = null)
    {
        // Generate cache key for live data - include months in cache key
        $cacheKey = "report:live:{$year}:m{$monthStart}-{$monthEnd}:q{$quarterStart}-{$quarterEnd}:" . ($barangayFilter ?? 'all');
        
        // Cache live data for 5 minutes
        return Cache::remember($cacheKey, 300, function () use ($year, $quarterStart, $quarterEnd, $barangayFilter, $monthStart, $monthEnd) {
            return [
                'data' => $this->calculateLiveData($year, $quarterStart, $quarterEnd, $barangayFilter),
                'source' => 'live',
                'is_locked' => false,
                'version' => null,
                'saved_at' => null,
                'date_range' => $this->getDateRange($year, $quarterStart, $quarterEnd, $monthStart, $monthEnd)
            ];
        });
    }
    
    /**
     * Get report data - checks for snapshots first, otherwise calculates live
     * Use this for viewing archived reports (Report History)
     * OPTIMIZED: Caches entire report result for 5 minutes
     */
    public function getReport($year, $quarterStart, $quarterEnd, $barangayFilter = null, $version = null)
    {
        // Generate cache key for this specific report query
        $cacheKey = $this->getReportCacheKey($year, $quarterStart, $quarterEnd, $barangayFilter, $version);
        
        // Try to get from cache first (5 minute TTL)
        return Cache::remember($cacheKey, 300, function () use ($year, $quarterStart, $quarterEnd, $barangayFilter, $version) {
            return $this->fetchReportData($year, $quarterStart, $quarterEnd, $barangayFilter, $version);
        });
    }
    
    /**
     * Fetch report data (called by getReport when cache misses)
     */
    protected function fetchReportData($year, $quarterStart, $quarterEnd, $barangayFilter = null, $version = null)
    {
        // Check if snapshot exists for this period
        $query = VaccinationReportSnapshot::forPeriod($year, $quarterStart, $quarterEnd);
        
        // If version specified, get that version; otherwise get latest
        if ($version !== null) {
            $query->where('version', $version);
        } else {
            // Get latest version only
            $latestVersion = $this->getLatestVersion($year, $quarterStart, $quarterEnd);
            if ($latestVersion) {
                $query->where('version', $latestVersion);
            }
        }
        
        if ($barangayFilter) {
            $query->where('barangay', $barangayFilter); // Exact match (field is now decrypted)
        }
        
        // Order by barangay - NULL (TOTAL) should come last
        $query->orderByRaw('CASE WHEN barangay IS NULL THEN 1 ELSE 0 END, barangay ASC');
        
        $snapshots = $query->get();
        
        if ($snapshots->isNotEmpty()) {
            $firstSnapshot = $snapshots->first();
            return [
                'data' => $this->formatSnapshotData($snapshots),
                'source' => 'snapshot',
                'is_locked' => $firstSnapshot->is_locked ?? false,
                'version' => $firstSnapshot->version ?? null,
                'saved_at' => $firstSnapshot->saved_at ?? null,
                'month_start' => $firstSnapshot->month_start,
                'month_end' => $firstSnapshot->month_end,
                'date_range' => $firstSnapshot->date_range // Use model's date_range accessor which respects month values
            ];
        }
        
        // Calculate from live data
        return [
            'data' => $this->calculateLiveData($year, $quarterStart, $quarterEnd, $barangayFilter),
            'source' => 'live',
            'is_locked' => false,
            'version' => null,
            'saved_at' => null,
            'date_range' => $this->getDateRange($year, $quarterStart, $quarterEnd)
        ];
    }
    
    /**
     * Generate cache key for report queries
     */
    protected function getReportCacheKey($year, $quarterStart, $quarterEnd, $barangayFilter = null, $version = null)
    {
        $parts = [
            'report',
            $year,
            "q{$quarterStart}-{$quarterEnd}",
            $barangayFilter ?? 'all',
            $version ?? 'latest'
        ];
        return implode(':', $parts);
    }
    
    /**
     * Clear report cache for a specific period
     */
    public function clearReportCache($year, $quarterStart, $quarterEnd, $barangayFilter = null)
    {
        // Clear all related cache keys (all versions, with/without barangay filter)
        $pattern = "report:{$year}:q{$quarterStart}-{$quarterEnd}:*";
        
        // Since we can't pattern-match in Redis easily via Laravel,
        // we'll clear the most common cache keys
        $keysToForget = [
            $this->getReportCacheKey($year, $quarterStart, $quarterEnd, $barangayFilter, null),
            $this->getReportCacheKey($year, $quarterStart, $quarterEnd, null, null),
        ];
        
        foreach ($keysToForget as $key) {
            Cache::forget($key);
        }
    }
    
    /**
     * Calculate live data with DOSE-LEVEL breakdown and FIC/CIC columns
     * NEW: Batch 2 - Enhanced to show separate columns per dose
     */
    public function calculateLiveData($year, $quarterStart, $quarterEnd, $barangayFilter = null)
    {
        // Detect if we're using month values (new format) or quarter values (old format)
        // If either value > 4, treat as months; otherwise treat as quarters
        if ($quarterStart > 4 || $quarterEnd > 4) {
            // New format: values are actual months (1-12)
            $monthStart = $quarterStart;
            $monthEnd = $quarterEnd;
        } else {
            // Old format: convert quarters to months
            $monthStart = ($quarterStart - 1) * 3 + 1;
            $monthEnd = $quarterEnd * 3;
        }
        
        $startDate = Carbon::create($year, $monthStart, 1)->startOfMonth();
        $endDate = Carbon::create($year, $monthEnd, 1)->endOfMonth();
        
        // Get barangays to process
        $barangays = $barangayFilter ? [$barangayFilter] : $this->barangays;
        
        // Get vaccine configuration
        $vaccineConfig = \App\Config\VaccineConfig::getDoseConfiguration();
        
        $reportData = [];
        
        foreach ($barangays as $barangay) {
            $rowData = [
                'barangay' => $barangay,
                'eligible_population_under_1_year' => $this->calculateEligiblePopulation($barangay, $endDate, 'under_1_year'),
                'eligible_population_0_12_months' => $this->calculateEligiblePopulation($barangay, $endDate, '0_12_months'),
                'eligible_population_13_23_months' => $this->calculateEligiblePopulation($barangay, $endDate, '13_23_months'),
                'eligible_population_0_23_months' => $this->calculateEligiblePopulation($barangay, $endDate, '0_23_months'),
                'vaccines' => [],
                'fic' => null,
                'cic' => null,
            ];
            
            // Get dose-level data for each vaccine
            foreach ($vaccineConfig as $vaccineName => $config) {
                $totalDoses = $config['total_doses'];
                
                for ($dose = 1; $dose <= $totalDoses; $dose++) {
                    // Special handling for IPV Dose 2 - split into Routine and Catch-up
                    $isIPV2 = ($vaccineName === 'Inactivated Polio' && $dose === 2);
                    
                    if ($isIPV2) {
                        // IPV 2 Routine
                        $routineKey = "{$vaccineName}|Dose {$dose}|Routine";
                        $routineData = $this->getVaccineDoseCount(
                            $barangay,
                            $vaccineName,
                            $dose,
                            $startDate,
                            $endDate,
                            'routine'
                        );
                        
                        // Calculate percentage
                        $targetAgeGroup = $config['target_age_group'];
                        $eligiblePop = 0;
                        
                        if ($targetAgeGroup === 'under_1_year') {
                            $eligiblePop = $rowData['eligible_population_under_1_year'];
                        } elseif ($targetAgeGroup === '0_12_months') {
                            $eligiblePop = $rowData['eligible_population_0_12_months'];
                        } elseif ($targetAgeGroup === '13_23_months') {
                            $eligiblePop = $rowData['eligible_population_13_23_months'];
                        }
                        
                        $routineData['percentage'] = $eligiblePop > 0 
                            ? round(($routineData['total_count'] / $eligiblePop) * 100, 2)
                            : 0.00;
                        
                        $rowData['vaccines'][$routineKey] = $routineData;
                        
                        // IPV 2 Catch-up
                        $catchupKey = "{$vaccineName}|Dose {$dose}|Catch-up";
                        $catchupData = $this->getVaccineDoseCount(
                            $barangay,
                            $vaccineName,
                            $dose,
                            $startDate,
                            $endDate,
                            'catchup'
                        );
                        
                        $catchupData['percentage'] = $eligiblePop > 0 
                            ? round(($catchupData['total_count'] / $eligiblePop) * 100, 2)
                            : 0.00;
                        
                        $rowData['vaccines'][$catchupKey] = $catchupData;
                        
                    } else {
                        // Regular dose handling for all other vaccines
                        if ($totalDoses > 1) {
                            $doseKey = "{$vaccineName}|Dose {$dose}";
                        } else {
                            $doseKey = $vaccineName;
                        }
                        
                        // Get counts for this specific dose
                        $doseData = $this->getVaccineDoseCount(
                            $barangay,
                            $vaccineName,
                            $dose,
                            $startDate,
                            $endDate
                        );
                        
                        // Calculate percentage based on target age group
                        $targetAgeGroup = $config['target_age_group'];
                        $eligiblePop = 0;
                        
                        if ($targetAgeGroup === 'under_1_year') {
                            $eligiblePop = $rowData['eligible_population_under_1_year'];
                        } elseif ($targetAgeGroup === '0_12_months') {
                            $eligiblePop = $rowData['eligible_population_0_12_months'];
                        } elseif ($targetAgeGroup === '13_23_months') {
                            $eligiblePop = $rowData['eligible_population_13_23_months'];
                        } elseif ($targetAgeGroup === 'grade_1') {
                            // Calculate grade 1 eligible population on the fly
                            $eligiblePop = $this->calculateEligiblePopulation($barangay, $endDate, 'grade_1');
                        } elseif ($targetAgeGroup === 'grade_7') {
                            // Calculate grade 7 eligible population on the fly
                            $eligiblePop = $this->calculateEligiblePopulation($barangay, $endDate, 'grade_7');
                        }
                        
                        $doseData['percentage'] = $eligiblePop > 0 
                            ? round(($doseData['total_count'] / $eligiblePop) * 100, 2)
                            : 0.00;
                        
                        $rowData['vaccines'][$doseKey] = $doseData;
                    }
                }
            }
            
            // Calculate FIC (Fully Immunized Children)
            $rowData['fic'] = $this->calculateFICCount($barangay, $year, $monthStart, $monthEnd);
            
            // Calculate CIC (Completely Immunized Children)
            $rowData['cic'] = $this->calculateCICCount($barangay, $year, $monthStart, $monthEnd);
            
            $reportData[] = $rowData;
        }
        
        // Add TOTAL row
        $reportData[] = $this->calculateTotalRow($reportData, $vaccineConfig);
        
        return $reportData;
    }
    
    /**
     * Get vaccination statistics for a specific vaccine and barangay
     * OPTIMIZED: Use database aggregation instead of PHP loops
     * Note: Counts any dose (dose_1, dose_2, or dose_3) as a vaccination
     */
    private function getVaccineStats($vaccineId, $barangay, $startDate, $endDate)
    {
        // Use raw SQL with CASE statements for better performance
        $stats = DB::table('patient_vaccine_records as pvr')
            ->join('patients as p', 'pvr.patient_id', '=', 'p.id')
            ->where('pvr.vaccine_id', $vaccineId)
            ->where('p.barangay', $barangay) // Exact match (field is now decrypted)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('pvr.dose_1_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_2_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_3_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_4_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_5_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_6_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_7_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_8_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_9_date', [$startDate, $endDate])
                      ->orWhereBetween('pvr.dose_10_date', [$startDate, $endDate]);
            })
            ->selectRaw('
                SUM(CASE WHEN p.sex LIKE "Male%" THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN p.sex LIKE "Female%" THEN 1 ELSE 0 END) as female_count
            ')
            ->first();
        
        $maleCount = (int) ($stats->male_count ?? 0);
        $femaleCount = (int) ($stats->female_count ?? 0);
        
        return [
            'male' => $maleCount,
            'female' => $femaleCount,
            'total' => $maleCount + $femaleCount
        ];
    }
    
    /**
     * Get eligible population for a barangay (children under 1 year old)
     * OPTIMIZED: Cache results for 10 minutes (population doesn't change frequently)
     */
    private function getEligiblePopulation($barangay, $year)
    {
        $cacheKey = "eligible_pop_{$barangay}_{$year}";
        
        return Cache::remember($cacheKey, 600, function () use ($barangay, $year) {
            // Calculate age range: children who will turn 0-1 years old during the year
            $startOfYear = Carbon::create($year, 1, 1);
            $endOfYear = Carbon::create($year, 12, 31);
            
            // Birth date range: born in current year or previous year
            $minBirthDate = $startOfYear->copy()->subYear();
            $maxBirthDate = $endOfYear;
            
            // Exact match for decrypted barangay field
            return Patient::where('barangay', $barangay)
                ->whereBetween('date_of_birth', [$minBirthDate, $maxBirthDate])
                ->count();
        });
    }
    
    /**
     * Calculate TOTAL row with dose-level breakdown and FIC/CIC
     * NEW: Batch 2 - Enhanced to sum all dose columns
     */
    private function calculateTotalRow($reportData, $vaccineConfig)
    {
        $totals = [
            'barangay' => 'TOTAL',
            'eligible_population_under_1_year' => 0,
            'eligible_population_0_12_months' => 0,
            'eligible_population_13_23_months' => 0,
            'eligible_population_0_23_months' => 0,
            'vaccines' => [],
            'fic' => [
                'male_count' => 0,
                'female_count' => 0,
                'total_count' => 0,
                'percentage' => 0.00
            ],
            'cic' => [
                'male_count' => 0,
                'female_count' => 0,
                'total_count' => 0,
                'percentage' => 0.00
            ],
        ];
        
        // Initialize totals for all vaccine doses
        foreach ($vaccineConfig as $vaccineName => $config) {
            $totalDoses = $config['total_doses'];
            
            for ($dose = 1; $dose <= $totalDoses; $dose++) {
                // Special handling for IPV Dose 2 - create separate keys for Routine and Catch-up
                $isIPV2 = ($vaccineName === 'Inactivated Polio' && $dose === 2);
                
                if ($isIPV2) {
                    $routineKey = "{$vaccineName}|Dose {$dose}|Routine";
                    $catchupKey = "{$vaccineName}|Dose {$dose}|Catch-up";
                    
                    $totals['vaccines'][$routineKey] = [
                        'male_count' => 0,
                        'female_count' => 0,
                        'total_count' => 0,
                        'percentage' => 0.00
                    ];
                    
                    $totals['vaccines'][$catchupKey] = [
                        'male_count' => 0,
                        'female_count' => 0,
                        'total_count' => 0,
                        'percentage' => 0.00
                    ];
                } else {
                    if ($totalDoses > 1) {
                        $doseKey = "{$vaccineName}|Dose {$dose}";
                    } else {
                        $doseKey = $vaccineName;
                    }
                    
                    $totals['vaccines'][$doseKey] = [
                        'male_count' => 0,
                        'female_count' => 0,
                        'total_count' => 0,
                        'percentage' => 0.00
                    ];
                }
            }
        }
        
        // Sum up all barangay data
        foreach ($reportData as $row) {
            if ($row['barangay'] === 'TOTAL') continue; // Skip if TOTAL already exists
            
            // Sum eligible populations
            $totals['eligible_population_under_1_year'] += $row['eligible_population_under_1_year'];
            $totals['eligible_population_0_12_months'] += $row['eligible_population_0_12_months'];
            $totals['eligible_population_13_23_months'] += $row['eligible_population_13_23_months'];
            $totals['eligible_population_0_23_months'] += $row['eligible_population_0_23_months'] ?? 0;
            
            // Sum vaccine doses
            foreach ($row['vaccines'] as $doseKey => $doseData) {
                if (!isset($totals['vaccines'][$doseKey])) {
                    $totals['vaccines'][$doseKey] = [
                        'male_count' => 0,
                        'female_count' => 0,
                        'total_count' => 0,
                        'percentage' => 0.00
                    ];
                }
                
                $totals['vaccines'][$doseKey]['male_count'] += $doseData['male_count'];
                $totals['vaccines'][$doseKey]['female_count'] += $doseData['female_count'];
                $totals['vaccines'][$doseKey]['total_count'] += $doseData['total_count'];
            }
            
            // Sum FIC counts
            if (isset($row['fic'])) {
                $totals['fic']['male_count'] += $row['fic']['male_count'];
                $totals['fic']['female_count'] += $row['fic']['female_count'];
                $totals['fic']['total_count'] += $row['fic']['total_count'];
            }
            
            // Sum CIC counts
            if (isset($row['cic'])) {
                $totals['cic']['male_count'] += $row['cic']['male_count'];
                $totals['cic']['female_count'] += $row['cic']['female_count'];
                $totals['cic']['total_count'] += $row['cic']['total_count'];
            }
        }
        
        // Calculate percentages for vaccine doses based on appropriate eligible population
        foreach ($vaccineConfig as $vaccineName => $config) {
            $totalDoses = $config['total_doses'];
            $targetAgeGroup = $config['target_age_group'];
            
            // Determine which eligible population to use for percentage
            $eligiblePop = 0;
            if ($targetAgeGroup === 'under_1_year') {
                $eligiblePop = $totals['eligible_population_under_1_year'];
            } elseif ($targetAgeGroup === '0_12_months') {
                $eligiblePop = $totals['eligible_population_0_12_months'];
            } elseif ($targetAgeGroup === '13_23_months') {
                $eligiblePop = $totals['eligible_population_13_23_months'];
            }
            // Note: grade_1 and grade_7 don't have aggregated totals, so % will be 0
            
            for ($dose = 1; $dose <= $totalDoses; $dose++) {
                // Special handling for IPV Dose 2 - calculate % for both Routine and Catch-up
                $isIPV2 = ($vaccineName === 'Inactivated Polio' && $dose === 2);
                
                if ($isIPV2) {
                    $routineKey = "{$vaccineName}|Dose {$dose}|Routine";
                    $catchupKey = "{$vaccineName}|Dose {$dose}|Catch-up";
                    
                    if (isset($totals['vaccines'][$routineKey]) && $eligiblePop > 0) {
                        $totals['vaccines'][$routineKey]['percentage'] = round(
                            ($totals['vaccines'][$routineKey]['total_count'] / $eligiblePop) * 100,
                            2
                        );
                    }
                    
                    if (isset($totals['vaccines'][$catchupKey]) && $eligiblePop > 0) {
                        $totals['vaccines'][$catchupKey]['percentage'] = round(
                            ($totals['vaccines'][$catchupKey]['total_count'] / $eligiblePop) * 100,
                            2
                        );
                    }
                } else {
                    if ($totalDoses > 1) {
                        $doseKey = "{$vaccineName}|Dose {$dose}";
                    } else {
                        $doseKey = $vaccineName;
                    }
                    
                    if (isset($totals['vaccines'][$doseKey]) && $eligiblePop > 0) {
                        $totals['vaccines'][$doseKey]['percentage'] = round(
                            ($totals['vaccines'][$doseKey]['total_count'] / $eligiblePop) * 100,
                            2
                        );
                    }
                }
            }
        }
        
        // Calculate FIC percentage
        if ($totals['eligible_population_0_12_months'] > 0) {
            $totals['fic']['percentage'] = round(
                ($totals['fic']['total_count'] / $totals['eligible_population_0_12_months']) * 100,
                2
            );
        }
        
        // Calculate CIC percentage
        if ($totals['eligible_population_13_23_months'] > 0) {
            $totals['cic']['percentage'] = round(
                ($totals['cic']['total_count'] / $totals['eligible_population_13_23_months']) * 100,
                2
            );
        }
        
        return $totals;
    }
    
    /**
     * OLD calculateTotals method - kept for backward compatibility with snapshots
     */
    private function calculateTotals($reportData, $vaccines)
    {
        $totals = [
            'barangay' => 'TOTAL',
            'eligible_population' => 0,
            'vaccines' => []
        ];
        
        // Initialize totals using composite keys to match calculateLiveData()
        foreach ($vaccines as $vaccine) {
            // Create composite key for vaccines with doses_description (e.g., "Measles Containing (Grade 1)")
            $vaccineKey = $vaccine->vaccine_name;
            if ($vaccine->doses_description) {
                $vaccineKey .= ' (' . $vaccine->doses_description . ')';
            }
            
            $totals['vaccines'][$vaccineKey] = [
                'male_count' => 0,
                'female_count' => 0,
                'total_count' => 0,
                'percentage' => 0.00
            ];
        }
        
        foreach ($reportData as $row) {
            if ($row['barangay'] === 'TOTAL') continue;
            
            $totals['eligible_population'] += $row['eligible_population'];
            
            foreach ($row['vaccines'] as $vaccineName => $vaccineData) {
                $totals['vaccines'][$vaccineName]['male_count'] += $vaccineData['male_count'];
                $totals['vaccines'][$vaccineName]['female_count'] += $vaccineData['female_count'];
                $totals['vaccines'][$vaccineName]['total_count'] += $vaccineData['total_count'];
            }
        }
        
        // Calculate percentages for totals
        foreach ($totals['vaccines'] as $vaccineName => &$vaccineData) {
            $vaccineData['percentage'] = $totals['eligible_population'] > 0
                ? round(($vaccineData['total_count'] / $totals['eligible_population']) * 100, 2)
                : 0.00;
        }
        
        return $totals;
    }
    
    /**
     * Format snapshot data to match live data structure
     */
    private function formatSnapshotData($snapshots)
    {
        $grouped = $snapshots->groupBy('barangay');
        $reportData = [];
        
        foreach ($grouped as $barangay => $barangaySnapshots) {
            // Handle NULL or empty string as TOTAL
            $barangayName = ($barangay === null || $barangay === '') ? 'TOTAL' : $barangay;
            $firstSnapshot = $barangaySnapshots->first();
            
            $barangayData = [
                'barangay' => $barangayName,
                'eligible_population' => $firstSnapshot->eligible_population ?? 0, // Keep for backward compatibility
                'eligible_population_under_1_year' => $firstSnapshot->eligible_population_under_1_year ?? 0,
                'eligible_population_0_12_months' => $firstSnapshot->eligible_population_0_12_months ?? 0,
                'eligible_population_13_23_months' => $firstSnapshot->eligible_population_13_23_months ?? 0,
                'eligible_population_0_23_months' => $firstSnapshot->eligible_population_0_23_months ?? 0,
                'vaccines' => []
            ];
            
            foreach ($barangaySnapshots as $snapshot) {
                if ($snapshot->vaccine_name === '__FIC__') {
                    $barangayData['fic'] = [
                        'male_count' => $snapshot->male_count,
                        'female_count' => $snapshot->female_count,
                        'total_count' => $snapshot->total_count,
                        'percentage' => $snapshot->percentage,
                    ];
                } elseif ($snapshot->vaccine_name === '__CIC__') {
                    $barangayData['cic'] = [
                        'male_count' => $snapshot->male_count,
                        'female_count' => $snapshot->female_count,
                        'total_count' => $snapshot->total_count,
                        'percentage' => $snapshot->percentage,
                    ];
                } else {
                    $barangayData['vaccines'][$snapshot->vaccine_name] = [
                        'male_count' => $snapshot->male_count,
                        'female_count' => $snapshot->female_count,
                        'total_count' => $snapshot->total_count,
                        'percentage' => $snapshot->percentage,
                        'data_source' => $snapshot->data_source,
                        'is_editable' => $snapshot->isEditable()
                    ];
                }
            }
            
            // Add to report data in order (TOTAL will be last due to query ordering)
            $reportData[] = $barangayData;
        }
        
        return $reportData;
    }
    
    /**
     * Save manual edit for a specific cell
     * Note: $vaccineName may include doses_description (e.g., "Measles Containing (Grade 1)")
     */
    public function saveManualEdit($year, $quarterStart, $quarterEnd, $barangay, $vaccineName, $data)
    {
        $snapshot = VaccinationReportSnapshot::updateOrCreate(
            [
                'year' => $year,
                'quarter_start' => $quarterStart,
                'quarter_end' => $quarterEnd,
                'barangay' => $barangay,
                'vaccine_name' => $vaccineName  // This now includes doses_description for uniqueness
            ],
            [
                'male_count' => $data['male_count'],
                'female_count' => $data['female_count'],
                'total_count' => $data['male_count'] + $data['female_count'],
                'percentage' => $data['percentage'],
                'eligible_population' => $data['eligible_population'] ?? 0,
                'data_source' => 'manual_edit',
                'is_locked' => true,
                'updated_by' => auth('health_worker')->id(),
                'notes' => $data['notes'] ?? null
            ]
        );
        
        // Clear cache for this report period
        $this->clearReportCache($year, $quarterStart, $quarterEnd, $barangay);
        
        return $snapshot;
    }
    
    /**
     * Save report as a new version (replaces lockReport)
     * Creates a versioned snapshot of current live data
     */
    public function saveReportVersion($year, $quarterStart, $quarterEnd, $notes = null, $monthStart = null, $monthEnd = null)
    {
        // Get next version number
        $version = $this->getNextVersion($year, $quarterStart, $quarterEnd);
        
        // Get current live data
        $liveData = $this->calculateLiveData($year, $quarterStart, $quarterEnd);
        
        // Save timestamp
        $savedAt = now();
        $savedBy = auth('health_worker')->id();
        
        // Get vaccine configuration for determining eligible populations
        $vaccineConfig = \App\Config\VaccineConfig::getDoseConfiguration();
        
        // Save each cell as a versioned snapshot
        foreach ($liveData as $barangayData) {
            // Get all three eligible populations for this barangay
            $eligPop_under1 = $barangayData['eligible_population_under_1_year'] ?? 0;
            $eligPop_0_12 = $barangayData['eligible_population_0_12_months'] ?? 0;
            $eligPop_13_23 = $barangayData['eligible_population_13_23_months'] ?? 0;
            $eligPop_0_23 = $barangayData['eligible_population_0_23_months'] ?? 0;
            
            foreach ($barangayData['vaccines'] as $vaccineName => $vaccineData) {
                // Determine the correct eligible population for this vaccine
                // Parse vaccine name to get base vaccine name (before the pipe)
                $parts = explode('|', $vaccineName);
                $baseVaccineName = $parts[0];
                
                // Get target age group from config
                $targetAgeGroup = $vaccineConfig[$baseVaccineName]['target_age_group'] ?? 'under_1_year';
                
                // Get the appropriate eligible population for the percentage calculation
                $eligiblePopForPercentage = 0;
                if ($targetAgeGroup === 'under_1_year') {
                    $eligiblePopForPercentage = $eligPop_under1;
                } elseif ($targetAgeGroup === '0_12_months') {
                    $eligiblePopForPercentage = $eligPop_0_12;
                } elseif ($targetAgeGroup === '13_23_months') {
                    $eligiblePopForPercentage = $eligPop_13_23;
                } elseif (in_array($targetAgeGroup, ['grade_1', 'grade_7'])) {
                    // For grade-level vaccines, use under_1_year as fallback
                    $eligiblePopForPercentage = $eligPop_under1;
                }
                
                // Debug log for vaccines with vaccinations but 0% 
                if ($vaccineData['total_count'] > 0 && $vaccineData['percentage'] == 0) {
                    \Log::warning('Vaccine has count but 0% - ' . $vaccineName, [
                        'barangay' => $barangayData['barangay'],
                        'total_count' => $vaccineData['total_count'],
                        'percentage' => $vaccineData['percentage'],
                        'eligible_pop' => $eligiblePopForPercentage,
                        'target_age_group' => $targetAgeGroup
                    ]);
                }
                
                VaccinationReportSnapshot::create([
                    'year' => $year,
                    'quarter_start' => $quarterStart,
                    'quarter_end' => $quarterEnd,
                    'month_start' => $monthStart,
                    'month_end' => $monthEnd,
                    'barangay' => $barangayData['barangay'] === 'TOTAL' ? null : $barangayData['barangay'],
                    'vaccine_name' => $vaccineName,
                    'version' => $version,
                    'male_count' => $vaccineData['male_count'],
                    'female_count' => $vaccineData['female_count'],
                    'total_count' => $vaccineData['total_count'],
                    'percentage' => $vaccineData['percentage'],
                    'eligible_population' => $eligiblePopForPercentage, // The one used for THIS vaccine's percentage
                    'eligible_population_under_1_year' => $eligPop_under1,
                    'eligible_population_0_12_months' => $eligPop_0_12,
                    'eligible_population_13_23_months' => $eligPop_13_23,
                    'eligible_population_0_23_months' => $eligPop_0_23,
                    'data_source' => 'calculated',
                    'is_locked' => true,
                    'created_by' => $savedBy,
                    'saved_at' => $savedAt,
                    'saved_by' => $savedBy,
                    'notes' => $notes ?? "Version {$version} saved on " . $savedAt->format('M d, Y h:i A')
                ]);
            }

            // Save FIC snapshot
            if (!empty($barangayData['fic'])) {
                $fic = $barangayData['fic'];
                VaccinationReportSnapshot::create([
                    'year' => $year, 'quarter_start' => $quarterStart, 'quarter_end' => $quarterEnd,
                    'month_start' => $monthStart, 'month_end' => $monthEnd,
                    'barangay' => $barangayData['barangay'] === 'TOTAL' ? null : $barangayData['barangay'],
                    'vaccine_name' => '__FIC__', 'version' => $version,
                    'male_count' => $fic['male_count'], 'female_count' => $fic['female_count'],
                    'total_count' => $fic['total_count'], 'percentage' => $fic['percentage'],
                    'eligible_population' => $eligPop_0_12,
                    'eligible_population_under_1_year' => $eligPop_under1,
                    'eligible_population_0_12_months' => $eligPop_0_12,
                    'eligible_population_13_23_months' => $eligPop_13_23,
                    'eligible_population_0_23_months' => $eligPop_0_23,
                    'data_source' => 'calculated', 'is_locked' => true,
                    'created_by' => $savedBy, 'saved_at' => $savedAt, 'saved_by' => $savedBy,
                    'notes' => $notes ?? "FIC for version {$version}"
                ]);
            }

            // Save CIC snapshot
            if (!empty($barangayData['cic'])) {
                $cic = $barangayData['cic'];
                VaccinationReportSnapshot::create([
                    'year' => $year, 'quarter_start' => $quarterStart, 'quarter_end' => $quarterEnd,
                    'month_start' => $monthStart, 'month_end' => $monthEnd,
                    'barangay' => $barangayData['barangay'] === 'TOTAL' ? null : $barangayData['barangay'],
                    'vaccine_name' => '__CIC__', 'version' => $version,
                    'male_count' => $cic['male_count'], 'female_count' => $cic['female_count'],
                    'total_count' => $cic['total_count'], 'percentage' => $cic['percentage'],
                    'eligible_population' => $eligPop_13_23,
                    'eligible_population_under_1_year' => $eligPop_under1,
                    'eligible_population_0_12_months' => $eligPop_0_12,
                    'eligible_population_13_23_months' => $eligPop_13_23,
                    'eligible_population_0_23_months' => $eligPop_0_23,
                    'data_source' => 'calculated', 'is_locked' => true,
                    'created_by' => $savedBy, 'saved_at' => $savedAt, 'saved_by' => $savedBy,
                    'notes' => $notes ?? "CIC for version {$version}"
                ]);
            }
        }
        
        // Clear cache for this report period
        $this->clearReportCache($year, $quarterStart, $quarterEnd);
        
        return [
            'success' => true,
            'version' => $version,
            'saved_at' => $savedAt,
            'message' => "Report saved as version {$version}"
        ];
    }
    
    /**
     * Save edited report data as a new version
     */
    public function saveEditedReportVersion($year, $quarterStart, $quarterEnd, $reportData, $monthStart = null, $monthEnd = null)
    {
        // Get next version number
        $version = $this->getNextVersion($year, $quarterStart, $quarterEnd);
        
        // Save timestamp
        $savedAt = now();
        $savedBy = auth('health_worker')->id();
        
        // Get vaccine configuration for determining eligible populations
        $vaccineConfig = \App\Config\VaccineConfig::getDoseConfiguration();
        
        // Get eligible populations from live data (they don't change)
        $liveData = $this->calculateLiveData($year, $quarterStart, $quarterEnd);
        $eligiblePopulations = [];
        
        foreach ($liveData as $barangayData) {
            $eligiblePopulations[$barangayData['barangay']] = [
                'under_1_year' => $barangayData['eligible_population_under_1_year'] ?? 0,
                '0_12_months' => $barangayData['eligible_population_0_12_months'] ?? 0,
                '13_23_months' => $barangayData['eligible_population_13_23_months'] ?? 0,
                '0_23_months' => $barangayData['eligible_population_0_23_months'] ?? 0
            ];
        }
        
        // Process each barangay from the edited data
        foreach ($reportData as $barangayRow) {
            $barangay = $barangayRow['barangay'];
            $eligPops = $eligiblePopulations[$barangay] ?? [
                'under_1_year' => 0,
                '0_12_months' => 0,
                '13_23_months' => 0,
                '0_23_months' => 0
            ];
            
            // Process each vaccine
            foreach ($barangayRow['vaccines'] as $vaccineName => $vaccineData) {
                $maleCount = (int) ($vaccineData['male'] ?? 0);
                $femaleCount = (int) ($vaccineData['female'] ?? 0);
                $totalCount = (int) ($vaccineData['total'] ?? 0);
                
                // Determine eligible population for this vaccine
                $parts = explode('|', $vaccineName);
                $baseVaccineName = $parts[0];
                $targetAgeGroup = $vaccineConfig[$baseVaccineName]['target_age_group'] ?? 'under_1_year';

                // Get appropriate eligible population
                if ($vaccineName === '__FIC__') {
                    $eligiblePopForPercentage = $eligPops['0_12_months'];
                } elseif ($vaccineName === '__CIC__') {
                    $eligiblePopForPercentage = $eligPops['13_23_months'];
                } elseif ($targetAgeGroup === 'under_1_year') {
                    $eligiblePopForPercentage = $eligPops['under_1_year'];
                } elseif ($targetAgeGroup === '0_12_months') {
                    $eligiblePopForPercentage = $eligPops['0_12_months'];
                } elseif ($targetAgeGroup === '13_23_months') {
                    $eligiblePopForPercentage = $eligPops['13_23_months'];
                } else {
                    $eligiblePopForPercentage = $eligPops['under_1_year'];
                }
                
                // Calculate percentage
                $percentage = $eligiblePopForPercentage > 0 
                    ? ($totalCount / $eligiblePopForPercentage * 100) 
                    : 0;
                
                VaccinationReportSnapshot::create([
                    'year' => $year,
                    'quarter_start' => $quarterStart,
                    'quarter_end' => $quarterEnd,
                    'month_start' => $monthStart,
                    'month_end' => $monthEnd,
                    'barangay' => $barangay === 'TOTAL' ? null : $barangay,
                    'vaccine_name' => $vaccineName,
                    'version' => $version,
                    'male_count' => $maleCount,
                    'female_count' => $femaleCount,
                    'total_count' => $totalCount,
                    'percentage' => $percentage,
                    'eligible_population' => $eligiblePopForPercentage,
                    'eligible_population_under_1_year' => $eligPops['under_1_year'],
                    'eligible_population_0_12_months' => $eligPops['0_12_months'],
                    'eligible_population_13_23_months' => $eligPops['13_23_months'],
                    'eligible_population_0_23_months' => $eligPops['0_23_months'],
                    'data_source' => 'manual_edit',
                    'is_locked' => true,
                    'created_by' => $savedBy,
                    'saved_at' => $savedAt,
                    'saved_by' => $savedBy,
                    'notes' => "Edited version {$version} saved on " . $savedAt->format('M d, Y h:i A')
                ]);
            }
        }

        // Clear cache for this report period
        $this->clearReportCache($year, $quarterStart, $quarterEnd);
        
        return [
            'success' => true,
            'version' => $version,
            'saved_at' => $savedAt,
            'message' => "Edited report saved as version {$version}"
        ];
    }

    /**
     * Get custom column titles for a specific report version.
     */
    public function getCustomColumns($year, $quarterStart, $quarterEnd, $version): array
    {
        $snapshots = VaccinationReportSnapshot::where('year', $year)
            ->where('quarter_start', $quarterStart)
            ->where('quarter_end', $quarterEnd)
            ->where('version', $version)
            ->where('vaccine_name', 'LIKE', '__custom__%')
            ->pluck('vaccine_name')
            ->unique()
            ->values();

        return $snapshots->map(fn($name) => substr($name, strlen('__custom__')))->toArray();
    }

    /**
     * Legacy lockReport method - redirects to saveReportVersion for backward compatibility
     * @deprecated Use saveReportVersion() instead
     */
    public function lockReport($year, $quarterStart, $quarterEnd)
    {
        return $this->saveReportVersion($year, $quarterStart, $quarterEnd);
    }
    
    /**
     * Import historical report data (bulk)
     */
    public function importHistoricalReport($year, $quarterStart, $quarterEnd, $importData, $notes = null, $monthStart = null, $monthEnd = null)
    {
        $version = $this->getNextVersion($year, $quarterStart, $quarterEnd);
        $savedAt = now();
        $savedBy = auth('health_worker')->id();
        $count = 0;
        
        // If months not provided, calculate from quarters
        if ($monthStart === null) {
            $monthStart = ($quarterStart - 1) * 3 + 1; // Q1=1, Q2=4, Q3=7, Q4=10
        }
        if ($monthEnd === null) {
            $monthEnd = $quarterEnd * 3; // Q1=3, Q2=6, Q3=9, Q4=12
        }
        
        foreach ($importData as $row) {
            VaccinationReportSnapshot::create([
                'year' => $year,
                'quarter_start' => $quarterStart,
                'quarter_end' => $quarterEnd,
                'month_start' => $monthStart,
                'month_end' => $monthEnd,
                'barangay' => $row['barangay'] === 'TOTAL' ? null : $row['barangay'],
                'vaccine_name' => $row['vaccine_name'],
                'version' => $version,
                'male_count' => $row['male_count'] ?? 0,
                'female_count' => $row['female_count'] ?? 0,
                'total_count' => $row['total_count'] ?? 0,
                'percentage' => $row['percentage'] ?? 0.00,
                'eligible_population' => $row['eligible_population'] ?? 0,
                'data_source' => 'imported',
                'is_locked' => true,
                'created_by' => $savedBy,
                'saved_at' => $savedAt,
                'saved_by' => $savedBy,
                'notes' => $notes ?? "Imported historical data - Version {$version}"
            ]);
            $count++;
        }
        
        return [
            'success' => true,
            'count' => $count,
            'version' => $version
        ];
    }
    
    /**
     * Delete snapshot and revert to live calculation
     */
    /**
     * Soft delete a report version
     * Sets deleted_at, deleted_by, and deletion_reason instead of hard delete
     */
    public function softDeleteReport($year, $quarterStart, $quarterEnd, $version, $reason = null)
    {
        $deletedBy = auth('health_worker')->id();
        
        $affected = VaccinationReportSnapshot::forPeriod($year, $quarterStart, $quarterEnd)
            ->where('version', $version)
            ->update([
                'deleted_by' => $deletedBy,
                'deletion_reason' => $reason ?? 'Deleted via Report History',
                'deleted_at' => now()
            ]);
        
        return [
            'success' => $affected > 0,
            'affected_rows' => $affected,
            'message' => $affected > 0 ? "Report version {$version} has been soft deleted" : "No records found to delete"
        ];
    }
    
    /**
     * Restore a soft-deleted report version
     */
    public function restoreReport($year, $quarterStart, $quarterEnd, $version)
    {
        $affected = VaccinationReportSnapshot::forPeriod($year, $quarterStart, $quarterEnd)
            ->where('version', $version)
            ->onlyTrashed()
            ->restore();
        
        return [
            'success' => $affected > 0,
            'affected_rows' => $affected,
            'message' => $affected > 0 ? "Report version {$version} has been restored" : "No deleted records found"
        ];
    }
    
    /**
     * Permanently delete a report version (admin only)
     * WARNING: This cannot be undone
     * 
     * DISABLED: This function is disabled for data protection.
     * Uncomment to re-enable permanent delete functionality.
     * Note: This was disabled to prevent accidental/malicious permanent data loss.
     */
    /*
    public function permanentlyDeleteReport($year, $quarterStart, $quarterEnd, $version)
    {
        $affected = VaccinationReportSnapshot::forPeriod($year, $quarterStart, $quarterEnd)
            ->where('version', $version)
            ->withTrashed()
            ->forceDelete();
        
        return [
            'success' => $affected > 0,
            'affected_rows' => $affected,
            'message' => $affected > 0 ? "Report version {$version} has been permanently deleted" : "No records found"
        ];
    }
    */
    
    /**
     * Reset to live data by deleting snapshots for the current period
     * @deprecated Use softDeleteReport() instead for better audit trail
     */
    public function resetToLiveData($year, $quarterStart, $quarterEnd, $barangay = null)
    {
        // Get the latest version
        $latestVersion = $this->getLatestVersion($year, $quarterStart, $quarterEnd);
        
        if (!$latestVersion) {
            return ['success' => false, 'message' => 'No report found to reset'];
        }
        
        // Clear cache before deleting
        $this->clearReportCache($year, $quarterStart, $quarterEnd, $barangay);
        
        // Soft delete instead of hard delete
        return $this->softDeleteReport($year, $quarterStart, $quarterEnd, $latestVersion, 'Reset to live data');
    }
    
    /**
     * Get list of all archived reports grouped by year and period with version info
     */
    public function getArchivedReports()
    {
        return VaccinationReportSnapshot::select(
                'year',
                'quarter_start',
                'quarter_end',
                'month_start',
                'month_end',
                'version',
                'saved_at',
                'saved_by',
                'data_source',
                'is_locked',
                'deleted_at',
                'deleted_by',
                'deletion_reason',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('MAX(updated_at) as updated_at'),
                DB::raw('COUNT(DISTINCT vaccine_name) as vaccine_count')
            )
            ->withTrashed() // Include soft-deleted reports
            ->groupBy('year', 'quarter_start', 'quarter_end', 'month_start', 'month_end', 'version', 'saved_at', 'saved_by', 'data_source', 'is_locked', 'deleted_at', 'deleted_by', 'deletion_reason')
            ->orderBy('year', 'desc')
            ->orderBy('month_start', 'desc')
            ->orderBy('version', 'desc')
            ->get();
    }
    
    /**
     * Helper: Get quarter start date
     */
    private function getQuarterStartDate($year, $quarter)
    {
        $month = ($quarter - 1) * 3 + 1;
        return Carbon::create($year, $month, 1)->startOfDay();
    }
    
    /**
     * Helper: Get quarter end date
     */
    private function getQuarterEndDate($year, $quarter)
    {
        $month = $quarter * 3;
        return Carbon::create($year, $month, 1)->endOfMonth();
    }
    
    /**
     * Helper: Get formatted date range
     */
    private function getDateRange($year, $quarterStart, $quarterEnd, $monthStart = null, $monthEnd = null)
    {
        // If explicit months are provided, use them directly
        if ($monthStart !== null && $monthEnd !== null) {
            $startMonth = $monthStart;
            $endMonth = $monthEnd;
        } else {
            // Convert quarters to months
            $startMonth = ($quarterStart - 1) * 3 + 1;
            $endMonth = $quarterEnd * 3;
        }
        
        $startDate = Carbon::create($year, $startMonth, 1)->startOfDay();
        $endDate = Carbon::create($year, $endMonth, 1)->endOfMonth();
        
        return $startDate->format('M d, Y') . ' to ' . $endDate->format('M d, Y');
    }
    
    /**
     * Get all barangays
     */
    public function getAllBarangays()
    {
        return $this->barangays;
    }
    
    /**
     * Get the next version number for a report period
     */
    public function getNextVersion($year, $quarterStart, $quarterEnd)
    {
        $maxVersion = VaccinationReportSnapshot::forPeriod($year, $quarterStart, $quarterEnd)
            ->withTrashed() // Include soft deleted to avoid version conflicts
            ->max('version');
        
        return ($maxVersion ?? 0) + 1;
    }
    
    /**
     * Get the latest version of a report
     */
    public function getLatestVersion($year, $quarterStart, $quarterEnd)
    {
        return VaccinationReportSnapshot::forPeriod($year, $quarterStart, $quarterEnd)
            ->max('version');
    }
    
    /**
     * Check if a specific version exists
     */
    public function versionExists($year, $quarterStart, $quarterEnd, $version)
    {
        return VaccinationReportSnapshot::forPeriod($year, $quarterStart, $quarterEnd)
            ->where('version', $version)
            ->exists();
    }
    
    /**
     * Get all versions for a report period
     */
    public function getVersionHistory($year, $quarterStart, $quarterEnd)
    {
        return VaccinationReportSnapshot::select(
                'version',
                'saved_at',
                'saved_by',
                'data_source',
                DB::raw('COUNT(DISTINCT vaccine_name) as vaccine_count'),
                DB::raw('COUNT(DISTINCT barangay) as barangay_count')
            )
            ->forPeriod($year, $quarterStart, $quarterEnd)
            ->groupBy('version', 'saved_at', 'saved_by', 'data_source')
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Calculate eligible population for a barangay by age group
     * 
     * @param string $barangay
     * @param \Carbon\Carbon|string $targetDate
     * @param string $ageGroup (under_1_year, 0_12_months, 13_23_months, grade_1, grade_7)
     * @return int
     */
    public function calculateEligiblePopulation($barangay, $targetDate, $ageGroup)
    {
        $targetDate = $targetDate instanceof Carbon ? $targetDate : Carbon::parse($targetDate);
        
        $query = Patient::where('barangay', $barangay);
        
        switch ($ageGroup) {
            case 'under_1_year':
                // Birth date is within last 12 months from target date (0-11 months)
                $query->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) < 12', [$targetDate]);
                break;
            
            case '0_12_months':
                // Birth date is within 0-12 months from target date
                $query->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) <= 12', [$targetDate]);
                break;
            
            case '13_23_months':
                // Birth date is 13-23 months before target date
                $query->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) BETWEEN 13 AND 23', [$targetDate]);
                break;
            
            case '0_23_months':
                // Birth date is within 0-23 months from target date (combined infant + toddler)
                $query->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) <= 23', [$targetDate]);
                break;
            
            case 'grade_1':
                // Age 6-7 years (72-84 months)
                $query->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) BETWEEN 72 AND 84', [$targetDate]);
                break;
            
            case 'grade_7':
                // Age 12-13 years (144-156 months)
                $query->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) BETWEEN 144 AND 156', [$targetDate]);
                break;
            
            default:
                return 0;
        }
        
        return $query->count();
    }

    /**
     * Get vaccine dose count for a specific vaccine, dose number, and barangay
     * Excludes external vaccinations (administered_elsewhere = true)
     * 
     * @param string $barangay
     * @param string $vaccineName
     * @param int $doseNumber
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @return array ['male_count' => int, 'female_count' => int, 'total_count' => int, 'percentage' => float]
     */
    public function getVaccineDoseCount($barangay, $vaccineName, $doseNumber, $startDate, $endDate, $type = 'all')
    {
        // Get vaccine ID
        $vaccine = Vaccine::where('vaccine_name', $vaccineName)->first();
        
        if (!$vaccine) {
            return [
                'male_count' => 0,
                'female_count' => 0,
                'total_count' => 0,
                'percentage' => 0.00
            ];
        }

        $doseField = "dose_{$doseNumber}_date";

        // Special handling for IPV 2 - split into routine and catch-up
        $isIPV2 = ($vaccineName === 'Inactivated Polio' && $doseNumber === 2);
        
        if ($isIPV2 && $type !== 'all') {
            // Get individual records to check age at vaccination
            $records = DB::table('vaccination_transactions as vt')
                ->join('patients as p', 'vt.patient_id', '=', 'p.id')
                ->join('vaccines as v', 'vt.vaccine_id', '=', 'v.id')
                ->where('v.vaccine_name', $vaccineName)
                ->where('vt.dose_number', $doseNumber)
                ->where('p.barangay', $barangay)
                ->where('vt.administered_elsewhere', false)
                ->whereBetween('vt.vaccinated_at', [$startDate, $endDate])
                ->select('p.sex', 'p.date_of_birth', 'vt.vaccinated_at')
                ->get();
            
            $maleCount = 0;
            $femaleCount = 0;
            
            foreach ($records as $record) {
                $ageAtVaccination = Carbon::parse($record->date_of_birth)
                    ->diffInMonths(Carbon::parse($record->vaccinated_at));
                
                $isCatchUp = VaccineConfig::isCatchUpDose($vaccineName, $doseNumber, $ageAtVaccination);
                
                // Count based on requested type
                if (($type === 'routine' && !$isCatchUp) || ($type === 'catchup' && $isCatchUp)) {
                    if (stripos($record->sex, 'Male') !== false) {
                        $maleCount++;
                    } else {
                        $femaleCount++;
                    }
                }
            }
            
            $totalCount = $maleCount + $femaleCount;
            
            return [
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'total_count' => $totalCount,
                'percentage' => 0.00
            ];
        }

        // Regular query for all other vaccines (including IPV 1)
        $stats = DB::table('vaccination_transactions as vt')
            ->join('patients as p', 'vt.patient_id', '=', 'p.id')
            ->join('vaccines as v', 'vt.vaccine_id', '=', 'v.id')
            ->where('v.vaccine_name', $vaccineName)
            ->where('vt.dose_number', $doseNumber)
            ->where('p.barangay', $barangay)
            ->where('vt.administered_elsewhere', false) // Exclude external vaccinations
            ->whereBetween('vt.vaccinated_at', [$startDate, $endDate])
            ->selectRaw('
                SUM(CASE WHEN p.sex LIKE "Male%" THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN p.sex LIKE "Female%" THEN 1 ELSE 0 END) as female_count
            ')
            ->first();

        $maleCount = (int) ($stats->male_count ?? 0);
        $femaleCount = (int) ($stats->female_count ?? 0);
        $totalCount = $maleCount + $femaleCount;

        return [
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'total_count' => $totalCount,
            'percentage' => 0.00 // Will be calculated later based on eligible population
        ];
    }

    /**
     * Calculate FIC (Fully Immunized Children) count for a barangay
     * FIC = Children 0-12 months who completed: BCG, HepB, Pentavalent (3), OPV (3), MMR (2)
     * 
     * @param string $barangay
     * @param int $year
     * @param int $monthStart
     * @param int $monthEnd
     * @return array ['male_count' => int, 'female_count' => int, 'total_count' => int, 'percentage' => float]
     */
    public function calculateFICCount($barangay, $year, $monthStart, $monthEnd)
    {
        $endDate = Carbon::create($year, $monthEnd, 1)->endOfMonth();

        // Get all patients in barangay who are 0-12 months old at end date
        $eligiblePatients = Patient::where('barangay', $barangay)
            ->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) <= 12', [$endDate])
            ->get();

        $maleCount = 0;
        $femaleCount = 0;

        foreach ($eligiblePatients as $patient) {
            if ($patient->isFIC($endDate)) {
                if (stripos($patient->sex, 'Male') !== false) {
                    $maleCount++;
                } else {
                    $femaleCount++;
                }
            }
        }

        $totalCount = $maleCount + $femaleCount;
        $eligiblePopulation = count($eligiblePatients);

        return [
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'total_count' => $totalCount,
            'percentage' => $eligiblePopulation > 0 ? round(($totalCount / $eligiblePopulation) * 100, 2) : 0.00
        ];
    }

    /**
     * Calculate CIC (Completely Immunized Children) count for a barangay
     * CIC = Children 13-23 months who completed all required vaccines
     * 
     * @param string $barangay
     * @param int $year
     * @param int $monthStart
     * @param int $monthEnd
     * @return array ['male_count' => int, 'female_count' => int, 'total_count' => int, 'percentage' => float]
     */
    public function calculateCICCount($barangay, $year, $monthStart, $monthEnd)
    {
        $endDate = Carbon::create($year, $monthEnd, 1)->endOfMonth();

        // Get all patients in barangay who are 13-23 months old at end date
        $eligiblePatients = Patient::where('barangay', $barangay)
            ->whereRaw('TIMESTAMPDIFF(MONTH, date_of_birth, ?) BETWEEN 13 AND 23', [$endDate])
            ->get();

        $maleCount = 0;
        $femaleCount = 0;

        foreach ($eligiblePatients as $patient) {
            if ($patient->isCIC($endDate)) {
                if (stripos($patient->sex, 'Male') !== false) {
                    $maleCount++;
                } else {
                    $femaleCount++;
                }
            }
        }

        $totalCount = $maleCount + $femaleCount;
        $eligiblePopulation = count($eligiblePatients);

        return [
            'male_count' => $maleCount,
            'female_count' => $femaleCount,
            'total_count' => $totalCount,
            'percentage' => $eligiblePopulation > 0 ? round(($totalCount / $eligiblePopulation) * 100, 2) : 0.00
        ];
    }
}
