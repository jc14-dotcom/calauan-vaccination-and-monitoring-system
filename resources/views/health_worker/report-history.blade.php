@extends('layouts.responsive-layout')

@section('title', 'Report History')

@section('additional-styles')
<style>
    /* Fade-in animation for header */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
@endsection

@section('content')
<div class="min-h-screen w-full">
    <!-- Main content area -->
    <div class="w-full max-w-full mx-auto px-4 sm:px-8 xl:px-10 py-6 pb-8">
        
        <!-- Banner Header (matching dashboard style) -->
        <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
            <div class="relative px-6 py-7 text-white flex items-center justify-between" data-animate>
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold leading-tight">Report History</h1>
                        <p class="text-sm md:text-base text-white/90 mt-1">Browse and manage archived vaccination reports by year and version</p>
                    </div>
                </div>
                
                <!-- Import Data Button -->
                <button onclick="showImportModal()" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 bg-white text-primary-700 rounded-lg font-semibold shadow-md hover:bg-white/90 transition-colors text-sm whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Import Historical Data
                </button>
            </div>
        </section>

        <!-- Mobile Import Button (visible only on mobile) -->
        <div class="sm:hidden mb-4">
            <button onclick="showImportModal()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-primary-700 text-white rounded-lg font-semibold shadow-md hover:bg-primary-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Import Historical Data
            </button>
        </div>

    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Search Box -->
            <div class="sm:col-span-2">
                <label for="search-reports" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">Search Reports</label>
                <div class="relative">
                    <input type="text" 
                           id="search-reports" 
                           placeholder="Search by year, period, version..." 
                           class="w-full px-3 sm:px-4 py-2 pl-9 sm:pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 absolute left-2.5 sm:left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
            
            <!-- Year Filter -->
            <div>
                <label for="filter-year" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">Filter by Year</label>
                <select id="filter-year" 
                        class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm sm:text-base">
                    <option value="">All Years</option>
                    @foreach($archivedReports->pluck('year')->unique()->sortDesc() as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Data Source Filter -->
            <div>
                <label for="filter-source" class="block text-xs sm:text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">Filter by Source</label>
                <select id="filter-source" 
                        class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm sm:text-base">
                    <option value="">All Sources</option>
                    <option value="calculated">Calculated</option>
                    <option value="manual_edit">Manual Edit</option>
                </select>
            </div>
        </div>
        
        <!-- Clear Filters Button -->
        <div class="mt-2 sm:mt-3 flex justify-end">
            <button onclick="clearFilters()" class="text-xs sm:text-sm text-purple-600 hover:text-purple-700 font-semibold">
                Clear all filters
            </button>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-4 sm:space-x-8 overflow-x-auto">
                <button onclick="switchTab('active')" id="tab-active" class="tab-button active border-b-2 border-purple-600 py-3 sm:py-4 px-1 text-xs sm:text-sm font-semibold text-purple-600 transition-colors whitespace-nowrap">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="hidden sm:inline">Active Reports</span>
                        <span class="sm:hidden">Active</span>
                        @php
                            $activeCount = $archivedReports->where('deleted_at', null)->count();
                        @endphp
                        <span class="badge bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full text-xs font-bold">{{ $activeCount }}</span>
                    </div>
                </button>
                <button onclick="switchTab('deleted')" id="tab-deleted" class="tab-button border-b-2 border-transparent py-3 sm:py-4 px-1 text-xs sm:text-sm font-semibold text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors whitespace-nowrap">
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        <span class="hidden sm:inline">Archived Reports</span>
                        <span class="sm:hidden">Archived</span>
                        @php
                            $deletedCount = $archivedReports->whereNotNull('deleted_at')->count();
                        @endphp
                        <span class="badge bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs font-bold">{{ $deletedCount }}</span>
                    </div>
                </button>
            </nav>
        </div>
    </div>

    @if($archivedReports->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="text-center py-16 px-5 text-gray-500">
                <svg class="w-20 h-20 mx-auto mb-5 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Archived Reports</h3>
                <p class="text-gray-500 mb-4">You haven't saved any reports yet.</p>
                <a href="{{ route('reports.current') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Go to Current Report
                </a>
            </div>
        </div>
    @else
        <!-- Active Reports Tab Content -->
        <div id="content-active" class="tab-content">
            @php
                $activeReports = $archivedReports->where('deleted_at', null);
                $groupedByYear = $activeReports->groupBy('year');
            @endphp
            
            @if($activeReports->isEmpty())
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="text-center py-16 px-5 text-gray-500">
                        <svg class="w-20 h-20 mx-auto mb-5 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Active Reports</h3>
                        <p class="text-gray-500">All reports have been deleted.</p>
                    </div>
                </div>
            @else
                @foreach($groupedByYear as $year => $yearReports)
            <!-- Year Section -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-4 report-year-section" data-year="{{ $year }}">
                <!-- Year Header (Collapsible) -->
                <button onclick="toggleYear({{ $year }})" class="w-full px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-primary-600 to-primary-800 text-white flex items-center justify-between hover:from-primary-700 hover:to-primary-900 transition-all">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                        <h2 class="text-base sm:text-xl font-bold">{{ $year }} Reports</h2>
                        <span class="bg-white/20 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs sm:text-sm font-semibold">{{ $yearReports->count() }} version(s)</span>
                    </div>
                    <svg id="arrow-{{ $year }}" class="w-4 h-4 sm:w-5 sm:h-5 transition-transform duration-300 -rotate-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Year Content (Collapsible) -->
                <div id="year-{{ $year }}" class="overflow-hidden transition-all duration-300" style="max-height: 0px;">
                    @php
                        // Group by month range for better organization
                        $groupedByPeriod = $yearReports->groupBy(function($item) {
                            // Use month_start and month_end if available
                            if ($item->month_start && $item->month_end) {
                                // Create sortable key: pad with zeros for proper ordering
                                return str_pad($item->month_start, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->month_end, 2, '0', STR_PAD_LEFT);
                            }
                            // Fall back to quarter for old data (use 99 to sort quarters at the end)
                            return '99-Q' . $item->quarter_start . '-' . $item->quarter_end;
                        })->sortKeys(); // Sort by key so January (01) comes before February (02)
                    @endphp

                    @foreach($groupedByPeriod as $periodKey => $periodReports)
                        @php
                            $firstReport = $periodReports->first();
                            
                            // Determine the period label based on month or quarter data
                            // Check if quarter_start/quarter_end contain month values (>4) or quarter values (1-4)
                            if ($firstReport->month_start && $firstReport->month_end) {
                                // If month_start/month_end columns exist (future-proofing)
                                $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                                               'July', 'August', 'September', 'October', 'November', 'December'];
                                
                                if ($firstReport->month_start === $firstReport->month_end) {
                                    // Single month
                                    $periodLabel = $monthNames[$firstReport->month_start];
                                } else {
                                    // Month range
                                    $monthShort = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                                   'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                    $periodLabel = $monthShort[$firstReport->month_start] . '-' . $monthShort[$firstReport->month_end];
                                }
                            } elseif ($firstReport->quarter_start > 4 || $firstReport->quarter_end > 4) {
                                // Month values stored in quarter_start/quarter_end (new format)
                                $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                                               'July', 'August', 'September', 'October', 'November', 'December'];
                                $monthShort = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                               'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                
                                if ($firstReport->quarter_start === $firstReport->quarter_end) {
                                    // Single month - use full month name
                                    $periodLabel = $monthNames[$firstReport->quarter_start] ?? 'Unknown';
                                } else {
                                    // Month range - use short month names
                                    $periodLabel = ($monthShort[$firstReport->quarter_start] ?? 'Jan') . '-' . ($monthShort[$firstReport->quarter_end] ?? 'Dec');
                                }
                            } else {
                                // Fall back to quarter labels for old data (1-4)
                                $periodLabel = $firstReport->quarter_start == $firstReport->quarter_end 
                                    ? "Q{$firstReport->quarter_start}" 
                                    : "Q{$firstReport->quarter_start}-Q{$firstReport->quarter_end}";
                            }
                        @endphp

                        <!-- Period Section -->
                        <div class="border-b border-gray-200 last:border-b-0">
                            <div class="px-4 sm:px-6 py-3 sm:py-4 bg-gray-50">
                                <h3 class="text-sm sm:text-lg font-bold text-gray-800 flex items-center gap-1.5 sm:gap-2">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $periodLabel }} {{ $year }}
                                    <span class="text-xs sm:text-sm font-normal text-gray-600">({{ $periodReports->count() }} version{{ $periodReports->count() > 1 ? 's' : '' }})</span>
                                </h3>
                            </div>

                            <!-- Versions Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Version</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Saved Date</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Data Source</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Vaccines</th>
                                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($periodReports->sortByDesc('version') as $report)
                                            <tr class="border-b border-gray-100 hover:bg-purple-50 transition-colors report-row" 
                                                data-year="{{ $report->year }}"
                                                data-version="{{ $report->version }}"
                                                data-period="{{ $periodLabel }}"
                                                data-source="{{ $report->data_source }}">
                                                <!-- Version -->
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-purple-600">v{{ $report->version }}</span>
                                                        @if($loop->first)
                                                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Latest</span>
                                                        @endif
                                                    </div>
                                                </td>

                                                <!-- Saved Date -->
                                                <td class="px-4 py-3 text-gray-600">
                                                    {{ $report->saved_at ? \Carbon\Carbon::parse($report->saved_at)->format('M d, Y h:i A') : 'N/A' }}
                                                </td>

                                                <!-- Data Source -->
                                                <td class="px-4 py-3">
                                                    @if($report->data_source === 'calculated')
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-primary-100 text-primary-800">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                            </svg>
                                                            Calculated
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            Manual Edit
                                                        </span>
                                                    @endif
                                                </td>

                                                <!-- Vaccine Count -->
                                                <td class="px-4 py-3 text-gray-600">
                                                    {{ $report->vaccine_count ?? 0 }} vaccine(s)
                                                </td>

                                                <!-- Actions -->
                                                <td class="px-2 sm:px-4 py-3">
                                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-1.5 sm:gap-2">
                                                        <!-- View Button -->
                                                        <a href="{{ route('reports.show', ['year' => $report->year, 'quarter_start' => $report->quarter_start, 'quarter_end' => $report->quarter_end, 'version' => $report->version]) }}" 
                                                           class="inline-flex items-center justify-center gap-1 px-2 sm:px-3 py-2 sm:py-1.5 bg-purple-600 text-white rounded-md text-xs font-semibold hover:bg-purple-700 transition-colors"
                                                           title="View this version">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                            </svg>
                                                            <span>View</span>
                                                        </a>
                                                        
                                                        <!-- Compare Button (only show if there are multiple active versions) -->
                                                        @php
                                                            $activePeriodReports = $periodReports->where('deleted_at', null);
                                                        @endphp
                                                        @if($activePeriodReports->count() > 1)
                                                            <button onclick="showCompareModal({{ $report->year }}, {{ $report->quarter_start }}, {{ $report->quarter_end }}, {{ $report->version }}, '{{ $periodLabel }}')" 
                                                                    class="inline-flex items-center justify-center gap-1 px-2 sm:px-3 py-2 sm:py-1.5 bg-primary-600 text-white rounded-md text-xs font-semibold hover:bg-primary-700 transition-colors"
                                                                    title="Compare with another version">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                                                </svg>
                                                                <span>Compare</span>
                                                            </button>
                                                        @endif
                                                        
                                                        <!-- Delete Button -->
                                                        <button onclick="confirmDelete({{ $report->year }}, {{ $report->quarter_start }}, {{ $report->quarter_end }}, {{ $report->version }}, '{{ $periodLabel }} {{ $year }} v{{ $report->version }}')" 
                                                                class="inline-flex items-center justify-center gap-1 px-2 sm:px-3 py-2 sm:py-1.5 bg-red-600 text-white rounded-md text-xs font-semibold hover:bg-red-700 transition-colors"
                                                                title="Delete this version">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            <span>Delete</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
            @endif
        </div>

        <!-- Archived Reports Tab Content -->
        <div id="content-deleted" class="tab-content hidden">
            @php
                $deletedReports = $archivedReports->whereNotNull('deleted_at');
                $deletedGroupedByYear = $deletedReports->groupBy('year');
            @endphp
            
            @if($deletedReports->isEmpty())
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="text-center py-16 px-5 text-gray-500">
                        <svg class="w-20 h-20 mx-auto mb-5 opacity-30" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Archived Reports</h3>
                        <p class="text-gray-500">There are no archived reports at this time.</p>
                    </div>
                </div>
            @else
                <!-- Bulk Actions Bar (Always visible) -->
                <div id="bulk-actions-bar" class="bg-white rounded-lg shadow-md p-3 sm:p-4 mb-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-0">
                        <div class="flex items-center gap-2 sm:gap-3">
                            <input type="checkbox" id="select-all-deleted" onchange="toggleSelectAll()" class="w-4 h-4 sm:w-5 sm:h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <span class="text-sm sm:text-base font-semibold text-gray-700">
                                <span id="selected-count">0</span> selected
                            </span>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <button id="bulk-restore-btn" onclick="bulkRestoreReports()" disabled class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 bg-gray-300 text-gray-500 rounded-lg font-semibold transition-colors cursor-not-allowed text-sm sm:text-base">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span class="hidden sm:inline">Restore Selected</span>
                                <span class="sm:hidden">Restore</span>
                            </button>
                            {{-- PERMANENT DELETE BUTTON - DISABLED FOR DATA PROTECTION
                                 Uncomment this button to re-enable permanent delete functionality
                                 Note: This was disabled to prevent accidental/malicious permanent data loss
                            <button id="bulk-delete-btn" onclick="bulkPermanentDelete()" disabled class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 bg-gray-300 text-gray-500 rounded-lg font-semibold transition-colors cursor-not-allowed text-sm sm:text-base">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span class="hidden sm:inline">Permanent Delete</span>
                                <span class="sm:hidden">Delete</span>
                            </button>
                            --}}
                        </div>
                    </div>
                </div>
                
                @foreach($deletedGroupedByYear as $year => $yearReports)
                    <!-- Year Section for Archived Reports -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-4 report-year-section" data-year="{{ $year }}">
                        <!-- Year Header -->
                        <button onclick="toggleYear('deleted-{{ $year }}')" class="w-full px-4 sm:px-6 py-3 sm:py-4 bg-gradient-to-r from-red-600 to-red-700 text-white flex items-center justify-between hover:from-red-700 hover:to-red-800 transition-all">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                <h2 class="text-base sm:text-xl font-bold">{{ $year }} Archived Reports</h2>
                                <span class="bg-white/20 px-2 sm:px-3 py-0.5 sm:py-1 rounded-full text-xs sm:text-sm font-semibold">{{ $yearReports->count() }} version(s)</span>
                            </div>
                            <svg id="arrow-deleted-{{ $year }}" class="w-4 h-4 sm:w-5 sm:h-5 transition-transform duration-300 -rotate-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Year Content -->
                        <div id="year-deleted-{{ $year }}" class="overflow-hidden transition-all duration-300" style="max-height: 0px;">
                            @php
                                $groupedByPeriod = $yearReports->groupBy(function($item) {
                                    if ($item->month_start && $item->month_end) {
                                        return str_pad($item->month_start, 2, '0', STR_PAD_LEFT) . '-' . str_pad($item->month_end, 2, '0', STR_PAD_LEFT);
                                    }
                                    return '99-Q' . $item->quarter_start . '-' . $item->quarter_end;
                                })->sortKeys();
                            @endphp

                            @foreach($groupedByPeriod as $periodKey => $periodReports)
                                @php
                                    $firstReport = $periodReports->first();
                                    
                                    // Determine the period label based on month or quarter data
                                    // Check if quarter_start/quarter_end contain month values (>4) or quarter values (1-4)
                                    if ($firstReport->month_start && $firstReport->month_end) {
                                        // If month_start/month_end columns exist (future-proofing)
                                        $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                                                       'July', 'August', 'September', 'October', 'November', 'December'];
                                        
                                        if ($firstReport->month_start === $firstReport->month_end) {
                                            $periodLabel = $monthNames[$firstReport->month_start];
                                        } else {
                                            $monthShort = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                                           'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                            $periodLabel = $monthShort[$firstReport->month_start] . '-' . $monthShort[$firstReport->month_end];
                                        }
                                    } elseif ($firstReport->quarter_start > 4 || $firstReport->quarter_end > 4) {
                                        // Month values stored in quarter_start/quarter_end (new format)
                                        $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                                                       'July', 'August', 'September', 'October', 'November', 'December'];
                                        $monthShort = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                                                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                        
                                        if ($firstReport->quarter_start === $firstReport->quarter_end) {
                                            // Single month - use full month name
                                            $periodLabel = $monthNames[$firstReport->quarter_start] ?? 'Unknown';
                                        } else {
                                            // Month range - use short month names
                                            $periodLabel = ($monthShort[$firstReport->quarter_start] ?? 'Jan') . '-' . ($monthShort[$firstReport->quarter_end] ?? 'Dec');
                                        }
                                    } else {
                                        // Fall back to quarter labels for old data (1-4)
                                        $periodLabel = $firstReport->quarter_start == $firstReport->quarter_end 
                                            ? "Q{$firstReport->quarter_start}" 
                                            : "Q{$firstReport->quarter_start}-Q{$firstReport->quarter_end}";
                                    }
                                @endphp

                                <div class="border-b border-gray-200 last:border-b-0 bg-red-50">
                                    <div class="px-4 sm:px-6 py-3 sm:py-4">
                                        <h3 class="text-sm sm:text-lg font-bold text-red-800 flex items-center gap-1.5 sm:gap-2">
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $periodLabel }} {{ $year }}
                                            <span class="text-xs sm:text-sm font-normal text-red-600">({{ $periodReports->count() }} deleted version{{ $periodReports->count() > 1 ? 's' : '' }})</span>
                                        </h3>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-red-100">
                                                    <th class="px-4 py-3 text-center font-semibold text-red-900 w-12">
                                                        <input type="checkbox" class="select-all-period w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" onchange="togglePeriodSelection(this)">
                                                    </th>
                                                    <th class="px-4 py-3 text-left font-semibold text-red-900">Version</th>
                                                    <th class="px-4 py-3 text-left font-semibold text-red-900">Deleted Date</th>
                                                    <th class="px-4 py-3 text-left font-semibold text-red-900">Deletion Reason</th>
                                                    <th class="px-4 py-3 text-left font-semibold text-red-900">Vaccines</th>
                                                    <th class="px-4 py-3 text-center font-semibold text-red-900">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($periodReports->sortByDesc('version') as $report)
                                                    <tr class="border-b border-red-200 hover:bg-red-100 transition-colors report-row"
                                                        data-year="{{ $report->year }}"
                                                        data-version="{{ $report->version }}"
                                                        data-period="{{ $periodLabel }}"
                                                        data-source="{{ $report->data_source }}">
                                                        <td class="px-4 py-3 text-center">
                                                            <input type="checkbox" 
                                                                   class="report-checkbox w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500" 
                                                                   data-year="{{ $report->year }}" 
                                                                   data-quarter-start="{{ $report->quarter_start }}" 
                                                                   data-quarter-end="{{ $report->quarter_end }}" 
                                                                   data-version="{{ $report->version }}"
                                                                   onchange="updateBulkActions()">
                                                        </td>
                                                        <td class="px-4 py-3">
                                                            <span class="font-bold text-red-600">v{{ $report->version }}</span>
                                                        </td>

                                                        <td class="px-4 py-3 text-red-700">
                                                            {{ $report->deleted_at ? \Carbon\Carbon::parse($report->deleted_at)->format('M d, Y h:i A') : 'N/A' }}
                                                        </td>

                                                        <td class="px-4 py-3 text-red-700">
                                                            <div class="max-w-xs truncate" title="{{ $report->deletion_reason ?? 'No reason provided' }}">
                                                                {{ $report->deletion_reason ?? 'No reason provided' }}
                                                            </div>
                                                        </td>

                                                        <td class="px-4 py-3 text-red-700">
                                                            {{ $report->vaccine_count ?? 0 }} vaccine(s)
                                                        </td>

                                                        <td class="px-2 sm:px-4 py-3">
                                                            <div class="flex items-center justify-center">
                                                                <button onclick="confirmRestore({{ $report->year }}, {{ $report->quarter_start }}, {{ $report->quarter_end }}, {{ $report->version }}, '{{ $periodLabel }} {{ $year }} v{{ $report->version }}', '{{ $report->deletion_reason ?? 'No reason provided' }}', '{{ $report->deleted_at ? \Carbon\Carbon::parse($report->deleted_at)->format('M d, Y h:i A') : '' }}')" 
                                                                        class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-green-600 text-white rounded-md text-xs font-semibold hover:bg-green-700 transition-colors w-full sm:w-auto"
                                                                        title="Restore this deleted version">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                                    </svg>
                                                                    <span>Restore</span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Confirm Deletion
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base mb-1">
                Are you sure you want to delete this version:
            </p>
            <p class="text-xl font-bold text-red-600 mb-4" id="deleteReportLabel"></p>
            
            <!-- Deletion Reason Field -->
            <div class="mb-4">
                <label for="deletionReason" class="block text-sm font-semibold text-gray-700 mb-2">
                    Deletion Reason <span class="text-red-600">*</span>
                </label>
                <textarea 
                    id="deletionReason" 
                    rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent resize-none"
                    placeholder="Please explain why this version is being deleted (required for audit trail)..."
                    maxlength="500"
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    This reason will be saved for compliance and audit purposes.
                </p>
            </div>
            
            <div class="text-gray-600 text-sm bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <span class="font-semibold">📋 Note:</span> This will soft delete the version. It can be restored later if needed.
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeDeleteModal()" 
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button onclick="deleteReport()" 
                    class="px-5 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors">
                Delete Version
            </button>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Success
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base" id="successMessage"></p>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex justify-end">
            <button onclick="closeSuccessModal()" 
                    class="px-5 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                OK
            </button>
        </div>
    </div>
