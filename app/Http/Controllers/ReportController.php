<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\VaccinationReportService;
use App\Models\VaccinationReportSnapshot;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Activitylog\Facades\Activity;

/**
 * ReportController
 * 
 * Performance Optimization Notes:
 * - Response caching: GET routes cached via CacheResponse middleware (5-30 min TTL)
 * - Query result caching: VaccinationReportService caches report data (5 min TTL)
 * - Database indexes: Composite indexes on patient_vaccine_records, patients, vaccines
 * - Query optimization: Database aggregation with SUM(CASE) instead of PHP loops
 */
class ReportController extends Controller
{
    protected $reportService;
    
    public function __construct(VaccinationReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    
    /**
     * Show current report page (live data)
     * Note: Year filter removed - current report always shows current year data.
     * Use Report History for previous years' data.
     */
    public function current(Request $request)
    {
        // Always use current year for live report
        // Previous years should be viewed through Report History
        $year = now()->year;
        $monthStart = $request->input('month_start', 1);
        $monthEnd = $request->input('month_end', 12);
        $barangayFilter = $request->input('barangay', null);
        
        // Validate month range
        $monthStart = max(1, min(12, (int)$monthStart));
        $monthEnd = max(1, min(12, (int)$monthEnd));
        
        // Ensure start is not after end
        if ($monthStart > $monthEnd) {
            $temp = $monthStart;
            $monthStart = $monthEnd;
            $monthEnd = $temp;
        }
        
        // Convert months to quarters for backend compatibility
        $quarterStart = (int)ceil($monthStart / 3);
        $quarterEnd = (int)ceil($monthEnd / 3);
        
        // Get LIVE report data (always calculated, never from snapshots)
        // Pass months for proper date range display
        $report = $this->reportService->getCurrentReport($year, $quarterStart, $quarterEnd, $barangayFilter, $monthStart, $monthEnd);
        
        return view('health_worker.report', [
            'reportData' => $report['data'],
            'dataSource' => $report['source'],
            'isLocked' => $report['is_locked'],
            'dateRange' => $report['date_range'],
            'year' => $year,
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'barangayFilter' => $barangayFilter,
            'barangays' => $this->reportService->getAllBarangays()
        ]);
    }
    
    /**
     * Show report history page
     */
    public function history()
    {
        $archivedReports = $this->reportService->getArchivedReports();
        
        return view('health_worker.report-history', [
            'archivedReports' => $archivedReports
        ]);
    }
    
    /**
     * Show specific archived report
     */
    public function show(Request $request)
    {
        $year = $request->input('year');
        $quarterStart = $request->input('quarter_start');
        $quarterEnd = $request->input('quarter_end');
        $version = $request->input('version'); // Support version parameter
        
        $report = $this->reportService->getReport($year, $quarterStart, $quarterEnd, null, $version);
        
        // Get the latest version for this period to determine if editing should be allowed
        $latestVersion = $this->reportService->getLatestVersion($year, $quarterStart, $quarterEnd);
        $isLatestVersion = ($report['version'] ?? 1) >= $latestVersion;
        
        // Get vaccine configuration for JavaScript
        $vaccineConfig = \App\Config\VaccineConfig::getDoseConfiguration();

        // Get custom column titles for this report version
        $actualVersion = $report['version'] ?? $version ?? 1;
        $customColumns = $this->reportService->getCustomColumns($year, $quarterStart, $quarterEnd, $actualVersion);

        return view('health_worker.report-view', [
            'reportData' => $report['data'],
            'dataSource' => $report['source'],
            'isLocked' => $report['is_locked'],
            'dateRange' => $report['date_range'],
            'year' => $year,
            'quarterStart' => $quarterStart,
            'quarterEnd' => $quarterEnd,
            'monthStart' => $report['month_start'] ?? null,
            'monthEnd' => $report['month_end'] ?? null,
            'version' => $report['version'] ?? 1,
            'latestVersion' => $latestVersion,
            'isLatestVersion' => $isLatestVersion,
            'savedAt' => $report['saved_at'] ?? null,
            'isArchived' => true,
            'vaccineConfig' => $vaccineConfig,
            'customColumns' => $customColumns
        ]);
    }
    
    /**
     * Save manual edit (AJAX)
     */
    public function saveEdit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'quarter_start' => 'required|integer|min:1|max:4',
            'quarter_end' => 'required|integer|min:1|max:4',
            'barangay' => 'required|string|max:100',
            'vaccine_name' => 'required|string|max:100',
            'male_count' => 'required|integer|min:0',
            'female_count' => 'required|integer|min:0',
            'eligible_population' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = [
            'male_count' => $request->male_count,
            'female_count' => $request->female_count,
            'percentage' => $request->percentage ?? 0,
            'eligible_population' => $request->eligible_population ?? 0,
            'notes' => $request->notes
        ];
        
        $snapshot = $this->reportService->saveManualEdit(
            $request->year,
            $request->quarter_start,
            $request->quarter_end,
            $request->barangay,
            $request->vaccine_name,
            $data
        );

        activity('report')->event('report_saved')
            ->withProperties([
                'year' => $request->year,
                'quarter_start' => $request->quarter_start,
                'quarter_end' => $request->quarter_end,
                'barangay' => $request->barangay,
                'vaccine_name' => $request->vaccine_name,
                'male_count' => $request->male_count,
                'female_count' => $request->female_count,
            ])
            ->log("Saved cell edit: {$request->barangay} - {$request->vaccine_name}");

        return response()->json([
            'success' => true,
            'message' => 'Cell updated successfully',
            'data' => $snapshot
        ]);
    }
    
