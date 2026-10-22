@extends('layouts.responsive-layout')

@section('title', 'Vaccination Schedule')

@section('additional-styles')
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
<style>
    .hw-container{ width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width:640px){ .hw-container{ padding-left:2rem; padding-right:2rem; } }
    @media (min-width:1280px){ .hw-container{ padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x{ overflow-x:hidden; }
    /* Fade-in animation for header */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex items-center gap-4" data-animate>
            <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold leading-tight">Vaccination Schedule</h1>
                <p class="text-sm md:text-base text-white/90 mt-1">Manage vaccination days and barangay assignments</p>
            </div>
        </div>
    </section>

    <!-- Toast Notification -->
    @if (session('success'))
        <div id="toast" class="fixed top-4 right-4 z-50 bg-white rounded-xl shadow-2xl border-l-4 border-green-600 p-4 min-w-[320px] max-w-md transform transition-all duration-300">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Success!</h3>
                    <p class="text-sm text-gray-600">{{ session('success') }}</p>
                </div>
                <button onclick="closeToast()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div id="toast" class="fixed top-4 right-4 z-50 bg-white rounded-xl shadow-2xl border-l-4 border-red-600 p-4 min-w-[320px] max-w-md transform transition-all duration-300">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Error</h3>
                    <p class="text-sm text-gray-600">{{ session('error') }}</p>
                </div>
                <button onclick="closeToast()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Add New Schedule Form -->
    @if(!isset($healthWorker) || $healthWorker->isRHU())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Schedule New Vaccination Day</h2>
        
        <!-- Validation Error Alert -->
        @if ($errors->any())
            <div class="mb-5 bg-red-50 border-l-4 border-red-500 rounded-lg p-4 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-red-800 mb-1">Unable to Create Schedule</h3>
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-red-400 hover:text-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('vaccination_schedule.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <!-- Date -->
                <div class="space-y-2">
                    <label for="vaccination_date" class="block text-base font-semibold text-gray-800">
                        Vaccination Date
                    </label>
                    <input type="date" id="vaccination_date" name="vaccination_date" value="{{ old('vaccination_date') }}" min="{{ date('Y-m-d') }}" required class="w-full h-12 text-base rounded-lg border-2 {{ $errors->has('vaccination_date') ? 'border-red-500' : 'border-gray-300' }} shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all px-4">
                </div>

                <!-- Barangay - Dynamic from controller -->
                <div class="space-y-2">
                    <label for="barangay" class="block text-base font-semibold text-gray-800">
                        Target Barangay
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <select id="barangay" name="barangay" required class="w-full h-12 text-base rounded-lg border-2 {{ $errors->has('barangay') ? 'border-red-500' : 'border-gray-300' }} shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all pl-11">
                            <option value="">Select Barangay</option>
                            @if(isset($schedulableBarangays))
                                @foreach($schedulableBarangays as $barangay)
                                    <option value="{{ $barangay }}" {{ old('barangay') == $barangay ? 'selected' : '' }}>{{ $barangay }}</option>
                                @endforeach
                            @else
                                {{-- Fallback: hardcoded list excluding Kanluran --}}
                                <option value="RHU (Health Center)" {{ old('barangay') == 'RHU (Health Center)' ? 'selected' : '' }}>RHU (Health Center)</option>
                                <option value="Balayhangin" {{ old('barangay') == 'Balayhangin' ? 'selected' : '' }}>Balayhangin</option>
                                <option value="Bangyas" {{ old('barangay') == 'Bangyas' ? 'selected' : '' }}>Bangyas</option>
                                <option value="Dayap" {{ old('barangay') == 'Dayap' ? 'selected' : '' }}>Dayap</option>
                                <option value="Hanggan" {{ old('barangay') == 'Hanggan' ? 'selected' : '' }}>Hanggan</option>
                                <option value="Imok" {{ old('barangay') == 'Imok' ? 'selected' : '' }}>Imok</option>
                                <option value="Lamot 1" {{ old('barangay') == 'Lamot 1' ? 'selected' : '' }}>Lamot 1</option>
                                <option value="Lamot 2" {{ old('barangay') == 'Lamot 2' ? 'selected' : '' }}>Lamot 2</option>
                                <option value="Limao" {{ old('barangay') == 'Limao' ? 'selected' : '' }}>Limao</option>
                                <option value="Mabacan" {{ old('barangay') == 'Mabacan' ? 'selected' : '' }}>Mabacan</option>
                                <option value="Masiit" {{ old('barangay') == 'Masiit' ? 'selected' : '' }}>Masiit</option>
                                <option value="Paliparan" {{ old('barangay') == 'Paliparan' ? 'selected' : '' }}>Paliparan</option>
                                <option value="Perez" {{ old('barangay') == 'Perez' ? 'selected' : '' }}>Perez</option>
                                <option value="Prinza" {{ old('barangay') == 'Prinza' ? 'selected' : '' }}>Prinza</option>
                                <option value="San Isidro" {{ old('barangay') == 'San Isidro' ? 'selected' : '' }}>San Isidro</option>
                                <option value="Santo Tomas" {{ old('barangay') == 'Santo Tomas' ? 'selected' : '' }}>Santo Tomas</option>
                                <option value="Silangan" {{ old('barangay') == 'Silangan' ? 'selected' : '' }}>Silangan</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="space-y-2">
                    <label for="notes" class="block text-base font-semibold text-gray-800">
                        Notes (Optional)
                    </label>
                    <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Add any additional notes..." class="w-full h-12 text-base rounded-lg border-2 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all placeholder:text-gray-400 px-4">
                </div>
            </div>

            <div class="mt-5">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary-700 px-6 py-3 text-base font-semibold text-white shadow-md hover:bg-primary-800 active:bg-primary-900 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Schedule
                </button>
            </div>
        </form>
    </div>
    @else
    {{-- Barangay workers cannot create schedules --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm text-blue-800">
                <strong>Viewing schedules for {{ $healthWorker->barangay->name ?? 'your barangay' }}.</strong> 
                Only RHU administrators can create or modify vaccination schedules.
            </p>
        </div>
    </div>
    @endif

    <!-- Upcoming Schedules -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Upcoming Vaccination Days</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-primary-700 text-white">
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide">Date</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide">Barangay</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide hidden md:table-cell">Days Until</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide">Status</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide hidden lg:table-cell">Notes</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($upcomingSchedules as $schedule)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base font-semibold text-gray-900 whitespace-nowrap">{{ $schedule->vaccination_date->format('M d, Y') }}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base text-gray-700">{{ $schedule->barangay }}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base text-gray-700 hidden md:table-cell">{{ $schedule->daysUntil() }}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                @if($schedule->status === 'active')
                                    <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-green-100 text-green-700 whitespace-nowrap">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="hidden sm:inline">Active</span>
                                    </span>
                                @elseif($schedule->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-gray-100 text-gray-700 whitespace-nowrap">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="hidden sm:inline">Cancelled</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-blue-100 text-blue-700 whitespace-nowrap">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="hidden sm:inline">Scheduled</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base text-gray-600 hidden lg:table-cell">{{ $schedule->notes ?? '-' }}</td>
                            <td class="px-4 sm:px-6 py-3 sm:py-4">
                                @if(!isset($healthWorker) || $healthWorker->isRHU())
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-1.5 sm:gap-2">
                                    @if($schedule->status === 'cancelled')
                                        {{-- Only show Delete button for cancelled schedules --}}
                                        <button type="button" onclick="showDeleteModal({{ $schedule->id }}, '{{ $schedule->barangay }}', '{{ $schedule->vaccination_date->format('M d, Y') }}')" class="inline-flex items-center justify-center gap-1 sm:gap-1.5 rounded-lg bg-red-600 text-white text-xs sm:text-sm font-semibold px-2 sm:px-3 py-1.5 sm:py-2 shadow-sm hover:bg-red-700 active:bg-red-800 transition-all whitespace-nowrap">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span>Delete</span>
                                        </button>
                                    @else
                                        {{-- Show Cancel button for scheduled/active schedules --}}
                                        <button type="button" onclick="showCancelModal({{ $schedule->id }}, '{{ $schedule->barangay }}', '{{ $schedule->vaccination_date->format('M d, Y') }}')" class="inline-flex items-center justify-center gap-1 sm:gap-1.5 rounded-lg text-white text-xs sm:text-sm font-semibold px-2 sm:px-3 py-1.5 sm:py-2 shadow-sm transition-all whitespace-nowrap" style="background-color: #f59e0b; hover:background-color: #d97706;">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                            <span>Cancel</span>
                                        </button>
                                    @endif
                                </div>
                                @else
                                <span class="text-xs text-gray-500">View only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 sm:px-6 py-8 sm:py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-10 h-10 sm:w-12 sm:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="text-base sm:text-lg font-medium">No upcoming vaccination days scheduled</p>
                                    <p class="text-xs sm:text-sm">Schedule a new vaccination day using the form above</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Past Schedules -->
    @if($pastSchedules->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h2 class="text-lg font-bold text-gray-900">Past Vaccination Days</h2>
                <p class="text-xs text-gray-500">Showing last 3 months • Older records are automatically archived</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold text-gray-700 uppercase tracking-wide">Date</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold text-gray-700 uppercase tracking-wide">Barangay</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold text-gray-700 uppercase tracking-wide">Status</th>
                            <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs sm:text-sm font-bold text-gray-700 uppercase tracking-wide hidden lg:table-cell">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($pastSchedules as $schedule)
                            <tr class="hover:bg-gray-50 opacity-75">
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base text-gray-600 whitespace-nowrap">{{ $schedule->vaccination_date->format('M d, Y') }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base text-gray-600">{{ $schedule->barangay }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    @if($schedule->status === 'completed')
                                        <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-green-100 text-green-700 whitespace-nowrap">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="hidden sm:inline">Completed</span>
                                        </span>
                                    @elseif($schedule->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-red-100 text-red-700 whitespace-nowrap">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="hidden sm:inline">Cancelled</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-semibold bg-green-100 text-green-700 whitespace-nowrap">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="hidden sm:inline">Completed</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-sm sm:text-base text-gray-600 hidden lg:table-cell">{{ $schedule->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($pastSchedules->hasPages())
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $pastSchedules->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-4 sm:p-6">
            <!-- Warning Icon with solid background -->
            <div class="flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 rounded-full shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
                <svg class="w-9 h-9 sm:w-11 sm:h-11 text-white drop-shadow-md" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 text-center mb-2">Cancel Vaccination Schedule?</h3>
            
            <!-- Description -->
            <p class="text-sm sm:text-base text-gray-600 text-center mb-5">
                You are about to cancel the vaccination schedule for <strong class="text-gray-900" id="cancelBarangay"></strong> on <strong class="text-gray-900" id="cancelDate"></strong>.
            </p>
            
            <!-- Cancellation Form -->
            <form id="cancelForm" method="POST" action="" class="space-y-4">
                @csrf
                
                <!-- Cancellation Reason -->
                <div>
                    <label for="cancellation_reason" class="block text-sm font-bold text-gray-900 mb-2">
                        Cancellation Reason <span class="text-red-600">*</span>
                    </label>
                    <textarea 
                        id="cancellation_reason" 
                        name="cancellation_reason"
                        rows="4" 
                        required
                        maxlength="500"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-amber-500 focus:ring-4 focus:ring-amber-100 resize-none text-sm sm:text-base text-gray-900 placeholder-gray-400 transition-all"
                        placeholder="E.g., Heavy rain, Staff unavailable, Medical supplies not available, etc."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1.5">
                        This reason will be saved for record-keeping purposes.
                    </p>
                </div>
                
                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-3.5 sm:p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-xs sm:text-sm text-blue-900">
                            <strong class="font-semibold">Future Feature:</strong> Parents will receive a cancellation notice with your reason when the notification system is implemented.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row gap-3 pt-2">
                    <button type="button" onclick="closeCancelModal()" class="w-full sm:w-auto sm:flex-1 px-5 py-3 rounded-lg text-base font-bold bg-gray-200 hover:bg-gray-300 active:bg-gray-400 text-gray-800 transition-all shadow-sm">
                        Go Back
                    </button>
                    <button type="submit" class="w-full sm:w-auto sm:flex-1 px-5 py-3 rounded-lg text-base font-bold text-white transition-all shadow-md hover:shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); hover:background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                        Cancel Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Delete Vaccination Schedule?</h3>
            <p class="text-base text-gray-600 text-center mb-6">
                Are you sure you want to delete the vaccination schedule for <strong id="deleteBarangay"></strong> on <strong id="deleteDate"></strong>? This action cannot be undone.
            </p>
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()" class="flex-1 px-4 py-3 rounded-lg text-base font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-all">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" action="" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-4 py-3 rounded-lg text-base font-semibold bg-red-600 hover:bg-red-700 text-white shadow-md hover:shadow-lg transition-all">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Cancel Modal Functions
function showCancelModal(scheduleId, barangay, date) {
    const modal = document.getElementById('cancelModal');
    const cancelForm = document.getElementById('cancelForm');
    const cancelBarangay = document.getElementById('cancelBarangay');
    const cancelDate = document.getElementById('cancelDate');
    const reasonTextarea = document.getElementById('cancellation_reason');
    
    cancelForm.action = `/health_worker/vaccination_schedule/${scheduleId}/cancel`;
    cancelBarangay.textContent = barangay;
    cancelDate.textContent = date;
    reasonTextarea.value = '';  // Clear previous input
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Focus on textarea for immediate input
    setTimeout(() => reasonTextarea.focus(), 100);
}

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close cancel modal on outside click
document.getElementById('cancelModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCancelModal();
    }
});

// Delete Modal Functions
function showDeleteModal(scheduleId, barangay, date) {
    const modal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    const deleteBarangay = document.getElementById('deleteBarangay');
    const deleteDate = document.getElementById('deleteDate');
    
    deleteForm.action = `/health_worker/vaccination_schedule/${scheduleId}`;
    deleteBarangay.textContent = barangay;
    deleteDate.textContent = date;
    
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close delete modal on outside click
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
        closeCancelModal();
    }
});

// Toast notification auto-close
function closeToast() {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }
}

// Auto close toast after 5 seconds
if (document.getElementById('toast')) {
    setTimeout(closeToast, 5000);
}
</script>
@endsection
