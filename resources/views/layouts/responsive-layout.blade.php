<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192x192.png') }}">
    
    @yield('head')
    <title>@yield('title', 'Health Worker')</title>
    
    <!-- PWA Manifest - DISABLED for Health Worker (To re-enable: uncomment these lines) -->
    {{-- 
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#7a5bbd">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Infant Vaccination">
    <link rel="apple-touch-icon" href="{{ asset('images/todoligtass.png') }}">
    --}}
    
    <link href="{{ versioned_asset('css/tailwind-full.css') }}" rel="stylesheet">
    <script src="{{ versioned_asset('javascript/logout-helper.js') }}"></script>
    <script src="{{ versioned_asset('javascript/session-guard.js') }}" defer></script>
    @yield('additional-styles')
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <div id="app" class="min-h-screen">
        <!-- Off-canvas overlay -->
        <div id="overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out bg-primary-600 text-white shadow-xl overflow-y-auto flex flex-col">
            <div class="h-44 sm:h-40 lg:h-48 px-5 pt-10 sm:pt-8 lg:pt-12 pb-4 flex flex-col items-center justify-center gap-2 sm:gap-2 border-b border-white/10 flex-shrink-0">
                <img src="{{ asset('images/todoligtass.png') }}" alt="Logo" class="h-24 sm:h-20 lg:h-32 w-auto object-contain select-none">
                @php
                    $sidebarHealthWorker = Auth::guard('health_worker')->user();
                    $sidebarTitle = 'Health Worker';
                    if ($sidebarHealthWorker) {
                        if ($sidebarHealthWorker->isRHU()) {
                            $sidebarTitle = 'RHU Admin';
                        } elseif ($sidebarHealthWorker->barangay) {
                            $sidebarTitle = 'Brgy. ' . $sidebarHealthWorker->barangay->name;
                        }
                    }
                @endphp
                <div class="text-lg sm:text-lg lg:text-xl font-extrabold text-white whitespace-nowrap pb-3">{{ $sidebarTitle }}</div>
            </div>
            <nav class="p-5 sm:p-4 lg:p-5 mt-4 sm:mt-4 lg:mt-6 flex flex-col gap-3 sm:gap-2 flex-1 overflow-y-auto pb-4">
                <a href="{{ route('health_worker.dashboard') }}" aria-current="{{ request()->routeIs('health_worker.dashboard') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-4 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.dashboard')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-7 h-7 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.dashboard')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="font-semibold text-lg sm:text-base lg:text-lg whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('health_worker.patients') }}" aria-current="{{ request()->routeIs('health_worker.patients') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-4 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.patients')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-7 h-7 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.patients')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                    <span class="font-semibold text-lg sm:text-base lg:text-lg whitespace-nowrap">Patient List</span>
                </a>
                <a href="{{ route('vaccination_schedule.index') }}" aria-current="{{ request()->routeIs('vaccination_schedule.index') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-4 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('vaccination_schedule.index')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-7 h-7 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('vaccination_schedule.index')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold text-lg sm:text-base lg:text-lg whitespace-nowrap">Vaccination Schedule</span>
                </a>
                <a href="{{ route('health_worker.vaccination_status') }}" aria-current="{{ request()->routeIs('health_worker.vaccination_status') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-4 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.vaccination_status')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-7 h-7 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.vaccination_status')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold text-lg sm:text-base lg:text-lg whitespace-nowrap">Vaccination Status</span>
                </a>
                <a href="{{ route('health_worker.inventory') }}" aria-current="{{ request()->routeIs('health_worker.inventory') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-4 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.inventory')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-7 h-7 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.inventory')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                    </svg>
                    <span class="font-semibold text-lg sm:text-base lg:text-lg whitespace-nowrap">Inventory</span>
                </a>
                <a href="{{ route('health_worker.feedback') }}" aria-current="{{ request()->routeIs('health_worker.feedback') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-4 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.feedback')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-7 h-7 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.feedback')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                    </svg>
                    <span class="font-semibold text-lg sm:text-base lg:text-lg whitespace-nowrap">Feedback</span>
                </a>
                
                @php
                    $currentHealthWorker = Auth::guard('health_worker')->user();
                    $isRHUAdmin = !$currentHealthWorker || !$currentHealthWorker->barangay_id;
                @endphp
                
                @if($isRHUAdmin)
                <a href="{{ route('backup.index') }}" aria-current="{{ request()->routeIs('backup.index') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-3.5 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('backup.index')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('backup.index')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <span class="font-semibold text-base sm:text-base lg:text-lg whitespace-nowrap">Backup & Restore</span>
                </a>
                
                <!-- Activity Log - RHU Only -->
                <a href="{{ route('activity-log.index') }}" aria-current="{{ request()->routeIs('activity-log.*') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-3.5 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('activity-log.*')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('activity-log.*')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="font-semibold text-base sm:text-base lg:text-lg whitespace-nowrap">Activity Log</span>
                </a>
                
                <!-- Account Management - RHU Only -->
                <a href="{{ route('health_worker.accounts.index') }}" aria-current="{{ request()->routeIs('health_worker.accounts.*') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-3.5 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.accounts.*')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.accounts.*')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="font-semibold text-base sm:text-base lg:text-lg whitespace-nowrap">Account Management</span>
                </a>

                <!-- Vaccine Settings - RHU Only -->
                <a href="{{ route('health_worker.vaccine-settings.index') }}" aria-current="{{ request()->routeIs('health_worker.vaccine-settings.*') ? 'page' : 'false' }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-3.5 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.vaccine-settings.*')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                    <svg class="w-6 h-6 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.vaccine-settings.*')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="font-semibold text-base sm:text-base lg:text-lg whitespace-nowrap">Vaccine Settings</span>
                </a>
                
                <!-- Reports Dropdown Menu - RHU Only -->
                <div class="space-y-1">
                    <button id="reportsToggle" type="button" class="group w-full flex items-center justify-between gap-4 sm:gap-3 px-4 sm:px-4 py-3.5 sm:py-2.5 lg:py-3 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.report') || request()->routeIs('reports.current') || request()->routeIs('reports.history')) bg-white text-primary-600 shadow-xl ring-2 ring-white font-bold border-l-4 border-primary-600 @else text-white/90 hover:text-white hover:bg-white/20 hover:ring-2 hover:ring-white/60 hover:shadow-lg hover:border-l-4 hover:border-white @endif">
                        <div class="flex items-center gap-4 sm:gap-3">
                            <svg class="w-6 h-6 sm:w-5 sm:h-5 lg:w-6 lg:h-6 transition-all duration-200 flex-shrink-0 @if(request()->routeIs('health_worker.report') || request()->routeIs('reports.current') || request()->routeIs('reports.history')) text-primary-600 @else text-gray-800 group-hover:text-white @endif" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                            </svg>
                            <span class="font-semibold text-base sm:text-base lg:text-lg whitespace-nowrap">Reports</span>
                        </div>
                        <svg id="reportsArrow" style="transform: @if(request()->routeIs('health_worker.report') || request()->routeIs('reports.current') || request()->routeIs('reports.history')) rotate(180deg) @else rotate(0deg) @endif; transition: transform 0.3s ease;" class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Submenu -->
                    <div id="reportsSubmenu" class="space-y-1 pl-4 sm:pl-4 @if(!request()->routeIs('health_worker.report') && !request()->routeIs('reports.current') && !request()->routeIs('reports.history')) hidden @endif">
                        <a href="{{ route('reports.current') }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-3 sm:py-2 rounded-xl transition-all duration-200 @if(request()->routeIs('health_worker.report') || request()->routeIs('reports.current')) bg-white/20 text-white font-semibold border-l-2 border-white @else text-white/80 hover:text-white hover:bg-white/10 hover:border-l-2 hover:border-white/60 @endif">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="text-sm sm:text-sm whitespace-nowrap">Current Report</span>
                        </a>
                        <a href="{{ route('reports.history') }}" class="group flex items-center gap-4 sm:gap-3 px-4 sm:px-4 py-3 sm:py-2 rounded-xl transition-all duration-200 @if(request()->routeIs('reports.history')) bg-white/20 text-white font-semibold border-l-2 border-white @else text-white/80 hover:text-white hover:bg-white/10 hover:border-l-2 hover:border-white/60 @endif">
                            <svg class="w-5 h-5 sm:w-4 sm:h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-sm sm:text-sm whitespace-nowrap">Report History</span>
                        </a>
                    </div>
                </div>
                @endif
            </nav>
            {{-- <div class="p-3 sm:p-4 mt-auto flex-shrink-0 bg-primary-700/30">
                <button id="logoutOpen" type="button" class="group w-full flex items-center gap-3 px-3 sm:px-4 py-2 sm:py-2.5 lg:py-3 rounded-xl bg-white/10 text-white/90 hover:bg-red-600 hover:text-white hover:shadow-lg transition-all duration-200">
                    <svg class="w-5 h-5 sm:w-5 sm:h-5 lg:w-6 lg:h-6 text-white transition-all duration-200 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v5a1 1 0 01-2 0V4H5v12h9v-4a1 1 0 112 0v5a1 1 0 01-1 1H4a1 1 0 01-1-1V3z" clip-rule="evenodd"/>
                        <path fill-rule="evenodd" d="M13.293 9.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L14.586 14H7a1 1 0 110-2h7.586l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-semibold text-sm sm:text-base lg:text-lg whitespace-nowrap">Logout</span>
                </button>
            </div> --}}
        </aside>

        <!-- Main -->
    <div class="lg:ml-72 min-h-screen flex flex-col">
            <!-- Header -->
            <header class="sticky top-0 z-30 bg-white border-b border-gray-200 shadow-sm">
                <div class="h-16 px-4 sm:px-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button id="menuBtn" class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-xl bg-primary-100 hover:bg-primary-200 transition-colors">
                            <img src="{{ asset('images/menu.png') }}" alt="Menu" class="h-5 w-5 brightness-0 opacity-70">
                        </button>
                        
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Notification Dropdown - DISABLED (To re-enable: uncomment this section) -->
                        {{-- 
                        <div class="relative" id="notificationDropdown">
                            <button id="notificationBtn" class="relative h-10 w-10 rounded-xl bg-primary-100 hover:bg-primary-200 flex items-center justify-center transition-colors" aria-label="Notifications">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span id="notifBadge" class="absolute -top-1 -right-1 h-4 w-4 rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center shadow-sm hidden">0</span>
                            </button>
                            <!-- Notification Dropdown Menu -->
                            <div id="notificationMenu" class="hidden fixed sm:absolute left-2 right-2 sm:left-auto sm:right-0 top-16 sm:top-full mt-0 sm:mt-2 w-auto sm:w-96 bg-white rounded-xl shadow-2xl ring-1 ring-gray-200 overflow-hidden z-50 max-h-[85vh] sm:max-h-[32rem]">
                                <div class="px-4 sm:px-4 py-4 sm:py-3 border-b border-gray-200 bg-primary-50">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-base sm:text-sm font-semibold text-gray-800">Notifications</h3>
                                        <button id="markAllReadBtn" class="text-sm sm:text-xs text-primary-600 hover:text-primary-700 font-medium whitespace-nowrap">Mark all as read</button>
                                    </div>
                                </div>
                                <div id="notificationList" class="max-h-[calc(85vh-8rem)] sm:max-h-96 overflow-y-auto">
                                    <div class="p-12 sm:p-8 text-center text-gray-500">
                                        <svg class="w-16 h-16 sm:w-12 sm:h-12 mx-auto mb-3 sm:mb-2 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155 6.852a3 3 0 005.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 003.536-1.003A8.967 8.967 0 0118 9.75V9A6 6 0 006.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53"/>
                                        </svg>
                                        <p class="text-base sm:text-sm">No notifications</p>
                                    </div>
                                </div>
                                <div class="px-4 sm:px-4 py-3 sm:py-2 border-t border-gray-200 bg-gray-50">
                                    <a href="#" class="text-sm sm:text-xs text-primary-600 hover:text-primary-700 font-medium">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        --}}

                        <!-- Profile dropdown (enhanced styling) -->
                        <div class="relative" id="hwProfile">
                            <button id="profileBtn" class="flex items-center gap-3 bg-primary-100 hover:bg-primary-200 rounded-xl pl-2 pr-3 py-1.5 transition-colors">
                                <img src="{{ asset('images/user (1).png') }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-primary-200" alt="Profile">
                                <img src="{{ asset('images/arrow-down-sign-to-navigate.png') }}" class="w-3.5 h-3.5 brightness-0 opacity-70" alt="Expand">
                            </button>
                            <div id="profileMenu" class="hidden absolute right-0 top-full mt-2 w-64 sm:w-56 bg-white rounded-xl shadow-2xl ring-1 ring-gray-200 overflow-hidden z-50">
                                <div class="px-4 py-3 border-b border-gray-200">
                                    @php
                                        $profileHealthWorker = Auth::guard('health_worker')->user();
                                        $profileRoleLabel = 'Health Worker';
                                        if ($profileHealthWorker) {
                                            if ($profileHealthWorker->isRHU()) {
                                                $profileRoleLabel = 'RHU Admin';
                                            } elseif ($profileHealthWorker->barangay) {
                                                $profileRoleLabel = 'Brgy. ' . $profileHealthWorker->barangay->name;
                                            }
                                        }
                                    @endphp
                                    <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ $profileRoleLabel }}</p>
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->name ?? auth()->user()->username ?? 'Health Worker' }}</p>
                                    @if(auth()->user() && auth()->user()->email)
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    @endif
                                </div>
                                <nav class="py-1 text-sm text-gray-700">
                                    <a href="{{ route('health_worker.profile') }}" class="block w-full text-left px-4 py-2 hover:bg-gray-50">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            Profile Settings
                                        </span>
                                    </a>
                                    <button id="logoutOpenFromMenu" class="w-full text-left px-4 py-2 hover:bg-gray-50">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Logout
                                        </span>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 px-4 sm:px-6 py-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Change Password Modal (parent dashboard style) -->
    <div id="changePwModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div id="changePwOverlay" class="absolute inset-0" style="background-color: rgba(107, 114, 128, 0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"></div>
        <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl ring-1 ring-gray-200 p-7 sm:p-8">
            <div class="flex items-center gap-2 mb-4">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary-50 text-primary-600 ring-1 ring-primary-200">
                    <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12c2.761 0 5-2.462 5-5.5S14.761 1 12 1 7 3.462 7 6.5 9.239 12 12 12z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 21c0-3.313 3.582-6 8-6s8 2.687 8 6"/></svg>
                </span>
                <h3 class="text-xl font-semibold text-gray-800">Update Your Password</h3>
            </div>
            <form id="changePwForm" method="POST" action="{{ route('health_worker.update-password') }}" class="space-y-6" novalidate>
                @csrf
                <!-- Current Password -->
                <div class="space-y-2">
                    <label for="current_password" class="text-sm font-medium text-gray-700">Current Password</label>
                    <div class="relative">
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none pr-12" />
                        <button type="button" data-toggle="current_password" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-600 transition" aria-label="Show password">
                            <svg class="w-5 h-5 toggle-icon-hide" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="w-5 h-5 hidden toggle-icon-show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.81 21.81 0 014.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.83 21.83 0 01-2.16 3.19M14.12 14.12a3 3 0 01-4.24-4.24"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                </div>

                <!-- New Password -->
                <div class="space-y-2">
                    <label for="new_password" class="text-sm font-medium text-gray-700">New Password</label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password" required autocomplete="new-password" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none pr-12" aria-describedby="passwordHelp" />
                        <button type="button" data-toggle="new_password" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-600 transition" aria-label="Show password">
                            <svg class="w-5 h-5 toggle-icon-hide" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="w-5 h-5 hidden toggle-icon-show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.81 21.81 0 014.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.83 21.83 0 01-2.16 3.19M14.12 14.12a3 3 0 01-4.24-4.24"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1l22 22"/></svg>
                        </button>
                    </div>

                    <!-- Requirements -->
                    <div id="passwordHelp" class="mt-4 bg-gray-50 rounded-xl p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 mb-3">Must include</p>
                        <ul class="space-y-2 text-xs" id="requirementsList">
                            <li data-req="length" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 8 characters</span>
                            </li>
                            <li data-req="uppercase" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 uppercase letter</span>
                            </li>
                            <li data-req="lowercase" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 lowercase letter</span>
                            </li>
                            <li data-req="number" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 number</span>
                            </li>
                            <li data-req="special" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 special character (@#$%^&*()_+-=[]{}|;:,.<>?)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Confirm -->
                <div class="space-y-2">
                    <label for="new_password_confirmation" class="text-sm font-medium text-gray-700">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required autocomplete="new-password" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none pr-12" />
                        <button type="button" data-toggle="new_password_confirmation" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-600 transition" aria-label="Show password">
                            <svg class="w-5 h-5 toggle-icon-hide" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="w-5 h-5 hidden toggle-icon-show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.81 21.81 0 014.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.83 21.83 0 01-2.16 3.19M14.12 14.12a3 3 0 01-4.24-4.24"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                    <p id="confirmError" class="text-xs font-medium text-red-600 min-h-[1rem]"></p>
                </div>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-end gap-4">
                    <button type="button" id="changePwCancel" class="inline-flex items-center gap-2 rounded-xl border border-primary-300 text-primary-700 bg-white hover:bg-primary-50 hover:border-primary-400 active:scale-[.97] text-sm font-semibold px-5 h-11 shadow-sm transition">Cancel</button>
                    <button type="submit" id="changePasswordBtn" disabled class="inline-flex items-center gap-2 rounded-xl bg-primary-600 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-primary-700 active:scale-[.97] text-white text-sm font-semibold px-6 h-11 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Email Modal -->
    <div id="changeEmailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div id="changeEmailOverlay" class="absolute inset-0" style="background-color: rgba(107, 114, 128, 0.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);"></div>
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl ring-1 ring-gray-200 p-7 sm:p-8">
            <div class="flex items-center gap-2 mb-4">
                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-primary-50 text-primary-600 ring-1 ring-primary-200">
                    <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <h3 class="text-xl font-semibold text-gray-800">Update Your Email</h3>
            </div>
            <form id="changeEmailForm" method="POST" action="{{ route('health_worker.update-email') }}" class="space-y-5" novalidate>
                @csrf
                <!-- Current Email Display -->
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">Current Email</label>
                    <div class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 h-12 text-sm flex items-center text-gray-600">
                        {{ auth()->user()->email ?? 'No email set' }}
                    </div>
                </div>
                
                <!-- New Email -->
                <div class="space-y-2">
                    <label for="new_email" class="text-sm font-medium text-gray-700">New Email <span class="text-red-500">*</span></label>
                    <input type="email" id="new_email" name="new_email" required autocomplete="email" placeholder="Enter new email address (e.g., name@gmail.com)" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none" />
                    <p id="emailError" class="text-xs font-medium text-red-600 min-h-[1rem]"></p>
                </div>

                <!-- Password Confirmation -->
                <div class="space-y-2">
                    <label for="email_password" class="text-sm font-medium text-gray-700">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="email_password" name="password" required autocomplete="current-password" placeholder="Enter your password to confirm" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none pr-12" />
                        <button type="button" data-toggle="email_password" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-600 transition" aria-label="Show password">
                            <svg class="w-5 h-5 toggle-icon-hide" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="w-5 h-5 hidden toggle-icon-show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.81 21.81 0 014.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.83 21.83 0 01-2.16 3.19M14.12 14.12a3 3 0 01-4.24-4.24"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-end gap-4">
                    <button type="button" id="changeEmailCancel" class="inline-flex items-center gap-2 rounded-xl border border-primary-300 text-primary-700 bg-white hover:bg-primary-50 hover:border-primary-400 active:scale-[.97] text-sm font-semibold px-5 h-11 shadow-sm transition">Cancel</button>
                    <button type="submit" id="changeEmailBtn" disabled class="inline-flex items-center gap-2 rounded-xl bg-primary-600 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-primary-700 active:scale-[.97] text-white text-sm font-semibold px-6 h-11 shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Update Email
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl ring-1 ring-black/10 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Logout</h3>
            <p class="text-sm text-gray-600">Are you sure you want to log out?</p>
            <div class="mt-6 flex justify-end gap-3">
                <button id="logoutCancel" class="px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 text-gray-700">Cancel</button>
                <button id="logoutConfirm" class="px-4 py-2 rounded-lg text-sm font-semibold bg-red-600 hover:bg-red-700 text-white">Logout</button>
            </div>
        </div>
    </div>

    <!-- Hidden forms -->
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

    @yield('additional-scripts')

    <script>
    (function(){
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const menuBtn = document.getElementById('menuBtn');
        function openSidebar(){
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.documentElement.style.overflow = 'hidden';
        }
        function closeSidebar(){
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.documentElement.style.overflow = '';
        }
        menuBtn?.addEventListener('click', openSidebar);
        overlay?.addEventListener('click', closeSidebar);

        // Reports dropdown toggle
        const reportsToggle = document.getElementById('reportsToggle');
        const reportsSubmenu = document.getElementById('reportsSubmenu');
        const reportsArrow = document.getElementById('reportsArrow');
        
        reportsToggle?.addEventListener('click', (e) => {
            e.preventDefault();
            const isHidden = reportsSubmenu?.classList.contains('hidden');
            reportsSubmenu?.classList.toggle('hidden');
            
            // Toggle arrow rotation using inline style
            if (reportsArrow) {
                if (isHidden) {
                    reportsArrow.style.transform = 'rotate(180deg)';
                } else {
                    reportsArrow.style.transform = 'rotate(0deg)';
                }
            }
        });

        // Profile dropdown
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        profileBtn?.addEventListener('click', (e)=>{
            e.stopPropagation();
            profileMenu?.classList.toggle('hidden');
        });
        window.addEventListener('click', (e)=>{
            if(!profileMenu?.classList.contains('hidden') && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)){
                profileMenu.classList.add('hidden');
            }
        });

        // Change password modal
        const changePwModal = document.getElementById('changePwModal');
        document.getElementById('changePwOpen')?.addEventListener('click', ()=>{
            profileMenu?.classList.add('hidden');
            changePwModal.classList.remove('hidden');
            changePwModal.classList.add('flex');
        });
        document.getElementById('changePwCancel')?.addEventListener('click', ()=>{
            changePwModal.classList.add('hidden');
            changePwModal.classList.remove('flex');
        });
        document.getElementById('changePwOverlay')?.addEventListener('click', ()=>{
            changePwModal.classList.add('hidden');
            changePwModal.classList.remove('flex');
        });

        // Password visibility toggles (parent style)
        document.querySelectorAll('[data-toggle]')?.forEach(btn=>{
            btn.addEventListener('click',()=>{
                const id=btn.getAttribute('data-toggle');
                const input=document.getElementById(id);
                if(!input) return;
                const show=btn.querySelector('.toggle-icon-show');
                const hide=btn.querySelector('.toggle-icon-hide');
                const isHidden=input.type==='password';
                input.type=isHidden?'text':'password';
                show?.classList.toggle('hidden',!isHidden);
                hide?.classList.toggle('hidden',isHidden);
                btn.setAttribute('aria-label', isHidden? 'Hide password':'Show password');
            });
        });

        // Realtime password requirements (parent parity)
        const form = document.getElementById('changePwForm');
        const newPw = document.getElementById('new_password');
        const confirmPw = document.getElementById('new_password_confirmation');
        const confirmError = document.getElementById('confirmError');
        const submitBtn = document.getElementById('changePasswordBtn');
        const reqItems = [...document.querySelectorAll('#requirementsList [data-req]')];

        function evaluate(){
            if(!newPw || !confirmPw || !submitBtn) return true;
            const v=newPw.value||'';
            const checks={
                length: v.length>=8,
                uppercase: /[A-Z]/.test(v),
                lowercase: /[a-z]/.test(v),
                number: /\d/.test(v),
                special: /[@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(v)
            };
            reqItems.forEach(li=>{
                const key=li.getAttribute('data-req');
                const ok=!!checks[key];
                li.classList.toggle('text-gray-500',!ok);
                li.classList.toggle('text-emerald-600',ok);
                const icon=li.querySelector('svg');
                if(icon) icon.classList.toggle('opacity-0',!ok);
                const badge=li.querySelector('.status');
                badge?.classList.toggle('ring-emerald-300',ok);
                badge?.classList.toggle('bg-emerald-50',ok);
            });
            const match = confirmPw.value.length? v===confirmPw.value : true;
            if(confirmError) confirmError.textContent = confirmPw.value.length && !match ? 'Passwords do not match.' : '';
            const allOk = Object.values(checks).every(Boolean) && match;
            submitBtn.disabled = !allOk;
            return allOk;
        }
        const debounce=(fn,ms=120)=>{let t;return (...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms);}};
        newPw?.addEventListener('input',debounce(evaluate));
        confirmPw?.addEventListener('input',debounce(evaluate));
        form?.addEventListener('submit',e=>{ if(!evaluate()) e.preventDefault(); });

        // Change email modal
        const changeEmailModal = document.getElementById('changeEmailModal');
        const newEmailInput = document.getElementById('new_email');
        const emailPasswordInput = document.getElementById('email_password');
        const emailError = document.getElementById('emailError');
        const changeEmailBtn = document.getElementById('changeEmailBtn');
        
        document.getElementById('changeEmailOpen')?.addEventListener('click', ()=>{
            profileMenu?.classList.add('hidden');
            changeEmailModal.classList.remove('hidden');
            changeEmailModal.classList.add('flex');
        });
        document.getElementById('changeEmailCancel')?.addEventListener('click', ()=>{
            changeEmailModal.classList.add('hidden');
            changeEmailModal.classList.remove('flex');
            // Reset form
            document.getElementById('changeEmailForm')?.reset();
            if(emailError) emailError.textContent = '';
            if(changeEmailBtn) changeEmailBtn.disabled = true;
        });
        document.getElementById('changeEmailOverlay')?.addEventListener('click', ()=>{
            changeEmailModal.classList.add('hidden');
            changeEmailModal.classList.remove('flex');
        });

        // Email validation
        function validateEmail() {
            if(!newEmailInput || !emailPasswordInput || !changeEmailBtn) return false;
            const email = newEmailInput.value.trim();
            const password = emailPasswordInput.value;
            // Stricter email regex: requires valid format with proper domain (at least 2 chars after last dot)
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            let isValid = true;
            if(email && !emailRegex.test(email)) {
                if(emailError) {
                    emailError.textContent = 'Please enter a valid email (e.g., name@gmail.com)';
                    newEmailInput.classList.add('border-red-400');
                    newEmailInput.classList.remove('border-gray-300');
                }
                isValid = false;
            } else {
                if(emailError) {
                    emailError.textContent = '';
                    newEmailInput.classList.remove('border-red-400');
                    newEmailInput.classList.add('border-gray-300');
                }
            }
            
            // Enable button only if email is valid and password is entered
            const canSubmit = isValid && email.length > 0 && password.length > 0 && emailRegex.test(email);
            changeEmailBtn.disabled = !canSubmit;
            return canSubmit;
        }
        
        newEmailInput?.addEventListener('input', debounce(validateEmail));
        emailPasswordInput?.addEventListener('input', debounce(validateEmail));
        document.getElementById('changeEmailForm')?.addEventListener('submit', e => { if(!validateEmail()) e.preventDefault(); });

        // Logout modal
        const logoutModal = document.getElementById('logoutModal');
        const logoutOpen = document.getElementById('logoutOpen');
        const logoutOpenFromMenu = document.getElementById('logoutOpenFromMenu');
        const logoutCancel = document.getElementById('logoutCancel');
        const logoutConfirm = document.getElementById('logoutConfirm');

        function openLogout(){
            profileMenu?.classList.add('hidden');
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
        }
        logoutOpen?.addEventListener('click', openLogout);
        logoutOpenFromMenu?.addEventListener('click', openLogout);
        logoutCancel?.addEventListener('click', ()=>{
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
        });
        logoutConfirm?.addEventListener('click', ()=>{
            // Mark user as logged out for session guard
            if (window.markUserLoggedOut) {
                window.markUserLoggedOut();
            }
            // Submit logout form
            document.getElementById('logoutForm')?.submit();
        });
    })();
    </script>

    <!-- Notification System - DISABLED (To re-enable: uncomment this line) -->
    {{-- <script src="{{ asset('javascript/notifications.js') }}?v={{ time() }}"></script> --}}

    <!-- PWA Service Worker Registration (for app installability) -->
    <script src="{{ asset('javascript/pwa.js') }}?v={{ time() }}"></script>
</body>
</html>
