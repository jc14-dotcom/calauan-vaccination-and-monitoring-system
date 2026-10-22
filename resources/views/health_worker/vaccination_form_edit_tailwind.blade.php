<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Immunization Card (View/Print)</title>
    
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
        .date-input{ height: 2.5rem; }
        .remarks-area{ min-height: 3rem; }
        .date-input::-webkit-calendar-picker-indicator{ filter: invert(28%) sepia(23%) saturate(2877%) hue-rotate(224deg) brightness(88%) contrast(88%); }
        .date-input{ accent-color: #6b46c1; }
        .table-grid th, .table-grid td { border-bottom: 1px solid rgba(122, 91, 189, 0.16); }
        .table-grid td:not(:first-of-type), .table-grid th:not(:first-of-type) { border-left: 1px solid rgba(122, 91, 189, 0.22); }
        .table-grid tbody tr > td:nth-of-type(2) { text-align: center; }
        @media print {
            /* Print at half-page size (booklet style) - Portrait paper, card on top half, fold in middle */
            @page { 
                size: portrait; /* Paper is vertical - fold horizontally to make booklet */
                margin: 0; /* No margins - select 'None' in print dialog */
            }
            .no-print { display: none !important; }
            
            /* Scale content to fit in TOP HALF of portrait page */
            body { 
                font-size: 5pt; 
                transform: scale(0.48);
                transform-origin: top left;
                width: 208%; /* Compensate for 48% scale */
            }
            .page-wrap {
                max-width: 100%;
                padding: 0.5rem;
            }
            header.brand-gradient { 
                background: #7a5bbd !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
            th { 
                background: #7a5bbd !important; 
                color: #fff !important; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
            /* Compact spacing for booklet size */
            .space-y-6 > * + * { margin-top: 0.75rem; }
            .space-y-4 > * + * { margin-top: 0.5rem; }
            .gap-6 { gap: 0.75rem; }
            .p-3, .p-4 { padding: 0.25rem; }
            .md\\:p-4 { padding: 0.25rem; }
            .py-2\\.5 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
            .px-3 { padding-left: 0.5rem; padding-right: 0.5rem; }
            .mb-6 { margin-bottom: 0.5rem; }
            .mt-8 { margin-top: 0.5rem; }
            input, select, textarea { font-size: 6pt !important; padding: 2px 4px !important; }
            .text-sm { font-size: 6pt; }
            .text-\\[15px\\] { font-size: 6pt; }
            h1 { font-size: 10pt !important; }
            .text-xl { font-size: 10pt; }
            .text-2xl { font-size: 11pt; }
        }
    </style>
</head>
<body class="bg-gray-50">
<div class="page-wrap">
    <header class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 brand-gradient text-white">
        <div class="px-3 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ route('health_worker.patients') }}" aria-label="Back" class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 px-2 sm:px-3 py-2 ring-1 ring-white/25 transition-transform duration-150 ease-out hover:-translate-y-[1px]">
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
            <a aria-hidden="true" tabindex="-1" class="invisible pointer-events-none inline-flex items-center gap-2 rounded-full bg-white/15 px-2 sm:px-3 py-2 ring-1 ring-white/25">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
        </div>
    </header>

    <form id="editForm" class="space-y-6">
        <!-- QR Code Section (visible on print) -->
        @if(isset($patient) && $patient->qr_uuid)
        <div class="flex justify-end mb-2 print:mb-1">
            <div class="bg-white border border-gray-200 rounded-lg p-2 print:p-1 shadow-sm inline-flex flex-col items-center">
                <div id="patientQrCode" class="w-20 h-20 print:w-14 print:h-14 flex items-center justify-center bg-gray-50 rounded">
                    <!-- QR Code will be loaded here -->
                    <svg class="w-full h-full text-gray-300 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 11h2v2H3zm0-4h2v2H3zm4 4h2v2H7zm0-4h2v2H7zm0-4h2v2H7zm0 12h2v2H7zm4-8h2v2h-2zm0 8h2v2h-2zm4 0h2v2h-2zm4 0h2v2h-2zm0-4h2v2h-2zm0-4h2v2h-2zm0-4h2v2h-2zm-8 4h2v2h-2zm4-8h2v2h-2zm0 4h2v2h-2z"/>
                    </svg>
                </div>
                <span class="text-[8px] text-gray-500 mt-1 print:text-[6px]">Scan for quick access</span>
            </div>
        </div>
        @endif
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 mb-1">Name</label>
                    <input type="text" id="name" name="name" value="{{ $patient->name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-semibold text-gray-800 mb-1">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ $patient->date_of_birth ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px] date-input">
                </div>
                <div>
                    <label for="place_of_birth" class="block text-sm font-semibold text-gray-800 mb-1">Place of Birth</label>
                    <input type="text" id="place_of_birth" name="place_of_birth" value="{{ $patient->place_of_birth ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-800 mb-1">Address</label>
                    <input type="text" id="address" name="address" value="{{ $patient->address ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label for="barangay" class="block text-sm font-semibold text-gray-800 mb-1">Barangay</label>
                    <select id="barangay" name="barangay" disabled class="block w-full h-11 rounded-md border-2 border-primary-300 bg-gray-50 px-3 text-[15px]">
                        <option value="" disabled>Select Barangay</option>
                        @foreach(['Balayhangin','Bangyas','Dayap','Hanggan','Imok','Kanluran','Lamot 1','Lamot 2','Limao','Mabacan','Masiit','Paliparan','Perez','Prinza','San Isidro','Santo Tomas','Silangan'] as $brgy)
                            <option value="{{ $brgy }}" {{ (isset($patient) && ($patient->barangay ?? '') === $brgy) ? 'selected' : '' }}>{{ $brgy }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="mothers_name" class="block text-sm font-semibold text-gray-800 mb-1">Mother's Name</label>
                    <input type="text" id="mothers_name" name="mothers_name" value="{{ $patient->mother_name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div>
                    <label for="fathers_name" class="block text-sm font-semibold text-gray-800 mb-1">Father's Name</label>
                    <input type="text" id="fathers_name" name="fathers_name" value="{{ $patient->father_name ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="birth_weight" class="block text-sm font-semibold text-gray-800 mb-1">Birth Weight (kg)</label>
                        <input type="number" id="birth_weight" name="birth_weight" value="{{ $patient->birth_weight ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                    </div>
                    <div>
                        <label for="birth_height" class="block text-sm font-semibold text-gray-800 mb-1">Birth Height (cm)</label>
                        <input type="number" id="birth_height" name="birth_height" value="{{ $patient->birth_height ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                    </div>
                </div>
                <div>
                    <label for="sex" class="block text-sm font-semibold text-gray-800 mb-1">Sex</label>
                    <select id="sex" name="sex" disabled class="block w-full h-11 rounded-md border-2 border-primary-300 bg-gray-50 px-3 text-[15px]">
                        <option value="" disabled>Select Sex</option>
                        <option value="Male" {{ (isset($patient) && ($patient->sex ?? '') === 'Male') ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ (isset($patient) && ($patient->sex ?? '') === 'Female') ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div>
                    <label for="contact_no" class="block text-sm font-semibold text-gray-800 mb-1">Contact No.</label>
                    <input type="text" id="contact_no" name="contact_no" value="{{ $patient->contact_no ?? '' }}" readonly class="block w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2.5 text-[15px]">
                </div>
            </div>
        </div>

        @php
            $byKey = [];
            foreach(($patient->vaccines ?? []) as $row) {
                $vn = $row->vaccine->vaccine_name ?? '';
                $dd = $row->vaccine->doses_description ?? '';
                $byKey[$vn.'|'.$dd] = $row;
            }
            $get = function($name,$doses) use ($byKey) { $k = $name.'|'.$doses; return $byKey[$k] ?? null; };
        @endphp

        <div class="w-full overflow-x-auto rounded-2xl shadow-sm ring-1 ring-primary-200 bg-white">
            <table class="min-w-full text-[15px] table-grid">
                <colgroup>
                    <col class="w-[26%]"><col class="w-[16%]"><col class="w-[31%]"><col class="w-[5%]"><col class="w-[22%]">
                </colgroup>
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white first:rounded-tl-2xl">BAKUNA</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white">DOSES</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white" colspan="2">PETSA NG BAKUNA</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white last:rounded-tr-2xl">REMARKS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @php $r = $get('BCG','Pagkapanganak'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">BCG Vaccine</td>
                        <td class="p-3 md:p-4">Pagkapanganak</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <input type="date" class="date-input w-full rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Hepatitis B','Pagkapanganak'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Hepatitis B Vaccine</td>
                        <td class="p-3 md:p-4">Pagkapanganak</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <input type="date" class="date-input w-full rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Pentavalent','1 1/2, 2 1/2, 3 1/2 Buwan'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Pentavalent Vaccine</td>
                        <td class="p-3 md:p-4">1 1/2, 2 1/2, 3 1/2 Buwan</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_3_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Oral Polio','1 1/2, 2 1/2, 3 1/2 Buwan'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Oral Polio Vaccine</td>
                        <td class="p-3 md:p-4">1 1/2, 2 1/2, 3 1/2 Buwan</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_3_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Inactivated Polio','3 1/2 & 9 Buwan'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Inactivated Polio Vaccine</td>
                        <td class="p-3 md:p-4">3 1/2 & 9 Buwan</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Pneumococcal Conjugate','1 1/2, 2 1/2, 3 1/2 Buwan'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Pneumococcal Conjugate Vaccine</td>
                        <td class="p-3 md:p-4">1 1/2, 2 1/2, 3 1/2 Buwan</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_3_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Measles, Mumps, Rubella','9 Buwan & 1 Taon'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Measles, Mumps, Rubella Vaccine</td>
                        <td class="p-3 md:p-4">9 Buwan & 1 Taon</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    <tr>
                        <td colspan="5" class="p-3 md:p-4 font-semibold text-primary-800 bg-primary-50 text-left">SCHOOL AGED CHILDREN</td>
                    </tr>

                    @php $r = $get('Measles Containing','Grade 1'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Measles Containing Vaccine</td>
                        <td class="p-3 md:p-4">Grade 1</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <input type="date" class="date-input w-full rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Measles Containing','Grade 7'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Measles Containing Vaccine</td>
                        <td class="p-3 md:p-4">Grade 7</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Tetanus Diphtheria','Grade 1 & 7'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Tetanus Diphtheria</td>
                        <td class="p-3 md:p-4">Grade 1 & 7</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>

                    @php $r = $get('Human Papillomavirus','Grade 4 - (Babae) 9-14 Taon Gulang'); @endphp
                    <tr class="odd:bg-white even:bg-gray-50">
                        <td class="p-3 md:p-4">Human Papillomavirus Vaccine</td>
                        <td class="p-3 md:p-4">Grade 4 - (Babae) 9-14 Taon Gulang</td>
                        <td class="p-3 md:p-4 text-center" colspan="2">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_1_date }}" readonly>
                                <input type="date" class="date-input rounded-md border-2 border-primary-300 bg-gray-50 text-center px-3 text-[15px]" value="{{ optional($r)->dose_2_date }}" readonly>
                            </div>
                        </td>
                        <td class="p-3 md:p-4"><textarea class="remarks-area w-full rounded-md border-2 border-primary-300 bg-gray-50 px-3 py-2 text-[15px]" readonly>{{ optional($r)->remarks }}</textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-8 no-print">
            <button type="button" id="printBtn" class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white shadow-sm ring-1 ring-primary-600/50 focus:outline-none focus:ring-4 focus:ring-primary-300 transition hover:bg-primary-700 active:scale-[.98]">
                <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-120%] group-hover:translate-x-[120%] transition duration-700"></span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
                <span>Print Immunization Card</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('printBtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        let trustedTypesPolicy = null;
        if (window.trustedTypes && trustedTypes.createPolicy) {
            trustedTypesPolicy = trustedTypes.createPolicy('printPolicy', {
                createHTML: (string) => string.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
            });
        }

        const printFrame = document.createElement('iframe');
        printFrame.style.position = 'fixed';
        printFrame.style.left = '-9999px';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        document.body.appendChild(printFrame);

        printFrame.onload = function() {
            const frameDoc = printFrame.contentDocument || printFrame.contentWindow.document;
            const headContent = document.head.innerHTML;
            const printStyle = `
                <style>
                    @page { size: portrait; margin: 0; }
                    body { 
                        font-size: 5pt; 
                        transform: scale(0.48);
                        transform-origin: top left;
                        width: 208%;
                    }
                    header { 
                        background: #7a5bbd !important; 
                        -webkit-print-color-adjust: exact; 
                        print-color-adjust: exact; 
                        padding: 4px !important;
                    }
                    header h1 { font-size: 10pt !important; margin: 0 !important; }
                    header p { font-size: 7pt !important; margin: 0 !important; }
                    th { 
                        background: #7a5bbd !important; 
                        color: white !important; 
                        -webkit-print-color-adjust: exact; 
                        print-color-adjust: exact;
                        font-size: 6pt !important;
                        padding: 2px !important;
                    }
                    table { width: 100%; border-collapse: collapse; }
                    td, th { border: 1px solid #e5e7eb !important; padding: 1px 2px !important; }
                    tr { line-height: 1.1 !important; }
                    .no-print { display:none !important; }
                    input, select, textarea, .date-formatted, .select-formatted {
                        border: 1px solid #7a5bbd !important; 
                        border-radius: 2px; 
                        padding: 0 1px !important; 
                        height: 10px !important; 
                        line-height: 10px; 
                        font-size: 6pt !important; 
                        text-align: center !important; 
                        background: #fff !important; 
                        color: #000 !important;
                    }
                    .remarks-area { height: 10px !important; min-height: 10px !important; resize: none; }
                    #editForm { padding: 0.3cm !important; }
                    .space-y-6 > * + * { margin-top: 0.3rem !important; }
                    .gap-6, .gap-4, .gap-3 { gap: 0.2rem !important; }
                    .p-4, .p-3, .p-2 { padding: 2px !important; }
                    .mb-4, .mb-6, .mt-4, .mt-6, .mt-8 { margin: 2px 0 !important; }
                </style>
            `;

            frameDoc.open();
            const htmlContent = `<!DOCTYPE html><html><head><title>Print Immunization Card</title>${headContent}${printStyle}</head><body>${document.querySelector('header').outerHTML}<div id="mainContent"></div></body></html>`;
            frameDoc.write(trustedTypesPolicy ? trustedTypesPolicy.createHTML(htmlContent) : htmlContent);
            frameDoc.close();

            const cloned = document.getElementById('editForm').cloneNode(true);
            frameDoc.getElementById('mainContent').appendChild(cloned);

            const formatDateForPrint = (val) => { if (!val) return ''; const d = new Date(val); return d.toLocaleDateString('en-PH', { month:'2-digit', day:'2-digit', year:'numeric' }); };

            const dobField = frameDoc.getElementById('date_of_birth');
            if (dobField && dobField.value) {
                const textInput = frameDoc.createElement('input');
                textInput.type = 'text'; textInput.className = 'date-formatted'; textInput.value = formatDateForPrint(dobField.value);
                textInput.id = 'date_of_birth'; textInput.name = 'date_of_birth'; textInput.readOnly = true;
                dobField.parentNode.replaceChild(textInput, dobField);
            }

            const selects = frameDoc.querySelectorAll('select');
            selects.forEach(function(select){
                const selectedText = (select.selectedIndex >= 0) ? select.options[select.selectedIndex].text : '';
                const textInput = frameDoc.createElement('input');
                textInput.type = 'text'; textInput.className = 'select-formatted'; textInput.value = selectedText; textInput.name = select.name; textInput.readOnly = true;
                select.parentNode.replaceChild(textInput, select);
            });

            frameDoc.querySelectorAll('.date-input').forEach(function(input){
                const textInput = frameDoc.createElement('input');
                textInput.type = 'text'; textInput.className = 'date-formatted'; textInput.value = formatDateForPrint(input.value); textInput.name = input.name; textInput.readOnly = true;
                input.parentNode.replaceChild(textInput, input);
            });

            setTimeout(function(){
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
                setTimeout(function(){ document.body.removeChild(printFrame); }, 800);
            }, 300);
        };
        printFrame.src = 'about:blank';
    });

    // Load QR Code for patient
    @if(isset($patient) && $patient->id)
    const qrContainer = document.getElementById('patientQrCode');
    if (qrContainer) {
        fetch('{{ route("patient.qr.image", $patient->id) }}')
            .then(response => {
                if (!response.ok) throw new Error('Failed to load QR');
                return response.text();
            })
            .then(svg => {
                qrContainer.innerHTML = svg;
                // Style the SVG to fit container
                const svgEl = qrContainer.querySelector('svg');
                if (svgEl) {
                    svgEl.style.width = '100%';
                    svgEl.style.height = '100%';
                }
            })
            .catch(err => {
                console.error('QR load error:', err);
                qrContainer.innerHTML = '<span class="text-[8px] text-gray-400">QR unavailable</span>';
            });
    }
    @endif
});
</script>
</body>
</html>