</div>

<!-- Import Historical Data Modal -->
<div id="importModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen w-full">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl my-8">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-4 sm:px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span class="text-base sm:text-xl">Import Historical Data</span>
                </h3>
                <button onclick="closeImportModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-6">
            <!-- Period Selection -->
            <div class="mb-6">
                <h4 class="text-base sm:text-lg font-semibold text-gray-800 mb-3">Select Report Period</h4>
                <p class="text-xs sm:text-sm text-gray-600 mb-4">Choose the year and month(s) for this imported data. You can select a single month or a range.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                    <!-- Year -->
                    <div>
                        <label for="import_year" class="block text-sm font-semibold text-gray-700 mb-2">Year</label>
                        <select id="import_year" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 transition">
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <!-- Month Start -->
                    <div>
                        <label for="import_month_start" class="block text-sm font-semibold text-gray-700 mb-2">From Month</label>
                        <select id="import_month_start" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 transition">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                    
                    <!-- Month End -->
                    <div>
                        <label for="import_month_end" class="block text-sm font-semibold text-gray-700 mb-2">To Month</label>
                        <select id="import_month_end" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 transition">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Upload File Section (No Tabs) -->
            <div id="upload-content" class="import-content">
                <!-- Download Template Button -->
                <div class="mb-4 flex justify-center">
                    <a href="{{ route('reports.downloadTemplate') }}" 
                       class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 bg-green-600 text-white rounded-lg font-semibold shadow-md hover:bg-green-700 transition-colors text-sm sm:text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="hidden sm:inline">Download Import Template</span>
                        <span class="sm:hidden">Download Template</span>
                    </a>
                </div>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 sm:p-8 text-center hover:border-primary-500 transition-colors">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <input type="file" id="import_file" accept=".xlsx,.xls,.csv" class="hidden" onchange="handleFileSelect(event)">
                    <label for="import_file" class="cursor-pointer">
                        <span class="text-primary-600 font-semibold hover:text-primary-700 text-sm sm:text-base">Click to upload</span>
                        <span class="text-gray-600 text-sm sm:text-base"> or drag and drop</span>
                    </label>
                    <p class="text-xs sm:text-sm text-gray-500 mt-2">Excel (.xlsx, .xls) or CSV (.csv)</p>
                    <p id="selected-file" class="text-xs sm:text-sm text-green-600 font-semibold mt-2"></p>
                </div>
                
                <div class="mt-4 bg-primary-50 border border-primary-200 rounded-lg p-3 sm:p-4">
                    <p class="text-xs sm:text-sm text-primary-900 mb-2"><strong>📋 How to Import:</strong></p>
                    <ul class="text-xs sm:text-sm text-primary-800 space-y-1 ml-4">
                        <li>1. <strong>Download the template</strong> using the button above</li>
                        <li>2. <strong>Open in Excel</strong> and fill in your vaccination data</li>
                        <li>3. <strong>Select the period</strong> (year and month range) above</li>
                        <li>4. <strong>Upload the file</strong> and click Import Data</li>
                        <li>• The period you select will be applied to all data in the uploaded file</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-4 sm:px-6 py-4 flex flex-col sm:flex-row gap-3 justify-end">
            <button onclick="closeImportModal()" 
                    class="w-full sm:w-auto px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors order-2 sm:order-1">
                Cancel
            </button>
            <button onclick="submitImport()" 
                    class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors order-1 sm:order-2">
                Import Data
            </button>
        </div>
    </div>
    </div>