    /**
     * Save edited report from in-place editing as new version
     */
    public function saveEditedReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'quarter_start' => 'required|integer|min:1|max:12',
            'quarter_end' => 'required|integer|min:1|max:12',
            'current_version' => 'required|integer|min:1',
            'report_data' => 'required|array'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Save the edited data as a new version
            $result = $this->reportService->saveEditedReportVersion(
                $request->year,
                $request->quarter_start,
                $request->quarter_end,
                $request->report_data,
                $request->input('month_start'),
                $request->input('month_end')
            );

            activity('report')->event('report_version_created')
                ->withProperties([
                    'year' => $request->year,
                    'quarter_start' => $request->quarter_start,
                    'quarter_end' => $request->quarter_end,
                    'version' => $result['version'],
                    'from_version' => $request->current_version,
                ])
                ->log("Saved edited report as version {$result['version']} from version {$request->current_version}");

            return response()->json([
                'success' => true,
                'message' => 'Report saved successfully as new version',
                'new_version' => $result['version']
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving edited report: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save report as new version (replaces lock functionality)
     */
    public function lock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'quarter_start' => 'required|integer|min:1|max:12',
            'quarter_end' => 'required|integer|min:1|max:12'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->reportService->saveReportVersion(
            $request->year,
            $request->quarter_start,
            $request->quarter_end,
            null,
            $request->input('month_start'),
            $request->input('month_end')
        );

        activity('report')->event('report_locked')
            ->withProperties([
                'year' => $request->year,
                'quarter_start' => $request->quarter_start,
                'quarter_end' => $request->quarter_end,
                'version' => $result['version'] ?? null,
            ])
            ->log("Saved report as version " . ($result['version'] ?? 'unknown'));

        return response()->json([
            'success' => $result['success'],
            'version' => $result['version'],
            'saved_at' => $result['saved_at'],
            'message' => $result['message']
        ]);
    }
    
    /**
     * Reset to live data (delete snapshots)
     */
    public function resetToLive(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'quarter_start' => 'required|integer|min:1|max:12',
            'quarter_end' => 'required|integer|min:1|max:12',
            'barangay' => 'nullable|string|max:100'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $this->reportService->resetToLiveData(
            $request->year,
            $request->quarter_start,
            $request->quarter_end,
            $request->barangay
        );

        activity('report')->event('report_reset')
            ->withProperties([
                'year' => $request->year,
                'quarter_start' => $request->quarter_start,
                'quarter_end' => $request->quarter_end,
                'barangay' => $request->barangay,
            ])
            ->log("Reset report to live data");

        return response()->json([
            'success' => true,
            'message' => 'Report reset to live data successfully'
        ]);
    }
    
    /**
     * Show import historical data page
     */
    public function importPage()
    {
        return view('health_worker.report-import', [
            'barangays' => $this->reportService->getAllBarangays()
        ]);
    }
    
    /**
     * Import historical data (Excel or manual)
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'month_start' => 'required|integer|min:1|max:12',
            'month_end' => 'required|integer|min:1|max:12',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            
            // DEBUG: Uncomment for debugging imports
            // Log::info('Import attempt', [
            //     'file' => $file->getClientOriginalName(),
            //     'extension' => $extension,
            //     'size' => $file->getSize()
            // ]);
            
            // Parse the file based on extension
            if (in_array($extension, ['xlsx', 'xls'])) {
                $importData = $this->parseExcelFile($file);
            } elseif ($extension === 'csv') {
                $importData = $this->parseCsvFile($file);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported file format'
                ], 400);
            }
            
            // DEBUG: Log::info('Parsed data count: ' . count($importData));
            
            if (empty($importData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid data found in file. Please ensure the file has the correct format with headers in row 1 and data starting from row 2.'
                ], 400);
            }
            
            // Import the data using the service
            // Convert months to quarters for storage
            $quarterStart = (int)ceil($request->month_start / 3);
            $quarterEnd = (int)ceil($request->month_end / 3);
            
            $result = $this->reportService->importHistoricalReport(
                $request->year,
                $quarterStart,
                $quarterEnd,
                $importData,
                "Imported from {$file->getClientOriginalName()}",
                $request->month_start,
                $request->month_end
            );

            activity('report')
                ->event('report_imported')
                ->withProperties([
                    'year' => $request->year,
                    'month_start' => $request->month_start,
                    'month_end' => $request->month_end,
                    'count' => $result['count'],
                    'filename' => $file->getClientOriginalName(),
                ])
                ->log("Imported {$result['count']} records from {$file->getClientOriginalName()}");

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['count']} records from {$file->getClientOriginalName()}"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Parse Excel file
     */
    private function parseExcelFile($file)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // DEBUG: Log::info('Excel file loaded', ['total_rows' => count($rows)]);
        
        if (count($rows) < 3) {
            Log::error('Not enough rows in file');
            return [];
        }
        
        // Check if this is a wide format (exported from system) or long format (manual template)
        $firstRow = $rows[0];
        $isWideFormat = isset($firstRow[0]) && strpos(strtolower($firstRow[0]), 'child care') !== false;
        
        if ($isWideFormat) {
            // DEBUG: Log::info('Detected wide format export');
            return $this->parseWideFormat($rows);
        } else {
            // DEBUG: Log::info('Detected long format template');
            return $this->parseLongFormat($rows);
        }
    }
    
