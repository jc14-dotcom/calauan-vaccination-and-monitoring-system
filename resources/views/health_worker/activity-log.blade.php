@extends('layouts.responsive-layout')

@section('title', 'Activity Log')

@section('additional-styles')
<style>
    .hw-container { width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width: 640px){ .hw-container { padding-left:2rem; padding-right:2rem; } }
    @media (min-width: 1280px){ .hw-container { padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x { overflow-x:hidden; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
    
    /* Event badges */
    .event-badge {
        @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
    }
    .event-created { @apply bg-green-100 text-green-800; }
    .event-updated { @apply bg-blue-100 text-blue-800; }
    .event-deleted { @apply bg-red-100 text-red-800; }
    
    /* Log type badges */
    .log-badge {
        @apply inline-flex items-center px-2 py-1 rounded text-xs font-medium;
    }
    .log-patient { @apply bg-purple-100 text-purple-800; }
    .log-schedule { @apply bg-indigo-100 text-indigo-800; }
    .log-vaccination { @apply bg-teal-100 text-teal-800; }
    .log-inventory { @apply bg-amber-100 text-amber-800; }
    .log-health_worker { @apply bg-cyan-100 text-cyan-800; }
    .log-barangay { @apply bg-orange-100 text-orange-800; }
    .log-user { @apply bg-gray-100 text-gray-800; }
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4" data-animate>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Activity Log</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">
                        @if(isset($filteredUser) && $filteredUser)
                            Activities by {{ $filteredUser->display_name }}
                        @else
                            Track all system activities and changes
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('activity-log.export', request()->query()) }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Activities</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">This Week</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['this_week']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Filter Activities</h3>
                <p class="text-sm text-gray-500">Narrow down results by type, event, date, or keyword</p>
            </div>
        </div>
        <form method="GET" action="{{ route('activity-log.index') }}" id="activityLogForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                <!-- Log Type Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Log Type</label>
                    <div class="relative">
                        <select name="log_name" id="filterLogName" class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All Types</option>
                            @foreach($logNames as $logName)
                                <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $logName)) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Event Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Event</label>
                    <div class="relative">
                        <select name="event" id="filterEvent" class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>
                                    {{ ucfirst($event) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                    <div class="relative">
                        <input type="date" name="date_from" id="filterDateFrom" value="{{ request('date_from') }}" 
                            class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                    <div class="relative">
                        <input type="date" name="date_to" id="filterDateTo" value="{{ request('date_to') }}" 
                            class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" placeholder="Search descriptions..."
                            class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Buttons Row -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('activity-log.index') }}" id="activityLogResetBtn" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-colors flex items-center gap-2 opacity-50 pointer-events-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </a>
                <button type="submit" id="activityLogApplyBtn" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2 shadow-sm opacity-50 cursor-not-allowed" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Filtered User Banner - Placed above table for better visibility -->
    @if(isset($filteredUser) && $filteredUser)
    <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-purple-600 font-medium">Showing activities for</p>
                <p class="text-lg font-bold text-purple-800">{{ $filteredUser->display_name }} ({{ $filteredUser->username }})</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('health_worker.accounts.index') }}" class="text-purple-600 hover:text-purple-800 font-medium text-sm flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Accounts
            </a>
            <a href="{{ route('activity-log.index') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                View All Activity
            </a>
        </div>
    </div>
    @endif

    <!-- Activity Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Date/Time</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Event</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Log Type</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Description</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">User</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $activity->created_at->timezone('Asia/Manila')->format('M d, Y') }}
                                <br>
                                <span class="text-xs text-gray-400">{{ $activity->created_at->timezone('Asia/Manila')->format('h:i:s A') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $eventClass = match($activity->event) {
                                        'created' => 'event-created',
                                        'updated' => 'event-updated',
                                        'deleted' => 'event-deleted',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                @endphp
                                <span class="event-badge {{ $eventClass }}">
                                    {{ ucfirst($activity->event ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="log-badge log-{{ $activity->log_name }}">
                                    {{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                {{ $activity->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($activity->causer)
                                    @php
                                        $causerType = class_basename($activity->causer_type);
                                        $causer = $activity->causer;
                                        $username = $causer->username ?? "#{$causer->id}";
                                        $displayName = null;
                                        
                                        if ($causerType === 'HealthWorker') {
                                            // Show display name with barangay context
                                            if ($causer->name) {
                                                $displayName = $causer->name;
                                                if ($causer->barangay_id && $causer->barangay) {
                                                    $displayName .= ' (' . $causer->barangay->name . ')';
                                                } else {
                                                    $displayName .= ' (RHU Admin)';
                                                }
                                            }
                                        }
                                    @endphp
                                    <span class="font-medium text-gray-900">{{ $username }}</span>
                                    @if($displayName)
                                        <br>
                                        <span class="text-xs text-primary-600 font-medium">{{ $displayName }}</span>
                                    @else
                                        <br>
                                        <span class="text-xs text-gray-400">{{ $causerType }}</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">System</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="showDetails({{ $activity->id }})" 
                                    class="text-primary-600 hover:text-primary-900 font-medium">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg font-medium">No activity logs found</p>
                                <p class="text-sm">Activities will appear here as they occur</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($activities->hasPages() || $activities->total() > 0)
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="text-sm text-gray-600">
                            Showing <span class="font-semibold">{{ $activities->firstItem() ?? 0 }}</span> 
                            to <span class="font-semibold">{{ $activities->lastItem() ?? 0 }}</span> 
                            of <span class="font-semibold">{{ $activities->total() }}</span> results
                        </div>
                        
                        {{-- Per Page Selector --}}
                        <div class="flex items-center gap-2">
                            <label for="per_page" class="text-sm text-gray-600">Show:</label>
                            <select id="per_page" name="per_page" onchange="changePerPage(this.value)" 
                                    class="text-sm border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 py-1 px-2">
                                <option value="25" {{ request('per_page', 50) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 50) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    
                    @if($activities->hasPages())
                    <div class="flex items-center gap-2">
                        {{-- Previous Page Link --}}
                        @if ($activities->onFirstPage())
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Previous
                            </span>
                        @else
                            <a href="{{ $activities->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Previous
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        <div class="hidden sm:flex items-center gap-1">
                            @foreach ($activities->getUrlRange(max(1, $activities->currentPage() - 2), min($activities->lastPage(), $activities->currentPage() + 2)) as $page => $url)
                                @if ($page == $activities->currentPage())
                                    <span class="px-3 py-2 text-sm font-semibold text-white rounded-lg" style="background-color: #7c3aed;">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">{{ $page }}</a>
                                @endif
                            @endforeach
                        </div>

                        {{-- Current Page Indicator (Mobile) --}}
                        <span class="sm:hidden px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg">
                            Page {{ $activities->currentPage() }} of {{ $activities->lastPage() }}
                        </span>

                        {{-- Next Page Link --}}
                        @if ($activities->hasMorePages())
                            <a href="{{ $activities->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                Next
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                Next
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 bg-primary-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-white">Activity Details</h3>
            <button onclick="closeModal()" class="text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="modalContent" class="p-6 overflow-y-auto max-h-[70vh]">
            <div class="flex justify-center">
                <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

@endsection

@section('additional-scripts')
<script>
    // Filter button state management
    document.addEventListener('DOMContentLoaded', function() {
        // Values currently applied (from URL)
        const appliedValues = {
            log_name: '{{ request("log_name") }}',
            event:    '{{ request("event") }}',
            date_from: '{{ request("date_from") }}',
            date_to:   '{{ request("date_to") }}',
            search:    '{{ addslashes(request("search")) }}',
        };

        const fields = {
            log_name:  document.getElementById('filterLogName'),
            event:     document.getElementById('filterEvent'),
            date_from: document.getElementById('filterDateFrom'),
            date_to:   document.getElementById('filterDateTo'),
            search:    document.getElementById('filterSearch'),
        };

        const resetBtn = document.getElementById('activityLogResetBtn');
        const applyBtn = document.getElementById('activityLogApplyBtn');

        function hasActiveFilters() {
            return Object.values(appliedValues).some(v => v !== '');
        }

        function hasUnsavedChanges() {
            return Object.keys(fields).some(key =>
                fields[key] && fields[key].value !== appliedValues[key]
            );
        }

        function updateButtonStates() {
            const changed = hasUnsavedChanges();
            const anyFieldHasValue = Object.values(fields).some(f => f && f.value !== '');

            // Reset: enabled when filters are applied in the URL OR any field currently has a value
            if (resetBtn) {
                if (hasActiveFilters() || anyFieldHasValue) {
                    resetBtn.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    resetBtn.classList.add('opacity-50', 'pointer-events-none');
                }
            }

            // Apply Filters: enabled only when fields differ from what's applied
            if (applyBtn) {
                if (changed) {
                    applyBtn.disabled = false;
                    applyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    applyBtn.disabled = true;
                    applyBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Listen for changes on all filter fields
        Object.values(fields).forEach(function(field) {
            if (field) {
                field.addEventListener('input', updateButtonStates);
                field.addEventListener('change', updateButtonStates);
            }
        });

        // Initialize on page load
        updateButtonStates();
    });

    function showDetails(id) {
        const modal = document.getElementById('detailsModal');
        const content = document.getElementById('modalContent');
        
        modal.classList.remove('hidden');
        content.innerHTML = `
            <div class="flex justify-center">
                <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;
        
        fetch(`/health_worker/activity-log/${id}`)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = renderDetails(data);
            })
            .catch(error => {
                content.innerHTML = `<p class="text-red-500">Error loading details</p>`;
            });
    }
    
    function renderDetails(data) {
        let html = `
            <div class="space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Event</label>
                        <p class="mt-1 text-base font-semibold text-gray-900">${formatEventBadge(data.event)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Log Type</label>
                        <p class="mt-1 text-base font-semibold text-gray-900">${formatLogType(data.log_name)}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Date/Time</label>
                        <p class="mt-1 text-base font-semibold text-gray-900">${data.created_at}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Modified By</label>
                        <p class="mt-1 text-base font-semibold text-gray-900">${data.causer}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                        <label class="block text-xs font-medium text-blue-600 uppercase tracking-wide">Record Modified</label>
                        <p class="mt-1 text-base font-semibold text-blue-900">${data.subject}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Description</label>
                        <p class="mt-1 text-base font-semibold text-gray-900">${data.description}</p>
                    </div>
                </div>
        `;
        
        if (data.properties && Object.keys(data.properties).length > 0) {
            html += `<div><label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-3">Changes</label>`;
            
            // Check if we have both old and new values (update event)
            if (data.properties.attributes && data.properties.old) {
                html += `
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-primary-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wide">Field</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wide">Old Value</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wide">New Value</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                `;
                
                const allKeys = new Set([...Object.keys(data.properties.attributes), ...Object.keys(data.properties.old)]);
                allKeys.forEach(key => {
                    const oldVal = data.properties.old[key] ?? '-';
                    const newVal = data.properties.attributes[key] ?? '-';
                    const isChanged = oldVal !== newVal;
                    html += `
                        <tr class="${isChanged ? 'bg-yellow-50' : ''}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${formatFieldName(key)}</td>
                            <td class="px-4 py-3 text-sm text-red-600">${formatValue(oldVal, key)}</td>
                            <td class="px-4 py-3 text-sm text-green-600">${formatValue(newVal, key)}</td>
                        </tr>
                    `;
                });
                
                html += `</tbody></table></div>`;
            } else if (data.properties.attributes) {
                // Created event - only new values
                html += `
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wide">Field</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wide">Value</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                `;
                
                Object.entries(data.properties.attributes).forEach(([key, val]) => {
                    html += `
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${formatFieldName(key)}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">${formatValue(val, key)}</td>
                        </tr>
                    `;
                });
                
                html += `</tbody></table></div>`;
            } else if (data.properties.old) {
                // Deleted event - only old values
                html += `
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-red-600">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wide">Field</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-white uppercase tracking-wide">Deleted Value</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                `;
                
                Object.entries(data.properties.old).forEach(([key, val]) => {
                    html += `
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">${formatFieldName(key)}</td>
                            <td class="px-4 py-3 text-sm text-red-600">${formatValue(val, key)}</td>
                        </tr>
                    `;
                });
                
                html += `</tbody></table></div>`;
            }
            
            html += `</div>`;
        }
        
        html += `</div>`;
        return html;
    }
    
    function formatFieldName(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    function formatValue(val, fieldName = '') {
        if (val === null || val === undefined) return '-';
        if (typeof val === 'boolean') return val ? 'Yes' : 'No';
        if (typeof val === 'object') return JSON.stringify(val);
        
        // Mask sensitive fields for privacy
        const strVal = String(val);
        
        // Mask email addresses (show first 2 chars + masked + domain)
        if (fieldName.toLowerCase().includes('email') || strVal.includes('@')) {
            const emailRegex = /^([a-zA-Z0-9._-]+)@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/;
            const match = strVal.match(emailRegex);
            if (match) {
                const localPart = match[1];
                const domain = match[2];
                const masked = localPart.length > 2 
                    ? localPart.substring(0, 2) + '***' 
                    : localPart.substring(0, 1) + '***';
                return `${masked}@${domain}`;
            }
        }
        
        return strVal;
    }
    
    function formatEventBadge(event) {
        const colors = {
            'created': 'bg-green-100 text-green-800',
            'updated': 'bg-blue-100 text-blue-800',
            'deleted': 'bg-red-100 text-red-800',
            'login': 'bg-purple-100 text-purple-800',
            'logout': 'bg-gray-100 text-gray-800'
        };
        const color = colors[event] || 'bg-gray-100 text-gray-800';
        return `<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${color}">${event ? event.charAt(0).toUpperCase() + event.slice(1) : 'N/A'}</span>`;
    }
    
    function formatLogType(logName) {
        return logName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    function closeModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }
    
    // Change per page and reload with new setting
    function changePerPage(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.delete('page'); // Reset to first page
        window.location.href = url.toString();
    }
    
    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Close modal on backdrop click
    document.getElementById('detailsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endsection