</div>

<!-- Compare Versions Modal -->
<div id="compareModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Compare Report Versions
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 mb-4">Select a version to compare with <strong id="compare-version-label"></strong>:</p>
            
            <div id="version-list" class="space-y-2 max-h-96 overflow-y-auto">
                <!-- Version options will be populated here -->
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeCompareModal()" 
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div id="restoreModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Restore Deleted Report
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base mb-1">
                Are you sure you want to restore this version:
            </p>
            <p class="text-xl font-bold text-green-600 mb-4" id="restoreReportLabel"></p>
            
            <!-- Deletion Information -->
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-sm font-semibold text-red-800 mb-2">Deletion Information:</p>
                <p class="text-sm text-red-700 mb-1"><strong>Deleted At:</strong> <span id="deletedAtInfo"></span></p>
                <p class="text-sm text-red-700"><strong>Reason:</strong></p>
                <p class="text-sm text-red-800 mt-1 italic" id="deletionReasonInfo"></p>
            </div>
            
            <div class="text-gray-600 text-sm bg-green-50 border border-green-200 rounded-lg p-3">
                <span class="font-semibold">✅ Note:</span> This will restore the version and make it available again.
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeRestoreModal()" 
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button onclick="restoreReport()" 
                    class="px-5 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                Restore Version
            </button>
        </div>
    </div>