    /**
     * Parse wide format (exported from system)
     * Structure: Vaccines as columns, Barangays as rows
     */
    private function parseWideFormat($rows)
    {
        // Find the actual header rows by looking for "M", "F", "T", "%" pattern
        $headerRowIndex = -1;
        $vaccineRowIndex = -1;
        
        for ($i = 0; $i < min(10, count($rows)); $i++) {
            $row = $rows[$i];
            // Look for M/F/T/% pattern (case insensitive)
            if (isset($row[2]) && isset($row[3]) && isset($row[4])) {
                $val2 = strtoupper(trim($row[2] ?? ''));
                $val3 = strtoupper(trim($row[3] ?? ''));
                $val4 = strtoupper(trim($row[4] ?? ''));
                
                if ($val2 === 'M' && $val3 === 'F' && $val4 === 'T') {
                    $headerRowIndex = $i;
                    break;
                }
            }
        }
        
        // Find vaccine names row (should be 1-2 rows before M/F/T/% row)
        if ($headerRowIndex > 0) {
            for ($i = $headerRowIndex - 1; $i >= 0; $i--) {
                $row = $rows[$i];
                // Look for vaccine names (should have "BCG" or similar)
                if (isset($row[2]) && !empty(trim($row[2]))) {
                    $vaccineRowIndex = $i;
                    break;
                }
            }
        }
        
        // DEBUG: Log::info('Found headers', [
        //     'vaccine_row' => $vaccineRowIndex,
        //     'subheader_row' => $headerRowIndex
        // ]);
        
        if ($headerRowIndex === -1 || $vaccineRowIndex === -1) {
            Log::error('Could not find proper header rows');
            return [];
        }
        
        $vaccineHeaders = $rows[$vaccineRowIndex];
        $subHeaders = $rows[$headerRowIndex];
        
        // DEBUG: Log::info('Vaccine headers row:', ['row' => $vaccineHeaders]);
        // DEBUG: Log::info('Sub headers row:', ['row' => $subHeaders]);
        
        // Build vaccine column map
        $vaccineMap = [];
        $currentVaccine = null;
        for ($col = 2; $col < count($vaccineHeaders); $col++) {
            if (!empty($vaccineHeaders[$col])) {
                $currentVaccine = trim($vaccineHeaders[$col]);
                $vaccineMap[$col] = ['vaccine' => $currentVaccine, 'type' => 'M'];
            } else {
                // Sub-columns for current vaccine
                if ($currentVaccine && isset($subHeaders[$col])) {
                    $subType = strtoupper(trim($subHeaders[$col]));
                    // Only accept M, F, T, % as valid types
                    if (in_array($subType, ['M', 'F', 'T', '%'])) {
                        $vaccineMap[$col] = ['vaccine' => $currentVaccine, 'type' => $subType];
                    }
                }
            }
        }
        
        // DEBUG: Log::info('Vaccine map created', ['map' => $vaccineMap]);
        
        // Parse data rows (start after header row)
        $data = [];
        $dataStartRow = $headerRowIndex + 1;
        
        for ($rowIdx = $dataStartRow; $rowIdx < count($rows); $rowIdx++) {
            $row = $rows[$rowIdx];
            
            // Get barangay name and eligible population
            $barangay = trim($row[0] ?? '');
            $eligiblePop = (int)($row[1] ?? 0);
            
            // DEBUG: Log first few data rows
            // if ($rowIdx <= $dataStartRow + 2) {
            //     Log::info("Data row {$rowIdx}:", ['barangay' => $barangay, 'eligible_pop' => $eligiblePop, 'row' => $row]);
            // }
            
            // Skip empty rows, total row, or rows with metadata
            if (empty($barangay) || 
                strtoupper($barangay) === 'TOTAL' ||
                stripos($barangay, 'version') !== false ||
                stripos($barangay, 'saved') !== false ||
                stripos($barangay, 'archived') !== false) {
                continue;
            }
            
            // Group data by vaccine
            $vaccineData = [];
            foreach ($vaccineMap as $col => $info) {
                $vaccine = $info['vaccine'];
                $type = $info['type'];
                
                if (!isset($vaccineData[$vaccine])) {
                    $vaccineData[$vaccine] = [
                        'M' => 0,
                        'F' => 0,
                        'T' => 0,
                        '%' => 0.00
                    ];
                }
                
                $value = $row[$col] ?? 0;
                if ($type === '%') {
                    $vaccineData[$vaccine][$type] = (float)$value;
                } else {
                    $vaccineData[$vaccine][$type] = (int)$value;
                }
            }
            
            // Convert to long format (one row per vaccine)
            foreach ($vaccineData as $vaccineName => $counts) {
                $data[] = [
                    'barangay' => $barangay,
                    'vaccine_name' => $vaccineName,
                    'male_count' => $counts['M'],
                    'female_count' => $counts['F'],
                    'total_count' => $counts['T'],
                    'percentage' => $counts['%'],
                    'eligible_population' => $eligiblePop
                ];
            }
        }
        
        // DEBUG: Log::info('Parsed wide format data', ['count' => count($data), 'sample' => array_slice($data, 0, 3)]);
        return $data;
    }
    
