@extends('layouts.responsive-layout')

@section('title', 'Vaccine Settings')

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

    {{-- ── Banner ── --}}
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex items-center gap-4" data-animate>
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25 flex-shrink-0">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold leading-tight">Vaccine Settings</h1>
                <p class="text-sm md:text-base text-white/90 mt-1">Configure which vaccines appear in patient records and reports. Changes take effect immediately.</p>
            </div>
        </div>
    </section>

    {{-- ── Flash messages ── --}}
    @if(session('success'))
    <div class="mb-6 flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-base font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl p-4">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span class="text-base font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- ── Legend ── --}}
    <div class="mb-5 flex flex-wrap gap-5 text-sm text-gray-600">
        <span class="flex items-center gap-2">
            <span class="inline-block w-3.5 h-3.5 rounded-full bg-green-500"></span>
            Active in reports
        </span>
        <span class="flex items-center gap-2">
            <span class="inline-block w-3.5 h-3.5 rounded-full bg-blue-500"></span>
            Standard (auto-added to new patients)
        </span>
        <span class="flex items-center gap-2">
            <span class="inline-block w-3.5 h-3.5 rounded-full bg-gray-300"></span>
            Disabled
        </span>
    </div>

    {{-- ── Vaccine Table ── --}}
    <div class="overflow-x-auto w-full shadow-md rounded-lg bg-white mb-6">
        <table class="w-full border-collapse">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide w-14">#</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Vaccine Name</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide hidden sm:table-cell">Acronym</th>
                    <th class="px-6 py-4 text-center text-sm font-bold bg-primary-700 text-white uppercase tracking-wide hidden md:table-cell">Doses</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide hidden lg:table-cell">Age Group</th>
                    <th class="px-6 py-4 text-center text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Standard</th>
                    <th class="px-6 py-4 text-center text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">In Reports</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vaccines as $vaccine)
                <tr class="border-b border-gray-200 hover:bg-primary-50 transition-colors">

                    <td class="px-6 py-4 text-gray-500 font-mono text-sm">{{ $vaccine->sort_order }}</td>

                    <td class="px-6 py-4">
                        <div class="text-base font-bold text-gray-900">{{ $vaccine->vaccine_name }}</div>
                    </td>

                    <td class="px-6 py-4 hidden sm:table-cell">
                        @if($vaccine->acronym)
                            <span class="inline-block px-2.5 py-1 rounded-md bg-primary-50 text-primary-700 font-mono text-sm font-semibold">
                                {{ $vaccine->acronym }}
                            </span>
                        @else
                            <span class="text-gray-400 text-sm italic">—</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-center hidden md:table-cell">
                        <span class="text-gray-700 font-semibold text-base">{{ $vaccine->total_doses ?? 1 }}</span>
                    </td>

                    <td class="px-6 py-4 hidden lg:table-cell">
                        @php
                            $ageLabels = [
                                'under_1_year' => 'Under 1 Year',
                                '0_12_months'  => '0–12 Months',
                                'grade_1'      => 'Grade 1',
                                'grade_7'      => 'Grade 7',
                            ];
                        @endphp
                        <span class="text-gray-700 text-sm">{{ $ageLabels[$vaccine->target_age_group] ?? $vaccine->target_age_group }}</span>
                    </td>

                    <td class="px-6 py-4 text-center">
                        @if($vaccine->is_standard)
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-center">
                        @if($vaccine->is_active_in_reports)
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                        <a href="{{ route('health_worker.vaccine-settings.edit', $vaccine->id) }}"
                           class="inline-flex items-center gap-2 rounded-lg bg-primary-700 text-white px-5 py-2.5 text-sm font-bold shadow hover:bg-primary-800 hover:shadow-lg transition-all transform hover:scale-105">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        <button type="button"
                                onclick="openDeleteModal({{ $vaccine->id }}, '{{ addslashes($vaccine->vaccine_name) }}')"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 text-red-700 px-3 py-2.5 text-sm font-bold border border-red-200 hover:bg-red-100 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                        <svg class="w-14 h-14 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        <p class="text-base">No vaccines found. Add vaccines in Inventory first.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Info box ── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 text-blue-800">
        <p class="font-bold text-base mb-2">How these settings work</p>
        <ul class="list-disc list-inside space-y-1.5 text-sm text-blue-700">
            <li><strong>Standard</strong> — when enabled, this vaccine is automatically added to every new patient's vaccination card at registration.</li>
            <li><strong>Active in Reports</strong> — when enabled, this vaccine appears as a column in the live and historical vaccination reports.</li>
            <li>Changes to age windows and dose count take effect on the next page load — no cache flush needed.</li>
            <li>New vaccines added via Inventory default to <em>Standard: No</em> and <em>Active in Reports: No</em> until explicitly enabled here.</li>
        </ul>
    </div>