</div>

<!-- Bulk Restore Modal -->
<div id="bulkRestoreModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Bulk Restore Reports
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base mb-4">
                Are you sure you want to restore <strong id="bulkRestoreCount" class="text-green-600"></strong> selected report(s)?
            </p>
            
            <div class="text-gray-600 text-sm bg-green-50 border border-green-200 rounded-lg p-3">
                <span class="font-semibold">✅ Note:</span> All selected reports will be restored and made available again.
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeBulkRestoreModal()" 
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button onclick="confirmBulkRestore()" 
                    class="px-5 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                Restore All
            </button>
        </div>
    </div>
</div>

{{-- BULK PERMANENT DELETE MODAL - DISABLED FOR DATA PROTECTION
     Uncomment this modal to re-enable permanent delete functionality
     Note: This was disabled to prevent accidental/malicious permanent data loss

<div id="bulkDeleteModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-red-700 to-red-800 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Permanent Delete - WARNING
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700 text-base mb-4">
                Are you sure you want to <strong class="text-red-600">permanently delete</strong> <strong id="bulkDeleteCount" class="text-red-600"></strong> selected report(s)?
            </p>
            <div class="text-red-700 text-sm bg-red-50 border border-red-300 rounded-lg p-3">
                <span class="font-semibold">⚠️ WARNING:</span> This action cannot be undone! Reports will be permanently removed from the database.
            </div>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeBulkDeleteModal()" 
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button onclick="confirmBulkDelete()" 
                    class="px-5 py-2 bg-red-700 text-white rounded-lg font-semibold hover:bg-red-800 transition-colors">
                Permanent Delete
            </button>
        </div>
    </div>
</div>
--}}

    </div>
