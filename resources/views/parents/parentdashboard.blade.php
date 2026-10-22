@php
    // Expects: $user (authenticated parent), $patients (collection of children)
    // Provide an API endpoint returning JSON: [{id,name,available,threshold(optional)}]
    // Example JSON shape for /api/vaccine-stocks:
    // [
    //   {"vaccine":"Hep B","stock":120,"unit":"doses","updated_at":"2025-09-11 09:32:00"},
    //   {"vaccine":"BCG","stock":18,"unit":"doses"},
    //   {"vaccine":"OPV","stock":42,"unit":"doses"}
    // ]
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <title>Parent Dashboard</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#7a5bbd">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Infant Vaccination">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192x192.png') }}">
    
    <link href="{{ versioned_asset('css/tailwind-full.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="{{ versioned_asset('javascript/logout-helper.js') }}"></script>
    <script src="{{ versioned_asset('javascript/session-guard.js') }}" defer></script>
    {{-- Removed aggressive immediate session check that was racing with login --}}
    {{-- The middleware already protects this route, no need for JavaScript check --}}
    <style>
        /* Subtle scroll fade for sections */
        [data-animate]{opacity:0;transform:translateY(16px);transition:.55s cubic-bezier(.4,.7,.2,1);will-change:opacity,transform;}
        [data-animate].in{opacity:1;transform:none;}
        @media (prefers-reduced-motion:reduce){
            [data-animate]{opacity:1 !important;transform:none !important;transition:none !important;}
        }
        .glass {background:rgba(255,255,255,.85);backdrop-filter:blur(14px);}
        .dark .glass {background:rgba(34,36,48,.72);}
    </style>
    <script>
        // Dark mode disabled temporarily. Previous init kept for reference:
        /* (() => {
            const s = localStorage.getItem('theme');
            if(s === 'dark') document.documentElement.classList.add('dark');
        })(); */
    </script>
