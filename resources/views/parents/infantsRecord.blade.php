<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Vaccine Monitoring</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <!-- Offline Tailwind CSS -->
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/infantsRecord.css') }}">
    <style>
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .animate-slide-in-right {
            animation: slideInRight 0.3s ease-out forwards;
        }
        
        .animate-slide-out-right {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        
        /* Desktop: wider container */
        .main-container { max-width: 1200px; }
        @media (min-width: 1024px) {
            .main-container { max-width: 1440px; }
        }

        /* Mobile responsive fixes */
        @media (max-width: 640px) {
            .dose-box {
                padding: 0.375rem;
                font-size: 0.65rem;
            }
            .dose-box .font-semibold {
                font-size: 0.6rem;
            }
            .dose-box .text-xs {
                font-size: 0.55rem;
            }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    @php
        use App\Models\VaccinationSchedule;
        use App\Models\Feedback;
        use Carbon\Carbon;
        
        $parent = auth('parents')->user();
        $eligibleSchedule = null;
        $canEvaluate = false;
        $statusMessage = '';
        $hoursRemaining = 0;
        
        if ($parent && $parent->barangay) {
            // SERVER-SIDE TIMEZONE (Production) - Uses Asia/Manila timezone
            $nowPHT = Carbon::now('Asia/Manila');
            
            // Calculate the cutoff time (24 hours ago from now)
            $cutoffTime = $nowPHT->copy()->subHours(24);
            
            // Find vaccination schedule that occurred within the last 24 hours
            // Priority 1: Check for RHU (Health Center) schedule - applies to ALL parents
            // Priority 2: Check for parent's specific barangay schedule
            $recentSchedule = VaccinationSchedule::where(function($query) use ($parent) {
                    // Match parent's barangay OR RHU (Health Center) which applies to everyone
                    $query->where('barangay', $parent->barangay)
                          ->orWhere('barangay', 'RHU (Health Center)')
                          ->orWhere('barangay', 'RHU/All Barangays');
                })
                ->where('vaccination_date', '<=', $nowPHT) // Schedule has already occurred
                ->where('vaccination_date', '>=', $cutoffTime) // Within last 24 hours
                ->orderBy('vaccination_date', 'desc')
                ->first();
            
            if ($recentSchedule) {
                // Parse the vaccination date and calculate time elapsed
                $vaccinationDate = Carbon::parse($recentSchedule->vaccination_date)->setTimezone('Asia/Manila');
                
                // Calculate hours elapsed since the vaccination (absolute value)
                $hoursElapsed = $vaccinationDate->diffInHours($nowPHT, false);
                $hoursRemaining = max(0, 24 - $hoursElapsed);
                
                // Only allow evaluation within 24-hour window
                if ($hoursElapsed >= 0 && $hoursElapsed <= 24) {
                    // Check if already submitted feedback
                    $existingFeedback = Feedback::where('parent_id', $parent->id)
                        ->where('vaccination_schedule_id', $recentSchedule->id)
                        ->first();
                    
                    if (!$existingFeedback) {
                        $eligibleSchedule = $recentSchedule;
                        $canEvaluate = true;
                        
                        // Show location info in status message
                        $locationInfo = in_array($recentSchedule->barangay, ['RHU (Health Center)', 'RHU/All Barangays']) 
                            ? 'RHU Health Center' 
                            : $recentSchedule->barangay;
                        $statusMessage = "Evaluation available for {$locationInfo} (expires in " . ceil($hoursRemaining) . " hours)";
                    } else {
                        $statusMessage = "Already submitted feedback for this vaccination schedule";
                    }
                } else {
                    $statusMessage = "24-hour evaluation window expired";
                }
            } else {
                $statusMessage = "No active vaccination schedule in the last 24 hours";
            }
        } else {
            $statusMessage = "Please update your profile with your barangay";
        }
    @endphp
    
    <!-- Back button -->
    <a href="{{ $returnUrl ?? route('parent.dashboard') }}" class="fixed top-3 left-3 w-9 h-9 sm:w-8 sm:h-8 bg-purple-600 rounded-full flex items-center justify-center shadow-md hover:bg-purple-700 transition-transform hover:scale-110 z-10">
        <img src="{{ asset('images/arrow.png') }}" alt="Back" class="w-4 h-4 brightness-0 invert">
    </a>

    <div class="main-container container mx-auto px-3 sm:px-4 py-4 sm:py-6">
        <div class="flex flex-col lg:flex-row gap-4 sm:gap-6">
            <!-- Patient Information Sidebar - Redesigned -->
            <div class="lg:w-1/3">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Profile Section with Photo -->
                    <div class="bg-gradient-to-br from-purple-600 to-indigo-700 p-4 sm:p-5">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <!-- Profile Photo (Clickable) -->
                            <div class="relative group flex-shrink-0">
                                <button type="button" 
                                        id="photoUploadBtn"
                                        class="w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden border-4 border-white/30 shadow-lg transition-transform group-hover:scale-105 focus:outline-none focus:ring-4 focus:ring-white/30">
                                    @if($patient->hasProfileImage())
                                        <img src="{{ $patient->profile_image_url }}?v={{ time() }}" 
                                             alt="{{ $patient->name }}"
                                             id="patientPhoto"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div id="patientInitials" class="w-full h-full bg-white/20 flex items-center justify-center">
                                            <span class="text-2xl sm:text-3xl font-bold text-white">{{ $patient->initials }}</span>
                                        </div>
                                    @endif
                                </button>
                                <!-- Camera overlay on hover -->
                                <div class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Basic Info beside photo -->
                            <div class="flex-1 min-w-0 text-white">
                                <h2 class="text-lg sm:text-xl font-bold truncate">{{ $patient->name ?? 'Patient Name' }}</h2>
                                <div class="mt-1.5 sm:mt-2 space-y-0.5 text-xs sm:text-sm">
                                    <p>
                                        <span class="opacity-75">Age:</span> 
                                        <span class="font-semibold">{{ $patient->formatted_age }}</span>
                                    </p>
                                    <p>
                                        <span class="opacity-75">Sex:</span> 
                                        <span class="font-semibold">{{ $patient->sex ?? 'N/A' }}</span>
                                    </p>
                                    <p>
                                        <span class="opacity-75">Birthday:</span> 
                                        <span class="font-semibold">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') : 'N/A' }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Measurements Section -->
                    <div class="p-3 sm:p-4 space-y-3 sm:space-y-4">
                        <!-- Birth Measurements -->
                        <div class="bg-purple-50 rounded-lg p-2.5 sm:p-3">
                            <div class="text-xs uppercase font-semibold text-purple-700 mb-1.5 sm:mb-2">Birth Measurements</div>
                            <div class="grid grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm text-gray-700">
                                <div>
                                    <span class="text-gray-500">Weight:</span><br>
                                    <span class="font-bold text-gray-800">{{ $patient->birth_weight ?? 'N/A' }} kg</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Height:</span><br>
                                    <span class="font-bold text-gray-800">{{ $patient->birth_height ?? 'N/A' }} cm</span>
                                </div>
                            </div>
                        </div>

                        <!-- Current Measurements -->
                        <div class="bg-purple-50 rounded-lg p-2.5 sm:p-3">
                            <div class="text-xs uppercase font-semibold text-purple-700 mb-1.5 sm:mb-2">Current Measurements</div>
                            <div class="grid grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm text-gray-700">
                                <div>
                                    <span class="text-gray-500">Weight:</span><br>
                                    <span class="font-bold text-gray-800">
                                        @if($patient->latestGrowthRecord)
                                            {{ $patient->latestGrowthRecord->weight ?? 'N/A' }} kg
                                        @else
                                            {{ $patient->birth_weight ?? 'N/A' }} kg
                                        @endif
                                    </span>
                                    <div class="text-[10px] sm:text-xs text-gray-500 mt-0.5">
                                        @if($patient->latestGrowthRecord)
                                            {{ \Carbon\Carbon::parse($patient->latestGrowthRecord->recorded_date)->format('M d, Y') }}
                                        @else
                                            (birth)
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="text-gray-500">Height:</span><br>
                                    <span class="font-bold text-gray-800">
                                        @if($patient->latestGrowthRecord)
                                            {{ $patient->latestGrowthRecord->height ?? 'N/A' }} cm
                                        @else
                                            {{ $patient->birth_height ?? 'N/A' }} cm
                                        @endif
                                    </span>
                                    <div class="text-[10px] sm:text-xs text-gray-500 mt-0.5">
                                        @if($patient->latestGrowthRecord)
                                            {{ \Carbon\Carbon::parse($patient->latestGrowthRecord->recorded_date)->format('M d, Y') }}
                                        @else
                                            (birth)
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <button id="growthHistoryBtn" class="w-full mt-2.5 sm:mt-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg py-1.5 sm:py-2 px-3 text-xs sm:text-sm transition font-medium">
                                📊 View Growth History
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Evaluation Button -->
                <div class="mt-4 sm:mt-5 flex flex-col items-end">
                    @if($canEvaluate)
                        <button id="evaluationBtn" class="bg-purple-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg hover:bg-purple-700 transition text-sm sm:text-base">
                            Evaluate Service
                        </button>
                        <p class="text-[10px] sm:text-xs text-green-600 mt-1 text-right">{{ $statusMessage }}</p>
                    @else
                        <button disabled class="bg-gray-400 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg cursor-not-allowed text-sm sm:text-base">
                            Evaluate Service
                        </button>
                        <p class="text-[10px] sm:text-xs text-gray-600 mt-1 text-right">{{ $statusMessage }}</p>
                    @endif
                </div>
                
                <!-- QR Code Section for Parents -->
                @if(isset($patient) && $patient->qr_uuid)
                <div class="mt-4 sm:mt-5">
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-br from-purple-600 to-indigo-700 px-3 py-2">
                            <h4 class="text-white text-xs sm:text-sm font-semibold flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h4.5v4.5h-4.5v-4.5zm0 10.5h4.5v4.5h-4.5v-4.5zm10.5-10.5h4.5v4.5h-4.5v-4.5z"/>
                                </svg>
                                Child's QR Code
                            </h4>
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col items-center">
                            <div id="patientQrCode" 
                                 onclick="openQrModal()"
                                 class="w-28 h-28 sm:w-32 sm:h-32 flex items-center justify-center bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:border-purple-400 hover:shadow-md transition-all duration-200">
                                <!-- QR Code will be loaded here -->
                                <svg class="w-12 h-12 text-gray-300 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h4.5v4.5h-4.5v-4.5zm0 10.5h4.5v4.5h-4.5v-4.5zm10.5-10.5h4.5v4.5h-4.5v-4.5zm-1.5 10.5h1.5v1.5h-1.5v-1.5zm3 0h1.5v1.5h-1.5v-1.5zm-3 3h1.5v1.5h-1.5v-1.5zm3 0h1.5v1.5h-1.5v-1.5z"/>
                                </svg>
                            </div>
                            <p class="text-[10px] sm:text-xs text-purple-600 mt-2 text-center font-medium">Tap to enlarge for scanning</p>
                            <p class="text-[10px] sm:text-xs text-gray-500 mt-1 text-center">Present this QR code during vaccination day for faster service</p>
                        </div>
                    </div>
                </div>
                
                <!-- QR Code Enlarged Modal -->
                <div id="qrCodeModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <!-- Dark overlay with blur (same as Growth History Modal) -->
                    <div class="fixed inset-0" style="background-color: rgba(107, 114, 128, 0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);" onclick="closeQrModal()"></div>
                    
                    <!-- Modal Panel -->
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative rounded-xl shadow-2xl transform transition-all overflow-hidden" style="background-color: #ffffff; width: 100%; max-width: 380px;">
                            
                            <!-- Header - Purple theme (matching Growth History) -->
                            <div style="background-color: #7c3aed; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
                                <h2 style="font-size: 18px; font-weight: 700; color: #ffffff; margin: 0;">Child's QR Code</h2>
                                <button onclick="closeQrModal()" style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; border: none; cursor: pointer;">
                                    <svg style="width: 18px; height: 18px; color: #ffffff;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- QR Code Content -->
                            <div class="p-6 flex flex-col items-center">
                                <!-- QR Code Container -->
                                <div id="modalQrCode" class="w-64 h-64 sm:w-72 sm:h-72 flex items-center justify-center bg-white rounded-lg border-2 border-gray-100 shadow-inner">
                                    <!-- Large QR Code will be loaded here -->
                                    <svg class="w-16 h-16 text-gray-300 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h4.5v4.5h-4.5v-4.5zm0 10.5h4.5v4.5h-4.5v-4.5zm10.5-10.5h4.5v4.5h-4.5v-4.5z"/>
                                    </svg>
                                </div>
                                
                                <!-- Patient Name (Large, centered below QR) -->
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 mt-4 text-center">{{ $patient->first_name }} {{ $patient->last_name }}</h3>
                                
                                <!-- Instructions -->
                                <p class="text-sm text-gray-500 mt-2 text-center">Present this QR code during vaccination day for faster service</p>
                                
                                <!-- Close Button -->
                                <button onclick="closeQrModal()" 
                                        class="mt-5 w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-purple-700 hover:to-indigo-700 transition-all duration-200 shadow-lg">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Vaccination Table -->
            <div class="lg:w-2/3">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-purple-600 text-white p-3 sm:p-4">
                        <h3 class="text-base sm:text-lg font-semibold">Vaccination Record</h3>
                    </div>
                    
                    <!-- Vaccination Table Section with horizontal scroll -->
                    <div class="overflow-x-auto -mx-0">
                        <table class="vaccine-table w-full border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap">Bakuna</th>
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap">Doses Description</th>
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap" colspan="3">Petsa ng Bakuna</th>
                                <th class="p-1.5 sm:p-2 text-left text-xs sm:text-sm font-semibold whitespace-nowrap">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($vaccinations) && $vaccinations->isNotEmpty())
                                @php
                                    $schoolAgedVaccines = ['Measles Containing', 'Tetanus Diphtheria', 'Human Papillomavirus'];
                                    $showSchoolAgedHeader = false;
                                    $showNewVaccineHeader = false;
                                @endphp
                                @foreach ($vaccinations as $vaccination)
                                    @php
                                        $isSchoolAgedVaccine = in_array($vaccination->vaccine->vaccine_name, $schoolAgedVaccines);
                                        $isNewVaccine = optional($vaccination->vaccine)->is_standard === false;
                                    @endphp
                                    @if($isSchoolAgedVaccine && !$showSchoolAgedHeader)
                                        <tr class="school-aged bg-gray-100">
                                            <td colspan="6" class="p-2 text-sm text-center font-semibold">SCHOOL-AGED CHILDREN</td>
                                        </tr>
                                        @php $showSchoolAgedHeader = true; @endphp
                                    @endif
                                    @if($isNewVaccine && !$showNewVaccineHeader)
                                        <tr class="school-aged bg-gray-100">
                                            <td colspan="6" class="p-2 text-sm text-center font-semibold">NEW VACCINE</td>
                                        </tr>
                                        @php $showNewVaccineHeader = true; @endphp
                                    @endif
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="p-2 text-sm font-bold">
                                            @if($vaccination->vaccine->vaccine_name === 'BCG')
                                                <span title="Bacillus Calmette-Guérin">{{ $vaccination->vaccine->vaccine_name ?? 'N/A' }}</span>
                                            @else
                                                {{ $vaccination->vaccine->vaccine_name ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td class="p-2 text-sm">{{ $vaccination->vaccine->doses_description ?? 'N/A' }}</td>
                                        
                    @php
                        $totalDoses = optional($vaccination->vaccine)->getTotalDosesCount() ?? 1;
                        $doseDates = [];
                        for ($i = 1; $i <= $totalDoses; $i++) {
                            $doseDates[$i] = $vaccination->{'dose_'.$i.'_date'} ?? '';
                        }

                        $doseGridClass = 'grid-cols-1';
                        if ($totalDoses === 2) {
                            $doseGridClass = 'grid-cols-1 sm:grid-cols-2';
                        } elseif ($totalDoses >= 3) {
                            $doseGridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
                        }
                        // Simplified dose status logic:
                        // Green = Completed (date exists)
                        // Yellow = Pending (date not yet set)
                        // No overdue logic - health workers manage custom schedules for delayed patients
                        $getDoseStatus = function($doseDate) {
                            if ($doseDate) {
                                return ['status' => 'completed', 'color' => 'bg-green-100 text-green-800 border-green-300'];
                            }
                            return ['status' => 'pending', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-300'];
                        };
                    @endphp

                    <td class="p-2 text-sm" colspan="3">
                        <div class="grid {{ $doseGridClass }} gap-3">
                            @foreach($doseDates as $doseNumber => $doseDate)
                                @php $doseStatus = $getDoseStatus($doseDate); @endphp
                                <div class="rounded-2xl border border-gray-200 bg-slate-50 p-3 shadow-sm text-center {{ $doseStatus['color'] }}">
                                    @if($doseDate)
                                        <div class="font-semibold text-sm text-gray-900">{{ \Carbon\Carbon::parse($doseDate)->format('M d, Y') }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-[0.18em] text-gray-600">✓ Dose {{ $doseNumber }}</div>
                                    @else
                                        <div class="font-semibold text-sm text-gray-900">Dose {{ $doseNumber }}</div>
                                        <div class="mt-1 text-xs uppercase tracking-[0.18em] text-gray-600">Pending</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </td>
                    <td class="p-2 text-sm">{{ $vaccination->remarks ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="p-2 text-sm text-center text-gray-600">No vaccination records available for this patient.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    </div> <!-- Close overflow-x-auto container -->
                </div>
            </div>
        </div>

        <!-- Evaluation Modal -->
        <div id="evaluationModal" class="modal hidden fixed inset-0 z-50 bg-black/50 overflow-y-auto">
            <div class="modal-content bg-white mx-auto my-4 p-4 sm:p-5 border border-gray-300 rounded-lg shadow-lg w-[95%] sm:w-[90%] max-w-2xl max-h-[95vh] overflow-y-auto -translate-y-16">
                <span class="close float-right text-2xl cursor-pointer ">×</span>
                <h2 class="text-lg text-black sm:text-xl font-bold mb-4">Vaccination Service Evaluation</h2>
                
                <form id="evaluationForm" method="POST" action="{{ route('feedback.store') }}">
                    @csrf
                    <input type="hidden" name="vaccination_schedule_id" value="{{ $eligibleSchedule ? $eligibleSchedule->id : '' }}">
                    
                    <table class="evaluation-table w-full border-collapse bg-white mb-4">
                        <thead>
                            <tr class="bg-purple-600 text-white">
                                <th class="p-2 sm:p-3 text-left text-sm font-semibold w-[60%]">Evaluation Criteria</th>
                                <th class="p-2 sm:p-3 text-center text-sm font-semibold w-[20%]">OO</th>
                                <th class="p-2 sm:p-3 text-center text-sm font-semibold w-[20%]">HINDI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">1. MADALI BA NINYONG NAHANAP ANG HEALTH CENTER AT MAY NAKITA BA KAYONG KARATULA NG DIREKSYON?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question1" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question1" value="HINDI"></td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">2. MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question2" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question2" value="HINDI"></td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">3. MAGALANG, MABAIT, AT MAAYOS BA ANG SERBISYO NG HEALTH WORKER NA NAGBIGAY NG BAKUNA?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question3" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question3" value="HINDI"></td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">4. NAIPALIWANAG BA SA INYO NG HEALTH WORKER ANG TUNGKOL SA BAKUNA AT KAILAN ANG SUSUNOD NA SCHEDULE?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question4" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question4" value="HINDI"></td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td class="p-2 sm:p-3 text-sm text-black">5. NASIYAHAN BA KAYO SA KABUUANG SERBISYO NG HEALTH CENTER SA ARAW NA ITO?</td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question5" value="OO" required></td>
                                <td class="p-2 sm:p-3 text-center"><input type="radio" name="question5" value="HINDI"></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="submit" class="submit-btn bg-purple-600 text-white w-full py-2 rounded-md text-sm hover:bg-purple-700 transition">Submit Evaluation</button>
                </form>
            </div>
        </div>

        <!-- Growth History Modal -->
        <!-- Growth History Modal - Responsive Design -->
        <div id="growthHistoryModal" class="modal hidden fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <!-- Dark overlay with blur -->
            <div class="fixed inset-0" style="background-color: rgba(107, 114, 128, 0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"></div>
            
            <!-- Modal Panel - Compact responsive width -->
            <div class="flex min-h-full items-center justify-center" style="padding: 8px;">
                <div id="growthModalCard" class="relative rounded-xl shadow-2xl transform transition-all overflow-hidden" style="background-color: #ffffff; width: 100%; max-width: 500px;">
                    
                    <!-- Header - Purple theme -->
                    <div style="background-color: #7c3aed; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h2 style="font-size: 18px; font-weight: 700; color: #ffffff; margin: 0;">Growth History</h2>
                            <p style="font-size: 12px; color: #ddd6fe; margin-top: 2px;">{{ $patient->first_name }} {{ $patient->last_name }}</p>
                        </div>
                        <button class="closeGrowth" style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; border: none; cursor: pointer;">
                            <svg style="width: 18px; height: 18px; color: #ffffff;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Current Stats Row -->
                    @php
                        $uniqueGrowthRecords = $patient->uniqueGrowthRecords;
                        $currentHeight = $uniqueGrowthRecords && $uniqueGrowthRecords->count() > 0 
                            ? $uniqueGrowthRecords->first()->height 
                            : $patient->birth_height;
                        $currentWeight = $uniqueGrowthRecords && $uniqueGrowthRecords->count() > 0 
                            ? $uniqueGrowthRecords->first()->weight 
                            : $patient->birth_weight;
                    @endphp
                    
                    <div style="background-color: #f5f3ff; padding: 16px 20px; display: flex; align-items: center; justify-content: center; gap: 24px; border-bottom: 1px solid #ddd6fe;">
                        <div style="text-align: center;">
                            <p style="font-size: 11px; font-weight: 600; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Current Height</p>
                            <p style="font-size: 26px; font-weight: 700; color: #7c3aed; margin: 0;">{{ $currentHeight }}<span style="font-size: 14px; font-weight: 500; color: #8b5cf6; margin-left: 2px;">cm</span></p>
                        </div>
                        <div style="width: 1px; height: 40px; background-color: #c4b5fd;"></div>
                        <div style="text-align: center;">
                            <p style="font-size: 11px; font-weight: 600; color: #6d28d9; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">Current Weight</p>
                            <p style="font-size: 26px; font-weight: 700; color: #7c3aed; margin: 0;">{{ $currentWeight }}<span style="font-size: 14px; font-weight: 500; color: #8b5cf6; margin-left: 2px;">kg</span></p>
                        </div>
                    </div>
                    
                    <!-- Table Section -->
                    <div style="padding: 16px; max-height: 45vh; overflow-y: auto; background-color: #ffffff;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <!-- Table Header - Purple theme -->
                            <thead>
                                <tr style="background-color: #7c3aed;">
                                    <th style="padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase;">Date</th>
                                    <th style="padding: 10px 8px; text-align: center; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase;">Height</th>
                                    <th style="padding: 10px 8px; text-align: center; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase;">Weight</th>
                                    <th style="padding: 10px 12px; text-align: center; font-size: 11px; font-weight: 700; color: #ffffff; text-transform: uppercase;">Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($uniqueGrowthRecords && $uniqueGrowthRecords->count() > 0)
                                    @foreach($uniqueGrowthRecords as $index => $record)
                                    <tr style="background-color: {{ $index % 2 === 0 ? '#faf5ff' : '#ffffff' }}; border-bottom: 1px solid #e5e7eb;">
                                        <td style="padding: 10px 12px; color: #1f2937; font-weight: 500; font-size: 13px;">{{ \Carbon\Carbon::parse($record->recorded_date)->format('M d, Y') }}</td>
                                        <td style="padding: 10px 8px; color: #1f2937; text-align: center; font-weight: 600; font-size: 14px;">{{ $record->height ?? '-' }}</td>
                                        <td style="padding: 10px 8px; color: #1f2937; text-align: center; font-weight: 600; font-size: 14px;">{{ $record->weight ?? '-' }}</td>
                                        <td style="padding: 10px 12px; text-align: center;">
                                            @if($index === 0)
                                            <span style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 600; background-color: #7c3aed; color: #ffffff; border-radius: 12px;">Latest</span>
                                            @else
                                            <span style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 500; background-color: #e5e7eb; color: #374151; border-radius: 12px;">Checkup</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                                
                                <!-- Birth Row -->
                                <tr style="background-color: #fef3c7; border-top: 2px solid #fbbf24;">
                                    <td style="padding: 10px 12px; color: #1f2937; font-weight: 500; font-size: 13px;">{{ \Carbon\Carbon::parse($patient->date_of_birth)->format('M d, Y') }}</td>
                                    <td style="padding: 10px 8px; color: #1f2937; text-align: center; font-weight: 600; font-size: 14px;">{{ $patient->birth_height }}</td>
                                    <td style="padding: 10px 8px; color: #1f2937; text-align: center; font-weight: 600; font-size: 14px;">{{ $patient->birth_weight }}</td>
                                    <td style="padding: 10px 12px; text-align: center;">
                                        <span style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 600; background-color: #f59e0b; color: #ffffff; border-radius: 12px;">Birth</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        @if(!$uniqueGrowthRecords || $uniqueGrowthRecords->count() === 0)
                        <p style="text-align: center; font-size: 13px; color: #6b7280; margin-top: 16px; padding: 12px; background-color: #f3f4f6; border-radius: 8px;">
                            No checkup records yet. Only birth measurements are available.
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Upload Modal -->
        <div id="photoUploadModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closePhotoModal()"></div>
            
            <!-- Modal Content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-700 px-5 py-4 rounded-t-2xl flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">Upload Photo</h3>
                                <p class="text-xs text-white/80">{{ $patient->name }}</p>
                            </div>
                        </div>
                        <button onclick="closePhotoModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Body -->
                    <div class="p-5">
                        <!-- Preview Area -->
                        <div id="photoPreviewArea" class="mb-4">
                            <div id="previewPlaceholder" class="w-32 h-32 mx-auto rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                            </div>
                            <img id="photoPreview" src="" alt="Preview" class="hidden w-32 h-32 mx-auto rounded-full object-cover border-4 border-purple-200 shadow-lg">
                        </div>
                        
                        <!-- File Input -->
                        <div class="text-center">
                            <input type="file" id="photoInput" accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="previewPhoto(event)">
                            <label for="photoInput" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer transition text-sm font-medium text-gray-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Choose from Gallery
                            </label>
                            <p class="mt-2 text-xs text-gray-500">JPG or PNG, max 5MB</p>
                        </div>
                        
                        <!-- Error Message -->
                        <div id="photoError" class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 text-center"></div>
                        
                        <!-- Upload Button -->
                        <div id="uploadBtnContainer" class="hidden mt-4">
                            <button type="button" onclick="uploadPhoto()" id="confirmUploadBtn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                <span id="uploadBtnText">Upload Photo</span>
                            </button>
                        </div>
                        
                        <!-- Loading Spinner (shown during upload) -->
                        <div id="uploadSpinner" class="hidden mt-4">
                            <div class="flex items-center justify-center gap-3 py-3">
                                <svg class="animate-spin h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-medium text-gray-600">Uploading...</span>
                            </div>
                        </div>
                        
                        @if($patient->hasProfileImage())
                        <!-- Delete Photo Option -->
                        <div class="mt-4 pt-4 border-t border-gray-100 text-center">
                            <button type="button" onclick="deletePhoto()" class="text-sm text-red-600 hover:text-red-700 font-medium transition">
                                Remove current photo
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Photo Upload Variables
            const photoUploadModal = document.getElementById('photoUploadModal');
            const photoUploadBtn = document.getElementById('photoUploadBtn');
            const photoInput = document.getElementById('photoInput');
            const photoPreview = document.getElementById('photoPreview');
            const previewPlaceholder = document.getElementById('previewPlaceholder');
            const uploadBtnContainer = document.getElementById('uploadBtnContainer');
            const photoError = document.getElementById('photoError');
            const uploadSpinner = document.getElementById('uploadSpinner');
            const confirmUploadBtn = document.getElementById('confirmUploadBtn');
            
            let selectedFile = null;
            
            // Open photo upload modal
            if (photoUploadBtn) {
                photoUploadBtn.onclick = function() {
                    photoUploadModal.classList.remove('hidden');
                    resetPhotoModal();
                }
            }
            
            // Close photo modal
            function closePhotoModal() {
                photoUploadModal.classList.add('hidden');
                resetPhotoModal();
            }
            
            // Reset modal state
            function resetPhotoModal() {
                photoInput.value = '';
                selectedFile = null;
                photoPreview.classList.add('hidden');
                previewPlaceholder.classList.remove('hidden');
                uploadBtnContainer.classList.add('hidden');
                photoError.classList.add('hidden');
                uploadSpinner.classList.add('hidden');
                confirmUploadBtn.disabled = false;
            }
            
            // Preview selected photo
            function previewPhoto(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    showPhotoError('Please select a JPG or PNG image.');
                    return;
                }
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showPhotoError('Image size must be less than 5MB.');
                    return;
                }
                
                selectedFile = file;
                photoError.classList.add('hidden');
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.src = e.target.result;
                    photoPreview.classList.remove('hidden');
                    previewPlaceholder.classList.add('hidden');
                    uploadBtnContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
            
            // Show error message
            function showPhotoError(message) {
                photoError.textContent = message;
                photoError.classList.remove('hidden');
            }
            
            // Upload photo to server
            async function uploadPhoto() {
                if (!selectedFile) return;
                
                // Show loading state
                uploadSpinner.classList.remove('hidden');
                uploadBtnContainer.classList.add('hidden');
                photoError.classList.add('hidden');
                
                const formData = new FormData();
                formData.append('photo', selectedFile);
                formData.append('_token', '{{ csrf_token() }}');
                
                try {
                    const response = await fetch('{{ route("patient.upload-photo", $patient->id) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update the photo in the sidebar
                        const patientPhoto = document.getElementById('patientPhoto');
                        const patientInitials = document.getElementById('patientInitials');
                        
                        if (patientPhoto) {
                            patientPhoto.src = data.image_url;
                        } else if (patientInitials) {
                            // Replace initials with photo
                            const photoContainer = patientInitials.parentElement;
                            patientInitials.remove();
                            const newImg = document.createElement('img');
                            newImg.src = data.image_url;
                            newImg.alt = '{{ $patient->name }}';
                            newImg.id = 'patientPhoto';
                            newImg.className = 'w-full h-full object-cover';
                            photoContainer.appendChild(newImg);
                        }
                        
                        closePhotoModal();
                        showPhotoSuccessNotification('Photo uploaded successfully!');
                    } else {
                        uploadSpinner.classList.add('hidden');
                        uploadBtnContainer.classList.remove('hidden');
                        showPhotoError(data.message || 'Upload failed. Please try again.');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    uploadSpinner.classList.add('hidden');
                    uploadBtnContainer.classList.remove('hidden');
                    showPhotoError('An error occurred. Please try again.');
                }
            }
            
            // Delete photo
            async function deletePhoto() {
                if (!confirm('Are you sure you want to remove this photo?')) return;
                
                uploadSpinner.classList.remove('hidden');
                
                try {
                    const response = await fetch('{{ route("patient.delete-photo", $patient->id) }}', {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Replace photo with initials
                        const patientPhoto = document.getElementById('patientPhoto');
                        if (patientPhoto) {
                            const photoContainer = patientPhoto.parentElement;
                            patientPhoto.remove();
                            
                            const initialsDiv = document.createElement('div');
                            initialsDiv.id = 'patientInitials';
                            initialsDiv.className = 'w-full h-full bg-white/20 flex items-center justify-center';
                            initialsDiv.innerHTML = `<span class="text-2xl sm:text-3xl font-bold text-white">${data.initials}</span>`;
                            photoContainer.appendChild(initialsDiv);
                        }
                        
                        closePhotoModal();
                        showPhotoSuccessNotification('Photo removed successfully!');
                    } else {
                        uploadSpinner.classList.add('hidden');
                        showPhotoError(data.message || 'Delete failed. Please try again.');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    uploadSpinner.classList.add('hidden');
                    showPhotoError('An error occurred. Please try again.');
                }
            }
            
            // Success notification
            function showPhotoSuccessNotification(message) {
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 z-[60] animate-slide-in-right';
                notification.innerHTML = `
                    <div class="bg-white rounded-lg shadow-2xl border-l-4 border-green-500 p-4 max-w-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">${message}</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(notification);
                setTimeout(() => {
                    notification.classList.add('animate-slide-out-right');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
        </script>

        <script>
            const modal = document.getElementById("evaluationModal");
            const btn = document.getElementById("evaluationBtn");
            const span = document.getElementsByClassName("close")[0];
            const form = document.getElementById("evaluationForm");

            // Only attach event listener if button exists and is not disabled
            if (btn) {
                btn.onclick = function() {
                    modal.style.display = "block";
                }
            }

            if (span) {
                span.onclick = function() {
                    modal.style.display = "none";
                }
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                
                const jsonData = {
                    vaccination_schedule_id: formData.get('vaccination_schedule_id'),
                    content: JSON.stringify({
                        question1: formData.get('question1'),
                        question2: formData.get('question2'),
                        question3: formData.get('question3'),
                        question4: formData.get('question4'),
                        question5: formData.get('question5'),
                    })
                };

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(jsonData),
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.error || 'Network response was not ok');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Close modal and reset form
                    modal.style.display = "none";
                    form.reset();
                    
                    // Disable the evaluation button after successful submission
                    if (btn) {
                        btn.disabled = true;
                        btn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                        btn.classList.add('bg-gray-400', 'cursor-not-allowed');
                        btn.onclick = null; // Remove click handler
                        
                        // Update the status message below the button
                        const statusMsg = btn.nextElementSibling;
                        if (statusMsg && statusMsg.tagName === 'P') {
                            statusMsg.textContent = 'Naisumite na ang pagsusuri. Salamat sa iyong feedback!';
                            statusMsg.classList.remove('text-green-600');
                            statusMsg.classList.add('text-gray-600');
                        }
                    }
                    
                    // Show enhanced success message
                    showSuccessNotification();
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorNotification();
                });
            });

            // Enhanced Success Notification
            function showSuccessNotification() {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 z-[60] animate-slide-in-right';
                notification.innerHTML = `
                    <div class="bg-white rounded-lg shadow-2xl border-l-4 border-green-500 p-5 max-w-md">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Evaluation Submitted!</h3>
                                <p class="text-sm text-gray-600 mb-3">Thank you for taking the time to evaluate our vaccination service. Your feedback helps us improve!</p>
                                <div class="flex items-center gap-2 text-xs text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Just now</span>
                                </div>
                            </div>
                            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    notification.classList.add('animate-slide-out-right');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            // Enhanced Error Notification
            function showErrorNotification() {
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 z-[60] animate-slide-in-right';
                notification.innerHTML = `
                    <div class="bg-white rounded-lg shadow-2xl border-l-4 border-red-500 p-5 max-w-md">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Submission Failed</h3>
                                <p class="text-sm text-gray-600">An error occurred while submitting your evaluation. Please try again.</p>
                            </div>
                            <button onclick="this.parentElement.parentElement.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('animate-slide-out-right');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            // Growth History Modal
            const growthModal = document.getElementById("growthHistoryModal");
            const growthBtn = document.getElementById("growthHistoryBtn");
            const growthCloseButtons = document.getElementsByClassName("closeGrowth");

            if (growthBtn) {
                growthBtn.onclick = function() {
                    growthModal.style.display = "block";
                }
            }

            // Handle all close buttons
            for (let i = 0; i < growthCloseButtons.length; i++) {
                growthCloseButtons[i].onclick = function() {
                    growthModal.style.display = "none";
                }
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
                // Close when clicking on backdrop
                if (event.target == growthModal || event.target.classList.contains('bg-gray-900/60')) {
                    growthModal.style.display = "none";
                }
            }
        </script>
        
        <!-- QR Code Loading Script -->
        @if(isset($patient) && $patient->qr_uuid)
        <script>
            // Store the QR SVG for modal use
            let qrSvgContent = null;
            
            (function() {
                const qrContainer = document.getElementById('patientQrCode');
                const modalQrContainer = document.getElementById('modalQrCode');
                
                if (qrContainer) {
                    fetch('{{ route("parent.patient.qr.image", $patient->id) }}')
                        .then(response => {
                            if (!response.ok) throw new Error('Failed to load QR');
                            return response.text();
                        })
                        .then(svg => {
                            // Store SVG for modal
                            qrSvgContent = svg;
                            
                            // Set small QR code
                            qrContainer.innerHTML = svg;
                            const svgEl = qrContainer.querySelector('svg');
                            if (svgEl) {
                                svgEl.style.width = '100%';
                                svgEl.style.height = '100%';
                            }
                            
                            // Set large QR code in modal
                            if (modalQrContainer) {
                                modalQrContainer.innerHTML = svg;
                                const modalSvgEl = modalQrContainer.querySelector('svg');
                                if (modalSvgEl) {
                                    modalSvgEl.style.width = '100%';
                                    modalSvgEl.style.height = '100%';
                                }
                            }
                        })
                        .catch(err => {
                            console.error('QR load error:', err);
                            qrContainer.innerHTML = '<span class="text-xs text-gray-400">QR unavailable</span>';
                            if (modalQrContainer) {
                                modalQrContainer.innerHTML = '<span class="text-sm text-gray-400">QR unavailable</span>';
                            }
                        });
                }
            })();
            
            // QR Modal Functions (matching Growth History Modal behavior)
            function openQrModal() {
                const modal = document.getElementById('qrCodeModal');
                if (modal) {
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden'; // Prevent background scroll
                }
            }
            
            function closeQrModal() {
                const modal = document.getElementById('qrCodeModal');
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = ''; // Restore scroll
                }
            }
            
            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeQrModal();
                }
            });
        </script>
        @endif
    </div>
</body>
</html>