</div>
@endsection

@section('additional-scripts')
<!-- Report History JavaScript - Loaded from external file for better caching and organization -->
<script src="{{ asset('javascript/report-history.min.js') }}?v={{ filemtime(public_path('javascript/report-history.min.js')) }}" defer></script>

<!-- Server-side data and AJAX handlers that need Laravel Blade variables -->
<script>
    // CSRF Token for all AJAX requests
    const csrfToken = '{{ csrf_token() }}';
    const routes = {
        delete: '{{ route("reports.delete") }}',
        restore: '{{ route("reports.restore") }}',
        import: '{{ route("reports.import") }}',
        bulkRestore: '{{ route("reports.bulk.restore") }}'
        // DISABLED: bulkDelete route - permanent delete functionality disabled for data protection
        // To re-enable, uncomment the route in web.php and add the bulkDelete property here
    };
    
    // ============================================
    // ACCORDION STATE PRESERVATION
    // ============================================
    
    // Toggle year section and save state
    function toggleYear(year) {
        const content = document.getElementById(`year-${year}`);
        const arrow = document.getElementById(`arrow-${year}`);
        const isExpanded = content.style.maxHeight && content.style.maxHeight !== '0px';
        
        if (isExpanded) {
            // Collapse
            content.style.maxHeight = '0px';
            arrow.classList.add('-rotate-90');
            removeExpandedYear(year);
        } else {
            // Expand
            content.style.maxHeight = content.scrollHeight + 'px';
            arrow.classList.remove('-rotate-90');
            saveExpandedYear(year);
        }
    }
    
    // Save expanded year to localStorage
    function saveExpandedYear(year) {
        let expandedYears = JSON.parse(localStorage.getItem('expandedYears') || '[]');
        if (!expandedYears.includes(year)) {
            expandedYears.push(year);
            localStorage.setItem('expandedYears', JSON.stringify(expandedYears));
        }
    }
    
    // Remove year from expanded list
    function removeExpandedYear(year) {
        let expandedYears = JSON.parse(localStorage.getItem('expandedYears') || '[]');
        expandedYears = expandedYears.filter(y => y !== year);
        localStorage.setItem('expandedYears', JSON.stringify(expandedYears));
    }
    
    // Restore accordion state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const expandedYears = JSON.parse(localStorage.getItem('expandedYears') || '[]');
        
        expandedYears.forEach(year => {
            const content = document.getElementById(`year-${year}`);
            const arrow = document.getElementById(`arrow-${year}`);
            
            if (content && arrow) {
                content.style.maxHeight = content.scrollHeight + 'px';
                arrow.classList.remove('-rotate-90');
            }
        });
    });
    
    // Save accordion state before page reload
    function saveStateAndReload() {
        // State is already saved by toggleYear function
        location.reload();
    }
    
    // ============================================
    // AJAX OPERATIONS (Require Laravel Blade)
    // ============================================
    
    function deleteReport() {
        // Get and validate deletion reason
        const deletionReason = document.getElementById('deletionReason').value.trim();
        
        if (!deletionReason) {
            // Show error - reason is required
            const textarea = document.getElementById('deletionReason');
            textarea.classList.add('border-red-500', 'ring-2', 'ring-red-500');
            textarea.focus();
            
            // Show inline error message if not already present
            if (!document.getElementById('reasonError')) {
                const errorMsg = document.createElement('p');
                errorMsg.id = 'reasonError';
                errorMsg.className = 'text-red-600 text-sm mt-1 font-semibold';
                errorMsg.textContent = '⚠️ Deletion reason is required';
                textarea.parentNode.appendChild(errorMsg);
            }
            return;
        }
        
        // Remove any error styling
        const textarea = document.getElementById('deletionReason');
        textarea.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
        const errorMsg = document.getElementById('reasonError');
        if (errorMsg) errorMsg.remove();
        
        closeDeleteModal();
        
        fetch(routes.delete, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                year: deleteYear,
                quarter_start: deleteQuarterStart,
                quarter_end: deleteQuarterEnd,
                version: deleteVersion,
                deletion_reason: deletionReason
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and reload after a brief moment
                document.getElementById('successMessage').textContent = 'Report version has been soft deleted successfully. It can be restored if needed.';
                document.getElementById('successModal').style.display = 'flex';
                
                // Auto-reload after 2 seconds (state already saved)
                setTimeout(() => {
                    saveStateAndReload();
                }, 2000);
            } else {
                alert('Error: ' + (data.message || 'Failed to delete report'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the report.');
        });
    }
    
    function submitImport() {
        const year = document.getElementById('import_year').value;
        const monthStart = document.getElementById('import_month_start').value;
        const monthEnd = document.getElementById('import_month_end').value;

        if (!selectedFile) {
            alert('Please select a file to upload');
            return;
        }

        // Create FormData for file upload
        const formData = new FormData();
        formData.append('file', selectedFile);
        formData.append('year', year);
        formData.append('month_start', monthStart);
        formData.append('month_end', monthEnd);

        // Show loading state
        const submitBtn = event.target;
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Importing...';
        submitBtn.disabled = true;

        fetch(routes.import, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;

            if (data.success) {
                closeImportModal();
                document.getElementById('successMessage').textContent = data.message || 'Historical data imported successfully!';
                document.getElementById('successModal').style.display = 'flex';
            } else {
                console.error('Import failed:', data);
                alert('Error: ' + (data.message || 'Failed to import data') + '\\n\\nDetails: ' + JSON.stringify(data.errors || {}));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            alert('An error occurred while importing the data: ' + error.message);
        });
    }
    
    function populateVersionList(year, quarterStart, quarterEnd, currentVersion) {
        const versionList = document.getElementById('version-list');
        versionList.innerHTML = '<p class=\"text-gray-500 text-sm\">Loading versions...</p>';
        
        // Find all ACTIVE (non-deleted) versions for this period from the page
        const allVersions = [];
        @foreach($archivedReports ?? [] as $report)
            @if($report->year && !$report->deleted_at)
                if ({{ $report->year }} === year && 
                    {{ $report->quarter_start }} === quarterStart && 
                    {{ $report->quarter_end }} === quarterEnd &&
                    {{ $report->version }} !== currentVersion) {
                    allVersions.push({
                        version: {{ $report->version }},
                        savedAt: '{{ $report->saved_at ? \Carbon\Carbon::parse($report->saved_at)->format('M d, Y h:i A') : 'N/A' }}',
                        dataSource: '{{ $report->data_source }}'
                    });
                }
            @endif
        @endforeach
        
        if (allVersions.length === 0) {
            versionList.innerHTML = '<p class=\"text-gray-500 text-sm\">No other versions available for comparison.</p>';
            return;
        }
        
        // Sort by version descending
        allVersions.sort((a, b) => b.version - a.version);
        
        // Create clickable version cards
        versionList.innerHTML = allVersions.map(v => `
            <button onclick=\"goToComparison(${v.version})\" 
                    class=\"w-full text-left p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-all\">
                <div class=\"flex items-center justify-between\">
                    <div>
                        <p class=\"font-bold text-purple-600\">Version ${v.version}</p>
                        <p class=\"text-sm text-gray-600\">${v.savedAt}</p>
                        <span class=\"inline-block mt-1 px-2 py-1 rounded-full text-xs font-semibold ${v.dataSource === 'calculated' ? 'bg-primary-100 text-primary-800' : 'bg-yellow-100 text-yellow-800'}\">
                            ${v.dataSource === 'calculated' ? 'Calculated' : 'Manual Edit'}
                        </span>
                    </div>
                    <svg class=\"w-5 h-5 text-gray-400\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" viewBox=\"0 0 24 24\">
                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M9 5l7 7-7 7\"/>
                    </svg>
                </div>
            </button>
        `).join('');
    }
    
    function restoreReport() {
        closeRestoreModal();
        
        fetch(routes.restore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                year: restoreYear,
                quarter_start: restoreQuarterStart,
                quarter_end: restoreQuarterEnd,
                version: restoreVersion
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and reload after a brief moment
                document.getElementById('successMessage').textContent = 'Report version has been restored successfully!';
                document.getElementById('successModal').style.display = 'flex';
                
                // Auto-reload after 2 seconds (state already saved)
                setTimeout(() => {
                    saveStateAndReload();
                }, 2000);
            } else {
                alert('Error: ' + (data.message || 'Failed to restore report'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while restoring the report.');
        });
    }
    
    function confirmBulkRestore() {
        const selected = getSelectedReports();
        const confirmBtn = event.target;
        
        // Show loading state
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Restoring...
        `;
        
        fetch(routes.bulkRestore, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ reports: selected })
        })
        .then(response => response.json())
        .then(data => {
            closeBulkRestoreModal();
            
            // Reset button
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm Restore';
            
            if (data.success) {
                document.getElementById('successMessage').textContent = `Successfully restored ${data.count} report(s)!`;
                document.getElementById('successModal').style.display = 'flex';
                
                // Auto-reload after 2 seconds (state already saved)
                setTimeout(() => {
                    saveStateAndReload();
                }, 2000);
            } else {
                alert('Error: ' + (data.message || 'Failed to restore reports'));
            }
        })
        .catch(error => {
            closeBulkRestoreModal();
            
            // Reset button
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm Restore';
            
            console.error('Error:', error);
            alert('An error occurred while restoring reports.');
        });
    }
    
    /* PERMANENT DELETE FUNCTION - DISABLED FOR DATA PROTECTION
       Uncomment this function to re-enable permanent delete functionality
       Note: This was disabled to prevent accidental/malicious permanent data loss
    
    function confirmBulkDelete() {
        const selected = getSelectedReports();
        const confirmBtn = event.target;
        
        // Show loading state
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Deleting...
        `;
        
        fetch(routes.bulkDelete, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ reports: selected })
        })
        .then(response => response.json())
        .then(data => {
            closeBulkDeleteModal();
            
            // Reset button
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm Permanent Delete';
            
            if (data.success) {
                document.getElementById('successMessage').textContent = `Successfully deleted ${data.count} report(s) permanently!`;
                document.getElementById('successModal').style.display = 'flex';
                
                // Auto-reload after 2 seconds (state already saved)
                setTimeout(() => {
                    saveStateAndReload();
                }, 2000);
            } else {
                alert('Error: ' + (data.message || 'Failed to delete reports'));
            }
        })
        .catch(error => {
            closeBulkDeleteModal();
            
            // Reset button
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = 'Confirm Permanent Delete';
            
            console.error('Error:', error);
            alert('An error occurred while deleting reports.');
        });
    }
    */
</script>
@endsection
