@extends('layouts.responsive-layout')

@section('title', 'Edit Vaccine: ' . $vaccine->vaccine_name)

@section('additional-styles')
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
<style>
    .hw-container{ width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width:640px){ .hw-container{ padding-left:2rem; padding-right:2rem; } }
    @media (min-width:1280px){ .hw-container{ padding-left:2.5rem; padding-right:2.5rem; } }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
@endsection

@section('content')
<div class="hw-container flex flex-col pb-8 min-w-0">

    {{-- ── Back breadcrumb (above banner) ── --}}
    <div class="mb-3">
        @if(($returnTo ?? 'settings') === 'inventory')
            <a href="{{ route('health_worker.inventory') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Vaccine Inventory
            </a>
        @else
            <a href="{{ route('health_worker.vaccine-settings.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Vaccine Settings
            </a>
        @endif
    </div>

    {{-- ── Banner ── --}}
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex items-center gap-4" data-animate>
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25 flex-shrink-0">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl md:text-3xl font-bold leading-tight">Edit Vaccine</h1>
                <p class="text-sm md:text-base text-white/90 mt-0.5">Editing configuration for <strong>{{ $vaccine->vaccine_name }}</strong>. Saving will immediately clear the report cache.</p>
            </div>
        </div>
    </section>

    {{-- ── Validation errors ── --}}
    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-5">
        <p class="text-base font-bold text-red-800 mb-2">Please fix the following errors:</p>
        <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="max-w-4xl">
    <form id="vaccineSettingsForm" method="POST" action="{{ route('health_worker.vaccine-settings.update', $vaccine->id) }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_to" value="{{ $returnTo ?? 'settings' }}">

        {{-- ── Basic Info ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Basic Information</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Vaccine Name --}}
                <div class="sm:col-span-2">
                    <label for="vaccine_name" class="block text-base font-semibold text-gray-700 mb-2">
                        Vaccine Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="vaccine_name" name="vaccine_name"
                           value="{{ old('vaccine_name', $vaccine->vaccine_name) }}"
                           class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base
                                  focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                  @error('vaccine_name') border-red-400 bg-red-50 @enderror"
                           required>
                    @error('vaccine_name')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-400">
                        Changing the name also updates how it appears in existing reports.
                        Avoid changing the name of standard vaccines.
                    </p>
                </div>

                {{-- Acronym --}}
                <div>
                    <label for="acronym" class="block text-base font-semibold text-gray-700 mb-2">
                        Acronym / Short Name
                    </label>
                    <input type="text" id="acronym" name="acronym"
                           value="{{ old('acronym', $vaccine->acronym) }}"
                           maxlength="50"
                           placeholder="e.g. BCG, OPV, HPV"
                           class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base
                                  focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                  @error('acronym') border-red-400 bg-red-50 @enderror">
                    @error('acronym')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-400">Used as the column header in reports.</p>
                </div>

                {{-- Sort Order --}}
                <div>
                    <label for="sort_order" class="block text-base font-semibold text-gray-700 mb-2">
                        Sort Order <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="sort_order" name="sort_order"
                           value="{{ old('sort_order', $vaccine->sort_order ?? 99) }}"
                           min="0" max="9999"
                           class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base
                                  focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                  @error('sort_order') border-red-400 bg-red-50 @enderror"
                           required>
                    @error('sort_order')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-400">Lower number = appears first in patient cards and reports.</p>
                </div>

            </div>
        </div>

        {{-- ── Schedule Config ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Schedule Configuration</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                {{-- Total Doses --}}
                <div>
                    <label for="total_doses" class="block text-base font-semibold text-gray-700 mb-2">
                        Total Doses <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="total_doses" name="total_doses"
                           value="{{ old('total_doses', $vaccine->total_doses ?? 1) }}"
                           min="1" max="10"
                           class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base
                                  focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                  @error('total_doses') border-red-400 bg-red-50 @enderror"
                           required>
                    @error('total_doses')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-400">
                        Changing this adds or removes dose age-window rows below automatically.
                        You can edit the new dose windows immediately.
                    </p>
                </div>

                {{-- Target Age Group --}}
                <div>
                    <label for="target_age_group" class="block text-base font-semibold text-gray-700 mb-2">
                        Target Age Group <span class="text-red-500">*</span>
                    </label>
                    <select id="target_age_group" name="target_age_group"
                            class="w-full rounded-xl border-2 border-gray-300 px-4 py-3 text-base
                                   focus:ring-2 focus:ring-primary-500 focus:border-primary-500
                                   @error('target_age_group') border-red-400 bg-red-50 @enderror"
                            required>
                        @foreach($ageGroupOptions as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('target_age_group', $vaccine->target_age_group) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('target_age_group')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-400">Controls which patient age group this vaccine counts toward in reports.</p>
                </div>

            </div>
        </div>

        {{-- ── Dose Age Windows ── --}}
        @php
            $currentTotalDoses = old('total_doses', $vaccine->total_doses ?? 1);
            $doseConfigs = $vaccine->doseConfigs->keyBy('dose_number');
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-800 mb-1">Recommended Age Windows</h2>
            <p class="text-xs text-gray-400 mb-5">
                These ranges (in months) define when each dose is considered "on-time" for a patient.
                Example: Dose 1 of Pentavalent is given at 6 weeks = 1.5 months.
            </p>

            <div id="doseAgeWindowsContainer" class="space-y-4">
                @for($dose = 1; $dose <= $currentTotalDoses; $dose++)
                    @php
                        $config = $doseConfigs->get($dose);
                        $min = old("dose_min.$dose", $config?->min_age_months ?? 0);
                        $max = old("dose_max.$dose", $config?->max_age_months ?? 0);
                    @endphp
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-bold text-sm">
                                {{ $dose }}
                            </span>
                        </div>
                        <div class="flex-1 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Min Age (months)
                                </label>
                                <input type="number" name="dose_min[{{ $dose }}]"
                                       value="{{ $min }}"
                                       step="0.01" min="0" max="999"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                              focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Max Age (months)
                                </label>
                                <input type="number" name="dose_max[{{ $dose }}]"
                                       value="{{ $max }}"
                                       step="0.01" min="0" max="999"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                              focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <p class="mt-3 text-xs text-gray-400">
                Tip: 1 month ≈ 4.33 weeks. Birth = 0. 6 weeks = 1.5 months. 14 weeks = 3.5 months.
            </p>
        </div>

        {{-- ── Visibility Toggles ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100">Visibility</h2>

            {{-- is_standard --}}
            <div class="flex items-start gap-4 mb-6 p-4 rounded-xl bg-gray-50 border border-gray-200">
                <div class="flex-shrink-0 mt-0.5">
                    <input type="hidden" name="is_standard" value="0">
                    <input type="checkbox" id="is_standard" name="is_standard" value="1"
                           {{ old('is_standard', $vaccine->is_standard) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-300 text-primary-600
                                  focus:ring-primary-500 cursor-pointer">
                </div>
                <div>
                    <label for="is_standard" class="block text-base font-bold text-gray-800 cursor-pointer">
                        Standard Vaccine
                    </label>
                    <p class="text-sm text-gray-500 mt-1">
                        When checked, this vaccine is automatically added to every
                        <strong>new patient's</strong> vaccination card at registration.
                    </p>
                    @if($vaccine->is_standard)
                        <p class="mt-2 text-sm text-amber-600 font-medium">
                            ⚠ Currently standard. Unchecking will not remove existing patient records —
                            it only affects future registrations.
                        </p>
                    @endif
                </div>
            </div>

            {{-- is_active_in_reports --}}
            <div class="flex items-start gap-4 p-4 rounded-xl bg-gray-50 border border-gray-200">
                <div class="flex-shrink-0 mt-0.5">
                    <input type="hidden" name="is_active_in_reports" value="0">
                    <input type="checkbox" id="is_active_in_reports" name="is_active_in_reports" value="1"
                           {{ old('is_active_in_reports', $vaccine->is_active_in_reports) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-300 text-primary-600
                                  focus:ring-primary-500 cursor-pointer">
                </div>
                <div>
                    <label for="is_active_in_reports" class="block text-base font-bold text-gray-800 cursor-pointer">
                        Active in Reports
                    </label>
                    <p class="text-sm text-gray-500 mt-1">
                        When checked, this vaccine appears as a column in the live report
                        and new report snapshots. Existing locked snapshots are unaffected.
                    </p>
                </div>
            </div>
        </div>

        {{-- ── Submit ── --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <button id="saveButton" type="submit" disabled
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl
                           bg-gray-300 text-gray-700 font-bold text-base opacity-60 cursor-not-allowed pointer-events-none transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
            <a href="{{ route('health_worker.vaccine-settings.index') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl
                      bg-gray-100 text-gray-700 font-semibold text-base hover:bg-gray-200 border border-gray-300 transition-colors">
                Cancel
            </a>
        </div>

    </form>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('vaccineSettingsForm');
        const totalDosesInput = document.getElementById('total_doses');
        const doseWindowsContainer = document.getElementById('doseAgeWindowsContainer');
        const saveButton = document.getElementById('saveButton');
        const initialButtonClass = saveButton.className;
        const activeButtonClass = 'flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-primary-600 text-white font-bold text-base hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all shadow-md hover:shadow-lg transform hover:scale-105';
        const disabledButtonClass = 'flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-gray-300 text-gray-700 font-bold text-base opacity-60 cursor-not-allowed pointer-events-none transition-all';

        const doseDefaults = @json($doseConfigs->mapWithKeys(function ($config) {
            return [$config->dose_number => [
                'min' => $config->min_age_months ?? 0,
                'max' => $config->max_age_months ?? 0,
            ]];
        })->all());

        const initialFormState = getFormState();

        function getFormState() {
            const state = {};
            const fields = form.querySelectorAll('input[name], select[name], textarea[name]');
            fields.forEach(field => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    state[field.name] = field.checked;
                } else {
                    state[field.name] = field.value;
                }
            });
            return state;
        }

        function isFormDirty() {
            const currentState = getFormState();
            const keys = Object.keys(currentState);
            return keys.some(name => currentState[name] !== initialFormState[name]);
        }

        function updateSaveButtonState() {
            if (isFormDirty()) {
                saveButton.disabled = false;
                saveButton.className = activeButtonClass;
            } else {
                saveButton.disabled = true;
                saveButton.className = disabledButtonClass;
            }
        }

        function collectCurrentDoseValues() {
            const values = {};
            const minInputs = doseWindowsContainer.querySelectorAll('input[name^="dose_min["]');
            const maxInputs = doseWindowsContainer.querySelectorAll('input[name^="dose_max["]');

            minInputs.forEach(input => {
                const match = input.name.match(/dose_min\[(\d+)\]/);
                if (!match) return;
                values[match[1]] = values[match[1]] || {};
                values[match[1]].min = input.value;
            });
            maxInputs.forEach(input => {
                const match = input.name.match(/dose_max\[(\d+)\]/);
                if (!match) return;
                values[match[1]] = values[match[1]] || {};
                values[match[1]].max = input.value;
            });
            return values;
        }

        function renderDoseWindows(totalDoses) {
            const currentValues = collectCurrentDoseValues();
            const doses = Math.max(1, Math.min(10, totalDoses));
            let html = '';

            for (let dose = 1; dose <= doses; dose += 1) {
                const existing = currentValues[dose] || {};
                const defaultConfig = doseDefaults[dose] || { min: 0, max: 0 };
                const minValue = existing.min !== undefined && existing.min !== '' ? existing.min : defaultConfig.min;
                const maxValue = existing.max !== undefined && existing.max !== '' ? existing.max : defaultConfig.max;

                html += `
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-bold text-sm">
                                ${dose}
                            </span>
                        </div>
                        <div class="flex-1 grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Min Age (months)</label>
                                <input type="number" name="dose_min[${dose}]" value="${minValue}"
                                       step="0.01" min="0" max="999"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Max Age (months)</label>
                                <input type="number" name="dose_max[${dose}]" value="${maxValue}"
                                       step="0.01" min="0" max="999"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            </div>
                        </div>
                    </div>
                `;
            }

            doseWindowsContainer.innerHTML = html;
            attachFieldListeners();
        }

        function attachFieldListeners() {
            const fields = form.querySelectorAll('input[name], select[name], textarea[name]');
            fields.forEach(field => {
                if (field.dataset.hasChangeListener) return;
                field.addEventListener('input', updateSaveButtonState);
                field.addEventListener('change', updateSaveButtonState);
                field.dataset.hasChangeListener = 'true';
            });
        }

        totalDosesInput.addEventListener('input', function () {
            const value = parseInt(totalDosesInput.value, 10);
            if (Number.isNaN(value)) return;
            renderDoseWindows(value);
            updateSaveButtonState();
        });

        attachFieldListeners();
        updateSaveButtonState();
    });
</script>
@endsection
