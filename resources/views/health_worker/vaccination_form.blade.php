<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Record</title>
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
    <style>
    .page-wrap{ width:100%; max-width:1600px; margin:0 auto; padding:1rem; }
        @media (min-width:640px){ .page-wrap{ padding:2rem 3rem; } }
        @media (min-width:1280px){ .page-wrap{ padding:2rem 4rem; } }
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
    <script>
        // Prevent FOUC while Tailwind loads in some browsers
        document.documentElement.classList.add('js');
    </script>
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
                <!-- Name Fields - Horizontal Layout -->
                <div>
                    <label class="block text-sm font-semibold text-gray-800 mb-1">Child's Name <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <!-- First Name (includes second name) -->
                        <div class="flex-[5]">
                            <input type="text" id="first_name" name="first_name" placeholder="First Name" required 
                                pattern="^[a-zA-Z\s'-]+$" 
                                title="First name can only contain letters, spaces, apostrophes, and hyphens"
                                value="{{ old('first_name') }}"
                                class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                        </div>
                        <!-- Middle Initial -->
                        <div class="flex-[1] min-w-[50px] max-w-[60px]">
                            <input type="text" id="middle_initial" name="middle_initial" placeholder="M.I." 
                                maxlength="2"
                                pattern="^[a-zA-Z]\.?$"
                                title="Middle initial should be a single letter (e.g., T or T.)"
                                value="{{ old('middle_initial') }}"
                                class="block w-full rounded-md border-2 border-primary-300 bg-white px-2 py-2.5 text-[15px] text-center focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                        </div>
                        <!-- Last Name -->
                        <div class="flex-[5]">
                            <input type="text" id="last_name" name="last_name" placeholder="Last Name" required 
                                pattern="^[a-zA-Z\s'-]+$" 
                                title="Last name can only contain letters, spaces, apostrophes, and hyphens"
                                value="{{ old('last_name') }}"
                                class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                        </div>
                        <!-- Suffix -->
                        <div class="flex-[1] min-w-[70px] max-w-[80px]">
                            <select id="suffix" name="suffix" 
                                class="block w-full h-[42px] rounded-md border-2 border-primary-300 bg-white px-1 text-[14px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                                <option value="">Suffix</option>
                                <option value="Jr." {{ old('suffix') == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                                <option value="Sr." {{ old('suffix') == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                                <option value="II" {{ old('suffix') == 'II' ? 'selected' : '' }}>II</option>
                                <option value="III" {{ old('suffix') == 'III' ? 'selected' : '' }}>III</option>
                                <option value="IV" {{ old('suffix') == 'IV' ? 'selected' : '' }}>IV</option>
                                <option value="V" {{ old('suffix') == 'V' ? 'selected' : '' }}>V</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="date_of_birth" class="block text-sm font-semibold text-gray-800 mb-1">Date of Birth <span class="text-red-500">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" required value="{{ old('date_of_birth') }}" max="{{ now()->format('Y-m-d') }}" class="block w-full rounded-md border-2 border-primary-300 bg-white px-3 py-2.5 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20">
                    <p id="dobError" class="mt-1 text-xs text-gray-500">Birth date cannot be in the future</p>
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
                    <select id="barangay" name="barangay" required class="block w-full h-11 rounded-md border-2 border-primary-300 bg-white px-3 text-[15px] focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20" @if(isset($healthWorker) && $healthWorker && !$healthWorker->isRHU() && count($accessibleBarangays ?? []) == 1) disabled @endif>
                        @if(isset($healthWorker) && $healthWorker && !$healthWorker->isRHU() && count($accessibleBarangays ?? []) == 1)
                            {{-- Barangay worker - only their assigned barangay, pre-selected --}}
                            <option value="{{ $accessibleBarangays[0] }}" selected>{{ $accessibleBarangays[0] }}</option>
                        @else
                            {{-- RHU admin - show all barangays --}}
                            <option value="" disabled selected>Select Barangay</option>
                            @foreach($accessibleBarangays ?? [] as $barangay)
                                <option value="{{ $barangay }}">{{ $barangay }}</option>
                            @endforeach
                        @endif
                    </select>
                    @if(isset($healthWorker) && $healthWorker && !$healthWorker->isRHU() && count($accessibleBarangays ?? []) == 1)
                        {{-- Hidden input to submit the value when select is disabled --}}
                        <input type="hidden" name="barangay" value="{{ $accessibleBarangays[0] }}">
                    @endif
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
                    $schoolAgedVaccines = ['Measles Containing', 'Tetanus Diphtheria', 'Human Papillomavirus'];
                    $showSchoolAgedHeader = false;
                    $showNewVaccineHeader = false;
                @endphp
                <tbody class="divide-y divide-primary-50">
                    @php $rowIndex = 0; @endphp
                    @foreach ($vaccines as $vaccine)
                        @php
                            $vaccineName = $vaccine->vaccine_name;
                            $dosesDescription = $vaccine->doses_description;
                            $totalDoses = $vaccine->getTotalDosesCount();
                            $isSchoolAged = collect($schoolAgedVaccines)->contains(fn($s) => str_contains($vaccineName, $s));
                            $isNewVaccine = !$vaccine->is_standard;
                            $needsHeader = $isSchoolAged && !$showSchoolAgedHeader;
                            $needsNewVaccineHeader = $isNewVaccine && !$showNewVaccineHeader;
                            if ($needsHeader) $showSchoolAgedHeader = true;
                            if ($needsNewVaccineHeader) $showNewVaccineHeader = true;
                        @endphp
                        @if($needsHeader)
                            <tr>
                                <td colspan="5" class="p-3 md:p-4 font-semibold text-primary-800 bg-primary-50 text-left">SCHOOL AGED CHILDREN</td>
                            </tr>
                        @endif
                        @if($needsNewVaccineHeader)
                            <tr>
                                <td colspan="5" class="p-3 md:p-4 font-semibold text-primary-800 bg-primary-50 text-left">NEW VACCINE</td>
                            </tr>
                        @endif
                        <tr class="odd:bg-white even:bg-gray-50">
                            <td class="p-3 md:p-4">{{ $vaccineName }}</td>
                            <td class="p-3 md:p-4">{{ $dosesDescription }}</td>
                            <input type="hidden" name="vaccines[{{ $rowIndex }}][vaccine_name]" value="{{ $vaccineName }}">
                            <input type="hidden" name="vaccines[{{ $rowIndex }}][doses_description]" value="{{ $dosesDescription }}">
                            <td class="p-3 md:p-4 text-center" colspan="2">
                                @php
                                    $effectiveDoses = min(max(1, (int) $totalDoses), 6);
                                    $doseGridClass = 'grid-cols-1';
                                    if ($effectiveDoses === 2) {
                                        $doseGridClass = 'grid-cols-1 sm:grid-cols-2';
                                    } elseif ($effectiveDoses >= 3) {
                                        $doseGridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
                                    }
                                @endphp
                                <div class="grid {{ $doseGridClass }} gap-3">
                                    @for ($dose = 1; $dose <= $effectiveDoses; $dose++)
                                        <div class="rounded-2xl border border-gray-200 bg-slate-50 p-3 shadow-sm">
                                            <div class="mb-2 text-[11px] uppercase tracking-[0.22em] text-gray-500">Dose {{ $dose }}</div>
                                            <input type="date" name="vaccines[{{ $rowIndex }}][dose_{{ $dose }}_date]" class="w-full rounded-xl border border-gray-200 bg-white text-center px-3 py-2 text-sm text-gray-700 outline-none focus:border-primary-500 focus:ring-primary-200 focus:ring focus:ring-opacity-50">
                                        </div>
                                    @endfor
                                </div>
                            </td>
                            <td class="p-3 md:p-4"><textarea name="vaccines[{{ $rowIndex }}][remarks]" class="remarks-area w-full rounded-md border-2 border-primary-300 focus:border-primary-600 focus:ring-2 focus:ring-primary-600/20 px-3 py-2 text-[15px]"></textarea></td>
                        </tr>
                        @php $rowIndex++; @endphp
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-center mt-8">
            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl brand-gradient text-white text-sm font-semibold px-8 py-3 shadow-md ring-1 ring-white/10 hover:opacity-[.97] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/30 transition-transform duration-150 ease-out hover:-translate-y-[1px] active:translate-y-0">
                Save
            </button>
        </div>

        <div class="mt-8 rounded-xl brand-gradient text-white px-6 py-5 text-center text-[13px] shadow-sm ring-1 ring-white/10">
            Sa column ng Petsa ng bakuna, isulat ang petsa ng pagbibigay ng bakuna ayon sa kung ilang dose ito. Sa column ng remarks, isulat ang petsa ng pagbalik para sa susunod na dose o anumang mahalagang impormasyon na maaaring makaapekto sa pagbabakuna ng bata.
        </div>
    </form>
</div>
<script src="{{ asset('javascript/vaccination_form.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handle success modal
    const successModal = document.getElementById('successModal');
    const okButton = document.getElementById('okButton');
    if (successModal && okButton) {
        okButton.addEventListener('click', function () {
            successModal.style.display = 'none';
            window.location.href = "{{ route('health_worker.patients') }}";
        });
    }

    // Handle error modal - auto-show on page load
    const errorModal = document.getElementById('errorModal');
    const errorOkButton = document.getElementById('errorOkButton');
    if (errorModal && errorOkButton) {
        // Show error modal on page load if error exists
        errorModal.style.display = 'flex';
        
        errorOkButton.addEventListener('click', function () {
            errorModal.style.display = 'none';
        });
    }

    // ===== REAL-TIME VALIDATION =====
    
    // Contact number validation - only allow digits and enforce format
    const contactInput = document.getElementById('contact_no');
    if (contactInput) {
        contactInput.addEventListener('input', function(e) {
            // Remove non-digits
            let value = this.value.replace(/\D/g, '');
            // Limit to 11 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            this.value = value;
            
            // Validate format
            const isValid = /^09\d{9}$/.test(value);
            if (value.length > 0 && !isValid) {
                this.classList.add('border-red-400');
                this.classList.remove('border-primary-300');
            } else {
                this.classList.remove('border-red-400');
                this.classList.add('border-primary-300');
            }
        });
    }

    // Name fields validation - only allow letters, spaces, commas, periods, apostrophes, hyphens
    const nameFields = ['name', 'mothers_name', 'fathers_name'];
    nameFields.forEach(function(fieldId) {
        const input = document.getElementById(fieldId);
        if (input) {
            input.addEventListener('input', function(e) {
                // Remove invalid characters (numbers and special chars except allowed ones)
                const cleaned = this.value.replace(/[^a-zA-Z\s,.'\-]/g, '');
                if (cleaned !== this.value) {
                    this.value = cleaned;
                }
            });
            
            // Auto-capitalize on blur (when leaving the field)
            input.addEventListener('blur', function(e) {
                this.value = formatProperName(this.value);
            });
        }
    });

    // Name fields that need auto-capitalization (child's name fields)
    const childNameFields = ['first_name', 'last_name'];
    childNameFields.forEach(function(fieldId) {
        const input = document.getElementById(fieldId);
        if (input) {
            input.addEventListener('input', function(e) {
                // Remove invalid characters
                const cleaned = this.value.replace(/[^a-zA-Z\s'\-]/g, '');
                if (cleaned !== this.value) {
                    this.value = cleaned;
                }
            });
            
            // Auto-capitalize on blur (when leaving the field)
            input.addEventListener('blur', function(e) {
                this.value = formatProperName(this.value);
            });
        }
    });

    // Middle initial field - auto uppercase
    const middleInitialInput = document.getElementById('middle_initial');
    if (middleInitialInput) {
        middleInitialInput.addEventListener('input', function(e) {
            // Remove invalid characters and auto-uppercase
            let value = this.value.replace(/[^a-zA-Z.]/g, '');
            // Only keep first letter
            if (value.length > 0) {
                value = value.charAt(0).toUpperCase() + (value.includes('.') ? '.' : '');
            }
            this.value = value;
        });
    }

    /**
     * Helper function to format name with proper title case
     * Each word's first letter is capitalized
     */
    function formatProperName(name) {
        if (!name || name.trim() === '') return name;
        
        // Convert to lowercase first
        name = name.toLowerCase().trim();
        
        // Capitalize first letter of each word
        name = name.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
        
        // Handle apostrophes (e.g., O'brien -> O'Brien)
        name = name.replace(/'([a-z])/g, function(match, char) {
            return "'" + char.toUpperCase();
        });
        
        // Handle hyphenated names (e.g., Anne-marie -> Anne-Marie)
        name = name.replace(/-([a-z])/g, function(match, char) {
            return '-' + char.toUpperCase();
        });
        
        return name;
    }

    // Birth weight validation
    const birthWeight = document.getElementById('birth-weight');
    if (birthWeight) {
        birthWeight.addEventListener('change', function() {
            const value = parseFloat(this.value);
            if (value < 0.5 || value > 10) {
                this.classList.add('border-red-400');
                this.classList.remove('border-primary-300');
                if (value < 0.5) this.setCustomValidity('Birth weight must be at least 0.5 kg');
                else this.setCustomValidity('Birth weight cannot exceed 10 kg');
            } else {
                this.classList.remove('border-red-400');
                this.classList.add('border-primary-300');
                this.setCustomValidity('');
            }
        });
    }

    // Birth height validation
    const birthHeight = document.getElementById('birth-height');
    if (birthHeight) {
        birthHeight.addEventListener('change', function() {
            const value = parseFloat(this.value);
            if (value < 20 || value > 70) {
                this.classList.add('border-red-400');
                this.classList.remove('border-primary-300');
                if (value < 20) this.setCustomValidity('Birth height must be at least 20 cm');
                else this.setCustomValidity('Birth height cannot exceed 70 cm');
            } else {
                this.classList.remove('border-red-400');
                this.classList.add('border-primary-300');
                this.setCustomValidity('');
            }
        });
    }

    // Date of birth validation - block future dates
    const dobInput = document.getElementById('date_of_birth');
    const dobError = document.getElementById('dobError');
    if (dobInput) {
        // Set max attribute dynamically using server time
        const serverDate = '{{ now()->format("Y-m-d") }}';
        dobInput.setAttribute('max', serverDate);
        
        dobInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date(serverDate);
            today.setHours(0, 0, 0, 0);
            selectedDate.setHours(0, 0, 0, 0);
            
            if (selectedDate > today) {
                this.classList.add('border-red-400');
                this.classList.remove('border-primary-300');
                this.setCustomValidity('Date of birth cannot be a future date');
                if (dobError) {
                    dobError.textContent = 'Date of birth cannot be a future date';
                    dobError.classList.add('text-red-600');
                    dobError.classList.remove('text-gray-500');
                }
            } else {
                this.classList.remove('border-red-400');
                this.classList.add('border-primary-300');
                this.setCustomValidity('');
                if (dobError) {
                    dobError.textContent = 'Birth date cannot be in the future';
                    dobError.classList.remove('text-red-600');
                    dobError.classList.add('text-gray-500');
                }
            }
        });
    }
});
</script>
<br/>
</div>
</body>
</html>