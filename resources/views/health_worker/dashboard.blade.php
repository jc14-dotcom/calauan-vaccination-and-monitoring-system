@extends('layouts.responsive-layout')

@section('title', 'Dashboard')

@section('additional-styles')
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
<style>
    /* Dashboard-only responsive guards */
    /* Full-width on desktop; keep comfy paddings */
    .hw-container { width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width: 640px){ .hw-container { padding-left:2rem; padding-right:2rem; } }
    @media (min-width: 1280px){ .hw-container { padding-left:2.5rem; padding-right:2.5rem; } }
    /* Prevent canvases from momentarily overflowing on small screens */
    #monthlyChart, #vaccineStatusChart, #vaccinationDistChart, #feedbackChart {
        display:block; max-width:100% !important;
    }
    /* Avoid rare horizontal scroll caused by inner rings/shadows */
    .hw-no-overflow-x { overflow-x:hidden; }
    /* Table long text wrapping */
    .break-words { overflow-wrap: anywhere; word-break: break-word; }
    /* Ensure animated sections don't push width */
    [data-animate]{ min-width: 0; }
    /* Container cards */
    .hw-card{ min-width: 0; }
    .hw-grid{ min-width: 0; }
    .hw-section{ min-width: 0; }
    .hw-banner{ min-width: 0; }
    .hw-flex{ min-width: 0; }
    .hw-title{ min-width: 0; }
    
    /* Gradient border-top with specific colors */
    .gradient-border-top {
        border-top: 3px solid #7a5bbd;
    }
    
    /* Loading skeleton animation */
    @keyframes shimmer {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 1000px 100%;
        animation: shimmer 2s infinite;
    }
    
    /* Card hover effect */
    .chart-card-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .chart-card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(122, 91, 189, 0.15);
    }
    
    /* Fade-in animation for cards */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    [data-animate] {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    [data-animate]:nth-child(1) { animation-delay: 0.1s; }
    [data-animate]:nth-child(2) { animation-delay: 0.2s; }
    [data-animate]:nth-child(3) { animation-delay: 0.3s; }
    [data-animate]:nth-child(4) { animation-delay: 0.4s; }
    
    /* Number counter animation */
    @keyframes countUp {
        from { opacity: 0; transform: scale(0.5); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-count {
        animation: countUp 0.5s ease-out;
    }
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="hw-section relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="hw-flex flex items-center gap-4" data-animate>
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 20h16"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 16v-3"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 16v-6"/>
                    </svg>
                </span>
                <div class="hw-title">
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Dashboard Overview</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Quick insights and upcoming schedules</p>
                </div>
            </div>
            <div class="flex items-center gap-2 md:hidden" data-animate>
                <a href="{{ route('health_worker.patients') }}" class="inline-flex items-center gap-2 rounded-lg bg-white text-primary-700 hover:bg-primary-50 text-sm font-semibold px-4 py-2 ring-1 ring-white/30 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
                    Patients
                </a>
                <a href="{{ route('health_worker.vaccination_status') }}" class="inline-flex items-center gap-2 rounded-lg bg-white/15 hover:bg-white/25 text-white text-sm font-semibold px-4 py-2 ring-1 ring-white/30 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h12v12H6z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 9h6M9 12h6M9 15h6"/>
                    </svg>
                    Status
                </a>
            </div>
        </div>
    </section>

    <!-- KPI Cards -->
    <section class="hw-grid grid gap-5 sm:grid-cols-2 xl:grid-cols-3 mb-6">
        <div class="hw-card rounded-2xl bg-white shadow-sm border-2 border-gray-200 p-5 flex items-center gap-4" data-animate>
            <span class="w-12 h-12 rounded-xl bg-primary-50 text-primary-700 inline-flex items-center justify-center ring-1 ring-primary-100">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z"/></svg>
            </span>
            <div>
                <p class="text-base text-gray-600 font-medium">Total Patients</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalPatients ?? 0 }}</p>
            </div>
        </div>
    <div class="hw-card rounded-2xl bg-white shadow-sm border-2 border-gray-200 p-5 flex items-center gap-4" data-animate>
            <span class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 inline-flex items-center justify-center ring-1 ring-emerald-100">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
            </span>
            <div>
                <p class="text-base text-gray-600 font-medium">Vaccinated</p>
                <p class="text-3xl font-bold text-gray-900">{{ $vaccinated ?? 0 }}</p>
            </div>
        </div>
    <div class="hw-card rounded-2xl bg-white shadow-sm border-2 border-gray-200 p-5 flex items-center gap-4" data-animate>
            <span class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 inline-flex items-center justify-center ring-1 ring-amber-100">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-4.5-7.794L21 4l-.794 4.5A8.963 8.963 0 0121 12z"/>
                </svg>
            </span>
            <div>
                <p class="text-base text-gray-600 font-medium">Feedback Complete</p>
                <p class="text-3xl font-bold text-gray-900">{{ $feedbackCount ?? 0 }}</p>
            </div>
        </div>
    </section>

    <!-- Charts -->
    <section class="hw-grid grid gap-6 mb-6">
        <!-- Row 1: Monthly Statistics & Vaccination Status -->
        <div class="grid gap-6 md:grid-cols-2">
            <!-- Monthly Statistics -->
            <div class="hw-card gradient-border-top chart-card-hover rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden" style="background-color: #ffffff;" data-animate>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-gray-900">Monthly Statistics</h2>
                            <p class="text-xs text-gray-500">Last updated: Today, {{ date('g:i A') }}</p>
                        </div>
                    </div>
                    <!-- Chart Legend -->
                    <div class="flex items-center gap-2 mb-3 px-2 py-2 bg-blue-50 rounded-lg border border-blue-100">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <p class="text-xs text-gray-600">
                            <span class="font-semibold text-blue-700">Patients Vaccinated</span> — Number of patients who received at least one vaccine dose each month in {{ date('Y') }}
                        </p>
                    </div>
                    <div class="h-80">
                        <canvas id="monthlyChart" class="w-full h-full"></canvas>
                    </div>
                    <!-- Summary Metrics -->
                    <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-1">This Month</p>
                            <p id="thisMonthTotal" class="text-xl font-bold text-blue-600">0</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-1">Last Month</p>
                            <p id="lastMonthTotal" class="text-xl font-bold text-gray-600">0</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-1">Change</p>
                            <p id="monthlyChange" class="text-xl font-bold text-emerald-600">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vaccination Status -->
            <div class="hw-card gradient-border-top chart-card-hover rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden" style="background-color: #ffffff;" data-animate>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center shadow-md" style="background-color: #ffffff; border: 2px solid #10b981;">
                            <svg class="w-5 h-5" style="color: #10b981;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-bold text-gray-900">Vaccinated Patient Status</h2>
                            <p class="text-xs text-gray-500">Last updated: Today, {{ date('g:i A') }}</p>
                        </div>
                    </div>
                    <div class="h-80 flex items-center justify-center">
                        <canvas id="vaccineStatusChart" class="max-w-full max-h-full"></canvas>
                    </div>
                    <!-- Legend Cards -->
                    <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-200">
                        <div class="rounded-lg p-3" style="background: linear-gradient(to bottom right, #d1fae5, #a7f3d0); border: 1px solid #6ee7b7;">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 rounded-full" style="background-color: #10b981;"></div>
                                <p class="text-xs font-semibold" style="color: #065f46;">Complete</p>
                            </div>
                            <p id="completeCount" class="text-2xl font-bold" style="color: #047857;">0</p>
                            <p id="completePercent" class="text-xs" style="color: #059669;">0%</p>
                        </div>
                        <div class="rounded-lg p-3" style="background: linear-gradient(to bottom right, #fef3c7, #fde68a); border: 1px solid #fcd34d;">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 rounded-full" style="background-color: #f59e0b;"></div>
                                <p class="text-xs font-semibold" style="color: #78350f;">Partial</p>
                            </div>
                            <p id="partialCount" class="text-2xl font-bold" style="color: #b45309;">0</p>
                            <p id="partialPercent" class="text-xs" style="color: #d97706;">0%</p>
                        </div>
                        <div class="rounded-lg p-3" style="background: linear-gradient(to bottom right, #fee2e2, #fecaca); border: 1px solid #fca5a5;">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-3 h-3 rounded-full" style="background-color: #ef4444;"></div>
                                <p class="text-xs font-semibold" style="color: #7f1d1d;">Not Started</p>
                            </div>
                            <p id="notStartedCount" class="text-2xl font-bold" style="color: #b91c1c;">0</p>
                            <p id="notStartedPercent" class="text-xs" style="color: #dc2626;">0%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Vaccination Distribution (Full Width) -->
        <div class="hw-card gradient-border-top chart-card-hover rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden" style="background-color: #ffffff;" data-animate>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-gray-900">Vaccination Distribution</h2>
                        <p class="text-xs text-gray-500">Last updated: Today, {{ date('g:i A') }}</p>
                    </div>
                </div>
                <!-- Quick Stats Row -->
                <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-4">
                    <div class="rounded-lg p-2 sm:p-4" style="background: linear-gradient(to bottom right, #f3e8ff, #e9d5ff); border: 1px solid #d8b4fe;">
                        <p class="text-[10px] sm:text-xs font-semibold mb-1 leading-tight" style="color: #581c87;">Most Administered</p>
                        <p id="mostAdministered" class="text-sm sm:text-lg font-bold truncate" style="color: #7c3aed;">-</p>
                        <p id="mostAdministeredCount" class="text-[10px] sm:text-xs" style="color: #8b5cf6;">0 doses</p>
                    </div>
                    <div class="rounded-lg p-2 sm:p-4" style="background: linear-gradient(to bottom right, #f3e8ff, #e9d5ff); border: 1px solid #d8b4fe;">
                        <p class="text-[10px] sm:text-xs font-semibold mb-1 leading-tight" style="color: #581c87;">Least Administered</p>
                        <p id="leastAdministered" class="text-sm sm:text-lg font-bold truncate" style="color: #7c3aed;">-</p>
                        <p id="leastAdministeredCount" class="text-[10px] sm:text-xs" style="color: #8b5cf6;">0 doses</p>
                    </div>
                    <div class="rounded-lg p-2 sm:p-4" style="background: linear-gradient(to bottom right, #f3e8ff, #e9d5ff); border: 1px solid #d8b4fe;">
                        <p class="text-[10px] sm:text-xs font-semibold mb-1 leading-tight" style="color: #581c87;">Average per Vaccine</p>
                        <p id="averagePerVaccine" class="text-sm sm:text-lg font-bold" style="color: #7c3aed;">0</p>
                        <p class="text-[10px] sm:text-xs" style="color: #8b5cf6;">doses</p>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="vaccinationDistChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Vaccine Schedule by Age (Separate Card) -->
        <div class="hw-card gradient-border-top chart-card-hover rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden mt-6" style="background-color: #ffffff;" data-animate>
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-md">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-gray-900">Vaccination Schedule by Age</h2>
                        <p class="text-xs text-gray-500">Guide to the proper age for each vaccine</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Newborn -->
                    <div class="rounded-xl p-4 bg-white border-l-4 border-emerald-500 shadow-sm ring-1 ring-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h4 class="text-base font-bold text-gray-800">Newborn</h4>
                                <p class="text-sm text-gray-500 mt-0.5">Within 24 hours</p>
                            </div>
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold">2</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold">BCG</span>
                            <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-sm font-semibold">Hepatitis B</span>
                        </div>
                    </div>
                    
                    <!-- 1.5-3.5 months -->
                    <div class="rounded-xl p-4 bg-white border-l-4 border-blue-500 shadow-sm ring-1 ring-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h4 class="text-base font-bold text-gray-800">1½ - 3½ Months</h4>
                                <p class="text-sm text-gray-500 mt-0.5">6, 10, 14 weeks</p>
                            </div>
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">9</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold">Penta ×3</span>
                            <span class="px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold">OPV ×3</span>
                            <span class="px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg text-sm font-semibold">PCV ×3</span>
                        </div>
                    </div>
                    
                    <!-- 3.5 and 9 months - IPV -->
                    <div class="rounded-xl p-4 bg-white border-l-4 border-purple-500 shadow-sm ring-1 ring-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h4 class="text-base font-bold text-gray-800">3½ and 9 Months</h4>
                                <p class="text-sm text-gray-500 mt-0.5">14 weeks and 9 months</p>
                            </div>
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold">2</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg text-sm font-semibold">IPV ×2</span>
                        </div>
                    </div>
                    
                    <!-- 9-15 months -->
                    <div class="rounded-xl p-4 bg-white border-l-4 border-amber-500 shadow-sm ring-1 ring-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div>
                                <h4 class="text-base font-bold text-gray-800">9 - 15 Months</h4>
                                <p class="text-sm text-gray-500 mt-0.5">9 months and 12-15 months</p>
                            </div>
                            <span class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold">2</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-sm font-semibold">MMR ×2</span>
                        </div>
                    </div>
                </div>
                
                <!-- Total Summary -->
                <div class="mt-4 p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-700">Total Doses</span>
                    <span class="px-4 py-1.5 bg-primary-600 text-white rounded-full text-sm font-bold">15 doses</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Upcoming Vaccinations -->
    <section class="hw-section rounded-2xl bg-gradient-to-br from-white to-primary-50/30 shadow-lg ring-1 ring-primary-200/50 overflow-hidden mb-6" data-animate>
        <div class="px-6 py-5 bg-gradient-to-r from-primary-600 to-primary-700 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center ring-1 ring-white/30">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-lg md:text-xl font-bold text-white">Scheduled Vaccination Days</h2>
        </div>
        <div class="p-6">
            @if(isset($upcomingVaccinations) && count($upcomingVaccinations) > 0)
                <div class="grid gap-4">
                    @foreach($upcomingVaccinations as $vaccination)
                    <div class="relative overflow-hidden rounded-xl bg-white border-2 border-gray-200 shadow-sm">
                        <div class="relative p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                            <div class="flex items-center gap-3 sm:gap-4 flex-1 min-w-0 w-full sm:w-auto">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg ring-4 ring-primary-100 flex-shrink-0">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-1 truncate">{{ $vaccination->patient_name ?? 'N/A' }}</h3>
                                    <p class="text-xs sm:text-sm text-gray-600">Vaccination day scheduled</p>
                                </div>
                            </div>
                            <div class="w-full sm:w-auto sm:text-right flex flex-col gap-2">
                                <div class="inline-flex items-center gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-md w-full sm:w-auto justify-center sm:justify-start">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="font-bold text-sm sm:text-base truncate">{{ $vaccination->vaccine_name ?? 'N/A' }}</span>
                                </div>
                                <p class="text-xs sm:text-sm text-gray-500 font-medium text-center sm:text-right">{{ $vaccination->scheduled_date ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-lg font-semibold text-gray-800 mb-1">No scheduled vaccination days</p>
                    <p class="text-base text-gray-600">No vaccination schedules have been created yet</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Feedback Analysis -->
    <section class="hw-section rounded-2xl bg-gradient-to-br from-white to-primary-50/30 shadow-lg ring-1 ring-primary-200/50 overflow-hidden" data-animate>
        <div class="px-6 py-5 bg-gradient-to-r from-primary-600 to-primary-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center ring-1 ring-white/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h2 class="text-lg md:text-xl font-bold text-white">Feedback Analysis</h2>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-800">Feedback Analysis for <span id="currentPeriod" class="text-primary-700">All Months 2025</span></h3>
                <p id="totalResponses" class="text-base text-gray-600 font-medium">Total Responses: 0</p>
            </div>
            <div class="h-[360px]"><canvas id="feedbackChart"></canvas></div>
        </div>
    </section>
</div>
@endsection

@section('additional-scripts')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sample data for charts if not provided by the controller
            const monthlyData = {!! json_encode($monthlyData ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!};
            const statusData = {!! json_encode($vaccineStatusData ?? [0,0,0]) !!};

            // Animate numbers with counter effect
            function animateValue(element, start, end, duration) {
                const range = end - start;
                const increment = range / (duration / 16);
                let current = start;
                const timer = setInterval(() => {
                    current += increment;
                    if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                        element.textContent = Math.round(end);
                        clearInterval(timer);
                    } else {
                        element.textContent = Math.round(current);
                    }
                }, 16);
            }

            // Calculate and display monthly statistics summary
            const currentMonth = new Date().getMonth();
            const thisMonth = monthlyData[currentMonth] || 0;
            const lastMonth = monthlyData[currentMonth - 1] || 0;
            const change = lastMonth > 0 ? (((thisMonth - lastMonth) / lastMonth) * 100).toFixed(1) : 0;
            
            const thisMonthEl = document.getElementById('thisMonthTotal');
            const lastMonthEl = document.getElementById('lastMonthTotal');
            animateValue(thisMonthEl, 0, thisMonth, 1000);
            animateValue(lastMonthEl, 0, lastMonth, 1000);
            
            const changeEl = document.getElementById('monthlyChange');
            setTimeout(() => {
                if (change > 0) {
                    changeEl.className = 'text-xl font-bold text-emerald-600 animate-count';
                    changeEl.innerHTML = '<span class="inline-flex items-center gap-1"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>+' + change + '%</span>';
                } else if (change < 0) {
                    changeEl.className = 'text-xl font-bold text-rose-600 animate-count';
                    changeEl.innerHTML = '<span class="inline-flex items-center gap-1"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>' + change + '%</span>';
                } else {
                    changeEl.className = 'text-xl font-bold text-gray-600 animate-count';
                    changeEl.innerHTML = '<span class="inline-flex items-center gap-1">→ 0%</span>';
                }
            }, 1000);

            // Monthly chart with gradient
            const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
            const gradientBlue = monthlyCtx.createLinearGradient(0, 0, 0, 400);
            gradientBlue.addColorStop(0, 'rgba(79, 70, 229, 0.8)');
            gradientBlue.addColorStop(1, 'rgba(99, 102, 241, 0.4)');
            
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Vaccinations',
                        data: monthlyData,
                        backgroundColor: gradientBlue,
                        borderWidth: 0,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    const total = monthlyData.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
                                    return context.parsed.y + ' vaccinations (' + percentage + '% of total)';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: { size: 12 },
                                stepSize: 1,
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
            
            // Vaccination status chart with refined colors and populate legend cards
            const statusCtx = document.getElementById('vaccineStatusChart').getContext('2d');
            const total = statusData.reduce((a, b) => a + b, 0);
            
            // Update legend cards with animation
            const completeCountEl = document.getElementById('completeCount');
            const partialCountEl = document.getElementById('partialCount');
            const notStartedCountEl = document.getElementById('notStartedCount');
            
            setTimeout(() => {
                animateValue(completeCountEl, 0, statusData[0] || 0, 1200);
                animateValue(partialCountEl, 0, statusData[1] || 0, 1200);
                animateValue(notStartedCountEl, 0, statusData[2] || 0, 1200);
                
                document.getElementById('completePercent').textContent = total > 0 ? ((statusData[0] / total) * 100).toFixed(1) + '%' : '0%';
                document.getElementById('partialPercent').textContent = total > 0 ? ((statusData[1] / total) * 100).toFixed(1) + '%' : '0%';
                document.getElementById('notStartedPercent').textContent = total > 0 ? ((statusData[2] / total) * 100).toFixed(1) + '%' : '0%';
            }, 200);
            
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Complete', 'Partial', 'Not Started'],
                    datasets: [{
                        data: statusData,
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.85)',
                            'rgba(245, 158, 11, 0.85)',
                            'rgba(239, 68, 68, 0.85)'
                        ],
                        borderColor: [
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return context.label + ': ' + value + ' patients (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
            
            // Vaccination Distribution Chart with gradient and populate stats
            const vaccineLabels = ['BCG', 'HepB', 'Penta', 'OPV', 'IPV', 'PCV', 'Measles'];
            const vaccineFullNames = {
                'BCG': 'Bacillus Calmette-Guérin',
                'HepB': 'Hepatitis B',
                'Penta': 'Pentavalent (DPT-HIB-HepB)',
                'OPV': 'Oral Polio Vaccine',
                'IPV': 'Inactivated Polio Vaccine',
                'PCV': 'Pneumococcal Conjugate Vaccine',
                'Measles': 'Measles, Mumps, Rubella (MMR)'
            };
            // Vaccine schedule information - showing AGE when to give
            const vaccineSchedule = {
                'BCG': {
                    doses: 1,
                    age: 'Bagong Panganak (Newborn)',
                    details: 'Ibigay sa loob ng 24 oras pagkapanganak'
                },
                'HepB': {
                    doses: 1,
                    age: 'Bagong Panganak (Newborn)',
                    details: 'Ibigay sa loob ng 24 oras pagkapanganak'
                },
                'Penta': {
                    doses: 3,
                    age: '1½ - 3½ buwan gulang (6-14 weeks old)',
                    details: '1st: 1½ buwan • 2nd: 2½ buwan • 3rd: 3½ buwan'
                },
                'OPV': {
                    doses: 3,
                    age: '1½ - 3½ buwan gulang (6-14 weeks old)',
                    details: '1st: 1½ buwan • 2nd: 2½ buwan • 3rd: 3½ buwan'
                },
                'IPV': {
                    doses: 2,
                    age: '3½ buwan at 9 buwan gulang',
                    details: '1st: 3½ buwan (14 weeks) • 2nd: 9 buwan'
                },
                'PCV': {
                    doses: 3,
                    age: '1½ - 3½ buwan gulang (6-14 weeks old)',
                    details: '1st: 1½ buwan • 2nd: 2½ buwan • 3rd: 3½ buwan'
                },
                'Measles': {
                    doses: 2,
                    age: '9 buwan at 12-15 buwan gulang',
                    details: '1st: 9 buwan • 2nd: 12-15 buwan'
                }
            };
            const vaccineDistData = {!! json_encode($vaccineDistData ?? [65,59,80,81,56,55,40]) !!};
            
            // Calculate and display distribution stats
            const maxValue = Math.max(...vaccineDistData);
            const minValue = Math.min(...vaccineDistData);
            const maxIndex = vaccineDistData.indexOf(maxValue);
            const minIndex = vaccineDistData.indexOf(minValue);
            const average = (vaccineDistData.reduce((a, b) => a + b, 0) / vaccineDistData.length).toFixed(0);
            
            document.getElementById('mostAdministered').textContent = vaccineLabels[maxIndex];
            document.getElementById('mostAdministeredCount').textContent = maxValue + ' doses';
            document.getElementById('leastAdministered').textContent = vaccineLabels[minIndex];
            document.getElementById('leastAdministeredCount').textContent = minValue + ' doses';
            document.getElementById('averagePerVaccine').textContent = average;
            
            const vaccineDistCtx = document.getElementById('vaccinationDistChart').getContext('2d');
            const gradientPurple = vaccineDistCtx.createLinearGradient(0, 0, 0, 400);
            gradientPurple.addColorStop(0, 'rgba(122, 91, 189, 0.85)');
            gradientPurple.addColorStop(1, 'rgba(155, 125, 212, 0.4)');
            
            new Chart(vaccineDistCtx, {
                type: 'bar',
                data: {
                    labels: vaccineLabels,
                    datasets: [{
                        label: 'Vaccinations',
                        data: vaccineDistData,
                        backgroundColor: gradientPurple,
                        borderColor: 'rgba(122, 91, 189, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            padding: 14,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 12 },
                            footerFont: { size: 11, style: 'italic' },
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    const label = context[0].label;
                                    const fullName = vaccineFullNames[label] || label;
                                    return fullName + ' (' + label + ')';
                                },
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const total = vaccineDistData.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return value + ' doses (' + percentage + '% of total)';
                                },
                                afterLabel: function(context) {
                                    const label = context.label;
                                    const schedule = vaccineSchedule[label];
                                    if (schedule) {
                                        return [
                                            '',
                                            '👶 Edad: ' + schedule.age,
                                            '💉 Doses: ' + schedule.doses,
                                            '📋 ' + schedule.details
                                        ];
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: { size: 12 },
                                stepSize: 1,
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });

            // Initialize feedback chart with empty data
            const feedbackCtx = document.getElementById('feedbackChart');
            
            // Define the full questions (shown on hover/tooltip)
            const questions = [
                'MADALI BA NINYONG NAHANAP ANG HEALTH CENTER AT MAY NAKITA BA KAYONG KARATULA NG DIREKSYON?',
                'MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?',
                'MAGALANG, MABAIT, AT MAAYOS BA ANG SERBISYO NG HEALTH WORKER NA NAGBIGAY NG BAKUNA?',
                'NAIPALIWANAG BA SA INYO NG HEALTH WORKER ANG TUNGKOL SA BAKUNA AT KAILAN ANG SUSUNOD NA SCHEDULE?',
                'NASIYAHAN BA KAYO SA KABUUANG SERBISYO NG HEALTH CENTER SA ARAW NA ITO?'
            ];
            
            // Define the shortened questions (shown as X-axis labels)
            const shortenedQuestions = [
                'Q1: Madali ninyong nahanap ang Health Center?',
                'Q2: Malinis ba ang tanggapan?',
                'Q3: Magalang ba ang Health Worker?',
                'Q4: Naipaliwanag ba ang bakuna?',
                'Q5: Nasiyahan ba sa serbisyo?'
            ];
            
            let feedbackChart = new Chart(feedbackCtx, {
                type: 'bar',
                data: {
                    labels: shortenedQuestions,
                    datasets: [
                        {
                            label: 'OO',
                            data: [0, 0, 0, 0, 0],
                            backgroundColor: 'rgba(75, 192, 112, 0.8)',
                            borderColor: 'rgb(75, 192, 112)',
                            borderWidth: 1,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        },
                        {
                            label: 'HINDI',
                            data: [0, 0, 0, 0, 0],
                            backgroundColor: 'rgba(255, 99, 132, 0.8)',
                            borderColor: 'rgb(255, 99, 132)',
                            borderWidth: 1,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(tooltipItems) {
                                    const index = tooltipItems[0].dataIndex;
                                    return questions[index];
                                },
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15
                            }
                        }
                    },
                    onHover: (event, elements, chart) => {
                        // Show cursor as pointer when hovering over bars
                        if (event && event.native && event.native.target) {
                            event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                        }
                        
                        // Show tooltip for the question
                        if (elements.length) {
                            const index = elements[0].index;
                            const datasetIndex = elements[0].datasetIndex;
                            const value = chart.data.datasets[datasetIndex].data[index];
                            
                            // Create tooltip element if it doesn't exist
                            let tooltip = document.getElementById('custom-tooltip');
                            if (!tooltip) {
                                tooltip = document.createElement('div');
                                tooltip.id = 'custom-tooltip';
                                tooltip.style.position = 'absolute';
                                tooltip.style.padding = '8px 12px';
                                tooltip.style.background = 'rgba(0,0,0,0.8)';
                                tooltip.style.color = 'white';
                                tooltip.style.borderRadius = '4px';
                                tooltip.style.pointerEvents = 'none';
                                tooltip.style.zIndex = 100;
                                tooltip.style.fontSize = '14px';
                                tooltip.style.transform = 'translate(-50%, -100%)';
                                document.body.appendChild(tooltip);
                            }
                            
                            // Update tooltip content and position
                            tooltip.innerHTML = `${questions[index]}<br>${chart.data.datasets[datasetIndex].label}: ${value}%`;
                            tooltip.style.left = elements[0].element.x + 'px';
                            tooltip.style.top = (elements[0].element.y - 10) + 'px';
                            tooltip.style.display = 'block';
                        } else {
                            // Hide tooltip when not hovering over bars
                            const tooltip = document.getElementById('custom-tooltip');
                            if (tooltip) tooltip.style.display = 'none';
                        }
                    }
                }
            });

            // Function to fetch feedback analytics data
            function fetchFeedbackAnalytics(month, year, barangay) {
                const params = new URLSearchParams();
                if (month) params.append('month', month);
                if (year) params.append('year', year);
                if (barangay) params.append('barangay', barangay);
                const url = `/api/feedback/analytics?${params.toString()}`;

                fetch(url, { headers: { 'Accept': 'application/json' } })
                    .then(r => {
                        if (!r.ok) throw new Error(`HTTP ${r.status}`);
                        return r.json();
                    })
                    .then(data => updateFeedbackChart(data))
                    .catch(err => {
                        console.error('Error fetching feedback analytics:', err);
                    });
            }

            // Function to update the feedback chart with data
            function updateFeedbackChart(data) {
                if (!data || !data.analysis) return;
                
                // Extract data for the chart
                const positiveData = [
                    data.analysis.Q1.OO_percent || 0,
                    data.analysis.Q2.OO_percent || 0,
                    data.analysis.Q3.OO_percent || 0,
                    data.analysis.Q4.OO_percent || 0,
                    data.analysis.Q5.OO_percent || 0
                ];
                
                const negativeData = [
                    data.analysis.Q1.HINDI_percent || 0,
                    data.analysis.Q2.HINDI_percent || 0,
                    data.analysis.Q3.HINDI_percent || 0,
                    data.analysis.Q4.HINDI_percent || 0,
                    data.analysis.Q5.HINDI_percent || 0
                ];
                
                // Update chart data
                feedbackChart.data.datasets[0].data = positiveData;
                feedbackChart.data.datasets[1].data = negativeData;
                feedbackChart.update();
                                
                // Update the title and total responses
                const currentPeriod = document.getElementById('currentPeriod');
                const totalResponses = document.getElementById('totalResponses');
                if (currentPeriod) currentPeriod.textContent = data.current_month || 'Current Month';
                if (totalResponses) totalResponses.textContent = 'Total Responses: ' + (data.total_responses || 0);
                
                // Update the month selector with available months
                if (data.available_months && data.available_months.length > 0) {
                    updateMonthSelector(data.available_months);
                }
            }
            
            // Function to update the month selector with available months
            function updateMonthSelector(availableMonths) {
                const monthSelector = document.getElementById('feedbackMonth');
                if (!monthSelector) return;
                const keepFirst = monthSelector.options[0];
                monthSelector.innerHTML = '';
                monthSelector.appendChild(keepFirst);
                availableMonths.forEach(month => {
                    const opt = document.createElement('option');
                    opt.value = month.value;
                    opt.textContent = month.label;
                    monthSelector.appendChild(opt);
                });
            }
            
            // Handle filter changes
            function onFiltersChange() {
                let selectedMonth = null;
                let selectedYear = null;
                const monthEl = document.getElementById('feedbackMonth');
                const barangayEl = document.getElementById('feedbackBarangay');
                const selectedMonthValue = monthEl ? monthEl.value : '';
                if (selectedMonthValue && selectedMonthValue.includes('-')) {
                    const [year, month] = selectedMonthValue.split('-');
                    selectedMonth = month;
                    selectedYear = year;
                } else if (selectedMonthValue) {
                    selectedMonth = selectedMonthValue;
                    selectedYear = new Date().getFullYear();
                }
                const selectedBarangay = barangayEl ? barangayEl.value : '';
                fetchFeedbackAnalytics(selectedMonth, selectedYear, selectedBarangay);
            }
            const monthEl = document.getElementById('feedbackMonth');
            const barangayEl = document.getElementById('feedbackBarangay');
            if (monthEl) monthEl.addEventListener('change', onFiltersChange);
            if (barangayEl) barangayEl.addEventListener('change', onFiltersChange);
            
            // Initialize by loading current month's data
            fetchFeedbackAnalytics();
        });
    </script>
@endsection