</div>

{{-- ── Delete Confirmation Modal ── --}}
<div id="deleteVaccineModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 hidden">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
        {{-- Modal header --}}
        <div class="px-6 py-5 bg-red-600 text-white">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </span>
                <h3 class="text-lg font-bold">Delete Vaccine</h3>
            </div>
        </div>

        <div class="px-6 py-5">
            <p class="text-gray-700 text-base mb-1">You are about to delete:</p>
            <p class="text-gray-900 font-bold text-lg mb-4" id="deleteVaccineName"></p>

            {{-- Warning shown only when vaccine has recorded data --}}
            <div id="deleteHasDataWarning" class="hidden mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <p class="text-red-800 font-semibold text-sm mb-2">⚠ This vaccine has recorded dose data in patient cards.</p>
                <p class="text-red-700 text-sm mb-3">Deleting it will permanently remove all dose records from every patient. This cannot be undone.</p>
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" id="forceDeleteCheckbox" class="mt-0.5 w-4 h-4 accent-red-600">
                    <span class="text-sm text-gray-700">I understand — delete this vaccine and all its patient data permanently.</span>
                </label>
            </div>

            {{-- Safe delete message --}}
            <div id="deleteSafeMessage" class="mb-4 bg-gray-50 border border-gray-200 rounded-xl p-4">
                <p class="text-gray-700 text-sm">No dose data has been recorded for this vaccine. It is safe to delete.</p>
            </div>
        </div>

        <div class="px-6 pb-5 flex justify-end gap-3">
            <button type="button" onclick="closeDeleteModal()"
                    class="px-5 py-2.5 rounded-lg bg-gray-100 text-gray-700 font-semibold text-sm hover:bg-gray-200 transition-colors">
                Cancel
            </button>
            <button type="button" id="confirmDeleteBtn" onclick="confirmDelete()"
                    class="px-5 py-2.5 rounded-lg bg-red-600 text-white font-semibold text-sm hover:bg-red-700 transition-colors">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
    let deleteVaccineId = null;
    let vaccineHasData = false;

    function openDeleteModal(id, name) {
        deleteVaccineId = id;
        vaccineHasData = false;

        document.getElementById('deleteVaccineName').textContent = name;
        document.getElementById('deleteHasDataWarning').classList.add('hidden');
        document.getElementById('deleteSafeMessage').classList.remove('hidden');
        document.getElementById('forceDeleteCheckbox').checked = false;
        document.getElementById('confirmDeleteBtn').disabled = false;
        document.getElementById('deleteVaccineModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteVaccineModal').classList.add('hidden');
        deleteVaccineId = null;
    }

    function confirmDelete() {
        if (!deleteVaccineId) return;

        const force = vaccineHasData ? document.getElementById('forceDeleteCheckbox').checked : false;

        if (vaccineHasData && !force) {
            alert('Please tick the checkbox to confirm deletion of a vaccine with recorded data.');
            return;
        }

        const btn = document.getElementById('confirmDeleteBtn');
        btn.disabled = true;
        btn.textContent = 'Deleting…';

        fetch(`/health_worker/vaccine-settings/${deleteVaccineId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ force_delete: force }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                closeDeleteModal();
                window.location.reload();
            } else if (data.has_data) {
                // Show the force-delete warning
                vaccineHasData = true;
                document.getElementById('deleteHasDataWarning').classList.remove('hidden');
                document.getElementById('deleteSafeMessage').classList.add('hidden');
                btn.disabled = false;
                btn.textContent = 'Delete';
            } else {
                alert(data.message || 'An error occurred.');
                btn.disabled = false;
                btn.textContent = 'Delete';
            }
        })
        .catch(() => {
            alert('An error occurred. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Delete';
        });
    }

    // Close modal on backdrop click
    document.getElementById('deleteVaccineModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>

@endsection
