<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Immunization Card</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
    <style>
        .page-wrap{ width:100%; max-width:1320px; margin:0 auto; padding:1rem; }
        @media (min-width:640px){ .page-wrap{ padding:2rem; } }
        .brand-gradient{ background: linear-gradient(135deg, #7a5bbd 0%, #5a3f99 45%, #402d73 100%); }
        .title-shadow{ text-shadow: 0 1px 0 rgba(255,255,255,.35); }
        .table-grid th, .table-grid td { border-bottom: 1px solid rgba(122, 91, 189, 0.16); }
        .table-grid td:not(:first-of-type), .table-grid th:not(:first-of-type) { border-left: 1px solid rgba(122, 91, 189, 0.22); }
        .table-grid tbody tr > td:nth-of-type(2) { text-align: center; }
        
        /* Screen/Print visibility classes - hide print-only elements on screen */
        @media screen {
            .print-only { display: none !important; }
            .print-only-flex { display: none !important; }
            .print-inline-row { display: none !important; }
        }
        
        /* ========== PRINT STYLES - Compact Immunization Card ========== */
        @media print {
            @page { 
                size: A4 landscape; 
                margin: 1.5cm 2cm; 
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            /* Show print-only, hide screen-only */
            .print-only { display: block !important; visibility: visible !important; }
            .print-only-flex { display: flex !important; visibility: visible !important; }
            .print-inline-row { display: flex !important; visibility: visible !important; }
            div.print-only.print-inline-row { display: flex !important; visibility: visible !important; }
            .screen-only,
            .screen-only *,
            div.screen-only,
            .grid.screen-only,
            .screen-only.grid {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            html, body {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 0;
                font-size: 10pt;
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
                max-width: 85% !important;
                width: 85% !important;
                padding: 0 !important;
                margin: 0 auto !important;
            }
            
            /* Header - Compact purple banner */
            header.brand-gradient,
            header {
                background: linear-gradient(135deg, #5a3f99 0%, #402d73 100%) !important;
                border-radius: 0 !important;
                margin-bottom: 0.4cm !important;
                padding: 0.3cm 0.5cm !important;
                page-break-inside: avoid;
            }
            
            header .px-3,
            header .sm\:px-6 {
                padding: 0.2cm 0.4cm !important;
            }
            
            header h1 {
                font-size: 16pt !important;
                font-weight: bold !important;
                margin: 0 !important;
            }
            
            header p {
                font-size: 8pt !important;
                margin: 0 !important;
            }
            
            header img {
                height: 1.2cm !important;
                width: auto !important;
            }
            
            header a,
            header .invisible {
                display: none !important;
            }
            
            /* Patient Info Section - Compact layout like physical card */
            .grid.grid-cols-1.md\:grid-cols-2 {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0.2cm 1.5cm !important;
                margin-bottom: 0.3cm !important;
                padding: 0 0.5cm !important;
            }
            
            .space-y-4 {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.1cm !important;
            }
            
            .space-y-4 > div {
                margin-bottom: 0 !important;
            }
            
            .space-y-4 label,
            label {
                font-size: 8pt !important;
                font-weight: 600 !important;
                color: #333 !important;
                margin-bottom: 0 !important;
                display: inline !important;
            }
            
            /* Hide barangay field in print */
            .hide-print-barangay {
                display: none !important;
            }
            
            /* Hide select dropdown arrow */
            select {
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                appearance: none !important;
                background-image: none !important;
            }
            
            /* Inline fields for short values - label and value on same line */
            .inline-print-field {
                display: flex !important;
                flex-direction: row !important;
                align-items: baseline !important;
                gap: 0.15cm !important;
                flex-wrap: nowrap !important;
            }
            
            .inline-print-field label {
                flex-shrink: 0 !important;
                white-space: nowrap !important;
            }
            
            .inline-print-field input,
            .inline-print-field select {
                flex: 0 0 auto !important;
                width: auto !important;
                min-width: 1cm !important;
                max-width: 4cm !important;
            }
            
            /* Print inline row: Label __value__ Label __value__ format */
            .print-inline-row {
                display: flex !important;
                flex-direction: row !important;
                align-items: baseline !important;
                gap: 0.3cm !important;
                margin-bottom: 0.15cm !important;
                visibility: visible !important;
                height: auto !important;
                overflow: visible !important;
            }
            
            .print-label {
                font-size: 8pt !important;
                font-weight: 600 !important;
                color: #333 !important;
                flex-shrink: 0 !important;
                display: inline !important;
            }
            
            .print-value {
                font-size: 10pt !important;
                border-bottom: 1px solid #333 !important;
                min-width: 1.5cm !important;
                padding: 0 0.1cm !important;
                display: inline !important;
            }
            
            .print-value-short {
                min-width: 1.5cm !important;
                max-width: 2cm !important;
            }
            
            .print-value-long {
                min-width: 3cm !important;
                flex: 1 !important;
            }
            
            /* Force show print-only content inside space-y-4 */
            .space-y-4 > .print-only,
            .space-y-4 > .print-inline-row {
                display: flex !important;
                visibility: visible !important;
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
                font-size: 10pt !important;
                height: auto !important;
                min-height: 0.5cm !important;
                line-height: 1.3 !important;
                box-shadow: none !important;
                outline: none !important;
            }
            
            /* Make fields inline with labels for compactness */
            .space-y-4 > div {
                display: flex !important;
                flex-wrap: wrap !important;
                align-items: baseline !important;
                gap: 0.15cm !important;
            }
            
            .space-y-4 > div > label {
                flex-shrink: 0 !important;
            }
            
            .space-y-4 > div > input,
            .space-y-4 > div > select {
                flex: 1 !important;
                min-width: 2.5cm !important;
            }
            
            /* Grid fields (Address/Barangay, Sex/Contact) */
            .grid.grid-cols-2 {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 0.3cm !important;
            }
            
            .grid.grid-cols-2 > div {
                display: flex !important;
                flex-wrap: wrap !important;
                align-items: baseline !important;
                gap: 0.1cm !important;
            }
            
            /* Vaccination Table - Compact like physical card */
            .bg-white.rounded-xl,
            .overflow-x-auto {
                border-radius: 0 !important;
                border: 1px solid #333 !important;
                box-shadow: none !important;
                overflow: visible !important;
                margin-bottom: 0.3cm !important;
            }
            
            table.table-grid,
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 9pt !important;
                table-layout: fixed !important;
            }
            
            /* Table column widths - narrower to fit with margins */
            table colgroup col:nth-child(1) { width: 32% !important; } /* Bakuna */
            table colgroup col:nth-child(2) { width: 20% !important; } /* Doses */
            table colgroup col:nth-child(3) { width: 30% !important; } /* Petsa ng Bakuna */
            table colgroup col:nth-child(4) { width: 18% !important; } /* Remarks */
            
            /* Dose columns in rows - for showing 1, 2, 3 numbers with separators */
            .print-dose-cell {
                display: flex !important;
                justify-content: center !important;
            }
            
            .print-dose-cell.single-dose {
                justify-content: center !important;
            }
            
            .print-dose-cell.multi-dose {
                justify-content: space-around !important;
            }
            
            .print-dose-cell span {
                min-width: 2cm !important;
                text-align: center !important;
            }
            
            /* Dose column separators - vertical lines between 1, 2, 3 */
            .dose-with-separator {
                display: flex !important;
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .dose-with-separator > span {
                flex: 1 1 0% !important;
                width: 0 !important;
                text-align: center !important;
                border-right: 1px solid #999 !important;
                padding: 0.1cm 0.05cm !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                word-break: break-word !important;
                font-size: 8pt !important;
            }
            
            .dose-with-separator > span:last-child {
                border-right: none !important;
            }
            
            /* Table cell containing doses - remove padding for full width separator */
            td:nth-child(3) {
                padding: 0 !important;
            }
            
            td:nth-child(3) .dose-with-separator {
                min-height: 0.5cm !important;
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
                font-size: 9pt !important;
                font-weight: 700 !important;
                padding: 0.15cm 0.2cm !important;
                border: 1px solid #333 !important;
                text-transform: uppercase !important;
                text-align: center !important;
            }
            
            /* Table Body */
            tbody td,
            td {
                padding: 0.1cm 0.15cm !important;
                border: 1px solid #666 !important;
                font-size: 9pt !important;
                vertical-align: middle !important;
                text-align: center !important;
            }
            
            /* First column (Bakuna) - left align */
            tbody td:first-child {
                text-align: left !important;
            }
            
            tbody tr:nth-child(odd) {
                background: #fff !important;
            }
            
            tbody tr:nth-child(even) {
                background: #f9f9f9 !important;
            }
            
            /* Date inputs in table - show only actual dates, no placeholders */
            td input[type="date"] {
                width: auto !important;
                font-size: 9pt !important;
                padding: 0 !important;
                text-align: center !important;
                border: none !important;
                background: transparent !important;
            }
            
            /* Hide empty date inputs (show blank instead of mm/dd/yyyy) */
            td input[type="date"]:not([value]),
            td input[type="date"][value=""] {
                color: transparent !important;
            }
            
            td .grid {
                display: flex !important;
                justify-content: center !important;
                gap: 0.3cm !important;
            }
            
            td .grid input {
                flex: 0 0 auto !important;
                min-width: 0 !important;
            }
            
            /* Remarks textarea */
            td textarea {
                width: 100% !important;
                min-height: 0.4cm !important;
                font-size: 8pt !important;
                resize: none !important;
                border: none !important;
                padding: 0.05cm !important;
                background: transparent !important;
            }
            
            /* School-Aged Children separator row */
            .school-aged-header,
            tr.school-aged-header {
                background: #e8d4f0 !important;
            }
            
            .school-aged-header td {
                background: #e8d4f0 !important;
                font-weight: bold !important;
                font-size: 9pt !important;
                text-transform: uppercase !important;
                padding: 0.15cm 0.3cm !important;
                text-align: left !important;
            }
            
            /* Info note at bottom - compact */
            .rounded-xl.bg-gradient-to-r,
            .brand-gradient:not(header) {
                background: #5a3f99 !important;
                color: white !important;
                padding: 0.2cm 0.4cm !important;
                font-size: 7pt !important;
                border-radius: 0 !important;
                margin-top: 0.3cm !important;
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
            <a href="{{ $returnUrl ?? route('health_worker.patients') }}" aria-label="Back" class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 px-2 sm:px-3 py-2 ring-1 ring-white/25 transition-transform duration-150 ease-out hover:-translate-y-[1px] no-print">
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
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Child's Name</label>
                <input type="text" value="{{ $patient->name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Date of Birth</label>
                <input type="date" value="{{ $patient->date_of_birth ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Place of Birth</label>
                <input type="text" value="{{ $patient->place_of_birth ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
            </div>
            {{-- Screen: separate Address and Barangay fields --}}
            <div class="grid grid-cols-2 gap-4 screen-only">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Address</label>
                    <input type="text" value="{{ $patient->address ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Barangay</label>
                    <input type="text" value="{{ $patient->barangay ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
            </div>
            {{-- Print: Address only (no Barangay field) --}}
            <div class="print-only">
                <label class="block text-sm font-semibold text-gray-800 mb-1">Address</label>
                <input type="text" value="{{ $patient->address ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
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
            {{-- Screen: normal grid layout --}}
            <div class="grid grid-cols-2 gap-4 screen-only">
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Birth Weight (kg)</label>
                    <input type="number" value="{{ $patient->birth_weight ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Birth Height (cm)</label>
                    <input type="number" value="{{ $patient->birth_height ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 screen-only">
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
            {{-- Print: inline layout - Birth Weight (kg) ____ Birth Height (cm) _____ --}}
            <div class="print-only print-inline-row">
                <span class="print-label">Birth Weight (kg)</span>
                <span class="print-value">{{ $patient->birth_weight ?? '' }}</span>
                <span class="print-label">Birth Height (cm)</span>
                <span class="print-value">{{ $patient->birth_height ?? '' }}</span>
            </div>
            {{-- Print: inline layout - Sex _______  Contact No. _____________________ --}}
            <div class="print-only print-inline-row">
                <span class="print-label">Sex</span>
                <span class="print-value print-value-short">{{ $patient->sex ?? '' }}</span>
                <span class="print-label">Contact No.</span>
                <span class="print-value print-value-long">{{ $patient->contact_no ?? '' }}</span>
            </div>
        </div>
    </div>

    <!-- Vaccination Table (Read-only) -->
    <div class="bg-white rounded-xl shadow-sm border-2 border-primary-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full table-grid">
                <thead>
                    <tr class="bg-primary-700 text-white">
                        <th class="p-3 md:p-4 text-center text-xs sm:text-sm font-bold uppercase tracking-wide">BAKUNA</th>
                        <th class="p-3 md:p-4 text-center text-xs sm:text-sm font-bold uppercase tracking-wide">DOSES</th>
                        <th class="p-3 md:p-4 text-center text-xs sm:text-sm font-bold uppercase tracking-wide">PETSA NG BAKUNA</th>
                        <th class="p-3 md:p-4 text-center text-xs sm:text-sm font-bold uppercase tracking-wide">REMARKS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @php
                        $showSchoolAgedHeader = false;
                        $showNewVaccineHeader = false;
                        $schoolAgedVaccines = ['Measles Containing', 'Tetanus Diphtheria', 'Human Papillomavirus'];
                    @endphp
                    @foreach(($vaccinations ?? []) as $vaccination)
                        @php
                            $vaccineName = optional($vaccination->vaccine)->vaccine_name;
                            $dosesDescription = optional($vaccination->vaccine)->doses_description;
                            
                            // Check if we need to show SCHOOL-AGED CHILDREN header
                            $isSchoolAged = in_array($vaccineName, $schoolAgedVaccines);
                            $needsHeader = $isSchoolAged && !$showSchoolAgedHeader;
                            if ($needsHeader) $showSchoolAgedHeader = true;

                            // Check if we need to show NEW VACCINE header
                            $isNewVaccine = optional($vaccination->vaccine)->is_standard === false;
                            $needsNewVaccineHeader = $isNewVaccine && !$showNewVaccineHeader;
                            if ($needsNewVaccineHeader) $showNewVaccineHeader = true;
                            
                            // Format vaccine names with "Vaccine" and acronym for print
                            $vaccineDisplayNames = [
                                'BCG' => 'BCG Vaccine',
                                'Hepatitis B' => 'Hepatitis B Vaccine',
                                'Pentavalent' => 'Pentavalent Vaccine (DPT-Hep B-HIB)',
                                'Oral Polio' => 'Oral Polio Vaccine (OPV)',
                                'Inactivated Polio' => 'Inactivated Polio Vaccine (IPV)',
                                'Pneumococcal Conjugate' => 'Pneumococcal Conjugate Vaccine (PCV)',
                                'Measles, Mumps, Rubella' => 'Measles, Mumps, Rubella Vaccine (MMR)',
                                'Measles Containing' => 'Measles Containing Vaccine (MCV) MR/MMR',
                                'Tetanus Diphtheria' => 'Tetanus Diphtheria (TD)',
                                'Human Papillomavirus' => 'Human Papillomavirus Vaccine',
                            ];
                            $displayName = $vaccineDisplayNames[$vaccineName] ?? $vaccineName;
                            
                            // Format dates for display
                            $formatDate = function($date) {
                                if (empty($date)) return '';
                                try {
                                    return \Carbon\Carbon::parse($date)->format('m/d/Y');
                                } catch (\Exception $e) {
                                    return $date;
                                }
                            };
                        @endphp
                        
                        {{-- SCHOOL-AGED CHILDREN separator row --}}
                        @if($needsHeader)
                            <tr class="school-aged-header">
                                <td colspan="4" class="p-2 md:p-3 text-sm font-bold uppercase bg-purple-100 text-purple-800">SCHOOL-AGED CHILDREN</td>
                            </tr>
                        @endif

                        @if($needsNewVaccineHeader)
                            <tr class="school-aged-header">
                                <td colspan="4" class="p-2 md:p-3 text-sm font-bold uppercase bg-purple-100 text-purple-800">NEW VACCINE</td>
                            </tr>
                        @endif
                        
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="p-3 md:p-4 text-sm sm:text-base text-gray-900 font-medium">
                                <span class="screen-only">{{ $vaccineName ?? 'N/A' }}</span>
                                <span class="print-only hidden">{{ $displayName ?? 'N/A' }}</span>
                            </td>
                            <td class="p-3 md:p-4 text-sm sm:text-base text-gray-700 text-center">{{ $dosesDescription ?? 'N/A' }}</td>
                            <td class="p-3 md:p-4 text-center">
                                @php
                                    $totalDoses = optional($vaccination->vaccine)->getTotalDosesCount() ?? 1;
                                    $doseDates = [];
                                    for ($dose = 1; $dose <= $totalDoses; $dose++) {
                                        $doseDates[$dose] = $vaccination->{'dose_'.$dose.'_date'} ?? '';
                                    }
                                    $gridColsClass = 'grid-cols-1 sm:grid-cols-1';
                                    if ($totalDoses === 2) {
                                        $gridColsClass = 'grid-cols-1 sm:grid-cols-2';
                                    } elseif ($totalDoses >= 3) {
                                        $gridColsClass = 'grid-cols-1 sm:grid-cols-3';
                                    }
                                @endphp

                                <div class="screen-only grid {{ $gridColsClass }} gap-3">
                                    @foreach($doseDates as $doseNumber => $doseDate)
                                        <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md">
                                            <div class="mb-2 text-xs font-semibold uppercase tracking-[0.22em] text-gray-500">Dose {{ $doseNumber }}</div>
                                            <input type="date" value="{{ $doseDate }}" readonly class="w-full h-12 rounded-xl border border-gray-200 bg-gray-50 px-3 text-sm text-center text-gray-700 outline-none focus:border-primary-500">
                                        </div>
                                    @endforeach
                                </div>

                                <div class="print-only hidden print-only-flex dose-with-separator" style="flex-wrap: wrap; gap: 0.75rem;">
                                    @foreach($doseDates as $doseNumber => $doseDate)
                                        <span>{{ $formatDate($doseDate) ?: $doseNumber }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-3 md:p-4">
                                <textarea readonly class="screen-only w-full min-h-[3rem] rounded-md border-2 border-gray-300 bg-gray-50 px-3 py-2 text-sm resize-none">{{ $vaccination->remarks ?? '' }}</textarea>
                                <span class="print-only hidden">{{ $vaccination->remarks ?? '' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Note -->
    <div class="mb-6 rounded-xl bg-gradient-to-r from-primary-600 to-primary-800 text-white px-6 py-5 text-center text-sm shadow-sm">
        Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon na maaaring makaapekto sa pagbabakuna ng bata.
    </div>

    <!-- Print Button -->
    <div class="no-print flex justify-center mb-6">
        <button onclick="window.print()" class="inline-flex items-center gap-3 px-8 py-3.5 bg-primary-700 text-white text-base font-bold rounded-lg shadow-md hover:bg-primary-800 hover:shadow-lg active:bg-primary-900 transition-all transform hover:scale-105">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Immunization Card
        </button>
    </div>
</div>

<!-- Load QR Code -->
@if(isset($patient) && $patient->qr_uuid)
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endif
</body>
</html>
