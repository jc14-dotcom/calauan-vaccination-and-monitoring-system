@extends('layouts.responsive-layout')

@section('title', 'Inventory')

@section('head')
<!-- NUCLEAR OPTION: Prevent ALL caching -->
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, post-check=0, pre-check=0, max-age=0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="-1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
@endsection

@section('additional-styles')
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
<style>
    .hw-container{ width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width:640px){ .hw-container{ padding-left:2rem; padding-right:2rem; } }
    @media (min-width:1280px){ .hw-container{ padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x{ overflow-x:hidden; }
    body.modal-open { overflow:hidden; }
    /* Fade-in animation for header */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0" data-page-id="{{ time() }}-{{ rand() }}">
    <!-- Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex items-center gap-4" data-animate>
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold leading-tight">Inventory</h1>
                <p class="text-sm md:text-base text-white/90 mt-1">Manage vaccines and update available stocks</p>
            </div>
        </div>
    </section>

    <!-- Header actions -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Inventory Management</h2>
        @if(!isset($healthWorker) || $healthWorker->isRHU())
        <button class="inline-flex items-center gap-2.5 rounded-lg bg-primary-700 text-white px-6 py-3.5 text-base font-semibold shadow-md hover:bg-primary-800 hover:shadow-lg active:bg-primary-900 transition-all transform hover:scale-105" onclick="showAddVaccineModal()">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
            Add New Vaccine
        </button>
        @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-blue-800"><strong>View Only</strong> - Inventory managed by RHU</span>
            </div>
        </div>
        @endif
    </div>

    <!-- Table -->
    <div class="overflow-x-auto w-full shadow-md rounded-lg bg-white mb-6">
        <table class="inventory-table w-full border-collapse">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Vaccine Name</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Available Bottles</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Doses Per Bottle</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Available Doses</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inventorySummary as $item)
                <tr class="border-b border-gray-200 hover:bg-primary-50 transition-colors inventory-row"
                    data-vaccine-id="{{ $item['vaccine']->id }}"
                    data-available-doses="{{ $item['available_doses'] }}"
                    data-available-bottles="{{ $item['available_bottles'] }}"
                    data-doses-per-bottle="{{ $item['doses_per_bottle'] }}">
                    <td class="px-6 py-4">
                        <div class="text-base font-bold text-gray-900">{{ $item['vaccine']->vaccine_name }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ $item['vaccine']->doses_description ?? 'No description' }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-700 available-bottles-cell">
                        <div class="text-lg font-semibold">{{ $item['available_bottles'] }} bottles</div>
                    </td>
                    <td class="px-6 py-4 text-gray-700 doses-per-bottle-cell">
                        <div class="text-lg font-semibold">{{ $item['doses_per_bottle'] }}</div>
                    </td>
                    <td class="px-6 py-4 available-doses-cell">
                        <div class="text-xl font-bold text-primary-700">{{ $item['available_doses'] }} doses</div>
                    </td>
                    <td class="px-6 py-4">
                        @if($item['status'] === 'out')
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-red-100 text-red-700 status-chip">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                Out of Stock
                            </span>
                        @elseif($item['status'] === 'low')
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-yellow-100 text-yellow-700 status-chip">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Low Stock
                            </span>
                        @elseif($item['status'] === 'medium')
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-blue-100 text-blue-700 status-chip">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"/>
                                </svg>
                                Medium Stock
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-green-100 text-green-700 status-chip">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                High Stock
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if(!isset($healthWorker) || $healthWorker->isRHU())
                        <div class="flex flex-wrap gap-2">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-primary-700 text-white px-4 py-2.5 text-sm font-bold shadow hover:bg-primary-800 transition-all update-stock-btn" onclick="showUpdateModal({{ $item['vaccine']->id }}, '{{ $item['vaccine']->vaccine_name }}')">Update Stock</button>
                            <a href="{{ route('health_worker.vaccine-settings.edit', ['id' => $item['vaccine']->id, 'return' => 'inventory']) }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 text-gray-700 px-4 py-2.5 text-sm font-bold border border-gray-300 hover:bg-gray-200 transition-all">Edit</a>
                        </div>
                        @else
                        <span class="text-sm text-gray-500">View only</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Update Modal -->
    <div id="updateModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="absolute inset-0 bg-black/50" onclick="closeUpdateModal()"></div>
        <div class="relative mx-auto mt-16 md:mt-20 mb-8 w-[95%] max-w-md rounded-xl bg-white shadow-xl flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 rounded-t-xl bg-gradient-to-r from-primary-600 to-primary-700 text-white">
                <h2 id="modalTitle" class="text-xl font-bold">Update Stock</h2>
                <button class="p-2 rounded-lg hover:bg-white/10 transition-colors" onclick="closeUpdateModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6 overflow-y-auto flex-1">
                <!-- Current Stock Display -->
                <div class="rounded-xl border-2 border-primary-200 bg-gradient-to-br from-primary-50 to-white p-5">
                    <div class="text-xs font-medium text-primary-600 uppercase tracking-wide mb-2">Current Stock</div>
                    <div class="flex items-baseline gap-2">
                        <div id="currentStockDisplay" class="text-4xl font-bold text-primary-700">0</div>
                        <div class="text-lg text-gray-600">doses available</div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="bottleCountInput" class="block text-sm font-medium text-gray-700 mb-2">Total Bottles</label>
                        <input id="bottleCountInput" type="number" min="0" value="0" class="w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-2xl font-bold focus:border-primary-500 focus:ring-2 focus:ring-primary-500" />
                        <p class="mt-1 text-xs text-gray-500">Enter how many vials/bottles are currently in stock.</p>
                    </div>

                    <div>
                        <label for="dosesPerBottleInput" class="block text-sm font-medium text-gray-700 mb-2">Patients Per Bottle</label>
                        <div class="flex gap-2 mb-3">
                            <button type="button" onclick="setDosesPerBottle(5)" class="preset-btn flex-1 px-4 py-2 rounded-lg border-2 border-gray-300 bg-white hover:border-primary-500 hover:bg-primary-50 transition-colors text-sm font-semibold">
                                5 doses
                            </button>
                            <button type="button" onclick="setDosesPerBottle(10)" class="preset-btn flex-1 px-4 py-2 rounded-lg border-2 border-primary-500 bg-primary-50 text-primary-700 text-sm font-semibold">
                                10 doses
                            </button>
                            <button type="button" onclick="setDosesPerBottle(20)" class="preset-btn flex-1 px-4 py-2 rounded-lg border-2 border-gray-300 bg-white hover:border-primary-500 hover:bg-primary-50 transition-colors text-sm font-semibold">
                                20 doses
                            </button>
                        </div>
                        <input id="dosesPerBottleInput" type="number" min="1" value="10" class="w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-2xl font-bold focus:border-primary-500 focus:ring-2 focus:ring-primary-500" />
                        <p class="mt-1 text-xs text-gray-500">Click a preset or enter a custom value</p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="totalDosesInput" class="block text-sm font-medium text-gray-700">Total Available Doses</label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input id="manualOverrideToggle" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" onchange="toggleManualOverride()" />
                                <span class="text-xs font-medium text-gray-600">Manual Override</span>
                            </label>
                        </div>
                        <div class="relative">
                            <input id="totalDosesInput" type="number" min="0" value="0" readonly class="w-full rounded-lg border-2 border-gray-300 px-4 py-3 text-2xl font-bold bg-gray-50 text-gray-700 cursor-not-allowed" />
                            <div id="overrideWarning" class="hidden mt-2 flex items-center gap-2 text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg border border-amber-200">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span>Manual override active - auto-calculation disabled</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Automatically calculated from bottles × patients per bottle</p>
                    </div>
                </div>

                <div class="rounded-xl border-2 border-primary-200 bg-gradient-to-br from-primary-50 to-white p-5">
                    <div class="text-xs font-semibold uppercase text-primary-600 tracking-wide mb-3">Calculation Preview</div>
                    <div class="flex flex-wrap items-center justify-center gap-3 text-lg font-semibold text-gray-700">
                        <div class="flex flex-col items-center">
                            <span id="bottlesPreview" class="text-3xl font-bold text-primary-700">0</span>
                            <span class="text-xs text-gray-500">bottles</span>
                        </div>
                        <span class="text-2xl text-gray-400">×</span>
                        <div class="flex flex-col items-center">
                            <span id="perBottleDisplay" class="text-3xl font-bold text-primary-700">0</span>
                            <span class="text-xs text-gray-500">per bottle</span>
                        </div>
                        <span class="text-2xl text-gray-400">=</span>
                        <div class="flex flex-col items-center px-4 py-2 rounded-lg bg-primary-100">
                            <span id="calculatedTotalDisplay" class="text-4xl font-bold text-primary-700">0</span>
                            <span class="text-xs font-medium text-primary-600">total doses</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 flex gap-3 rounded-b-xl border-t border-gray-100">
                <button class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-white transition-colors" onclick="closeUpdateModal()">
                    Cancel
                </button>
                <button id="saveStockBtn" class="flex-1 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold transition-colors shadow-sm" onclick="saveStock()">
                    Update Stock
                </button>
            </div>
        </div>
    </div>

    <!-- Add Vaccine Modal -->
    <div id="addVaccineModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="absolute inset-0 bg-black/50" onclick="closeAddVaccineModal()"></div>
        <div class="relative mx-auto mt-16 md:mt-20 mb-8 w-[95%] max-w-lg rounded-xl bg-white shadow-xl flex flex-col max-h-[90vh]">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 rounded-t-xl bg-gradient-to-r from-primary-600 to-primary-700 text-white">
                <h2 class="text-xl font-bold">Add New Vaccine</h2>
                <button class="p-2 rounded-lg hover:bg-white/10 transition-colors" onclick="closeAddVaccineModal()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 space-y-6 overflow-y-auto flex-1">
                <!-- Vaccine Name -->
                <div>
                    <label for="vaccineNameInput" class="block mb-2 text-base font-bold text-gray-800">
                        Vaccine Name <span class="text-red-500">*</span>
                    </label>
                    <input id="vaccineNameInput" type="text" placeholder="e.g., COVID-19, Influenza, HPV" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg text-base focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all" />
                </div>
                
                <!-- Description -->
                <div>
                    <label for="vaccineDescriptionInput" class="block mb-2 text-base font-bold text-gray-800">
                        Description/Notes
                    </label>
                    <textarea id="vaccineDescriptionInput" rows="3" placeholder="Brief description or administration notes" class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg text-base focus:border-primary-500 focus:ring-2 focus:ring-primary-200 resize-y transition-all"></textarea>
                </div>

                <!-- Vaccine Configuration -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Vaccine Configuration</h3>

                    <!-- Acronym -->
                    <div class="mb-5">
                        <label for="vaccineAcronymInput" class="block mb-2 text-sm font-semibold text-gray-700">Acronym / Short Name</label>
                        <input id="vaccineAcronymInput" type="text" maxlength="50" placeholder="e.g., BCG, OPV, MMR"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all" />
                        <p class="mt-1 text-xs text-gray-500">Optional short code used in reports</p>
                    </div>

                    <!-- Total Doses in Schedule -->
                    <div class="mb-5">
                        <label for="addTotalDosesScheduleInput" class="block mb-2 text-sm font-semibold text-gray-700">
                            Number of Doses in Schedule <span class="text-red-500">*</span>
                        </label>
                        <input id="addTotalDosesScheduleInput" type="number" min="1" max="10" value="1"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all"
                               oninput="updateAddDoseWindows()" />
                        <p class="mt-1 text-xs text-gray-500">How many doses a patient receives in their schedule (1–10)</p>
                    </div>

                    <!-- Target Age Group -->
                    <div class="mb-5">
                        <label for="addTargetAgeGroupInput" class="block mb-2 text-sm font-semibold text-gray-700">
                            Target Age Group <span class="text-red-500">*</span>
                        </label>
                        <select id="addTargetAgeGroupInput" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg text-base focus:border-primary-500 focus:ring-2 focus:ring-primary-200 transition-all">
                            <option value="under_1_year">Under 1 Year (Infants)</option>
                            <option value="0_12_months">0–12 Months</option>
                            <option value="grade_1">Grade 1 (6–7 years old)</option>
                            <option value="grade_7">Grade 7 (12–13 years old)</option>
                        </select>
                    </div>

                    <!-- Dose Age Windows -->
                    <div class="mb-5">
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Dose Age Windows (months)</label>
                        <div id="addDoseWindowsContainer" class="space-y-3">
                            <!-- Rendered by JS based on total doses -->
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Eligible age range (in months) for each dose. Set 0–0 to configure later via Vaccine Settings.</p>
                    </div>

                    <!-- Flags -->
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input id="addIsStandardInput" type="checkbox" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Standard Vaccine</span>
                                <p class="text-xs text-gray-500">Automatically added to all new patients</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input id="addIsActiveReportsInput" type="checkbox" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Active in Reports</span>
                                <p class="text-xs text-gray-500">Include this vaccine in monthly/quarterly reports</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Initial Stock Setup</h3>
                    
                    <!-- Patients Per Bottle -->
                    <div class="mb-5">
                        <label for="addDosesPerBottleInput" class="block text-base font-semibold text-gray-700 mb-2">
                            Patients Per Bottle
                        </label>
                        <div class="flex gap-2 mb-3">
                            <button type="button" onclick="setAddDosesPerBottle(5)" class="add-preset-btn flex-1 px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-white hover:border-primary-500 hover:bg-primary-50 transition-colors text-sm font-semibold">
                                5 doses
                            </button>
                            <button type="button" onclick="setAddDosesPerBottle(10)" class="add-preset-btn flex-1 px-4 py-2.5 rounded-lg border-2 border-primary-500 bg-primary-50 text-primary-700 text-sm font-semibold">
                                10 doses
                            </button>
                            <button type="button" onclick="setAddDosesPerBottle(20)" class="add-preset-btn flex-1 px-4 py-2.5 rounded-lg border-2 border-gray-300 bg-white hover:border-primary-500 hover:bg-primary-50 transition-colors text-sm font-semibold">
                                20 doses
                            </button>
                        </div>
                        <input id="addDosesPerBottleInput" type="number" min="1" value="10" class="w-full rounded-lg border-2 border-gray-300 px-4 py-3.5 text-lg font-bold focus:border-primary-500 focus:ring-2 focus:ring-primary-200" />
                        <p class="mt-2 text-sm text-gray-500">Click a preset or enter a custom value</p>
                    </div>

                    <!-- Initial Bottles -->
                    <div class="mb-5">
                        <label for="initialBottlesInput" class="block mb-2 text-base font-semibold text-gray-700">
                            Initial Bottles <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center justify-center gap-4">
                            <button type="button" onclick="changeInitialBottles(-1)" class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-700 text-white shadow-md hover:bg-primary-800 transition-all text-xl font-bold">−</button>
                            <input id="initialBottlesInput" type="number" value="0" min="0" class="h-14 w-32 text-center text-2xl font-bold border-2 border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200" />
                            <button type="button" onclick="changeInitialBottles(1)" class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-700 text-white shadow-md hover:bg-primary-800 transition-all text-xl font-bold">+</button>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 text-center">Number of bottles/vials in stock</p>
                    </div>

                    <!-- Calculation Preview -->
                    <div class="rounded-xl border-2 border-primary-200 bg-gradient-to-br from-primary-50 to-white p-5">
                        <div class="text-xs font-semibold uppercase text-primary-600 tracking-wide mb-3">Total Stock Preview</div>
                        <div class="flex flex-wrap items-center justify-center gap-3 text-lg font-semibold text-gray-700">
                            <div class="flex flex-col items-center">
                                <span id="addBottlesPreview" class="text-3xl font-bold text-primary-700">0</span>
                                <span class="text-xs text-gray-500">bottles</span>
                            </div>
                            <span class="text-2xl text-gray-400">×</span>
                            <div class="flex flex-col items-center">
                                <span id="addPerBottlePreview" class="text-3xl font-bold text-primary-700">10</span>
                                <span class="text-xs text-gray-500">per bottle</span>
                            </div>
                            <span class="text-2xl text-gray-400">=</span>
                            <div class="flex flex-col items-center px-4 py-2 rounded-lg bg-primary-100">
                                <span id="addTotalPreview" class="text-4xl font-bold text-primary-700">0</span>
                                <span class="text-xs font-medium text-primary-600">total doses</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 flex gap-3 rounded-b-xl border-t border-gray-100">
                <button class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg text-gray-700 text-base font-semibold hover:bg-white hover:border-gray-400 transition-all" onclick="closeAddVaccineModal()">
                    Cancel
                </button>
                <button id="addVaccineBtn" class="save-btn flex-1 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-base font-bold transition-all shadow-md hover:shadow-lg" onclick="saveNewVaccine()">
                    Add Vaccine
                </button>
            </div>
        </div>
    </div>

    <!-- Toasts -->
    <div id="toastContainer" class="fixed right-4 top-24 z-[10000] space-y-3"></div>
</div>
@endsection

@section('additional-scripts')
<script src="{{ asset('javascript/inventory-manager.js') }}?v={{ time() }}"></script>
@endsection