    /**
     * Parse long format (manual template)
     * Structure: Barangay, Vaccine Name, Male, Female, Total, Percentage, Eligible Population
     */
    private function parseLongFormat($rows)
    {
        // Skip header row
        $headers = array_shift($rows);
        
        $data = [];
        foreach ($rows as $index => $row) {
            // Skip empty rows
            if (empty($row[0]) && empty($row[1])) {
                continue;
            }
            
            $data[] = [
                'barangay' => trim($row[0] ?? ''),
                'vaccine_name' => trim($row[1] ?? ''),
                'male_count' => (int)($row[2] ?? 0),
                'female_count' => (int)($row[3] ?? 0),
                'total_count' => (int)($row[4] ?? 0),
                'percentage' => (float)($row[5] ?? 0.00),
                'eligible_population' => (int)($row[6] ?? 0)
            ];
        }
        
        // DEBUG: Log::info('Parsed long format data', ['count' => count($data)]);
        return $data;
    }
    
    /**
     * Parse CSV file
     */
    private function parseCsvFile($file)
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header row
        fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty($row[0]) || empty($row[1])) {
                continue;
            }
            
            $data[] = [
                'barangay' => trim($row[0]),
                'vaccine_name' => trim($row[1]),
                'male_count' => (int)($row[2] ?? 0),
                'female_count' => (int)($row[3] ?? 0),
                'total_count' => (int)($row[4] ?? 0),
                'percentage' => (float)($row[5] ?? 0.00),
                'eligible_population' => (int)($row[6] ?? 0)
            ];
        }
        
        fclose($handle);
        return $data;
    }
    
    /**
     * Download import template with pre-filled headers and sample data
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Infantsystem - DOH Vaccination Report')
            ->setTitle('Vaccination Report Import Template')
            ->setSubject('Import Template')
            ->setDescription('Use this template to import historical vaccination data');
        
        // Define headers
        $headers = [
            'Barangay',
            'Vaccine Name',
            'Male Count',
            'Female Count',
            'Total Count',
            'Percentage',
            'Eligible Population'
        ];
        
        // Set header row
        $sheet->fromArray($headers, null, 'A1');
        
        // Style the header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'] // Indigo color
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25); // Barangay
        $sheet->getColumnDimension('B')->setWidth(25); // Vaccine Name
        $sheet->getColumnDimension('C')->setWidth(15); // Male Count
        $sheet->getColumnDimension('D')->setWidth(15); // Female Count
        $sheet->getColumnDimension('E')->setWidth(15); // Total Count
        $sheet->getColumnDimension('F')->setWidth(15); // Percentage
        $sheet->getColumnDimension('G')->setWidth(20); // Eligible Population
        
        // Get all barangays and vaccines from the system
        $barangays = $this->reportService->getAllBarangays();
        $vaccines = \App\Models\Vaccine::orderBy('vaccine_name')->get();
        
        // Add sample data (first 3 barangays with first 2 vaccines as examples)
        $row = 2;
        $sampleBarangays = array_slice($barangays, 0, 2);
        $sampleVaccines = $vaccines->take(2);
        
        foreach ($sampleBarangays as $barangay) {
            foreach ($sampleVaccines as $vaccine) {
                $sheet->setCellValue('A' . $row, $barangay);
                $sheet->setCellValue('B' . $row, $vaccine->vaccine_name);
                $sheet->setCellValue('C' . $row, 0); // Male Count (example)
                $sheet->setCellValue('D' . $row, 0); // Female Count (example)
                $sheet->setCellValue('E' . $row, 0); // Total Count (example)
                $sheet->setCellValue('F' . $row, 0.00); // Percentage (example)
                $sheet->setCellValue('G' . $row, 0); // Eligible Population (example)
                
                // Apply borders to sample data
                $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);
                
                $row++;
            }
        }
        
        // Add instructions sheet
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('Instructions');
        
        $instructions = [
            ['IMPORT TEMPLATE INSTRUCTIONS'],
            [''],
            ['How to use this template:'],
            ['1. Fill in the "Template" sheet with your vaccination data'],
            ['2. Each row represents one vaccine for one barangay'],
            ['3. Make sure to use exact barangay and vaccine names from your system'],
            ['4. When uploading, you will select the PERIOD (year and month range) in the import modal'],
            ['5. All data in this file will be assigned to the period you select during upload'],
            ['6. Save the file and upload it in the Import Historical Data section'],
            [''],
            ['IMPORTANT - About Periods:'],
            ['• The Excel file does NOT need period/month columns'],
            ['• You select the reporting period (e.g., "January 2025" or "Jan-Mar 2025") when uploading'],
            ['• This keeps the template simple and flexible for any time period'],
            [''],
            ['Column Descriptions:'],
            ['• Barangay: Name of the barangay (must match system records)'],
            ['• Vaccine Name: Name of the vaccine (must match system records)'],
            ['• Male Count: Number of male children vaccinated'],
            ['• Female Count: Number of female children vaccinated'],
            ['• Total Count: Total children vaccinated (should equal Male + Female)'],
            ['• Percentage: Vaccination coverage percentage'],
            ['• Eligible Population: Total eligible population for this vaccine in this barangay'],
            [''],
            ['Available Barangays:'],
        ];
        
        foreach ($barangays as $barangay) {
            $instructions[] = ['• ' . $barangay];
        }
        
        $instructions[] = [''];
        $instructions[] = ['Available Vaccines:'];
        
        foreach ($vaccines as $vaccine) {
            $instructions[] = ['• ' . $vaccine->vaccine_name];
        }
        
        $instructionsSheet->fromArray($instructions, null, 'A1');
        $instructionsSheet->getColumnDimension('A')->setWidth(80);
        
        // Style instructions title
        $instructionsSheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '4F46E5']
            ]
        ]);
        
        // Set active sheet back to Template
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setTitle('Template');
        
        // Generate filename - generic name to avoid confusion about periods
        $filename = 'DOH_Vaccination_Import_Template.xlsx';
        
        // Create writer and download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    /**
     * Delete archived report version
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'quarter_start' => 'required|integer|min:1|max:12',
            'quarter_end' => 'required|integer|min:1|max:12',
            'version' => 'required|integer|min:1',
            'deletion_reason' => 'required|string|min:3|max:500'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Use soft delete with the required deletion reason
        $result = $this->reportService->softDeleteReport(
            $request->year,
            $request->quarter_start,
            $request->quarter_end,
            $request->version,
            $request->deletion_reason
        );

        activity('report')
            ->event('report_deleted')
            ->withProperties([
                'year' => $request->year,
                'quarter_start' => $request->quarter_start,
                'quarter_end' => $request->quarter_end,
                'version' => $request->version,
                'deletion_reason' => $request->deletion_reason,
            ])
            ->log("Deleted report version {$request->version}");

        // Clear all caches to ensure fresh data on next load
        Cache::flush();
        
        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }
    
    /**
     * Restore a soft-deleted report version
     */
    public function restore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'year' => 'required|integer',
            'quarter_start' => 'required|integer|min:1|max:12',
            'quarter_end' => 'required|integer|min:1|max:12',
            'version' => 'required|integer|min:1'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $result = $this->reportService->restoreReport(
            $request->year,
            $request->quarter_start,
            $request->quarter_end,
            $request->version
        );

        activity('report')
            ->event('report_restored')
            ->withProperties([
                'year' => $request->year,
                'quarter_start' => $request->quarter_start,
                'quarter_end' => $request->quarter_end,
                'version' => $request->version,
            ])
            ->log("Restored report version {$request->version}");

        // Clear all caches to ensure fresh data on next load
        Cache::flush();
        
        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
        ]);
    }
    
    /**
     * Bulk restore multiple soft-deleted report versions
     */
    public function bulkRestore(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reports' => 'required|array|min:1',
                'reports.*.year' => 'required|integer',
                'reports.*.quarter_start' => 'required|integer|min:1|max:12',
                'reports.*.quarter_end' => 'required|integer|min:1|max:12',
                'reports.*.version' => 'required|integer|min:1'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $count = 0;
            $errors = [];
            
            foreach ($request->reports as $report) {
                try {
                    $result = $this->reportService->restoreReport(
                        $report['year'],
                        $report['quarter_start'],
                        $report['quarter_end'],
                        $report['version']
                    );
                    
                    if ($result['success']) {
                        $count++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to restore report: " . $e->getMessage();
                    \Log::error('Bulk restore error for report: ' . json_encode($report), [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Clear all caches to ensure fresh data on next load
            Cache::flush();

            if ($count > 0) {
                activity('report')
                    ->event('report_bulk_restored')
                    ->withProperties([
                        'count' => $count,
                        'errors' => count($errors),
                    ])
                    ->log("Bulk restored {$count} report version(s)");
            }

            return response()->json([
                'success' => true,
                'count' => $count,
                'errors' => $errors,
                'message' => "Successfully restored {$count} report(s)"
            ]);
        } catch (\Exception $e) {
            \Log::error('Bulk restore error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk permanent delete multiple soft-deleted report versions
     * 
     * DISABLED: This function is disabled for data protection.
     * Uncomment to re-enable permanent delete functionality.
     * Note: This was disabled to prevent accidental/malicious permanent data loss.
     */
    /*
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'reports' => 'required|array|min:1',
                'reports.*.year' => 'required|integer',
                'reports.*.quarter_start' => 'required|integer|min:1|max:12',
                'reports.*.quarter_end' => 'required|integer|min:1|max:12',
                'reports.*.version' => 'required|integer|min:1'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $count = 0;
            $errors = [];
            
            foreach ($request->reports as $report) {
                try {
                    $result = $this->reportService->permanentlyDeleteReport(
                        $report['year'],
                        $report['quarter_start'],
                        $report['quarter_end'],
                        $report['version']
                    );
                    
                    if ($result['success']) {
                        $count++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete report: " . $e->getMessage();
                    \Log::error('Bulk delete error for report: ' . json_encode($report), [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            // Clear all caches to ensure fresh data on next load
            Cache::flush();
            
            return response()->json([
                'success' => true,
                'count' => $count,
                'errors' => $errors,
                'message' => "Successfully deleted {$count} report(s) permanently"
            ]);
        } catch (\Exception $e) {
            \Log::error('Bulk delete error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    */
    
    /**
     * Compare two report versions
     */
    public function compare(Request $request)
    {
        $year = $request->input('year');
        $quarterStart = $request->input('quarter_start');
        $quarterEnd = $request->input('quarter_end');
        $version1 = $request->input('version1');
        $version2 = $request->input('version2');
        
        // Get both report versions
        $report1 = $this->reportService->getReport($year, $quarterStart, $quarterEnd, null, $version1);
        $report2 = $this->reportService->getReport($year, $quarterStart, $quarterEnd, null, $version2);
        
        return view('health_worker.report-compare', [
            'report1' => $report1,
            'report2' => $report2,
            'year' => $year,
            'quarterStart' => $quarterStart,
            'quarterEnd' => $quarterEnd,
            'version1' => $version1,
            'version2' => $version2,
            'dateRange' => $report1['date_range']
        ]);
    }
    
    /**
     * Show report settings page
     */
    public function showSettings()
    {
        $settings = [
            'enabled' => config('auto_save.enabled', true),
            'monthly_enabled' => config('auto_save.monthly.enabled', true),
            'quarterly_enabled' => config('auto_save.quarterly.enabled', true),
            'notifications_enabled' => config('auto_save.notifications.enabled', false),
            'notification_email' => config('auto_save.notifications.email', 'admin@example.com'),
            'keep_versions' => config('auto_save.retention.keep_versions', 5),
            'auto_delete_old' => config('auto_save.retention.auto_delete_old', false),
        ];
        
        return view('health_worker.report-settings', compact('settings'));
    }
    
    /**
     * Update report settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'enabled' => 'required|boolean',
                'monthly_enabled' => 'required|boolean',
                'quarterly_enabled' => 'required|boolean',
                'notifications_enabled' => 'required|boolean',
                'notification_email' => 'nullable|email',
                'keep_versions' => 'required|integer|min:1|max:50',
                'auto_delete_old' => 'required|boolean',
            ]);
            
            // Update .env file
            $this->updateEnvFile([
                'AUTO_SAVE_REPORTS_ENABLED' => $validated['enabled'] ? 'true' : 'false',
                'AUTO_SAVE_MONTHLY_ENABLED' => $validated['monthly_enabled'] ? 'true' : 'false',
                'AUTO_SAVE_QUARTERLY_ENABLED' => $validated['quarterly_enabled'] ? 'true' : 'false',
                'AUTO_SAVE_NOTIFICATIONS_ENABLED' => $validated['notifications_enabled'] ? 'true' : 'false',
                'AUTO_SAVE_NOTIFICATION_EMAIL' => $validated['notification_email'] ?? 'admin@example.com',
                'AUTO_SAVE_KEEP_VERSIONS' => $validated['keep_versions'],
                'AUTO_SAVE_AUTO_DELETE_OLD' => $validated['auto_delete_old'] ? 'true' : 'false',
            ]);
            
            // Clear config cache
            \Artisan::call('config:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update settings', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update .env file with new values
     */
    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);
        
        foreach ($data as $key => $value) {
            // Check if key exists
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    $envContent
                );
            } else {
                // Add new key at the end
                $envContent .= "\n{$key}={$value}";
            }
        }
        
        file_put_contents($envPath, $envContent);
    }

    /**
     * Log custom column actions (add, rename, delete) for activity tracking.
     */
    public function logCustomColumn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:add,rename,delete',
            'column_title' => 'required|string|max:50',
            'old_title' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false], 422);
        }

        $action = $request->action;
        $title = $request->column_title;

        $description = match ($action) {
            'add' => "Added custom column: {$title}",
            'rename' => "Renamed custom column from '{$request->old_title}' to '{$title}'",
            'delete' => "Deleted custom column: {$title}",
        };

        activity('report')
            ->event("custom_column_{$action}")
            ->withProperties([
                'action' => $action,
                'column_title' => $title,
                'old_title' => $request->old_title,
            ])
            ->log($description);

        return response()->json(['success' => true]);
    }
}