</head>
<body class="min-h-full bg-gray-50 dark:bg-[#16161d] text-gray-800 dark:text-gray-200 font-sans flex flex-col">

    <!-- Top Bar -->
    <header class="w-full bg-gradient-to-r from-primary-700 to-primary-600 dark:from-primary-800 dark:to-primary-700 text-white shadow-lg relative z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-5 h-16 sm:h-20 flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-full bg-white/15 flex items-center justify-center ring-1 ring-white/25 flex-shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs sm:text-sm leading-tight opacity-80">Welcome</p>
                    <h1 class="text-base sm:text-lg font-semibold tracking-tight truncate">{{ $user->patients->first()->mother_name ?? $user->username ?? 'Parent' }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Notification Dropdown / Mobile Modal -->
                <div class="relative" id="notificationDropdown">
                    <button id="notificationBtn" class="relative h-9 w-9 sm:h-10 sm:w-10 rounded-lg bg-white/15 hover:bg-white/25 flex items-center justify-center transition-colors" aria-label="Notifications">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span id="notifBadge" class="absolute -top-1 -right-1 h-4 w-4 sm:h-5 sm:w-5 rounded-full bg-red-500 text-white text-[9px] sm:text-[10px] font-semibold flex items-center justify-center shadow-sm hidden">0</span>
                    </button>
                    <!-- Desktop: Dropdown / Mobile: Full-screen Modal -->
                    <div id="notificationMenu" class="hidden fixed inset-0 z-50 bg-white flex flex-col sm:absolute sm:inset-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96 sm:rounded-xl sm:shadow-2xl sm:ring-1 sm:ring-gray-200 sm:overflow-hidden sm:flex-none">
                        <!-- Mobile Header with Close Button -->
                        <div class="flex sm:hidden items-center justify-between px-4 py-3 bg-primary-600 text-white" style="padding-top: max(0.75rem, env(safe-area-inset-top));">
                            <h3 class="text-base font-semibold">Notifications</h3>
                            <button id="closeNotificationMobile" class="p-1 hover:bg-white/20 rounded-lg transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Desktop Header -->
                        <div class="hidden sm:flex px-4 py-3 border-b border-gray-200 bg-primary-50 items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                            <div class="flex items-center gap-2">
                                <button id="clearReadBtn" class="text-xs text-gray-500 hover:text-red-600 font-medium whitespace-nowrap transition" title="Clear read notifications">
                                    <svg class="w-3.5 h-3.5 inline-block mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Clear read
                                </button>
                                <span class="text-gray-300">|</span>
                                <button id="markAllReadBtn" class="text-xs text-primary-600 hover:text-primary-700 font-medium whitespace-nowrap">Mark all as read</button>
                            </div>
                        </div>
                        <!-- Mobile Action Bar -->
                        <div class="flex sm:hidden items-center justify-between px-4 py-2 border-b border-gray-200 bg-gray-50">
                            <button id="clearReadBtnMobile" class="text-xs text-gray-500 hover:text-red-600 font-medium transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Clear read
                            </button>
                            <button id="markAllReadBtnMobile" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Mark all as read</button>
                        </div>
                        <!-- Scrollable Notification List -->
                        <div id="notificationList" class="flex-1 overflow-y-auto overscroll-contain sm:max-h-80" style="padding-bottom: env(safe-area-inset-bottom);">
                            <div class="p-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155 6.852a3 3 0 005.667 1.97m1.965-2.277L21 21m-4.225-4.225a23.81 23.81 0 003.536-1.003A8.967 8.967 0 0118 9.75V9A6 6 0 006.53 6.53m10.245 10.245L6.53 6.53M3 3l3.53 3.53"/>
                                </svg>
                                <p class="text-sm">No notifications</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Theme toggle removed (dark mode disabled) -->
                {{-- <button id="themeToggle" class="hidden sm:inline-flex items-center gap-1 px-3 py-2 rounded-lg bg-white/15 hover:bg-white/25 transition text-xs font-medium">Toggle</button> --}}

                <!-- Profile dropdown -->
                <div class="relative" id="profileWrapper">
                    <button id="profileBtn" class="flex items-center gap-2 sm:gap-3 bg-white/10 hover:bg-white/20 rounded-xl pl-1.5 sm:pl-2 pr-2 sm:pr-3 py-1 sm:py-1.5 transition focus:outline-none focus:ring-2 focus:ring-white/40">
                        <img src="{{ asset('images/account.png') }}" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover ring-2 ring-white/30" alt="Profile">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 opacity-80 hidden sm:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div id="profileMenu" class="hidden absolute right-0 top-full translate-y-2 w-56 bg-white rounded-xl shadow-2xl ring-1 ring-black/10 overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-semibold truncate text-gray-800">{{ $user->patients->first()->mother_name ?? $user->username ?? 'Parent' }}</p>
                            <p class="text-xs text-gray-500">Parent Account</p>
                        </div>
                        <nav class="py-1 text-sm text-gray-700">
                            <a href="{{ route('parents.profile') }}" class="flex items-center gap-2 px-4 py-2.5 sm:py-2 hover:bg-primary-50 transition text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c2.761 0 5-2.462 5-5.5S14.761 1 12 1 7 3.462 7 6.5 9.239 12 12 12z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21c0-3.313 3.582-6 8-6s8 2.687 8 6"/>
                                </svg>
                                Profile
                            </a>
                            <a href="{{ route('parents.change-password') }}" class="flex items-center gap-2 px-4 py-2.5 sm:py-2 hover:bg-primary-50 transition text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="4" y="11" width="16" height="11" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0v4"/>
                                </svg>
                                Change Password
                            </a>
                            <button id="logoutBtn" class="w-full flex items-center gap-2 px-4 py-2.5 sm:py-2 text-left hover:bg-primary-50 transition text-gray-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3M12 8l4 4-4 4"/>
                                </svg>
                                Logout
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-5 py-6 sm:py-8 space-y-6 sm:space-y-8">

        <!-- Children & Vaccine Schedule - Side by Side on Desktop -->
        <section data-animate>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6">
                
                <!-- Left Column: Children Section -->
                <div class="lg:col-span-5">
                    <div class="bg-white dark:bg-[#222633] rounded-xl sm:rounded-2xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm overflow-hidden h-full">
                        <!-- Children Header -->
                        <div class="px-4 sm:px-5 py-3 sm:py-4 bg-gradient-to-r from-primary-600 to-primary-500 flex items-center justify-between">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-white">Aking mga Anak</h2>
                                    <p class="text-[10px] sm:text-xs text-white font-medium">I-click para makita ang record</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-white/20 rounded-full text-[10px] sm:text-xs font-bold text-white">{{ $patients->count() }}</span>
                        </div>
                        
                        <!-- Children List -->
                        <div class="p-3 sm:p-4">
                            @if($patients->count())
                                <div class="space-y-2.5 sm:space-y-3">
                                    @foreach($patients as $patient)
                                        <div class="group cursor-pointer relative rounded-xl bg-gray-50 dark:bg-gray-800/50 ring-1 ring-gray-200 dark:ring-gray-700 p-3 sm:p-4 hover:bg-primary-50 dark:hover:bg-primary-900/20 hover:ring-primary-300 dark:hover:ring-primary-600 transition-all duration-200"
                                             data-patient-id="{{ $patient->id }}">
                                            <div class="flex items-center gap-3">
                                                <!-- Profile Photo or Initials -->
                                                @if($patient->hasProfileImage())
                                                    <img src="{{ $patient->profile_image_url }}" 
                                                         alt="{{ $patient->name }}"
                                                         class="flex-shrink-0 h-10 w-10 sm:h-12 sm:w-12 rounded-full object-cover ring-2 ring-primary-200 dark:ring-primary-700 shadow-sm">
                                                @else
                                                    <span class="flex-shrink-0 inline-flex items-center justify-center h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white font-bold text-sm sm:text-base shadow-sm">
                                                        {{ $patient->initials }}
                                                    </span>
                                                @endif
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="font-semibold text-sm sm:text-base text-gray-800 dark:text-gray-100 truncate">
                                                        {{ $patient->name }}
                                                    </h3>
                                                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                        Tingnan ang record
                                                    </p>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 sm:py-12">
                                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">Walang nakarehistrong anak</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: Vaccine Schedule -->
                <div class="lg:col-span-7">
                    <div class="bg-white dark:bg-[#222633] rounded-xl sm:rounded-2xl ring-1 ring-gray-200 dark:ring-white/10 shadow-sm overflow-hidden h-full flex flex-col">
                        <!-- Header -->
                        <div class="px-4 sm:px-5 py-3 sm:py-4 bg-gradient-to-r from-primary-600 to-primary-500 flex items-center justify-between">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-white">Gabay sa Pagbabakuna</h2>
                                    <p class="text-[10px] sm:text-xs text-white font-medium">Iskedyul ng Bakuna ayon sa Edad</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-white/20 rounded-full text-[10px] sm:text-xs font-bold text-white"></span>
                        </div>

                        <!-- Table -->
                        <div class="flex-1 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                                        <th class="px-4 pl-8 py-3 text-left font-bold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 text-sm">
                                            Edad ng Bata
                                        </th>
                                        <th class="px-4 py-3 text-left font-bold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 text-sm">
                                            Mga Bakuna
                                        </th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 text-sm w-20">
                                            Doses
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <!-- Newborn -->
                                    <tr class="hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 transition-colors">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 flex-shrink-0 shadow-sm"></span>
                                                <div>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Bagong Panganak</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sa loob ng 24 oras</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">BCG</span>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-semibold">Hepatitis B</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 font-bold text-sm">2</span>
                                        </td>
                                    </tr>
                                    
                                    <!-- 1.5 - 3.5 Months -->
                                    <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0 shadow-sm"></span>
                                                <div>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">1½ - 3½ Buwan</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">6, 10, 14 linggo</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-semibold">Penta <span class="ml-1 opacity-70">(×3)</span></span>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-semibold">OPV <span class="ml-1 opacity-70">(×3)</span></span>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-semibold">PCV <span class="ml-1 opacity-70">(×3)</span></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 font-bold text-sm">9</span>
                                        </td>
                                    </tr>
                                    
                                    <!-- 3.5 and 9 Months - IPV -->
                                    <tr class="hover:bg-purple-50/50 dark:hover:bg-purple-900/10 transition-colors">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-purple-500 flex-shrink-0 shadow-sm"></span>
                                                <div>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">3½ at 9 Buwan</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">14 linggo, 9 buwan</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 text-xs font-semibold">IPV <span class="ml-1 opacity-70">(×2)</span></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 font-bold text-sm">2</span>
                                        </td>
                                    </tr>
                                    
                                    <!-- 9 - 15 Months - Measles -->
                                    <tr class="hover:bg-amber-50/50 dark:hover:bg-amber-900/10 transition-colors">
                                        <td class="px-4 py-3.5">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 flex-shrink-0 shadow-sm"></span>
                                                <div>
                                                    <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm">9 - 15 Buwan</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">9 buwan, 12-15 buwan</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="flex flex-wrap gap-1.5">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-xs font-semibold">MMR <span class="ml-1 opacity-70">(×2)</span></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-center">
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 font-bold text-sm">2</span>
                                        </td>
                                    </tr>
                                </tbody>
                                <!-- Footer Summary -->
                                <tfoot>
                                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800/80 dark:to-gray-800/50">
                                        <td class="px-4 py-3 font-bold text-gray-700 dark:text-gray-200 border-t border-gray-200 dark:border-gray-700 text-sm">
                                            Kabuuan
                                        </td>
                                        <td class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                                            <span class="text-gray-600 dark:text-gray-300 font-medium text-sm">7 uri ng bakuna</span>
                                        </td>
                                        <td class="px-4 py-3 text-center border-t border-gray-200 dark:border-gray-700">
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-primary-600 text-white font-bold text-sm shadow-sm">15</span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Footer Note -->
                        <div class="px-4 py-3 bg-gradient-to-r from-primary-50 to-primary-100/50 dark:from-primary-900/20 dark:to-primary-900/10 border-t border-primary-200/50 dark:border-primary-800/30 mt-auto">
                            <p class="text-xs sm:text-sm text-primary-700 dark:text-primary-300 font-medium">
                                <strong>Paalala:</strong> Dalhin ang vaccination card ng iyong anak. Kumpletuhin bago mag 1 taon.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Real-Time Vaccine Availability -->
        <section data-animate id="stocksSection">
            <div class="flex items-start sm:items-center justify-between flex-wrap gap-3 sm:gap-4 mb-4 sm:mb-6">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-800 dark:text-gray-100">Vaccine Availability</h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Live status from RHU Calauan (auto refresh every 30s)</p>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                    <button id="refreshStocks" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-3 sm:px-4 py-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6"/><path stroke-linecap="round" stroke-linejoin="round" d="M20 9A8 8 0 006 5.3M4 15a8 8 0 0014 3.7"/>
                        </svg>
                        <span class="hidden sm:inline">Refresh</span>
                    </button>
                </div>
            </div>

            <!-- Loading skeleton -->
            <div id="stocksLoading" class="grid gap-3 sm:gap-5 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3">
                @for($i=0;$i<6;$i++)
                    <div class="h-28 sm:h-32 rounded-xl sm:rounded-2xl bg-gradient-to-br from-gray-200 to-gray-100 dark:from-gray-700 dark:to-gray-600 animate-pulse"></div>
                @endfor
            </div>

            <!-- Cards container -->
            <div id="stocksCards" class="hidden grid gap-3 sm:gap-5 grid-cols-2 sm:grid-cols-2 lg:grid-cols-3"></div>

            <p id="stocksError" class="hidden mt-4 text-sm font-medium text-red-600 dark:text-red-400"></p>
        </section>

    </main>

    <!-- Logout Modal -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative w-full max-w-sm glass rounded-xl sm:rounded-2xl shadow-xl ring-1 ring-black/10 dark:ring-white/10 p-5 sm:p-7">
            <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 text-gray-800 dark:text-gray-100">Logout</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">Are you sure you want to log out?</p>
            <div class="mt-5 sm:mt-6 flex justify-end gap-2 sm:gap-3">
                <button id="cancelLogout" class="px-3 sm:px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 transition">Cancel</button>
                <button id="confirmLogout" class="px-3 sm:px-4 py-2 rounded-lg text-sm font-semibold bg-red-600 hover:bg-red-700 text-white transition">Logout</button>
            </div>
        </div>
    </div>

    <!-- Hidden Logout Form -->
    <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <footer class="mt-10 pb-8 text-center text-xs text-gray-500 dark:text-gray-400">
        &copy; {{ date('Y') }} Parent Dashboard
    </footer>

    <script>
        // Intersection animations
        const animEls = [...document.querySelectorAll('[data-animate]')];
        if('IntersectionObserver' in window){
            const io = new IntersectionObserver(es => es.forEach(e=>{
                if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
            }),{threshold:.15});
            animEls.forEach(el=>io.observe(el));
        } else animEls.forEach(el=>el.classList.add('in'));

    // Theme toggle disabled (dark mode removed)
    // document.getElementById('themeToggle')?.addEventListener('click', ()=>{});

        // Profile dropdown
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        
        profileBtn.addEventListener('click', e=>{
            e.stopPropagation();
            // Close notification menu if open
            const notificationMenu = document.getElementById('notificationMenu');
            if (notificationMenu && !notificationMenu.classList.contains('hidden')) {
                notificationMenu.classList.add('hidden');
            }
            profileMenu.classList.toggle('hidden');
        });
        
        window.addEventListener('click', e=>{
            // Close profile menu
            if(!profileMenu.classList.contains('hidden') && !profileMenu.contains(e.target) && !profileBtn.contains(e.target)){
                profileMenu.classList.add('hidden');
            }
        });

        // Logout modal
        const logoutBtn = document.getElementById('logoutBtn');
        const logoutModal = document.getElementById('logoutModal');
        const cancelLogout = document.getElementById('cancelLogout');
        const confirmLogout = document.getElementById('confirmLogout');

        logoutBtn.addEventListener('click', ()=>{
            profileMenu.classList.add('hidden');
            logoutModal.classList.remove('hidden');
            logoutModal.classList.add('flex');
        });
        cancelLogout.addEventListener('click', ()=> {
            logoutModal.classList.add('hidden');
            logoutModal.classList.remove('flex');
        });
        confirmLogout.addEventListener('click', ()=>{
            // Mark user as logged out for session guard
            if (window.markUserLoggedOut) {
                window.markUserLoggedOut();
            }
            
            // Clear notification system
            if (window.notificationSystem) {
                window.notificationSystem.stopPolling();
            }
            
            // Submit logout form (POST request)
            document.getElementById('logoutForm').submit();
        });

        // Child card navigation
        document.querySelectorAll('[data-patient-id]').forEach(card=>{
            card.addEventListener('click',()=>{
                const id = card.getAttribute('data-patient-id');
                if(id) window.location.href = '/infantsRecord/' + id;
            });
        });

        // Stocks logic
        const stocksAPI = '{{ url("/api/vaccine-stocks") }}';
        const loading = document.getElementById('stocksLoading');
        const cards = document.getElementById('stocksCards');
        const errorBox = document.getElementById('stocksError');
        const refreshBtn = document.getElementById('refreshStocks');
        let pollTimer;
        let abortCtrl;

        // Simplified 3-tier availability classification for parents
        // 0 = Not Available (Red), 1-49 = Limited Supply (Yellow), 50+ = Available (Green)
        function classify(stock){
            if(stock === null || stock === undefined) return {label:'Unknown', color:'#6b7280', bg:'bg-gray-100', text:'text-gray-600', icon:'?'};
            if(stock <= 0) return {label:'Not Available', color:'#dc2626', bg:'bg-red-50', text:'text-red-600', ring:'ring-red-200', icon:'✕'};
            if(stock < 50) return {label:'Limited Supply', color:'#eab308', bg:'bg-yellow-50', text:'text-yellow-700', ring:'ring-yellow-200', icon:'!'};
            return {label:'Available', color:'#22c55e', bg:'bg-green-50', text:'text-green-600', ring:'ring-green-200', icon:'✓'};
        }

        function formatTime(ts){
            if(!ts) return '—';
            return new Date(ts.replace(' ','T')).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
        }

        async function loadStocks(manual=false){
            if(abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();
            errorBox.classList.add('hidden');
            if(manual) loading.classList.remove('hidden');
            try {
                const res = await fetch(stocksAPI, {signal: abortCtrl.signal, headers:{'Accept':'application/json'}});
                if(!res.ok) throw new Error('HTTP '+res.status);
                const data = await res.json();
                renderStocks(data);
            } catch(e){
                errorBox.textContent = 'Unable to load vaccine availability. ' + (e.name === 'AbortError' ? '' : 'Retry shortly.');
                errorBox.classList.remove('hidden');
            } finally {
                loading.classList.add('hidden');
            }
        }

        function renderStocks(response){
            // Extract data from API Resource format
            const data = response.data || response;
            
            // Render simplified availability cards
            cards.innerHTML = '';
            data.forEach(item=>{
                const cls = classify(item.stock);
                cards.insertAdjacentHTML('beforeend', `
                    <div class="rounded-xl sm:rounded-2xl bg-white dark:bg-[#222633] ring-1 ${cls.ring} dark:ring-white/10 p-4 sm:p-5 flex flex-col gap-3 shadow-sm hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-semibold text-sm sm:text-base text-gray-800 dark:text-gray-100 leading-snug line-clamp-2">${item.vaccine ?? 'Unknown'}</h4>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold" style="background-color: ${cls.color}20; color: ${cls.color};">${cls.icon}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 sm:w-3 sm:h-3 rounded-full flex-shrink-0" style="background-color: ${cls.color};"></span>
                            <span class="text-sm sm:text-base font-semibold" style="color: ${cls.color};">${cls.label}</span>
                        </div>
                        <p class="text-[10px] sm:text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1 mt-auto">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3 3"/>
                                <circle cx="12" cy="12" r="9"/>
                            </svg>
                            Updated ${formatTime(item.updated_at)}
                        </p>
                    </div>
                `);
            });

            // Show cards
            cards.classList.remove('hidden');
        }

        refreshBtn.addEventListener('click', ()=> loadStocks(true));

        function startPolling(){
            clearInterval(pollTimer);
            pollTimer = setInterval(loadStocks, 30000);
        }

        loadStocks();
        startPolling();

        // Clean up on page unload
        window.addEventListener('beforeunload', ()=>{ clearInterval(pollTimer); if(abortCtrl) abortCtrl.abort(); });
    </script>

    <!-- Notification System -->
    <script src="{{ asset('javascript/notifications.js') }}?v={{ time() }}"></script>

    <!-- PWA Service Worker Registration (for app installability) -->
    <script src="{{ asset('javascript/pwa.js') }}?v={{ time() }}"></script>
    
    <!-- Firebase Cloud Messaging (FCM) - NEW -->
    <script src="{{ asset('javascript/fcm.js') }}?v={{ time() }}"></script>
</body>
</html>