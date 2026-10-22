@extends('layouts.responsive-layout')

@section('title', 'Vaccination Status')

@section('additional-styles')
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
<!-- html5-qrcode library for QR scanning -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<style>
    /* Page container matches dashboard spacing and full-width desktop */
    .hw-container{ width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width:640px){ .hw-container{ padding-left:2rem; padding-right:2rem; } }
    @media (min-width:1280px){ .hw-container{ padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x{ overflow-x:hidden; }
    
    /* Spinner animation */
    .spinner{ display:inline-block; width:20px; height:20px; border:3px solid rgba(0,0,0,0.1); border-radius:50%; border-top-color:#7a5bbd; animation:spin 1s ease-in-out infinite; margin-right:10px; }
    @keyframes spin{ to{ transform:rotate(360deg); } }
    /* Fade-in animation for header */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex items-center gap-4" data-animate>
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold leading-tight">Vaccination Status</h1>
                <p class="text-sm md:text-base text-white/90 mt-1">Filter by status and search patients</p>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Status Filter -->
            <div class="space-y-2">
                <label for="statusFilter" class="block text-base font-semibold text-gray-800">
                    Filter by Status
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                    </div>
                    <select id="statusFilter" class="w-full h-12 text-base rounded-lg border-2 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all pl-11">
                        <option value="">All Status</option>
                        <option value="vaccinated" {{ ($filters['status'] ?? '') == 'vaccinated' ? 'selected' : '' }}>Vaccinated</option>
                        <option value="missed" {{ ($filters['status'] ?? '') == 'missed' ? 'selected' : '' }}>Missed</option>
                        <option value="not_done" {{ ($filters['status'] ?? '') == 'not_done' ? 'selected' : '' }}>Not Done</option>
                    </select>
                </div>
            </div>

            <!-- Search Input -->
            <div class="space-y-2">
                <label for="searchBar" class="block text-base font-semibold text-gray-800">
                    Search by Name or Contact
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" id="searchBar" value="{{ $filters['search'] ?? '' }}" placeholder="Type patient name or contact..." class="w-full h-12 text-base rounded-lg border-2 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all placeholder:text-gray-400 pl-11" />
                    </div>
                    <!-- QR Scanner Button -->
                    <button type="button" id="openQrScannerBtn" class="h-12 w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-base font-semibold px-5 shadow-sm transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span>Scan QR</span>
                    </button>
                    <a href="{{ route('health_worker.vaccination_status') }}" id="clearFiltersBtn" class="h-12 w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-base font-semibold px-6 shadow-sm transition-all {{ !(($filters['status'] ?? '') || ($filters['search'] ?? '')) ? 'opacity-50 pointer-events-none' : '' }}">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Clear Filters</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Vaccination Day Banner (Auto-detected from Schedule) -->
    @if(isset($todaySchedules) && $todaySchedules->count() > 0)
        <div class="mb-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-5 shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 text-white">
                    <h3 class="text-lg font-bold mb-1">Vaccination Day Active</h3>
                    @if($todaySchedules->count() === 1)
                        <p class="text-sm text-white/95 mb-2">Today's vaccination is scheduled for <strong>{{ $todaySchedules->first()->barangay }}</strong></p>
                    @else
                        <p class="text-sm text-white/95 mb-2">Multiple barangays scheduled for vaccination today:</p>
                        <ul class="text-sm text-white/95 ml-4 list-disc">
                            @foreach($todaySchedules as $schedule)
                                <li><strong>{{ $schedule->barangay }}</strong></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <a href="{{ route('vaccination_schedule.index') }}" class="flex-shrink-0 inline-flex items-center gap-2 px-4 py-2 bg-white text-green-700 rounded-lg font-semibold text-sm hover:bg-green-50 transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Manage Schedule
                </a>
            </div>
        </div>
    @endif

    <!-- Error/Success Messages -->
    @if (session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-600 rounded-xl p-4 shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-800">{{ session('error') }}</h3>
                    @if (session('stock_errors'))
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                            @foreach(session('stock_errors') as $stockError)
                                <li>{{ $stockError }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <p class="text-xs text-red-600 mt-2">Please check the inventory and restock if needed before administering vaccines.</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-600 rounded-xl p-4 shadow-md">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-bold text-green-800">{{ session('success') }}</h3>
                </div>
            </div>
        </div>
    @endif

    <!-- Table with Priority Sections -->
    <div class="overflow-x-auto w-full shadow-md rounded-lg bg-white mb-6">
        <table class="min-w-full" id="vaccinationStatusTable">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Name</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Age</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Barangay</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody id="patient-list" class="divide-y divide-gray-200">
                @php
                    // Check if we have priority patients (today's schedule)
                    $hasPriorityPatients = isset($priorityPatients) && $priorityPatients->count() > 0;
                    $hasOtherPatients = isset($otherPatients) && $otherPatients->count() > 0;
                    $hasActiveSchedule = isset($todaySchedules) && $todaySchedules->count() > 0;
                    
                    // Determine section display
                    $showSections = $hasActiveSchedule && ($hasPriorityPatients || $hasOtherPatients);
                @endphp
                
                @if($patients->count() > 0)
                    {{-- Priority Section: Today's Vaccination Schedule --}}
                    @if($showSections && $hasPriorityPatients)
                        <tr class="bg-green-50">
                            <td colspan="5" class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-base font-bold text-green-800 uppercase tracking-wide">
                                        Today's Vaccination Schedule - 
                                        @if($todaySchedules->count() === 1)
                                            {{ $todaySchedules->first()->barangay }}
                                        @else
                                            Multiple Barangays
                                        @endif
                                    </span>
                                    <span class="ml-auto text-sm font-semibold text-green-700 bg-green-200 px-3 py-1 rounded-full">
                                        {{ $priorityPatients->count() }} {{ Str::plural('patient', $priorityPatients->count()) }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @foreach($patients as $patient)
                            @if($patient->is_priority)
                                @php
                                    // Use status already calculated in controller
                                    $status = $patient->status ?? 'not_done';
                                    
                                    if ($status === 'missed') {
                                        $statusClass = 'bg-red-100 text-red-700';
                                        $statusLabel = 'Missed';
                                    } elseif ($status === 'vaccinated') {
                                        $statusClass = 'bg-green-100 text-green-700';
                                        $statusLabel = 'Vaccinated';
                                    } else {
                                        $statusClass = 'bg-amber-100 text-amber-700';
                                        $statusLabel = 'Not Done';
                                    }
                                @endphp
                                <tr data-id="{{ $patient->id }}" data-status="{{ $status }}" class="clickable-row hover:bg-primary-50 cursor-pointer transition-colors bg-green-50/30" data-url="{{ route('health_worker.patient_card', $patient->id) }}">
                                    <td class="px-6 py-4 text-base font-semibold text-gray-900">{{ $patient->display_name }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ $patient->display_age }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $patient->barangay }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                            @if($status === 'vaccinated')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    
                    {{-- Other Patients Section --}}
                    @if($showSections && $hasOtherPatients)
                        <tr class="bg-blue-50">
                            <td colspan="5" class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                    </svg>
                                    <span class="text-base font-bold text-blue-800 uppercase tracking-wide">Other Patients</span>
                                    <span class="ml-auto text-sm font-semibold text-blue-700 bg-blue-200 px-3 py-1 rounded-full">
                                        {{ $otherPatients->count() }} {{ Str::plural('patient', $otherPatients->count()) }}
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @foreach($patients as $patient)
                            @if(!$patient->is_priority)
                                @php
                                    // Use status already calculated in controller
                                    $status = $patient->status ?? 'not_done';
                                    
                                    if ($status === 'missed') {
                                        $statusClass = 'bg-red-100 text-red-700';
                                        $statusLabel = 'Missed';
                                    } elseif ($status === 'vaccinated') {
                                        $statusClass = 'bg-green-100 text-green-700';
                                        $statusLabel = 'Vaccinated';
                                    } else {
                                        $statusClass = 'bg-amber-100 text-amber-700';
                                        $statusLabel = 'Not Done';
                                    }
                                @endphp
                                <tr data-id="{{ $patient->id }}" data-status="{{ $status }}" class="clickable-row hover:bg-primary-50 cursor-pointer transition-colors" data-url="{{ route('health_worker.patient_card', $patient->id) }}">
                                    <td class="px-6 py-4 text-base font-semibold text-gray-900">{{ $patient->display_name }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ $patient->display_age }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $patient->barangay }}</td>
                                    <td class="px-6 py-4 text-base text-gray-700">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                            @if($status === 'vaccinated')
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    
                    {{-- If no sections needed (no active schedule), show all patients normally --}}
                    @if(!$showSections)
                        @foreach($patients as $patient)
                            @php
                                // Use status already calculated in controller
                                $status = $patient->status ?? 'not_done';
                                
                                if ($status === 'missed') {
                                    $statusClass = 'bg-red-100 text-red-700';
                                    $statusLabel = 'Missed';
                                } elseif ($status === 'vaccinated') {
                                    $statusClass = 'bg-green-100 text-green-700';
                                    $statusLabel = 'Vaccinated';
                                } else {
                                    $statusClass = 'bg-amber-100 text-amber-700';
                                    $statusLabel = 'Not Done';
                                }
                            @endphp
                            <tr data-id="{{ $patient->id }}" data-status="{{ $status }}" class="clickable-row hover:bg-primary-50 cursor-pointer transition-colors" data-url="{{ route('health_worker.patient_card', $patient->id) }}">
                                <td class="px-6 py-4 text-base font-semibold text-gray-900">{{ $patient->display_name }}</td>
                                <td class="px-6 py-4 text-base text-gray-700">{{ $patient->display_age }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $patient->barangay }}</td>
                                <td class="px-6 py-4 text-base text-gray-700">{{ \Carbon\Carbon::now()->format('m-d-Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold {{ $statusClass }}">
                                        @if($status === 'vaccinated')
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                        @endif
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                @else
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-lg font-medium">No patients with incomplete vaccinations found</p>
                                <p class="text-sm">All patients are either fully immunized or match your filter criteria</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($patients->hasMorePages())
        <!-- Load More Button -->
        <div class="text-center mb-6">
            <button id="loadMoreBtn" onclick="loadMorePatients()" class="inline-flex items-center gap-2.5 px-8 py-3.5 bg-primary-700 text-white text-base rounded-lg font-semibold shadow-md hover:bg-primary-800 hover:shadow-lg active:bg-primary-900 transition-all transform hover:scale-105">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
                Load More Patients
            </button>
        </div>

        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="hidden text-center mb-6">
            <div class="inline-flex items-center gap-3 px-6 py-3.5 bg-white rounded-lg shadow-md border border-gray-200">
                <div class="spinner"></div>
                <span class="text-base text-gray-700 font-medium">Loading patients...</span>
            </div>
        </div>
    @endif

    <!-- Pagination Info -->
    @if($patients->total() > 0)
        <div id="paginationInfo" class="text-center text-base text-gray-600 mb-6 font-medium">
            Showing <strong class="text-primary-700" id="currentCount">{{ $patients->count() }}</strong> of <strong class="text-primary-700" id="totalCount">{{ $patients->total() }}</strong> patients
            <span id="filterInfo">
                @if($filters['search'] ?? false)
                    matching "<strong class="text-gray-900">{{ $filters['search'] }}</strong>"
                @endif
                @if($filters['status'] ?? false)
                    with status <strong class="text-gray-900">{{ ucfirst(str_replace('_', ' ', $filters['status'])) }}</strong>
                @endif
            </span>
        </div>
    @endif
</div>

<!-- QR Scanner Modal -->
<div id="qrScannerModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="qrScannerOverlay"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Scan Patient QR Code
                    </h3>
                    <button type="button" id="closeQrScannerBtn" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="bg-white px-6 py-5">
                <p class="text-sm text-gray-600 mb-4 text-center">Position the QR code within the scanner area</p>
                <!-- QR Scanner Container -->
                <div id="qr-reader" class="w-full rounded-lg overflow-hidden border-2 border-gray-200" style="min-height: 300px;"></div>
                <!-- Scanner Status -->
                <div id="qr-scanner-status" class="mt-4 text-center">
                    <p class="text-sm text-gray-500">Initializing camera...</p>
                </div>
                <!-- Error Message -->
                <div id="qr-scanner-error" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700 text-center"></p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                <button type="button" id="cancelQrScanBtn" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Patient Quick View Modal (shows after QR scan) -->
<div id="patientQuickViewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="patientQuickViewOverlay"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full max-h-[90vh] overflow-y-auto">
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Patient Found
                    </h3>
                    <button type="button" id="closeQuickViewBtn" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="bg-white px-6 py-5">
                <!-- Patient Info -->
                <div class="space-y-4">
                    <!-- Profile Picture & Name -->
                    <div class="text-center pb-4 border-b border-gray-200">
                        <div class="flex justify-center mb-3">
                            <div id="qv-profile-image" class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center overflow-hidden border-4 border-primary-200">
                                <!-- Will be replaced with image or initials -->
                                <span class="text-2xl font-bold text-primary-600">?</span>
                            </div>
                        </div>
                        <h4 id="qv-patient-name" class="text-xl font-bold text-gray-900">-</h4>
                        <p id="qv-patient-age" class="text-sm text-gray-600 mt-1">-</p>
                    </div>
                    
                    <!-- Patient Details Grid -->
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="text-gray-500">Sex:</span>
                            <span id="qv-patient-sex" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Barangay:</span>
                            <span id="qv-patient-barangay" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500">Mother/Guardian:</span>
                            <span id="qv-parent-name" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-500">Contact:</span>
                            <span id="qv-parent-contact" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                    </div>
                    
                    <!-- Vaccines Taken -->
                    <div class="mt-4">
                        <h5 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Vaccines Taken (<span id="qv-taken-count">0</span>)
                        </h5>
                        <div id="qv-taken-vaccines" class="space-y-1 text-sm max-h-32 overflow-y-auto">
                            <p class="text-gray-400 italic">None yet</p>
                        </div>
                    </div>
                    
                    <!-- Vaccines Not Taken -->
                    <div class="mt-4">
                        <h5 class="font-semibold text-gray-800 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Vaccines Needed (<span id="qv-not-taken-count">0</span>)
                        </h5>
                        <div id="qv-not-taken-vaccines" class="space-y-1 text-sm max-h-32 overflow-y-auto">
                            <p class="text-gray-400 italic">All vaccines completed!</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3">
                <button type="button" id="closeQuickViewActionBtn" class="w-full sm:w-auto px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-semibold text-sm transition-all order-3 sm:order-1">
                    Close
                </button>
                <a href="#" id="qv-view-full-record-btn" class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-semibold text-sm transition-all text-center flex items-center justify-center gap-2 order-2 sm:order-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    View & Print
                </a>
                <a href="#" id="qv-update-vaccination-btn" class="w-full sm:w-auto px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm transition-all text-center flex items-center justify-center gap-2 order-1 sm:order-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Update Vaccination
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional-scripts')
<script>
    let currentPage = 1;
    let hasMore = {{ $patients->hasMorePages() ? 'true' : 'false' }};
    let isLoading = false;
    let searchDebounceTimer = null;

    document.addEventListener('DOMContentLoaded', function() {
        // Let blade template handle initial render, don't replace
        setupEventListeners();
        setupClickableRows();
    });

    function setupClickableRows() {
        // Add click handlers to all clickable rows
        document.querySelectorAll('.clickable-row').forEach(function(row) {
            row.onclick = function() {
                window.location = this.getAttribute('data-url');
            };
        });
    }

    function setupEventListeners() {
        const searchInput = document.getElementById('searchBar');
        const statusFilter = document.getElementById('statusFilter');
        const clearBtn = document.getElementById('clearFiltersBtn');

        // Function to update clear button state
        function updateClearButtonState() {
            const hasFilters = (searchInput && searchInput.value.trim() !== '') || 
                               (statusFilter && statusFilter.value !== '');
            if (clearBtn) {
                if (hasFilters) {
                    clearBtn.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    clearBtn.classList.add('opacity-50', 'pointer-events-none');
                }
            }
        }

        // Debounced search input (300ms)
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                updateClearButtonState();
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    handleSearch();
                }, 300);
            });

            // Enter key triggers immediate search
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    handleSearch();
                }
            });
        }

        // Status filter - triggers immediate search
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                updateClearButtonState();
                clearTimeout(searchDebounceTimer);
                handleSearch();
            });
        }
        
        // Initialize clear button state
        updateClearButtonState();
    }

    function handleSearch() {
        currentPage = 1;
        const searchQuery = document.getElementById('searchBar').value.trim();
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams();
        params.append('page', 1);
        if (searchQuery) params.append('search', searchQuery);
        if (status) params.append('status', status);

        showLoading(true);

        fetch(`/vaccination-status/load?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                replaceTableRows(data.patients);
                hasMore = data.has_more || false;
                updateLoadMoreButton();
                updatePaginationInfo(data.patients.length, data.total, searchQuery, status);
                showLoading(false);
            })
            .catch(error => {
                console.error('Search failed:', error);
                showLoading(false);
            });
    }

    function loadMorePatients() {
        if (isLoading || !hasMore) return;

        currentPage++;
        const searchQuery = document.getElementById('searchBar').value.trim();
        const status = document.getElementById('statusFilter').value;

        const params = new URLSearchParams();
        params.append('page', currentPage);
        if (searchQuery) params.append('search', searchQuery);
        if (status) params.append('status', status);

        showLoading(true);

        fetch(`/vaccination-status/load?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                appendTableRows(data.patients);
                hasMore = data.has_more || false;
                updateLoadMoreButton();
                const currentCount = document.querySelectorAll('.clickable-row').length;
                updatePaginationInfo(currentCount, data.total, searchQuery, status);
                showLoading(false);
            })
            .catch(error => {
                console.error('Load more failed:', error);
                showLoading(false);
                currentPage--;
            });
    }

    function replaceTableRows(patients) {
        const tbody = document.getElementById('patient-list');
        tbody.innerHTML = '';

        if (patients.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-lg font-medium">No patients found</p>
                            <p class="text-sm">Try adjusting your search or filter criteria</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        patients.forEach(patient => {
            tbody.appendChild(createPatientRow(patient));
        });
    }

    function appendTableRows(patients) {
        const tbody = document.getElementById('patient-list');
        patients.forEach(patient => {
            tbody.appendChild(createPatientRow(patient));
        });
    }

    function createPatientRow(patient) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', patient.id);
        tr.setAttribute('data-status', patient.status);
        tr.className = 'clickable-row hover:bg-primary-50 cursor-pointer transition-colors';
        tr.setAttribute('data-url', `/vaccination_form/${patient.id}`);
        tr.onclick = function() { window.location = this.getAttribute('data-url'); };

        // Determine status styling
        let statusClass, statusLabel, statusIcon;
        if (patient.status === 'missed') {
            statusClass = 'bg-red-100 text-red-700';
            statusLabel = 'Missed';
            statusIcon = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
        } else if (patient.status === 'vaccinated') {
            statusClass = 'bg-green-100 text-green-700';
            statusLabel = 'Vaccinated';
            statusIcon = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
        } else {
            statusClass = 'bg-amber-100 text-amber-700';
            statusLabel = 'Not Done';
            statusIcon = '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
        }

        const currentDate = new Date().toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' });

        tr.innerHTML = `
            <td class="px-6 py-4 text-base font-semibold text-gray-900">${patient.display_name}</td>
            <td class="px-6 py-4 text-base text-gray-700">${patient.display_age}</td>
            <td class="px-6 py-4 text-base text-gray-700">${currentDate}</td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-semibold ${statusClass}">
                    ${statusIcon}
                    ${statusLabel}
                </span>
            </td>
        `;

        return tr;
    }

    function showLoading(show) {
        isLoading = show;
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        const loadingIndicator = document.getElementById('loadingIndicator');

        if (loadMoreBtn) loadMoreBtn.style.display = show ? 'none' : '';
        if (loadingIndicator) loadingIndicator.classList.toggle('hidden', !show);
    }

    function updateLoadMoreButton() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = hasMore ? '' : 'none';
        }
    }

    function updatePaginationInfo(currentCount, total, searchQuery, status) {
        const paginationInfo = document.getElementById('paginationInfo');
        if (!paginationInfo) return;

        const currentCountEl = document.getElementById('currentCount');
        const totalCountEl = document.getElementById('totalCount');
        const filterInfoEl = document.getElementById('filterInfo');

        if (currentCountEl) currentCountEl.textContent = currentCount;
        if (totalCountEl) totalCountEl.textContent = total;

        if (filterInfoEl) {
            let filterText = '';
            if (searchQuery) {
                filterText += ` matching "<strong class="text-gray-900">${searchQuery}</strong>"`;
            }
            if (status) {
                const statusLabel = status.replace('_', ' ');
                filterText += ` with status <strong class="text-gray-900">${statusLabel.charAt(0).toUpperCase() + statusLabel.slice(1)}</strong>`;
            }
            filterInfoEl.innerHTML = filterText;
        }

        paginationInfo.style.display = total > 0 ? '' : 'none';
    }

    // =========================================
    // QR SCANNER FUNCTIONALITY
    // =========================================
    let html5QrcodeScanner = null;
    
    // DOM Elements
    const qrScannerModal = document.getElementById('qrScannerModal');
    const patientQuickViewModal = document.getElementById('patientQuickViewModal');
    const openQrScannerBtn = document.getElementById('openQrScannerBtn');
    const closeQrScannerBtn = document.getElementById('closeQrScannerBtn');
    const cancelQrScanBtn = document.getElementById('cancelQrScanBtn');
    const qrScannerOverlay = document.getElementById('qrScannerOverlay');
    const closeQuickViewBtn = document.getElementById('closeQuickViewBtn');
    const closeQuickViewActionBtn = document.getElementById('closeQuickViewActionBtn');
    const patientQuickViewOverlay = document.getElementById('patientQuickViewOverlay');
    
    // Open QR Scanner Modal
    openQrScannerBtn?.addEventListener('click', function() {
        qrScannerModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        startQrScanner();
    });
    
    // Close QR Scanner Modal
    function closeQrScannerModal() {
        qrScannerModal.classList.add('hidden');
        document.body.style.overflow = '';
        stopQrScanner();
    }
    
    closeQrScannerBtn?.addEventListener('click', closeQrScannerModal);
    cancelQrScanBtn?.addEventListener('click', closeQrScannerModal);
    qrScannerOverlay?.addEventListener('click', closeQrScannerModal);
    
    // Close Patient Quick View Modal
    function closePatientQuickViewModal() {
        patientQuickViewModal.classList.add('hidden');
        document.body.style.overflow = '';
    }
    
    closeQuickViewBtn?.addEventListener('click', closePatientQuickViewModal);
    closeQuickViewActionBtn?.addEventListener('click', closePatientQuickViewModal);
    patientQuickViewOverlay?.addEventListener('click', closePatientQuickViewModal);
    
    // Track scanner state
    let scannerRunning = false;
    let processingQr = false;
    
    // Start QR Scanner
    function startQrScanner() {
        if (scannerRunning) return;
        
        const statusEl = document.getElementById('qr-scanner-status');
        const errorEl = document.getElementById('qr-scanner-error');
        
        statusEl.innerHTML = '<p class="text-sm text-gray-500">Initializing camera...</p>';
        errorEl.classList.add('hidden');
        processingQr = false;
        
        html5QrcodeScanner = new Html5Qrcode("qr-reader");
        
        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };
        
        html5QrcodeScanner.start(
            { facingMode: "environment" }, // Use back camera
            config,
            onQrCodeScanned,
            onQrScanError
        ).then(() => {
            scannerRunning = true;
            statusEl.innerHTML = '<p class="text-sm text-green-600 font-medium">Camera ready - Scan a QR code</p>';
        }).catch((err) => {
            console.error('QR Scanner Error:', err);
            statusEl.innerHTML = '';
            errorEl.classList.remove('hidden');
            errorEl.querySelector('p').textContent = 'Unable to access camera. Please ensure camera permissions are granted.';
        });
    }
    
    // Stop QR Scanner
    function stopQrScanner() {
        if (html5QrcodeScanner && scannerRunning) {
            scannerRunning = false;
            html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }).catch((err) => {
                console.error('Error stopping scanner:', err);
                html5QrcodeScanner = null;
            });
        }
    }
    
    // Handle successful QR scan
    function onQrCodeScanned(decodedText, decodedResult) {
        // Prevent multiple scans being processed
        if (processingQr) return;
        processingQr = true;
        
        // console.log('QR Code Scanned:', decodedText); // Debug log
        
        // Parse the QR URL to extract UUID
        // Expected format: https://domain.com/qr/{uuid} or http://localhost/infantsSystem/public/qr/{uuid}
        const uuidMatch = decodedText.match(/\/qr\/([a-f0-9\-]{36})/i);
        
        if (!uuidMatch) {
            // console.log('UUID not found in:', decodedText);
            showScanError('Invalid QR code format. This doesn\'t appear to be a patient QR code.');
            processingQr = false;
            return;
        }
        
        const uuid = uuidMatch[1];
        // console.log('Extracted UUID:', uuid); // Debug log
        
        // Stop scanner and close modal
        stopQrScanner();
        closeQrScannerModal();
        
        lookupPatientByUuid(uuid);
    }
    
    // Handle QR scan errors (called frequently, so we filter noise)
    function onQrScanError(errorMessage) {
        // Most errors are just "no QR code found" which is expected - don't log those
        // The "Cannot transition" error is a library state issue - also suppress
        // Keeping this commented since it's only for debugging
        // if (errorMessage && 
        //     !errorMessage.includes('No QR code found') && 
        //     !errorMessage.includes('No MultiFormat') &&
        //     !errorMessage.includes('Cannot transition')) {
        //     console.warn('QR Scan Error:', errorMessage);
        // }
    }
    
    // Lookup patient by UUID
    async function lookupPatientByUuid(uuid) {
        // console.log('Looking up patient with UUID:', uuid);
        const apiUrl = `{{ url('/api/patient/qr') }}/${uuid}`;
        // console.log('API URL:', apiUrl);
        
        try {
            const response = await fetch(apiUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });
            
            // console.log('Response status:', response.status);
            const data = await response.json();
            // console.log('QR Lookup Response:', data);
            
            if (!response.ok || !data.success) {
                showScanError(data.message || 'Patient not found');
                processingQr = false;
                return;
            }
            
            showPatientQuickView(data);
            processingQr = false;
            
        } catch (error) {
            // console.error('Lookup error:', error);
            showScanError('Unable to lookup patient. Please try again.');
            processingQr = false;
        }
    }
    
    // Show error after scan
    function showScanError(message) {
        alert(message); // Simple alert for now, could be enhanced with a toast
    }
    
    // Show patient quick view modal
    function showPatientQuickView(data) {
        const patient = data.patient;
        const summary = data.vaccination_summary;
        const links = data.links;
        
        // Populate profile image
        const profileContainer = document.getElementById('qv-profile-image');
        if (patient.profile_image_url) {
            profileContainer.innerHTML = `<img src="${patient.profile_image_url}" alt="${patient.name}" class="w-full h-full object-cover">`;
        } else {
            // Show initials
            const initials = patient.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            profileContainer.innerHTML = `<span class="text-2xl font-bold text-primary-600">${initials}</span>`;
        }
        
        // Populate patient info
        document.getElementById('qv-patient-name').textContent = patient.name;
        document.getElementById('qv-patient-age').textContent = `${patient.age} • Born: ${formatDate(patient.date_of_birth)}`;
        document.getElementById('qv-patient-sex').textContent = patient.sex;
        document.getElementById('qv-patient-barangay').textContent = patient.barangay || 'N/A';
        document.getElementById('qv-parent-name').textContent = patient.parent_name;
        document.getElementById('qv-parent-contact').textContent = patient.parent_contact;
        
        // Populate vaccines taken
        document.getElementById('qv-taken-count').textContent = summary.taken_count;
        const takenContainer = document.getElementById('qv-taken-vaccines');
        if (summary.taken && summary.taken.length > 0) {
            takenContainer.innerHTML = summary.taken.map(vax => {
                const isComplete = vax.doses_taken_count === vax.total_doses;
                const bgClass = isComplete ? 'bg-green-100' : 'bg-green-50';
                const badgeClass = isComplete ? 'bg-green-600 text-white' : 'bg-green-200 text-green-800';
                // Use doses_label if available, otherwise show "X/Y" format
                const doseLabel = vax.doses_label || `${vax.doses_taken_count}/${vax.total_doses}`;
                return `
                <div class="flex justify-between items-center p-2 ${bgClass} rounded border border-green-200">
                    <span class="font-medium text-gray-800 text-sm">${vax.name}</span>
                    <span class="text-xs font-medium px-2 py-0.5 rounded ${badgeClass}">${doseLabel}</span>
                </div>
            `}).join('');
        } else {
            takenContainer.innerHTML = '<p class="text-gray-400 italic text-center py-2">No vaccines taken yet</p>';
        }
        
        // Populate vaccines not taken
        document.getElementById('qv-not-taken-count').textContent = summary.not_taken_count;
        const notTakenContainer = document.getElementById('qv-not-taken-vaccines');
        if (summary.not_taken && summary.not_taken.length > 0) {
            notTakenContainer.innerHTML = summary.not_taken.map(vax => `
                <div class="flex justify-between items-center p-2 bg-yellow-100 rounded border border-yellow-300">
                    <span class="font-medium text-gray-800 text-sm">${vax.name}</span>
                    <span class="text-xs text-yellow-800">${vax.doses_description || 'Pending'}</span>
                </div>
            `).join('');
        } else {
            notTakenContainer.innerHTML = '<p class="text-green-600 italic text-center py-2">All vaccines completed! 🎉</p>';
        }
        
        // Set links
        document.getElementById('qv-view-full-record-btn').href = links.full_record;
        document.getElementById('qv-update-vaccination-btn').href = links.update_vaccination;
        
        // Show modal
        patientQuickViewModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    
    // Helper: Format date
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    // Handle Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!qrScannerModal.classList.contains('hidden')) {
                closeQrScannerModal();
            }
            if (!patientQuickViewModal.classList.contains('hidden')) {
                closePatientQuickViewModal();
            }
        }
    });
</script>
@endsection
