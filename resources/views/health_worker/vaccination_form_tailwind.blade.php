<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Record</title>
    
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
        .date-input{ height: 2.75rem; } /* slightly bigger date inputs */
        .remarks-area{ min-height: 3.25rem; } /* slightly bigger remarks */
    /* Recolor the native datepicker calendar icon to purple in Chromium/WebKit */
    .date-input::-webkit-calendar-picker-indicator{ filter: invert(28%) sepia(23%) saturate(2877%) hue-rotate(224deg) brightness(88%) contrast(88%); }
    /* Firefox: use accent-color to hint calendar button color */
    .date-input{ accent-color: #6b46c1; }
    /* Faint inner borders for a crisp grid */
    .table-grid th, .table-grid td { border-bottom: 1px solid rgba(122, 91, 189, 0.16); }
    /* Use :not(:first-of-type) to survive hidden inputs between cells */
    .table-grid td:not(:first-of-type), .table-grid th:not(:first-of-type) { border-left: 1px solid rgba(122, 91, 189, 0.22); }
    /* Center-align the DOSES column (2nd TD of each row) */
    .table-grid tbody tr > td:nth-of-type(2) { text-align: center; }
    </style>
</head>
<body class="bg-gray-50">
<div class="page-wrap">
    <!-- Branded Header with Logos and Title -->
    <header class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 brand-gradient text-white">
        <div class="px-3 sm:px-6 py-4 flex items-center justify-between">
            <!-- Back Button (icon-only on mobile) -->
            <a href="{{ route('health_worker.patients') }}" aria-label="Back" class="inline-flex items-center gap-2 rounded-full bg-white/15 hover:bg-white/25 px-2 sm:px-3 py-2 ring-1 ring-white/25 transition-transform duration-150 ease-out hover:-translate-y-[1px]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
            <!-- Center Title and Logos Row -->
            <div class="flex-1 flex items-center justify-center">
                <div class="w-full max-w-3xl sm:max-w-4xl">
                    <!-- Desktop/Tablet layout -->
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
                    <!-- Mobile layout: title then centered logos row -->
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
            <!-- Invisible back button clone to auto-balance width and keep center aligned -->
            <a aria-hidden="true" tabindex="-1" class="invisible pointer-events-none inline-flex items-center gap-2 rounded-full bg-white/15 px-2 sm:px-3 py-2 ring-1 ring-white/25">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                <span class="hidden sm:inline text-sm font-medium">Back</span>
            </a>
        </div>
    </header>

    @if(session('success'))
        <div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 text-center">
                <p class="text-gray-800 mb-4">{{ session('success') }}</p>
                <button id="okButton" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-primary-700 text-white font-semibold shadow-sm ring-1 ring-primary-700/30 hover:bg-primary-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-700/50">OK!</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div id="errorModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Patient Already Exists</h3>
                <p class="text-gray-700 mb-6">{{ session('error') }}</p>
                <button id="errorOkButton" class="inline-flex items-center justify-center px-6 py-2.5 rounded-md bg-red-600 text-white font-semibold shadow-sm hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600/50">OK</button>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 p-4 text-red-800">
            <div class="font-semibold mb-2">There were some problems with your input:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('auth.saveRecord') }}" method="POST" id="editForm" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 mb-1">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" placeholder="Firstname, M.I, Lastname" required pattern="^[a-zA-Z\s,.'-]+$" title="Name can only contain letters, spaces, commas, periods, apostrophes, and hyphens" class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-semibold text-gray-800 mb-1">Date of Birth <span class="text-red-500">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="place_of_birth" class="block text-sm font-semibold text-gray-800 mb-1">Place of Birth <span class="text-red-500">*</span></label>
                    <input type="text" id="place_of_birth" name="place_of_birth" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="address" class="block text-sm font-semibold text-gray-800 mb-1">Address <span class="text-red-500">*</span></label>
                    <input type="text" id="address" name="address" required maxlength="255" class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="barangay" class="block text-sm font-semibold text-gray-800 mb-1">Barangay <span class="text-red-500">*</span></label>
                    <select id="barangay" name="barangay" required class="block w-full h-11 rounded-md border-2 border-primary-300 bg-white px-3 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                        <option value="" disabled selected>Select Barangay</option>
                        <option value="Balayhangin">Balayhangin</option>
                        <option value="Bangyas">Bangyas</option>
                        <option value="Dayap">Dayap</option>
                        <option value="Hanggan">Hanggan</option>
                        <option value="Imok">Imok</option>
                        <option value="Kanluran">Kanluran</option>
                        <option value="Lamot 1">Lamot 1</option>
                        <option value="Lamot 2">Lamot 2</option>
                        <option value="Limao">Limao</option>
                        <option value="Mabacan">Mabacan</option>
                        <option value="Masiit">Masiit</option>
                        <option value="Paliparan">Paliparan</option>
                        <option value="Perez">Perez</option>
                        <option value="Prinza">Prinza</option>
                        <option value="San Isidro">San Isidro</option>
                        <option value="Santo Tomas">Santo Tomas</option>
                        <option value="Silangan">Silangan</option>
                    </select>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
                <div>
                    <label for="mothers_name" class="block text-sm font-semibold text-gray-800 mb-1">Mother's Name <span class="text-red-500">*</span></label>
                    <input type="text" id="mothers_name" name="mothers_name" placeholder="Firstname, M.I, Lastname" required pattern="^[a-zA-Z\s,.'-]+$" title="Name can only contain letters, spaces, commas, periods, apostrophes, and hyphens" class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div>
                    <label for="fathers_name" class="block text-sm font-semibold text-gray-800 mb-1">Father's Name <span class="text-red-500">*</span></label>
                    <input type="text" id="fathers_name" name="fathers_name" placeholder="Firstname, M.I, Lastname" required pattern="^[a-zA-Z\s,.'-]+$" title="Name can only contain letters, spaces, commas, periods, apostrophes, and hyphens" class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="birth-weight" class="block text-sm font-semibold text-gray-800 mb-1">Birth Weight (kg) <span class="text-red-500">*</span></label>
                        <input type="number" id="birth-weight" name="birth_weight" step="0.01" min="0.5" max="10" placeholder="e.g., 3.5" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                    </div>
                    <div>
                        <label for="birth-height" class="block text-sm font-semibold text-gray-800 mb-1">Birth Height (cm) <span class="text-red-500">*</span></label>
                        <input type="number" id="birth-height" name="birth_height" step="0.1" min="20" max="70" placeholder="e.g., 50" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                    </div>
                </div>
                <div>
                    <label for="sex" class="block text-sm font-semibold text-gray-800 mb-1">Sex <span class="text-red-500">*</span></label>
                    <select id="sex" name="sex" required class="block w-full h-11 rounded-md border-2 border-primary-300 bg-white px-3 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                        <option value="" disabled selected>Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div>
                    <label for="contact_no" class="block text-sm font-semibold text-gray-800 mb-1">Contact No. <span class="text-red-500">*</span></label>
                    <input type="tel" id="contact_no" name="contact_no" placeholder="09xxxxxxxxx" pattern="09\d{9}" maxlength="11" title="Please enter a valid Philippine mobile number starting with 09 (11 digits)" required class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                    <p class="text-xs text-gray-500 mt-1">Format: 09xxxxxxxxx (11 digits)</p>
                </div>
            </div>
        </div>

        <!-- Required fields note -->
        <p class="text-sm text-gray-600 mt-2"><span class="text-red-500">*</span> Required fields</p>

        <!-- Vaccine Table -->
    <div class="w-full overflow-x-auto rounded-2xl shadow-sm ring-1 ring-primary-200 bg-white">
            <table class="min-w-full text-[15px] table-grid">
                <colgroup>
                    <col class="w-[26%]">
                    <col class="w-[16%]">
                    <col class="w-[31%]">
                    <col class="w-[5%]">
                    <col class="w-[22%]">
                </colgroup>
                <thead>
                    <tr>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white first:rounded-tl-2xl">BAKUNA</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white">DOSES</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white" colspan="2">PETSA NG BAKUNA</th>
                        <th class="p-3 md:p-4 text-center font-bold uppercase tracking-wide bg-primary-700 text-white last:rounded-tr-2xl">REMARKS</th>
                    </tr>
                </thead>
                @php
                    $vaccines = [
                        ['name' => 'BCG Vaccine', 'description' => 'Pagkapanganak', 'total_doses' => 1],
                        ['name' => 'Hepatitis B Vaccine', 'description' => 'Pagkapanganak', 'total_doses' => 1],
                        ['name' => 'Pentavalent Vaccine', 'description' => '1 1/2, 2 1/2, 3 1/2 Buwan', 'total_doses' => 3],
                        ['name' => 'Oral Polio Vaccine', 'description' => '1 1/2, 2 1/2, 3 1/2 Buwan', 'total_doses' => 3],
                        ['name' => 'Inactivated Polio Vaccine', 'description' => '3 1/2 & 9 Buwan', 'total_doses' => 2],
                        ['name' => 'Pneumococcal Conjugate Vaccine', 'description' => '1 1/2, 2 1/2, 3 1/2 Buwan', 'total_doses' => 3],
                        ['name' => 'Measles, Mumps, Rubella Vaccine', 'description' => '9 Buwan & 1 Taon', 'total_doses' => 2],
                        ['type' => 'section', 'label' => 'SCHOOL AGED CHILDREN'],
                        ['name' => 'Measles Containing Vaccine', 'description' => 'Grade 1', 'total_doses' => 1],
                        ['name' => 'Measles Containing Vaccine', 'description' => 'Grade 7', 'total_doses' => 2],
                        ['name' => 'Tetanus Diphtheria', 'description' => 'Grade 1 & 7', 'total_doses' => 2],
                        ['name' => 'Human Papillomavirus Vaccine', 'description' => 'Grade 4 - (Babae) 9-14 Taon Gulang', 'total_doses' => 2],
                    ];
                @endphp
                <tbody class="divide-y divide-primary-50">
                    @php $rowIndex = 0; @endphp
                    @foreach ($vaccines as $vaccine)
                        @if (isset($vaccine['type']) && $vaccine['type'] === 'section')
                            <tr>
                                <td colspan="5" class="p-3 md:p-4 font-semibold text-primary-800 bg-primary-50 text-left">{{ $vaccine['label'] }}</td>
                            </tr>
                        @else
                            <tr class="odd:bg-white even:bg-gray-50">
                                <td class="p-3 md:p-4">{{ $vaccine['name'] }}</td>
                                <td class="p-3 md:p-4">{{ $vaccine['description'] }}</td>
                                <input type="hidden" name="vaccines[{{ $rowIndex }}][vaccine_name]" value="{{ $vaccine['name'] }}">
                                <input type="hidden" name="vaccines[{{ $rowIndex }}][doses_description]" value="{{ $vaccine['description'] }}">
                                <td class="p-3 md:p-4 text-center" colspan="2">
                                    @php
                                        $effectiveDoses = min(max(1, (int) $vaccine['total_doses']), 6);
                                        $gridCols = min(3, $effectiveDoses);
                                    @endphp
                                    <div class="grid grid-cols-1 sm:grid-cols-{{ $gridCols }} gap-2">
                                        @for ($dose = 1; $dose <= $effectiveDoses; $dose++)
                                            <input type="date" name="vaccines[{{ $rowIndex }}][dose_{{ $dose }}_date]" class="date-input rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 text-center px-3 text-[15px]">
                                        @endfor
                                    </div>
                                </td>
                                <td class="p-3 md:p-4"><textarea name="vaccines[{{ $rowIndex }}][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                            </tr>
                            @php $rowIndex++; @endphp
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-8">
            <button type="button" id="saveButton" class="inline-flex items-center justify-center gap-2 rounded-xl brand-gradient text-white text-sm font-semibold px-8 py-3 shadow-md ring-1 ring-white/10 hover:opacity-[.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 transition-transform duration-150 ease-out hover:-translate-y-[1px] active:translate-y-0">
                Save
            </button>
        </div>

        <!-- Data Privacy Modal -->
        <div id="privacyModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50">
            <div class="bg-white w-full max-w-2xl mx-4 rounded-xl shadow-xl p-6 max-h-[85vh] overflow-y-auto">
                <h2 class="text-xl font-semibold text-gray-800 text-center mb-4">PAGPAPATUNAY NG PAGSUNOD SA BATAS SA PRIVACY NG DATA</h2>
                <div class="space-y-3 text-gray-700 text-sm leading-6">
                    <p>
                        Bago ipasok ang datos ng pasyente sa RHU Infant Vaccination System, pinatutunayan ko na ipinaalam ko
                        sa magulang o tagapag-alaga ang layunin ng pagkolekta ng kanilang personal na impormasyon at
                        impormasyon ng kanilang anak. Ipinaliwanag ko na ito ay gagamitin para sa pagtatala ng kasaysayan ng
                        pagbabakuna, pag-iskedyul ng mga susunod na bakuna, pagsubaybay at pag-uulat ng pampublikong
                        kalusugan, at pakikipag-ugnayan sa kanila kaugnay ng kalusugan ng kanilang anak.
                    </p>
                    <p>
                        Ipinaliwanag ko rin na ang kanilang datos ay ligtas na itatago sa sistema at magkakaroon lamang ng
                        limitadong access ang mga awtorisadong kawani ng kalusugan. Bukod dito, ipinaalam ko na ang kanilang
                        impormasyon ay maaaring ibahagi sa Department of Health at iba pang kaugnay na ahensya ng kalusugan
                        para sa layunin ng pagsubaybay ng pampublikong kalusugan.
                    </p>
                    <p>
                        Dagdag pa rito, ipinaalam ko sa kanila ang kanilang mga karapatan sa ilalim ng Data Privacy Act of
                        2012, kabilang ang karapatan nilang i-access ang kanilang impormasyon, itama ang anumang hindi
                        tamang datos, at humiling ng pagtanggal ng impormasyon kung naaangkop. Nagbigay rin ako ng
                        impormasyon sa pakikipag-ugnayan para sa RHU Data Protection Officer kung sakaling may mga
                        katanungan o alalahanin sila tungkol sa kanilang datos.
                    </p>
                    <p>
                        Pinatutunayan ko na ang magulang o tagapag-alaga ay nagbigay ng kanilang pasalitang pahintulot upang
                        ipasok ang kanilang impormasyon at impormasyon ng kanilang anak sa RHU Infant Vaccination System.
                        Nauunawaan nila kung paano ito gagamitin at poprotektahan, at nabigyan sila ng pagkakataong
                        magtanong tungkol sa privacy ng kanilang datos.
                    </p>
                </div>
                <div class="mt-4 p-3 rounded-md border border-gray-200 bg-gray-50">
                    <label for="privacyConsent" class="flex items-start gap-3 text-sm font-medium text-gray-800">
                        <input type="checkbox" id="privacyConsent" name="privacy_consent" class="mt-1 h-4 w-4 rounded border-gray-300 text-primary-700 focus:ring-primary-600">
                        <span>Tinitiyak ko na natapos ko ang lahat ng hakbang sa proseso at nakakuha ng tamang pahintulot bago ipasok ang kanilang impormasyon sa sistema.</span>
                    </label>
                </div>
                <div class="mt-5 flex items-center justify-between gap-3">
                    <button type="button" id="cancelButton" class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition-transform duration-150 ease-out hover:-translate-y-[1px]">Kanselahin</button>
                    <button type="button" id="agreeButton" disabled class="inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:pointer-events-none transition-transform duration-150 ease-out enabled:hover:-translate-y-[1px]">Sumasang-ayon ako at Magpatuloy</button>
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-xl brand-gradient text-white px-6 py-5 text-center text-[13px] shadow-sm ring-1 ring-white/10">
            Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon na maaaring makaapekto sa pagbabakuna ng bata.
        </div>
    </form>
</div>
<script src="{{ asset('javascript/vaccination_form.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editForm');
    const saveButton = document.getElementById('saveButton');
    const privacyModal = document.getElementById('privacyModal');
    const agreeButton = document.getElementById('agreeButton');
    const cancelButton = document.getElementById('cancelButton');
    const consentCheckbox = document.getElementById('privacyConsent');

    if (!form || !saveButton || !privacyModal) return;

    saveButton.addEventListener('click', function (e) {
        e.preventDefault();
        if (form.checkValidity()) {
            privacyModal.classList.remove('hidden');
            privacyModal.classList.add('flex');
        } else {
            form.reportValidity();
        }
    });

    consentCheckbox.addEventListener('change', function () {
        agreeButton.disabled = !this.checked;
    });

    agreeButton.addEventListener('click', function () {
        if (!consentCheckbox.checked) return;
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = 'data_privacy_consent';
        hiddenField.value = 'yes';
        form.appendChild(hiddenField);
        form.submit();
    });

    cancelButton.addEventListener('click', function () {
        privacyModal.classList.add('hidden');
        privacyModal.classList.remove('flex');
    });

    window.addEventListener('click', function (event) {
        if (event.target === privacyModal) {
            privacyModal.classList.add('hidden');
            privacyModal.classList.remove('flex');
        }
    });

    // Handle success modal
    const successModal = document.getElementById('successModal');
    const okButton = document.getElementById('okButton');
    if (successModal && okButton) {
        okButton.addEventListener('click', function () {
            successModal.style.display = 'none';
            window.location.href = "{{ route('health_worker.patients') }}";
        });
    }

    // Handle error modal
    const errorModal = document.getElementById('errorModal');
    const errorOkButton = document.getElementById('errorOkButton');
    if (errorModal && errorOkButton) {
        // Show error modal on page load if error exists
        errorModal.style.display = 'flex';
        
        errorOkButton.addEventListener('click', function () {
            errorModal.style.display = 'none';
        });
    }
});
</script>
<br/>
</div>
</body>
</html>
