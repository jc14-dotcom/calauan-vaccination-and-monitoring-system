<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Immunization Card</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
    <style>
        .page-wrap{ width:100%; max-width:1600px; margin:0 auto; padding:1rem; }
        @media (min-width:640px){ .page-wrap{ padding:2rem; } }
        .brand-gradient{ background: linear-gradient(135deg, #7a5bbd 0%, #5a3f99 45%, #402d73 100%); }
        .title-shadow{ text-shadow: 0 1px 0 rgba(255,255,255,.35); }
        .date-input{ height: 2.5rem; }
        .remarks-area{ min-height: 3rem; }
        .date-input::-webkit-calendar-picker-indicator{ filter: invert(28%) sepia(23%) saturate(2877%) hue-rotate(224deg) brightness(88%) contrast(88%); }
        .date-input{ accent-color: #6b46c1; }
        .table-grid th, .table-grid td { border-bottom: 1px solid rgba(122, 91, 189, 0.16); }
        .table-grid td:not(:first-of-type), .table-grid th:not(:first-of-type) { border-left: 1px solid rgba(122, 91, 189, 0.22); }
        .table-grid tbody tr > td:nth-of-type(2) { text-align: center; }
        
        /* Prevent vaccine name text wrapping */
        .vaccine-name-cell { white-space: nowrap; min-width: 180px; }
        
        /* Modal animations */
        #errorModal { transition: opacity 0.3s ease; }
        
        /* Toggle switch styles */
        .toggle-bg {
            transition: all 0.3s ease;
        }
        .toggle-dot {
            transition: transform 0.3s ease;
        }
        
        /* Disabled vaccine row styling */
        .vaccine-row.not-eligible-row input[type="date"]:disabled,
        .vaccine-row.not-eligible-row textarea:disabled {
            background-color: #f3f4f6 !important;
            cursor: not-allowed !important;
            opacity: 0.5 !important;
        }
        
        /* ========== PRINT STYLES - Compact Immunization Card ========== */
        @media print {
            @page { 
                size: landscape; 
                margin: 0.3cm; 
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                font-size: 8pt;
                background: white !important;
            }
            
            .no-print { 
                display: none !important; 
            }
            
            /* QR Code in Header - White box on right side */
            #headerQrContainer {
                display: block !important;
                background: white !important;
                border-radius: 0 !important;
                padding: 0.15cm !important;
                margin-left: 0.3cm !important;
            }
            
            #patientQrCode {
                width: 1.8cm !important;
                height: 1.8cm !important;
            }
            
            #patientQrCode svg {
                width: 100% !important;
                height: 100% !important;
            }
            
            .page-wrap {
                max-width: 100% !important;
                padding: 0.2cm !important;
                margin: 0 !important;
                position: relative !important;
            }
            
            /* Header - Compact purple banner */
            header.brand-gradient,
            header {
                background: linear-gradient(135deg, #5a3f99 0%, #402d73 100%) !important;
                border-radius: 0 !important;
                margin-bottom: 0.3cm !important;
                padding: 0.2cm 0.4cm !important;
                page-break-inside: avoid;
            }
            
            header .px-3,
            header .sm\\:px-6 {
                padding: 0.15cm 0.3cm !important;
            }
            
            header h1 {
                font-size: 14pt !important;
                margin: 0 !important;
            }
            
            header p {
                font-size: 7pt !important;
                margin: 0 !important;
            }
            
            header img {
                height: 1cm !important;
                width: auto !important;
            }
            
            header a,
            header .invisible {
                display: none !important;
            }
            
            /* Hide growth measurements section in print */
            .mb-6.p-4.rounded-xl.bg-gradient-to-r {
                display: none !important;
            }
            
            /* Patient Info Section - Underline style like physical card */
            .grid.grid-cols-1.md\:grid-cols-2 {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0.2cm !important;
                margin-bottom: 0.3cm !important;
            }
            
            .space-y-4 > div {
                margin-bottom: 0.1cm !important;
            }
            
            .space-y-4 label,
            label {
                font-size: 7pt !important;
                font-weight: 600 !important;
                color: #333 !important;
                margin-bottom: 0 !important;
                display: inline !important;
            }
            
            /* Input fields - No border, just underline */
            input[type="text"],
            input[type="date"],
            input[type="number"],
            select,
            textarea {
                border: none !important;
                border-bottom: 1px solid #333 !important;
                border-radius: 0 !important;
                background: transparent !important;
                padding: 0.05cm 0.1cm !important;
                font-size: 8pt !important;
                height: auto !important;
                min-height: 0.4cm !important;
                line-height: 1.2 !important;
                box-shadow: none !important;
                outline: none !important;
            }
            
            /* Make fields inline with labels for compactness */
            .space-y-4 > div {
                display: flex !important;
                flex-wrap: wrap !important;
                align-items: baseline !important;
                gap: 0.1cm !important;
            }
            
            .space-y-4 > div > label {
                flex-shrink: 0 !important;
            }
            
            .space-y-4 > div > input,
            .space-y-4 > div > select {
                flex: 1 !important;
                min-width: 2cm !important;
            }
            
            /* Grid fields (Address/Barangay, Sex/Contact) */
            .grid.grid-cols-2 {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0.2cm !important;
            }
            
            .grid.grid-cols-2 > div {
                display: flex !important;
                flex-wrap: wrap !important;
                align-items: baseline !important;
                gap: 0.05cm !important;
            }
            
            /* Vaccination Table - Compact like physical card */
            .bg-white.rounded-xl,
            .overflow-x-auto,
            .rounded-2xl.shadow-sm {
                border-radius: 0 !important;
                border: 1px solid #333 !important;
                box-shadow: none !important;
                overflow: visible !important;
                margin-bottom: 0.2cm !important;
            }
            
            table.table-grid,
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 7pt !important;
            }
            
            /* Table Header - Orange/Yellow like physical card */
            thead tr,
            .bg-primary-700 {
                background: #f5a623 !important;
            }
            
            thead th,
            th {
                background: #f5a623 !important;
                color: #000 !important;
                font-size: 7pt !important;
                font-weight: 700 !important;
                padding: 0.1cm 0.15cm !important;
                border: 1px solid #333 !important;
                text-transform: uppercase !important;
            }
            
            /* Table Body */
            tbody td,
            td {
                padding: 0.08cm 0.1cm !important;
                border: 1px solid #666 !important;
                font-size: 7pt !important;
                vertical-align: middle !important;
            }
            
            tbody tr:nth-child(odd) {
                background: #fff !important;
            }
            
            tbody tr:nth-child(even) {
                background: #f9f9f9 !important;
            }
            
            /* Date inputs in table - very compact */
            td input[type="date"],
            td .date-input {
                width: 100% !important;
                font-size: 6.5pt !important;
                padding: 0.02cm !important;
                text-align: center !important;
                border: none !important;
                border-bottom: 1px solid #999 !important;
                height: auto !important;
            }
            
            td .grid {
                display: flex !important;
                gap: 0.1cm !important;
            }
            
            td .grid input {
                flex: 1 !important;
                min-width: 0 !important;
            }
            
            /* Remarks textarea */
            td textarea,
            td .remarks-area {
                width: 100% !important;
                min-height: 0.3cm !important;
                font-size: 6.5pt !important;
                resize: none !important;
                border: none !important;
                border-bottom: 1px solid #999 !important;
                padding: 0.02cm !important;
            }
            
            /* Info note at bottom - compact */
            .rounded-xl.brand-gradient,
            .mt-8.rounded-xl {
                background: #5a3f99 !important;
                color: white !important;
                padding: 0.15cm 0.3cm !important;
                font-size: 6pt !important;
                border-radius: 0 !important;
                margin-top: 0.2cm !important;
            }
            
            /* Hide modals, alerts, buttons in print */
            #errorModal,
            #successModal,
            #backdateModal,
            #stockAlertBanner,
            form button[type="submit"],
            .flex.justify-center button {
                display: none !important;
            }
            
            /* Show ALL vaccines when printing (override filtering) */
            .vaccine-row.hidden,
            .school-aged-header.hidden {
                display: table-row !important;
            }
            
            /* Reset eligibility styling for print - show all as normal */
            .vaccine-row.not-eligible-row {
                background: inherit !important;
                opacity: 1 !important;
            }
            
            /* Ensure everything fits on one page */
            .page-wrap > * {
                page-break-inside: avoid !important;
            }
            
            /* Hide wave/decorative elements */
            svg[viewBox="0 0 1440 100"],
            .absolute.inset-x-0 {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
<div class="page-wrap">
    <header class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 brand-gradient text-white">
        <div class="px-3 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ $returnUrl ?? route('health_worker.vaccination_status') }}" aria-label="Back" class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 px-2 sm:px-3 py-2 ring-1 ring-white/25 transition-transform duration-150 ease-out hover:-translate-y-[1px] no-print">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
            <div class="flex-1 flex items-center justify-center">
                <div class="w-full max-w-3xl sm:max-w-4xl">
                    <div class="hidden sm:grid sm:grid-cols-3 sm:items-center">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/todoligtass.png') }}" alt="Left Logo" class="h-16 md:h-18 w-auto drop-shadow object-contain" loading="lazy"/>
                        </div>
                        <div class="text-center">
                            <h1 class="text-xl md:text-2xl font-bold uppercase tracking-wide title-shadow">Immunization Card</h1>
                            <p class="text-[11px] md:text-xs text-white/85">Infant Vaccination & Growth Monitoring</p>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <img src="{{ asset('images/doh-logo.png') }}" alt="DOH" class="h-14 md:h-16 w-auto drop-shadow object-contain" loading="lazy"/>
                            <img src="{{ asset('images/right.png') }}" alt="Right Logo" class="h-14 md:h-16 w-auto drop-shadow object-contain" loading="lazy"/>
                        </div>
                    </div>
                    <div class="sm:hidden flex flex-col items-center">
                        <div class="text-center">
                            <h1 class="text-xl font-bold uppercase tracking-wide title-shadow">Immunization Card</h1>
                            <p class="text-[11px] text-white/85">Infant Vaccination & Growth Monitoring</p>
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-3">
                            <img src="{{ asset('images/todoligtass.png') }}" alt="Left Logo" class="h-10 w-auto drop-shadow object-contain" loading="lazy"/>
                            <img src="{{ asset('images/doh-logo.png') }}" alt="DOH" class="h-10 w-auto drop-shadow object-contain" loading="lazy"/>
                            <img src="{{ asset('images/right.png') }}" alt="Right Logo" class="h-10 w-auto drop-shadow object-contain" loading="lazy"/>
                        </div>
                    </div>
                </div>
            </div>
            <!-- QR Code Section (inline with header) -->
            @if(isset($patient) && $patient->qr_uuid)
            <div class="flex-shrink-0 ml-3">
                <div class="bg-white rounded-lg p-1.5 shadow-lg" id="headerQrContainer">
                    <div id="patientQrCode" class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-300 animate-pulse" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h4.5v4.5h-4.5v-4.5zm0 10.5h4.5v4.5h-4.5v-4.5zm10.5-10.5h4.5v4.5h-4.5v-4.5z"/>
                        </svg>
                    </div>
                </div>
            </div>
            @else
            <a aria-hidden="true" tabindex="-1" class="invisible pointer-events-none inline-flex items-center gap-2 rounded-full bg-white/15 px-2 sm:px-3 py-2 ring-1 ring-white/25">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
            @endif
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="space-y-4">
            <!-- Child's Name - Single combined field (read-only) -->
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Child's Name</label>
                <input type="text" value="{{ $patient->name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Date of Birth</label>
                <input type="date" id="date_of_birth" value="{{ $patient->date_of_birth ?? '' }}" readonly class="date-input block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Place of Birth</label>
                <input type="text" value="{{ $patient->place_of_birth ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Address</label>
                    <input type="text" value="{{ $patient->address ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Barangay</label>
                    <input type="text" value="{{ $patient->barangay ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Mother's Name</label>
                <input type="text" value="{{ $patient->mother_name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Father's Name</label>
                <input type="text" value="{{ $patient->father_name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <!-- Birth Weight & Height (Read-only) -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Birth Weight (kg)</label>
                    <input type="number" value="{{ $patient->birth_weight ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Birth Height (cm)</label>
                    <input type="number" value="{{ $patient->birth_height ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Sex</label>
                    <select disabled class="block w-full h-11 rounded-md border-2 border-primary-300 bg-gray-50 px-3 text-[15px]">
                        <option value="" disabled {{ empty($patient->sex ?? '') ? 'selected' : '' }}>Select Sex</option>
                        <option value="Male" {{ ($patient->sex ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ ($patient->sex ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Contact No.</label>
                    <input type="text" value="{{ $patient->contact_no ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
            </div>
        </div>
    </div>

    <!-- Current Growth Measurements Section (Full Width) -->
    <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-primary-50 to-purple-50 border border-primary-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-600 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800">Current Growth Measurements</h3>
                    <p class="text-xs text-gray-500">Update the infant's current weight and height • Auto-saves when you change values</p>
                </div>
            </div>
            <div id="growthSaveStatus" class="hidden items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full">
                <span id="statusIcon"></span>
                <span id="statusText"></span>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="current_weight" class="block text-sm font-semibold text-gray-700 mb-1">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        Current Weight (kg)<span class="text-red-500">*</span>
                    </span>
                </label>
                @php
                    $currentWeight = $patient->latestGrowthRecord->weight ?? null;
                    $displayWeight = $currentWeight ? (floor($currentWeight) == $currentWeight ? (int)$currentWeight : $currentWeight) : '';
                @endphp
                <input type="number" step="0.01" min="0.5" max="50" id="current_weight" name="current_weight" 
                    value="{{ $displayWeight }}" 
                    placeholder="Enter weight"
                    class="growth-input block w-full rounded-lg border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] font-medium focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 transition-all shadow-sm hover:border-primary-400">
            </div>
            <div>
                <label for="current_height" class="block text-sm font-semibold text-gray-700 mb-1">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        Current Height (cm)<span class="text-red-500">*</span>
                    </span>
                </label>
                @php
                    $currentHeight = $patient->latestGrowthRecord->height ?? null;
                    $displayHeight = $currentHeight ? (floor($currentHeight) == $currentHeight ? (int)$currentHeight : $currentHeight) : '';
                @endphp
                <input type="number" step="0.01" min="20" max="200" id="current_height" name="current_height" 
                    value="{{ $displayHeight }}" 
                    placeholder="Enter height"
                    class="growth-input block w-full rounded-lg border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] font-medium focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 transition-all shadow-sm hover:border-primary-400">
            </div>
            <div class="sm:col-span-2 lg:col-span-2 flex items-end">
                @if($patient->latestGrowthRecord && $patient->latestGrowthRecord->recorded_date)
                <div class="w-full px-4 py-3 bg-white/60 rounded-lg border border-primary-100">
                    <p class="text-xs text-gray-500 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Last updated: <strong class="text-gray-700">{{ \Carbon\Carbon::parse($patient->latestGrowthRecord->recorded_date)->format('M d, Y') }}</strong></span>
                    </p>
                </div>
                @else
                <div class="w-full px-4 py-3 bg-yellow-50/60 rounded-lg border border-yellow-200">
                    <p class="text-xs text-yellow-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>No growth record yet. Enter current measurements to start tracking.</span>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if (session('error'))
        <!-- Error Modal Backdrop -->
        <div id="errorModal" class="no-print fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
                <!-- Modal Header -->
                <div class="bg-red-600 rounded-t-2xl px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Vaccination Failed</h3>
                    </div>
                    <button onclick="closeErrorModal()" class="text-white hover:text-red-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="px-6 py-5">
                    <p class="text-gray-800 font-semibold mb-3">{{ session('error') }}</p>
                    @if (session('stock_errors'))
                        <ul class="space-y-2 mb-4">
                            @foreach(session('stock_errors') as $stockError)
                                <li class="flex items-start gap-2 text-sm text-gray-700 bg-red-50 rounded-lg px-3 py-2">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>{{ $stockError }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg px-4 py-3 mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-sm text-blue-800">Please update the inventory before administering these vaccines. You can access the inventory from the dashboard menu.</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Modal Footer -->
                @if (session('stock_errors'))
                    <div class="bg-gray-50 rounded-b-2xl px-6 py-4 flex justify-end">
                        <a href="{{ route('health_worker.inventory') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            Go to Inventory
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if (session('success'))
        <!-- Success Modal Backdrop -->
        <div id="successModal" class="no-print fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
                <!-- Modal Header -->
                <div class="bg-green-600 rounded-t-2xl px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Success!</h3>
                    </div>
                    <button onclick="closeSuccessModal()" class="text-white hover:text-green-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="px-6 py-5 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-gray-800 font-semibold text-lg mb-2">{{ session('success') }}</p>
                    <p class="text-sm text-gray-600">The vaccination records and inventory have been updated.</p>
                </div>
                
            </div>
        </div>
    @endif

    <!-- Date Validation Error Modal (JavaScript-controlled) -->
    <div id="dateValidationModal" class="no-print hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
            <!-- Modal Header -->
            <div class="bg-red-600 rounded-t-2xl px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-white">Vaccination Failed</h3>
                </div>
                <button onclick="closeDateValidationModal()" class="text-white hover:text-red-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="px-6 py-5">
                <p id="dateValidationMessage" class="text-gray-800 font-semibold mb-3"></p>
                <ul id="dateValidationErrors" class="space-y-2 mb-4"></ul>
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gray-50 rounded-b-2xl px-6 py-4 flex justify-end">
                <button onclick="closeDateValidationModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors shadow-sm">
                    Got It
                </button>
            </div>
        </div>
    </div>

    <!-- Backdate Detection Modal -->
    <div id="backdateModal" class="no-print hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all">
            <!-- Modal Header -->
            <div class="relative overflow-hidden rounded-t-2xl px-6 py-5" style="background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);">
                <div class="relative flex items-center gap-3">
                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-7 h-7" style="color: #f97316;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-white" style="text-shadow: 0 2px 4px rgba(0,0,0,0.2);">Backdated Vaccination Entry</h3>
                        <p class="text-sm text-white mt-0.5" style="text-shadow: 0 1px 2px rgba(0,0,0,0.15);">Please specify the vaccination location</p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Body -->
            <div class="px-6 py-6">
                <div class="bg-orange-50 border-l-4 border-orange-500 rounded-r-lg px-4 py-3 mb-5">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-orange-800">You entered a vaccination date in the past:</p>
                            <p id="backdateInfo" class="text-base font-bold text-orange-900 mt-1"></p>
                        </div>
                    </div>
                </div>
                
                <p class="text-gray-700 font-medium mb-4">Where was this vaccine administered?</p>
                
                <div class="space-y-3">
                    <label class="group flex items-start gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary-400 hover:bg-primary-50/50 transition-all duration-200 hover:shadow-md">
                        <input type="radio" name="backdateType" value="calauan" class="mt-1 w-5 h-5 text-primary-600 focus:ring-primary-500 focus:ring-2">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="font-bold text-gray-900">Calauan Health Center</div>
                            </div>
                            <div class="text-sm text-gray-600 space-y-0.5 ml-7">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    <span>Delayed entry - vaccination happened in Calauan</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                    <span><strong class="text-green-700">Will be included</strong> in Calauan reports</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <span>Stock will NOT be deducted (already consumed)</span>
                                </div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="group flex items-start gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-primary-400 hover:bg-primary-50/50 transition-all duration-200 hover:shadow-md">
                        <input type="radio" name="backdateType" value="external" class="mt-1 w-5 h-5 text-primary-600 focus:ring-primary-500 focus:ring-2">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                </svg>
                                <div class="font-bold text-gray-900">External Facility</div>
                            </div>
                            <div class="text-sm text-gray-600 space-y-0.5 ml-7">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                                    <span>Vaccinated outside Calauan (private clinic, other RHU)</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                    <span><strong class="text-red-700">Will NOT be included</strong> in Calauan reports</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <span>Stock will NOT be deducted (not from Calauan supply)</span>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-b-2xl px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
                <button type="button" onclick="closeBackdateModal()" class="px-6 py-2.5 bg-white border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all shadow-sm">
                    Cancel
                </button>
                <button type="button" onclick="confirmBackdateChoice()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg font-semibold hover:from-primary-700 hover:to-primary-800 transition-all shadow-md hover:shadow-lg transform hover:scale-[1.02]">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Confirm & Save
                </button>
            </div>
        </div>
    </div>

    {{-- Inventory stocks section removed per evaluator feedback - not related to patient information --}}

    {{-- Age-Based Filtering Info Bar --}}
    <div class="mb-4 p-4 rounded-xl bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 shadow-sm no-print">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800">Age-Based Vaccine Filtering</h3>
                    <p class="text-sm text-gray-600">
                        Patient Age: <span class="font-semibold text-blue-700">{{ $patientAgeFormatted ?? 'N/A' }}</span>
                        • Vaccines shown based on age eligibility
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <label class="flex items-center cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" id="showAllVaccinesToggle" class="toggle-checkbox sr-only" {{ ($showAllVaccines ?? false) ? 'checked' : '' }}>
                        <div class="toggle-bg block w-14 h-8 rounded-full border-2 border-gray-300 bg-gray-300"></div>
                        <div class="toggle-dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full shadow-md transition-transform"></div>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Show All Vaccines</span>
                </label>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-100 text-green-800">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Green = Age-appropriate
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                Yellow = Missed/Overdue
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-gray-600">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                Gray = Not yet eligible (locked)
            </span>
        </div>
    </div>

    <form action="{{ route('vaccinations.update', $patient->id ?? 0) }}" method="POST" id="vaccinationForm" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="w-full overflow-x-auto rounded-2xl shadow-sm ring-1 ring-primary-200 bg-white">
                    <table class="min-w-full text-[15px] table-grid">
                        <colgroup>
                            <col class="w-[22%]"><col class="w-[18%]"><col class="w-[35%]"><col class="w-[5%]"><col class="w-[20%]">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white first:rounded-tl-2xl">BAKUNA</th>
                                <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white">DOSES</th>
                                <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white" colspan="2">PETSA NG BAKUNA</th>
                                <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white last:rounded-tr-2xl">REMARKS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary-50" id="vaccineTableBody">
                            @php
                                $showSchoolAgedHeader = false;
                                $showNewVaccineHeader = false;
                                $schoolAgedVaccines = ['Measles Containing', 'Tetanus Diphtheria', 'Human Papillomavirus'];
                                
                                // Pre-calculate if any school-aged vaccine should be visible (for header visibility)
                                $anySchoolAgedVisible = false;
                                foreach(($vaccinations ?? []) as $v) {
                                    $vName = optional($v->vaccine)->vaccine_name;
                                    if (in_array($vName, $schoolAgedVaccines)) {
                                        $vId = optional($v->vaccine)->id;
                                        $vElig = $vaccineEligibility[$vId] ?? ['status' => 'eligible'];
                                        $vHasDates = !empty($v->dose_1_date) || !empty($v->dose_2_date) || !empty($v->dose_3_date)
                                            || !empty($v->dose_4_date) || !empty($v->dose_5_date) || !empty($v->dose_6_date)
                                            || !empty($v->dose_7_date) || !empty($v->dose_8_date) || !empty($v->dose_9_date)
                                            || !empty($v->dose_10_date);
                                        if ($vElig['status'] === 'eligible' || $vElig['status'] === 'missed' || $vHasDates) {
                                            $anySchoolAgedVisible = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            @foreach(($vaccinations ?? []) as $vaccination)
                                @php
                                    $vaccineName = optional($vaccination->vaccine)->vaccine_name;
                                    $dosesDescription = optional($vaccination->vaccine)->doses_description;
                                    $vaccineId = optional($vaccination->vaccine)->id;
                                    
                                    // Get eligibility info for this vaccine
                                    $eligibility = $vaccineEligibility[$vaccineId] ?? ['status' => 'eligible', 'reason' => ''];
                                    $isEligible = $eligibility['status'] === 'eligible';
                                    $isMissed = $eligibility['status'] === 'missed';
                                    $isNotEligible = $eligibility['status'] === 'not_eligible';
                                    
                                    // Check if vaccine has any recorded dates (completed vaccines should always show)
                                    $hasRecordedDates = !empty($vaccination->dose_1_date) || !empty($vaccination->dose_2_date) || !empty($vaccination->dose_3_date)
                                        || !empty($vaccination->dose_4_date) || !empty($vaccination->dose_5_date) || !empty($vaccination->dose_6_date)
                                        || !empty($vaccination->dose_7_date) || !empty($vaccination->dose_8_date) || !empty($vaccination->dose_9_date)
                                        || !empty($vaccination->dose_10_date);
                                    
                                    // Row should be visible if: show all, eligible, missed, has recorded dates, or it is a custom/new vaccine record
                                    $isNewVaccine = optional($vaccination->vaccine)->is_standard === false;
                                    $showRow = ($showAllVaccines ?? false) || $isEligible || $isMissed || $hasRecordedDates || $isNewVaccine;
                                    
                                    // Determine row styling based on eligibility
                                    $rowClasses = 'vaccine-row';
                                    if ($isNotEligible && !$hasRecordedDates && !$isNewVaccine) {
                                        $rowClasses .= ' not-eligible-row bg-gray-100 opacity-60';
                                    } elseif ($isMissed && !$hasRecordedDates) {
                                        $rowClasses .= ' missed-row bg-yellow-50';
                                    } else {
                                        $rowClasses .= ' eligible-row';
                                    }
                                    
                                    // Check if we need to show SCHOOL-AGED CHILDREN header
                                    $isSchoolAged = in_array($vaccineName, $schoolAgedVaccines);
                                    $needsSchoolAgedHeader = $isSchoolAged && !$showSchoolAgedHeader && $showRow;
                                    if ($needsSchoolAgedHeader) $showSchoolAgedHeader = true;

                                    // Check if we need to show NEW VACCINE header
                                    $needsNewVaccineHeader = $isNewVaccine && !$showNewVaccineHeader && $showRow;
                                    if ($needsNewVaccineHeader) $showNewVaccineHeader = true;
                                @endphp
                                
                                @if($needsSchoolAgedHeader)
                                    <tr class="school-aged-header {{ !($showAllVaccines ?? false) && !$anySchoolAgedVisible ? 'hidden' : '' }}" data-school-aged-header="true">
                                        <td colspan="5" class="p-2 md:p-3 text-sm font-bold uppercase bg-purple-100 text-purple-800">SCHOOL-AGED CHILDREN</td>
                                    </tr>
                                @endif

                                @if($needsNewVaccineHeader)
                                    <tr class="new-vaccine-header">
                                        <td colspan="5" class="p-2 md:p-3 text-sm font-bold uppercase bg-purple-100 text-purple-800">NEW VACCINE</td>
                                    </tr>
                                @endif
                                
                                <tr class="{{ $rowClasses }} odd:bg-white even:bg-gray-50 {{ !$showRow ? 'hidden' : '' }}" 
                                    data-vaccine-id="{{ $vaccineId }}"
                                    data-eligibility="{{ $eligibility['status'] }}"
                                    data-has-dates="{{ $hasRecordedDates ? 'true' : 'false' }}"
                                    data-is-school-aged="{{ $isSchoolAged ? 'true' : 'false' }}">
                                    <td class="p-3 md:p-4 vaccine-name-cell">
                                        <div class="flex items-center gap-2">
                                            @if($isNotEligible && !$hasRecordedDates)
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @elseif($isMissed && !$hasRecordedDates)
                                                <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            <span class="whitespace-nowrap">{{ $vaccineName ?? 'N/A' }}</span>
                                        </div>
                                        @if($eligibility['reason'] && !$hasRecordedDates)
                                            <p class="text-xs mt-1 whitespace-nowrap {{ $isMissed ? 'text-yellow-600' : 'text-gray-500' }}">{{ $eligibility['reason'] }}</p>
                                        @endif
                                    </td>
                                    <td class="p-3 md:p-4">{{ $dosesDescription ?? 'N/A' }}</td>
                                    <td class="p-3 md:p-4 text-center" colspan="2">
                                        @php
                                            $inputDisabled = $isNotEligible && !$hasRecordedDates;
                                            $disabledClass = $inputDisabled ? 'bg-gray-100 cursor-not-allowed opacity-50' : '';
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
                                        @endphp

                                        <div class="grid {{ $doseGridClass }} gap-3">
                                            @foreach($doseDates as $doseNumber => $doseDate)
                                                <div class="rounded-2xl border border-gray-200 bg-slate-50 p-3 shadow-sm">
                                                    <div class="mb-2 text-[11px] uppercase tracking-[0.22em] text-gray-500">Dose {{ $doseNumber }}</div>
                                                    <input type="date" name="vaccinations[{{ $vaccination->id }}][dose_{{ $doseNumber }}_date]" value="{{ $doseDate }}" {{ $doseDate ? 'readonly' : '' }} {{ $inputDisabled ? 'disabled' : '' }} class="w-full rounded-xl border border-gray-200 bg-white text-center px-3 py-2 text-sm text-gray-700 outline-none focus:border-primary-500 focus:ring-primary-200 focus:ring focus:ring-opacity-50 {{ $disabledClass }}" data-vaccine-name="{{ $vaccineName }}" data-dose="{{ $doseNumber }}">
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="p-3 md:p-4">
                                        <textarea name="vaccinations[{{ $vaccination->id }}][remarks]" {{ $inputDisabled ? 'disabled' : '' }} class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px] {{ $disabledClass }}">{{ $vaccination->remarks ?? '' }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-center mt-6">
                    <button type="submit" id="saveButton" disabled class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white shadow-sm ring-1 ring-primary-600/50 focus:outline-none focus:ring-4 focus:ring-primary-300 transition hover:bg-primary-700 active:scale-[.98] disabled:opacity-60 disabled:cursor-not-allowed">
                        <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-120%] group-hover:translate-x-[120%] transition duration-700"></span>
                        {{-- <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                        </svg> --}}
                        <span>Save</span>
                    </button>
                </div>
            </form>

    <div class="mt-8 rounded-xl brand-gradient text-white px-6 py-5 text-center text-[13px] shadow-sm ring-1 ring-white/10">
        Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon na maaaring makaapekto sa pagbabakuna ng bata.
    </div>

    <script>
    // Set the growth record URL for auto-save
    window.growthRecordUrl = "{{ route('patient.growth_record.store', $patient->id) }}";

    // Toggle visual update function (same as report-settings)
    function updateToggleVisual(checkbox) {
        const toggleBg = checkbox.nextElementSibling;
        const toggleDot = toggleBg ? toggleBg.nextElementSibling : null;
        
        if (!toggleBg || !toggleDot) return;
        
        if (checkbox.checked) {
            toggleBg.style.backgroundColor = '#7c3aed';
            toggleBg.style.borderColor = '#7c3aed';
            toggleDot.style.transform = 'translateX(24px)';
        } else {
            toggleBg.style.backgroundColor = '#d1d5db';
            toggleBg.style.borderColor = '#d1d5db';
            toggleDot.style.transform = 'translateX(0)';
        }
    }

    // Age-Based Vaccine Filtering functionality
    function applyVaccineFilter(showAll) {
        const rows = document.querySelectorAll('.vaccine-row');
        const schoolAgedHeaders = document.querySelectorAll('[data-school-aged-header="true"]');
        
        // Track if any school-aged vaccine is visible
        let schoolAgedVisible = false;
        
        rows.forEach(function(row) {
            const eligibility = row.dataset.eligibility;
            const hasDates = row.dataset.hasDates === 'true';
            const isSchoolAged = row.dataset.isSchoolAged === 'true';
            
            // Show row if: show all is on, or it's eligible/missed, or has recorded dates
            const shouldShow = showAll || eligibility === 'eligible' || eligibility === 'missed' || hasDates;
            
            if (shouldShow) {
                row.classList.remove('hidden');
                if (isSchoolAged) schoolAgedVisible = true;
            } else {
                row.classList.add('hidden');
            }
        });
        
        // Show/hide school-aged headers based on visibility of school-aged vaccines
        schoolAgedHeaders.forEach(function(header) {
            if (showAll || schoolAgedVisible) {
                header.classList.remove('hidden');
            } else {
                header.classList.add('hidden');
            }
        });
    }

    // Show All Vaccines Toggle functionality
    document.addEventListener('DOMContentLoaded', function() {
        const showAllToggle = document.getElementById('showAllVaccinesToggle');
        
        if (showAllToggle) {
            // Update toggle visual on page load
            updateToggleVisual(showAllToggle);
            
            // Apply initial filter state on page load (toggle is OFF by default)
            const initialShowAll = showAllToggle.checked;
            applyVaccineFilter(initialShowAll);
            
            // Listen for toggle changes
            showAllToggle.addEventListener('change', function() {
                // Update visual
                updateToggleVisual(this);
                
                // Apply filter
                const showAll = this.checked;
                applyVaccineFilter(showAll);
                
                // Update URL with show_all parameter (without page reload)
                const url = new URL(window.location.href);
                if (showAll) {
                    url.searchParams.set('show_all', '1');
                } else {
                    url.searchParams.delete('show_all');
                }
                window.history.replaceState({}, '', url);
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('vaccinationForm');
        const saveButton = document.getElementById('saveButton');
        if (!form || !saveButton) return;
        const inputs = Array.from(form.querySelectorAll('input[type="date"]:not([readonly]):not([disabled]), textarea'));
        const initial = inputs.map(i => i.value || '');
        const check = () => { saveButton.disabled = !inputs.some((i, idx) => (i.value || '') !== initial[idx]); };
        inputs.forEach(i => i.addEventListener('input', check));
        check();
    });

    // Toggle stock alert collapse/expand
    function toggleStockAlert() {
        const collapsed = document.getElementById('stockAlertCollapsed');
        const expanded = document.getElementById('stockAlertExpanded');
        const chevron = document.getElementById('stockAlertChevron');
        const chevronExpanded = document.getElementById('stockAlertChevronExpanded');
        
        if (collapsed && expanded) {
            if (collapsed.style.display === 'none') {
                // Show collapsed, hide expanded
                collapsed.style.display = 'block';
                expanded.style.display = 'none';
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            } else {
                // Hide collapsed, show expanded
                collapsed.style.display = 'none';
                expanded.style.display = 'block';
                if (chevronExpanded) chevronExpanded.style.transform = 'rotate(180deg)';
            }
        }
    }

    // Close error modal
    function closeErrorModal() {
        const modal = document.getElementById('errorModal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 300);
        }
    }

    // Close success modal
    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => modal.remove(), 300);
        }
    }

    // Close date validation error modal
    function closeDateValidationModal() {
        const modal = document.getElementById('dateValidationModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Show date validation error modal
    function showDateValidationError(message, errors) {
        const modal = document.getElementById('dateValidationModal');
        const messageEl = document.getElementById('dateValidationMessage');
        const errorsEl = document.getElementById('dateValidationErrors');
        
        messageEl.textContent = message;
        errorsEl.innerHTML = errors.map(error => `
            <li class="flex items-start gap-2 text-sm text-gray-700 bg-red-50 rounded-lg px-3 py-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>${error}</span>
            </li>
        `).join('');
        
        modal.classList.remove('hidden');
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeErrorModal();
            closeSuccessModal();
            closeDateValidationModal();
        }
    });

    // Close modal by clicking backdrop
    document.addEventListener('DOMContentLoaded', function() {
        const errorModal = document.getElementById('errorModal');
        if (errorModal) {
            errorModal.addEventListener('click', function(e) {
                if (e.target === errorModal) {
                    closeErrorModal();
                }
            });
        }
        
        const successModal = document.getElementById('successModal');
        if (successModal) {
            successModal.addEventListener('click', function(e) {
                if (e.target === successModal) {
                    closeSuccessModal();
                }
            });
            
            // Auto-close success modal after 2 seconds
            setTimeout(function() {
                closeSuccessModal();
            }, 2000);
        }
        
        const dateValidationModal = document.getElementById('dateValidationModal');
        if (dateValidationModal) {
            dateValidationModal.addEventListener('click', function(e) {
                if (e.target === dateValidationModal) {
                    closeDateValidationModal();
                }
            });
        }
    });

    // Backdate detection and modal handling
    let backdateDetected = false;
    let backdateDateString = '';

    // Close backdate modal
    function closeBackdateModal() {
        const modal = document.getElementById('backdateModal');
        if (modal) {
            modal.classList.add('hidden');
            // Reset radio buttons
            document.querySelectorAll('input[name="backdateType"]').forEach(radio => {
                radio.checked = false;
            });
        }
    }

    // Confirm backdate choice and submit form
    function confirmBackdateChoice() {
        const selectedType = document.querySelector('input[name="backdateType"]:checked');
        if (!selectedType) {
            alert('Please select where the vaccination was administered.');
            return;
        }

        const form = document.getElementById('vaccinationForm');
        if (!form) return;

        // Add hidden input with backdate type
        let backdateInput = document.getElementById('backdateTypeInput');
        if (!backdateInput) {
            backdateInput = document.createElement('input');
            backdateInput.type = 'hidden';
            backdateInput.name = 'backdate_type';
            backdateInput.id = 'backdateTypeInput';
            form.appendChild(backdateInput);
        }
        backdateInput.value = selectedType.value;

        // Close modal and submit form
        closeBackdateModal();
        form.submit();
    }

    // Check if any date is backdated before form submission
    document.getElementById('vaccinationForm')?.addEventListener('submit', function(e) {
        // Get patient birth date
        const patientBirthDateStr = '{{ $patient->date_of_birth ?? "" }}';
        const patientBirthDate = patientBirthDateStr ? new Date(patientBirthDateStr + 'T00:00:00') : null;
        
        // Format birth date for display
        const birthDateFormatted = patientBirthDate ? patientBirthDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }) : '';
        
        // SERVER-SIDE TIME (Production) - Uses server time from PHP
        const serverDateStr = '{{ now()->format("Y-m-d") }}';
        const today = new Date(serverDateStr + 'T00:00:00');
        const todayFormatted = today.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        // LOCAL MACHINE TIME (Testing) - Uses client's local time
        // Uncomment below and comment above to use local machine time for testing
        // const today = new Date();
        // today.setHours(0, 0, 0, 0);
        
        // Check for date validation errors (before birth or future dates)
        const vaccineInputs = this.querySelectorAll('.vaccine-date-input:not([readonly])');
        const dateValidationErrors = [];
        
        vaccineInputs.forEach(input => {
            if (input.value) {
                const inputDate = new Date(input.value + 'T00:00:00');
                const vaccineName = input.dataset.vaccineName || 'Vaccine';
                const doseNum = input.dataset.dose || '1';
                
                // Check if date is before birth date
                if (patientBirthDate && inputDate < patientBirthDate) {
                    dateValidationErrors.push(`${vaccineName} Dose ${doseNum}: Cannot be before birth date (${birthDateFormatted}).`);
                }
                
                // Check if date is in the future
                if (inputDate > today) {
                    dateValidationErrors.push(`${vaccineName} Dose ${doseNum}: Cannot be a future date (after ${todayFormatted}).`);
                }
            }
        });
        
        // If there are date validation errors, show modal and prevent submission
        if (dateValidationErrors.length > 0) {
            e.preventDefault();
            showDateValidationError('Date validation failed:', dateValidationErrors);
            return;
        }
        
        const dateInputs = this.querySelectorAll('input[type="date"]:not([readonly])');
        backdateDetected = false;
        let oldestBackdate = null;

        dateInputs.forEach(input => {
            if (input.value) {
                const inputDate = new Date(input.value);
                inputDate.setHours(0, 0, 0, 0);
                
                if (inputDate < today) {
                    backdateDetected = true;
                    if (!oldestBackdate || inputDate < oldestBackdate) {
                        oldestBackdate = inputDate;
                    }
                }
            }
        });

        // If backdate detected and user hasn't chosen type yet
        const backdateTypeInput = document.getElementById('backdateTypeInput');
        if (backdateDetected && (!backdateTypeInput || !backdateTypeInput.value)) {
            e.preventDefault();
            
            // Calculate days ago
            const daysAgo = Math.floor((today - oldestBackdate) / (1000 * 60 * 60 * 24));
            const dateStr = oldestBackdate.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            // Update modal with the date info
            document.getElementById('backdateInfo').textContent = 
                `${dateStr} (${daysAgo} ${daysAgo === 1 ? 'day' : 'days'} ago)`;
            
            // Show modal
            document.getElementById('backdateModal').classList.remove('hidden');
        }
    });

    // Close backdate modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBackdateModal();
        }
    });

    // Close backdate modal by clicking backdrop
    document.getElementById('backdateModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeBackdateModal();
        }
    });
    // Auto-save Growth Record functionality
    (function() {
        const weightInput = document.getElementById('current_weight');
        const heightInput = document.getElementById('current_height');
        const statusContainer = document.getElementById('growthSaveStatus');
        const statusIcon = document.getElementById('statusIcon');
        const statusText = document.getElementById('statusText');
        let initialWeight = weightInput ? weightInput.value : '';
        let initialHeight = heightInput ? heightInput.value : '';
        let isSaving = false;
        
        function showStatus(type, message) {
            if (!statusContainer) return;
            statusContainer.classList.remove('hidden');
            statusContainer.classList.add('flex');
            
            // Reset classes
            statusContainer.className = statusContainer.className.replace(/bg-\w+-\d+/g, '').replace(/text-\w+-\d+/g, '');
            statusContainer.classList.add('flex', 'items-center', 'gap-1.5', 'text-xs', 'font-medium', 'px-2.5', 'py-1', 'rounded-full', 'transition-all');
            
            if (type === 'saving') {
                statusContainer.classList.add('bg-blue-100', 'text-blue-700');
                statusIcon.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                statusText.textContent = message || 'Saving...';
            } else if (type === 'success') {
                statusContainer.classList.add('bg-green-100', 'text-green-700');
                statusIcon.innerHTML = '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
                statusText.textContent = message || 'Saved!';
                setTimeout(hideStatus, 3000);
            } else if (type === 'error') {
                statusContainer.classList.add('bg-red-100', 'text-red-700');
                statusIcon.innerHTML = '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
                statusText.textContent = message || 'Error!';
                setTimeout(hideStatus, 4000);
            }
        }
        
        function hideStatus() {
            if (statusContainer) {
                statusContainer.classList.add('hidden');
                statusContainer.classList.remove('flex');
            }
        }
        
        function showToast(message, type) {
            type = type || 'success';
            const existingToast = document.getElementById('growthToast');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.id = 'growthToast';
            toast.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-xl shadow-lg text-white text-sm z-50 transition-all duration-300 flex items-center gap-2 ' + (type === 'success' ? 'bg-green-600' : 'bg-red-600');
            
            const icon = type === 'success' 
                ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
            
            toast.innerHTML = icon + '<span>' + message + '</span>';
            document.body.appendChild(toast);
            
            setTimeout(function() {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(function() { toast.remove(); }, 300);
            }, 3000);
        }
        
        function saveGrowthRecord() {
            const weight = weightInput ? weightInput.value : null;
            const height = heightInput ? heightInput.value : null;
            
            // Don't save if already saving
            if (isSaving) return;
            
            // Don't save if both empty
            if (!weight && !height) return;
            
            // Don't save if nothing changed
            if (weight === initialWeight && height === initialHeight) return;
            
            isSaving = true;
            showStatus('saving');
            
            fetch(window.growthRecordUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ weight: weight, height: height })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                isSaving = false;
                if (data.success) {
                    showStatus('success', 'Saved!');
                    showToast('Growth record saved!', 'success');
                    // Only update initial values after successful save
                    initialWeight = weight;
                    initialHeight = height;
                } else {
                    showStatus('error', data.message || 'Failed');
                    showToast(data.message || 'Failed to save', 'error');
                    // Don't update initial values on error - allow retry
                }
            })
            .catch(function(error) {
                isSaving = false;
                console.error('Error saving growth record:', error);
                showStatus('error', 'Error!');
                showToast('Error saving record', 'error');
                // Don't update initial values on error - allow retry
            });
        }
        
        // Save on blur (when user clicks outside the field)
        if (weightInput) {
            weightInput.addEventListener('blur', function() {
                saveGrowthRecord();
            });
            weightInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    weightInput.blur(); // This will trigger blur event and save
                }
            });
        }
        
        if (heightInput) {
            heightInput.addEventListener('blur', function() {
                saveGrowthRecord();
            });
            heightInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    heightInput.blur(); // This will trigger blur event and save
                }
            });
        }
    })();
    
    // Load QR Code for patient (in header)
    @if(isset($patient) && $patient->id)
    (function() {
        const qrContainer = document.getElementById('patientQrCode');
        
        if (qrContainer) {
            fetch('{{ route("patient.qr.image", $patient->id) }}')
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load QR');
                    return response.text();
                })
                .then(svg => {
                    qrContainer.innerHTML = svg;
                    const svgEl = qrContainer.querySelector('svg');
                    if (svgEl) {
                        svgEl.style.width = '100%';
                        svgEl.style.height = '100%';
                    }
                })
                .catch(err => {
                    console.error('QR load error:', err);
                    qrContainer.innerHTML = '<span class="text-xs text-gray-400">QR</span>';
                });
        }
    })();
    @endif
    </script>
    
</div>
</body>
</html>