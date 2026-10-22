@extends('layouts.responsive-layout')

@section('title', 'View Archived Report')

@section('additional-styles')
    @include('health_worker.report-styles')
    <style>
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 400px;
        }
        
        .toast {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 16px 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideInRight 0.3s ease-out;
            border-left: 4px solid;
        }
        
        .toast.success {
            border-left-color: #10b981;
        }
        
        .toast.error {
            border-left-color: #ef4444;
        }
        
        .toast.warning {
            border-left-color: #f59e0b;
        }
        
        .toast-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .toast-message {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .toast-close {
            flex-shrink: 0;
            cursor: pointer;
            color: #9ca3af;
            transition: color 0.2s;
        }
        
        .toast-close:hover {
            color: #4b5563;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
        
        .toast.hiding {
            animation: slideOutRight 0.3s ease-in forwards;
        }
        
        /* ===== STICKY COLUMNS OVERRIDE (5 columns: Area + 4 Eligible Populations) =====
           Override shared styles with equal 85px widths for all eligible population columns */
        
        /* Column 1: AREA - Header */
        #vaccinationReportTable thead tr:first-child th:nth-child(1) {
            position: sticky;
            left: 0;
            z-index: 13;
            background-color: #E5E7EB;
            color: #111827;
            font-weight: 700;
            min-width: 110px;
            width: 110px;
            font-size: 13px;
        }
        
        /* Column 1: AREA - Body */
        #vaccinationReportTable tbody td:nth-child(1) {
            position: sticky;
            left: 0;
            z-index: 9;
            background-color: #FFFFFF;
            font-weight: 600;
            min-width: 110px;
            width: 110px;
            font-size: 13px;
            text-align: left;
            padding-left: 10px;
        }
        
        /* Column 2: Eligible Pop (Under 1 yr) - Header */
        #vaccinationReportTable thead tr:first-child th:nth-child(2) {
            position: sticky;
            left: 110px;
            z-index: 12;
            background-color: #E5E7EB;
            color: #111827;
            min-width: 85px;
            width: 85px;
            font-size: 10px;
            padding: 8px 4px;
            line-height: 1.3;
        }
        
        /* Column 2: Eligible Pop (Under 1 yr) - Body */
        #vaccinationReportTable tbody td:nth-child(2) {
            position: sticky;
            left: 110px;
            z-index: 8;
            background-color: #FFFFFF;
            min-width: 85px;
            width: 85px;
        }
        
        /* Column 3: Eligible Pop (0-12 mos) - Header */
        #vaccinationReportTable thead tr:first-child th:nth-child(3) {
            position: sticky;
            left: 195px;
            z-index: 11;
            background-color: #E5E7EB;
            color: #111827;
            min-width: 85px;
            width: 85px;
            font-size: 10px;
            padding: 8px 4px;
            line-height: 1.3;
        }
        
        /* Column 3: Eligible Pop (0-12 mos) - Body */
        #vaccinationReportTable tbody td:nth-child(3) {
            position: sticky;
            left: 195px;
            z-index: 7;
            background-color: #FFFFFF;
            min-width: 85px;
            width: 85px;
        }
        
        /* Column 4: Eligible Pop (13-23 mos) - Header */
        #vaccinationReportTable thead tr:first-child th:nth-child(4) {
            position: sticky;
            left: 280px;
            z-index: 10;
            background-color: #E5E7EB;
            color: #111827;
            min-width: 85px;
            width: 85px;
            font-size: 10px;
            padding: 8px 4px;
            line-height: 1.3;
            box-shadow: none;
        }
        
        /* Column 4: Eligible Pop (13-23 mos) - Body */
        #vaccinationReportTable tbody td:nth-child(4) {
            position: sticky;
            left: 280px;
            z-index: 6;
            background-color: #FFFFFF;
            min-width: 85px;
            width: 85px;
            box-shadow: none;
        }
        
        /* Column 5: Eligible Pop (0-23 mos) - Header with right shadow */
        #vaccinationReportTable thead tr:first-child th:nth-child(5) {
            position: sticky;
            left: 365px;
            z-index: 10;
            background-color: #E5E7EB;
            color: #111827;
            min-width: 85px;
            width: 85px;
            font-size: 10px;
            padding: 8px 4px;
            line-height: 1.3;
            box-shadow: 4px 0 8px rgba(0,0,0,0.15);
        }
        
        /* Column 5: Eligible Pop (0-23 mos) - Body with right shadow */
        #vaccinationReportTable tbody td:nth-child(5) {
            position: sticky;
            left: 365px;
            z-index: 5;
            background-color: #FFFFFF;
            min-width: 85px;
            width: 85px;
            box-shadow: 4px 0 8px rgba(0,0,0,0.15);
        }
        
        /* Vaccine header columns start from column 6 */
        #vaccinationReportTable thead tr:first-child th:nth-child(n+6) {
            background-color: #E5E7EB;
            min-width: 180px;
            font-size: 11px;
            z-index: 1;
        }
        
        /* Vaccine data columns start from column 6 */
        #vaccinationReportTable tbody td:nth-child(n+6) {
            min-width: 45px;
            width: 45px;
            padding: 8px 4px;
            font-size: 13px;
        }
        
        /* Bold the Total (T) columns - every 4th column starting from 8 */
        #vaccinationReportTable tbody td:nth-child(4n+8) {
            font-weight: 700;
            color: #1F2937;
        }
        
        /* TOTAL row styling for 5 sticky columns */
        #vaccinationReportTable tbody tr[data-is-total="true"] td:nth-child(1),
        #vaccinationReportTable tbody tr[data-is-total="true"] td:nth-child(2),
        #vaccinationReportTable tbody tr[data-is-total="true"] td:nth-child(3),
        #vaccinationReportTable tbody tr[data-is-total="true"] td:nth-child(4),
        #vaccinationReportTable tbody tr[data-is-total="true"] td:nth-child(5) {
            background-color: #FEF3C7;
        }
        
        /* Hover effect for 5 sticky columns */
        #vaccinationReportTable tbody tr:hover td:nth-child(1),
        #vaccinationReportTable tbody tr:hover td:nth-child(2),
        #vaccinationReportTable tbody tr:hover td:nth-child(3),
        #vaccinationReportTable tbody tr:hover td:nth-child(4),
        #vaccinationReportTable tbody tr:hover td:nth-child(5) {
            background-color: #F3E8FF;
        }
        
        /* TOTAL row hover override */
        #vaccinationReportTable tbody tr[data-is-total="true"]:hover td:nth-child(1),
        #vaccinationReportTable tbody tr[data-is-total="true"]:hover td:nth-child(2),
        #vaccinationReportTable tbody tr[data-is-total="true"]:hover td:nth-child(3),
        #vaccinationReportTable tbody tr[data-is-total="true"]:hover td:nth-child(4),
        #vaccinationReportTable tbody tr[data-is-total="true"]:hover td:nth-child(5) {
            background-color: #FDE68A;
        }
    </style>
@endsection

@section('content')
<div class="container mx-auto px-4 py-6">
    
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('reports.history') }}" class="inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 font-semibold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Report History
        </a>
    </div>

    <!-- Report Banner (READ-ONLY VERSION) -->
    <div class="report-banner bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl shadow-xl p-8 mb-6 text-white">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Left side - Report title and info -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-3">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">CHILD CARE PROGRAM</h1>
                        <p class="text-white/90 text-sm">Vaccine Preventable Diseases Surveillance Report</p>
                    </div>
                </div>
                
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-semibold">{{ $dateRange }}</span>
                    </div>
                    
                    <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="font-semibold">CALAUAN, LAGUNA</span>
                    </div>
                    
                    <!-- Version Badge -->
                    <div class="flex items-center gap-2 bg-blue-500/90 backdrop-blur-sm px-4 py-2 rounded-lg ring-2 ring-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span class="font-bold">VERSION {{ $version ?? 1 }}</span>
                    </div>
                    
                    <!-- Archived Badge -->
                    <div class="flex items-center gap-2 bg-yellow-500/90 backdrop-blur-sm px-4 py-2 rounded-lg ring-2 ring-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span class="font-bold">READ-ONLY ARCHIVE</span>
                    </div>
                    
                    @if($savedAt ?? null)
                        <!-- Saved Date Badge -->
                        <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-semibold">Saved: {{ \Carbon\Carbon::parse($savedAt)->format('M d, Y h:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Read-Only Notice Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-blue-900 mb-1">Archived Report - Read Only</h3>
                <p class="text-blue-800 text-sm mb-3">
                    This is Version {{ $version ?? 1 }} of the report, saved on {{ $savedAt ? \Carbon\Carbon::parse($savedAt)->format('M d, Y \a\t h:i A') : 'N/A' }}. 
                    Archived reports cannot be edited directly to preserve data integrity.
                </p>
                <p class="text-blue-700 text-sm">
                    If you need to make changes, click <strong>"Edit & Save New Version"</strong> below to create an editable copy with an incremented version number.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Vaccination Report Table -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table id="vaccinationReportTable" class="vaccination-table">
                <thead>
                    @php
                        // Get ALL unique vaccine names across ALL barangays
                        // This ensures vaccines that don't exist in first barangay still appear as columns
                        $vaccineNames = [];
                        if (isset($reportData) && count($reportData) > 0) {
                            foreach ($reportData as $row) {
                                if (isset($row['vaccines'])) {
                                    foreach (array_keys($row['vaccines']) as $vaccineName) {
                                        if (str_starts_with($vaccineName, '__FIC__') || str_starts_with($vaccineName, '__CIC__')) continue;
                                        if (!in_array($vaccineName, $vaccineNames)) {
                                            $vaccineNames[] = $vaccineName;
                                        }
                                    }
                                }
                            }
                        }
                    @endphp
                    <!-- Vaccine Names Header Row -->
                    <tr class="vaccine-header-row">
                        <th rowspan="2" class="area-column">Area</th>
                        <th rowspan="2" class="population-column">Eligible Pop<br>(Under 1 yr)</th>
                        <th rowspan="2" class="population-column">Eligible Pop<br>(0-12 mos)</th>
                        <th rowspan="2" class="population-column">Eligible Pop<br>(13-23 mos)</th>
                        <th rowspan="2" class="population-column">Eligible Pop<br>(0-23 mos)</th>
                        @foreach($vaccineNames as $vaccineName)
                            @php $isCustom = str_starts_with($vaccineName, '__custom__'); @endphp
                            <th colspan="4" class="vaccine-header {{ $isCustom ? 'custom-column-header' : '' }}"
                                @if($isCustom) data-custom-title="{{ substr($vaccineName, 10) }}" @endif>
                                @if($isCustom)
                                    <span class="custom-header-text">{{ substr($vaccineName, 10) }}</span>
                                    <span class="custom-header-actions hidden">
                                        <button onclick="editCustomColumnHeader(this)" title="Rename" class="inline-flex items-center justify-center w-5 h-5 rounded bg-white/30 hover:bg-white/50 text-[10px] leading-none ml-1 align-middle">&#9998;</button>
                                        <button onclick="showDeleteColumnModal('{{ $vaccineName }}')" title="Remove" class="inline-flex items-center justify-center w-5 h-5 rounded bg-red-400/60 hover:bg-red-500 text-white text-[10px] leading-none ml-0.5 align-middle">&#10005;</button>
                                    </span>
                                @else
                                    {{ $vaccineName }}
                                @endif
                            </th>
                        @endforeach
                        <!-- FIC Column -->
                        <th colspan="4" class="vaccine-header" style="background-color: #DBEAFE; color: #1E40AF;">FIC**</th>
                        <!-- CIC Column -->
                        <th colspan="4" class="vaccine-header" style="background-color: #D1FAE5; color: #065F46;">CIC***</th>
                    </tr>

                    <!-- M/F/T/% Sub-header Row -->
                    <tr class="vaccine-subheader-row">
                        @foreach($vaccineNames as $vaccineName)
                            <th class="gender-header">M</th>
                            <th class="gender-header">F</th>
                            <th class="gender-header total-header">T</th>
                            <th class="gender-header percent-header">%</th>
                        @endforeach
                        <!-- FIC sub-headers -->
                        <th class="gender-header" style="color: #1E40AF;">M</th>
                        <th class="gender-header" style="color: #1E40AF;">F</th>
                        <th class="gender-header" style="color: #1E40AF;">T</th>
                        <th class="gender-header" style="color: #1E40AF;">%</th>
                        <!-- CIC sub-headers -->
                        <th class="gender-header" style="color: #065F46;">M</th>
                        <th class="gender-header" style="color: #065F46;">F</th>
                        <th class="gender-header" style="color: #065F46;">T</th>
                        <th class="gender-header" style="color: #065F46;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData as $barangay)
                        <tr class="@if($barangay['barangay'] === 'TOTAL') total-row @endif" @if($barangay['barangay'] === 'TOTAL') data-is-total="true" @endif>
                            <!-- Area Name -->
                            <td class="area-column" data-barangay-name="{{ $barangay['barangay'] }}">{{ $barangay['barangay'] }}</td>
                            
                            <!-- Four Eligible Population Columns -->
                            <td class="population-column">{{ number_format($barangay['eligible_population_under_1_year'] ?? 0) }}</td>
                            <td class="population-column">{{ number_format($barangay['eligible_population_0_12_months'] ?? 0) }}</td>
                            <td class="population-column">{{ number_format($barangay['eligible_population_13_23_months'] ?? 0) }}</td>
                            <td class="population-column">{{ number_format($barangay['eligible_population_0_23_months'] ?? 0) }}</td>
                            
                            <!-- Vaccine Data - loop through standardized vaccine names to match headers -->
                            @foreach($vaccineNames as $vaccineName)
                                @php
                                    // Get vaccine data if it exists for this barangay, otherwise use defaults
                                    $vaccine = $barangay['vaccines'][$vaccineName] ?? [
                                        'male_count' => 0,
                                        'female_count' => 0,
                                        'total_count' => 0,
                                        'percentage' => 0
                                    ];
                                @endphp
                                <td class="gender-cell editable-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="male"
                                    data-original="{{ $vaccine['male_count'] }}">{{ $vaccine['male_count'] }}</td>
                                <td class="gender-cell editable-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="female"
                                    data-original="{{ $vaccine['female_count'] }}">{{ $vaccine['female_count'] }}</td>
                                <td class="gender-cell total-cell editable-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="total"
                                    data-original="{{ $vaccine['total_count'] }}">{{ $vaccine['total_count'] }}</td>
                                <td class="gender-cell percent-cell" 
                                    data-barangay="{{ $barangay['barangay'] }}" 
                                    data-vaccine="{{ $vaccineName }}" 
                                    data-type="percentage">{{ number_format($vaccine['percentage'], 2) }}%</td>
                            @endforeach

                            <!-- FIC Data -->
                            @php
                                $ficData = $barangay['fic'] ?? ['male_count' => 0, 'female_count' => 0, 'total_count' => 0, 'percentage' => 0];
                            @endphp
                            <td class="gender-cell editable-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__FIC__"
                                data-type="male">{{ $ficData['male_count'] }}</td>
                            <td class="gender-cell editable-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__FIC__"
                                data-type="female">{{ $ficData['female_count'] }}</td>
                            <td class="gender-cell total-cell editable-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__FIC__"
                                data-type="total">{{ $ficData['total_count'] }}</td>
                            <td class="gender-cell percent-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__FIC__"
                                data-type="percentage">{{ number_format($ficData['percentage'], 2) }}%</td>

                            <!-- CIC Data -->
                            @php
                                $cicData = $barangay['cic'] ?? ['male_count' => 0, 'female_count' => 0, 'total_count' => 0, 'percentage' => 0];
                            @endphp
                            <td class="gender-cell editable-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__CIC__"
                                data-type="male">{{ $cicData['male_count'] }}</td>
                            <td class="gender-cell editable-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__CIC__"
                                data-type="female">{{ $cicData['female_count'] }}</td>
                            <td class="gender-cell total-cell editable-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__CIC__"
                                data-type="total">{{ $cicData['total_count'] }}</td>
                            <td class="gender-cell percent-cell"
                                data-barangay="{{ $barangay['barangay'] }}"
                                data-vaccine="__CIC__"
                                data-type="percentage">{{ number_format($cicData['percentage'], 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Actions
        </h3>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Edit Mode Controls - Only show for latest version -->
            @if($isLatestVersion)
                <!-- Toggle Edit Mode Button (shown in view mode) -->
                <button id="toggleEditBtn" onclick="toggleEditMode()" class="inline-flex items-center gap-2 px-5 py-3 bg-purple-600 text-white rounded-lg font-semibold shadow-md hover:bg-purple-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit & Save New Version
                </button>
                
                <!-- Save Button (shown in edit mode) -->
                <button id="saveEditBtn" onclick="saveEditedReport()" class="hidden items-center gap-2 px-5 py-3 bg-green-600 text-white rounded-lg font-semibold shadow-md hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save as Version {{ ($version ?? 1) + 1 }}
                </button>
                
                <!-- Cancel Button (shown in edit mode) -->
                <button id="cancelEditBtn" onclick="cancelEditMode()" class="hidden items-center gap-2 px-5 py-3 bg-gray-500 text-white rounded-lg font-semibold shadow-md hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>

                <!-- Add Column Button (shown in edit mode) -->
                <button id="addColumnBtn" onclick="showAddColumnModal()" class="hidden items-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-lg font-semibold shadow-md hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>
                    Add Column
                </button>
            @else
                <!-- Show message and link to latest version for older versions -->
                <div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-900">You're viewing an older version</p>
                        <p class="text-xs text-amber-700">Editing is only available for the latest version (v{{ $latestVersion }})</p>
                    </div>
                    <a href="{{ route('reports.show', ['year' => $year, 'quarter_start' => $quarterStart, 'quarter_end' => $quarterEnd, 'version' => $latestVersion]) }}" 
                       class="inline-flex items-center gap-1 px-4 py-2 bg-amber-600 text-white rounded-lg text-sm font-semibold hover:bg-amber-700 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View Latest (v{{ $latestVersion }})
                    </a>
                </div>
            @endif
            
            <!-- Divider -->
            <div class="h-10 w-px bg-gray-300"></div>
            
            <!-- Export to PDF Button -->
            <button onclick="exportToPDF()" class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 text-white rounded-lg font-semibold shadow-md hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button>
            
            <!-- Export to Excel Button -->
            <button onclick="exportToExcel()" class="inline-flex items-center gap-2 px-5 py-3 bg-green-600 text-white rounded-lg font-semibold shadow-md hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
        </div>
        
        <!-- Helper Text -->
        <div id="editModeHelper" class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-600">
                <svg class="w-4 h-4 inline text-purple-600 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <strong>Tip:</strong> Click "Edit & Save New Version" to modify vaccination counts directly in the table. Changes will be saved as Version {{ ($version ?? 1) + 1 }}.
            </p>
        </div>
        
        <!-- Edit Mode Active Helper (hidden by default) -->
        <div id="editModeActive" class="hidden mt-4 p-4 bg-purple-50 rounded-lg border-2 border-purple-300">
            <p class="text-sm font-semibold text-purple-900 mb-2">
                <svg class="w-5 h-5 inline text-purple-600 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Mode Active
            </p>
            <ul class="text-sm text-purple-800 space-y-1 ml-6 list-disc">
                <li>Click any Male, Female, or Total cell to edit (except TOTAL row)</li>
                <li>Percentages will recalculate automatically</li>
                <li>Click "Save as Version {{ ($version ?? 1) + 1 }}" when done</li>
                <li>Click "Cancel" to discard changes</li>
            </ul>
        </div>
    </div>
    
</div>

<!-- Edit & Save New Version Confirmation Modal -->
<div id="editNewVersionModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit & Save New Version
            </h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-base mb-4">
                You're about to edit this report and create <strong>Version {{ ($version ?? 1) + 1 }}</strong>.
            </p>
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-lg mb-4">
                <p class="text-sm text-purple-900 mb-2">
                    <strong>📝 What happens next:</strong>
                </p>
                <ul class="text-sm text-purple-800 space-y-1 ml-4">
                    <li>• The table will become editable</li>
                    <li>• Click any Male, Female, or Total cell to modify values</li>
                    <li>• Percentages will update automatically</li>
                    <li>• Click "Save" to create <strong>Version {{ ($version ?? 1) + 1 }}</strong></li>
                    <li>• This version (v{{ $version ?? 1 }}) will remain unchanged</li>
                </ul>
            </div>
            <p class="text-sm text-gray-600">
                Do you want to proceed?
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeEditNewVersionModal()" 
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button onclick="confirmEditNewVersion()" 
                    class="px-5 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                Start Editing
            </button>
        </div>
    </div>
</div>

<!-- Add Custom Column Modal -->
<div id="addColumnModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>
                Add Custom Column
            </h3>
        </div>
        <div class="p-6">
            <label for="customColumnTitle" class="block text-sm font-semibold text-gray-700 mb-2">Column Title</label>
            <input type="text" id="customColumnTitle" maxlength="50" placeholder="Enter column title..."
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 text-gray-800 font-medium focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200">
            <p class="text-xs text-gray-500 mt-2">This column will appear with M, F, T, % sub-columns for each barangay row.</p>
        </div>
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeAddColumnModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">Cancel</button>
            <button onclick="confirmAddColumn()" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">Add Column</button>
        </div>
    </div>
</div>

<!-- Rename Custom Column Modal -->
<div id="renameColumnModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Rename Column
            </h3>
        </div>
        <div class="p-6">
            <label for="renameColumnInput" class="block text-sm font-semibold text-gray-700 mb-2">Column Title</label>
            <input type="text" id="renameColumnInput" maxlength="50"
                   class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 text-gray-800 font-medium focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200">
            <input type="hidden" id="renameColumnOldKey">
        </div>
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeRenameColumnModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">Cancel</button>
            <button onclick="confirmRenameColumn()" class="px-5 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors">Rename</button>
        </div>
    </div>
</div>

<!-- Delete Custom Column Confirmation Modal -->
<div id="deleteColumnModal" class="fixed inset-0 bg-black/50 z-50 items-center justify-center" style="display: none;">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete Column
            </h3>
        </div>
        <div class="p-6">
            <p class="text-gray-700">Are you sure you want to delete the <strong id="deleteColumnTitle"></strong> column?</p>
            <p class="text-sm text-red-600 mt-2">This action cannot be undone.</p>
            <input type="hidden" id="deleteColumnKey">
        </div>
        <div class="bg-gray-50 px-6 py-4 flex gap-3 justify-end">
            <button onclick="closeDeleteColumnModal()" class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-colors">Cancel</button>
            <button onclick="confirmDeleteColumn()" class="px-5 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-colors">Delete</button>
        </div>
    </div>
</div>
@endsection

@section('additional-scripts')
    <!-- Export libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        // Global state for edit mode
        let isEditMode = false;
        let originalData = {};
        let modifiedCells = new Set();
        let customColumnCounter = 0;

        // ========== Custom Column Functions ==========

        function showAddColumnModal() {
            document.getElementById('addColumnModal').style.display = 'flex';
            const input = document.getElementById('customColumnTitle');
            input.value = '';
            input.focus();
        }

        function closeAddColumnModal() {
            document.getElementById('addColumnModal').style.display = 'none';
        }

        function logCustomColumnAction(action, title, oldTitle) {
            fetch('{{ route("reports.logCustomColumn") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ action, column_title: title, old_title: oldTitle || null })
            }).catch(() => {});
        }

        function confirmAddColumn() {
            const title = document.getElementById('customColumnTitle').value.trim();
            if (!title) {
                document.getElementById('customColumnTitle').classList.add('border-red-400');
                return;
            }
            closeAddColumnModal();
            addCustomColumn(title);
            logCustomColumnAction('add', title);
        }

        function addCustomColumn(title) {
            customColumnCounter++;
            const key = '__custom__' + title;

            // Add header cell to the first header row
            const headerRow = document.querySelector('#vaccinationReportTable thead tr:first-child');
            const headerTh = document.createElement('th');
            headerTh.setAttribute('colspan', '4');
            headerTh.className = 'vaccine-header custom-column-header';
            headerTh.dataset.customTitle = title;
            headerTh.innerHTML = '<span class="custom-header-text">' + escapeHtml(title) + '</span>' +
                '<span class="custom-header-actions" style="display:inline-flex;align-items:center;">' +
                '<button onclick="editCustomColumnHeader(this)" title="Rename" class="inline-flex items-center justify-center w-5 h-5 rounded bg-white/30 hover:bg-white/50 text-[10px] leading-none ml-1 align-middle">&#9998;</button>' +
                '<button onclick="showDeleteColumnModal(\'' + key + '\')" title="Remove" class="inline-flex items-center justify-center w-5 h-5 rounded bg-red-400/60 hover:bg-red-500 text-white text-[10px] leading-none ml-0.5 align-middle">&#10005;</button>' +
                '</span>';
            headerRow.appendChild(headerTh);

            // Add sub-header cells (M/F/T/%)
            const subHeaderRow = document.querySelector('#vaccinationReportTable thead tr:nth-child(2)');
            ['M', 'F', 'T', '%'].forEach(label => {
                const subTh = document.createElement('th');
                subTh.className = 'gender-header custom-column-subheader';
                subTh.textContent = label;
                subHeaderRow.appendChild(subTh);
            });

            // Add data cells to each body row
            const bodyRows = document.querySelectorAll('#vaccinationReportTable tbody tr');
            bodyRows.forEach(row => {
                const barangay = row.querySelector('.area-column')?.dataset.barangayName || '';
                ['male', 'female', 'total', 'percentage'].forEach(type => {
                    const td = document.createElement('td');
                    td.className = type === 'percentage'
                        ? 'gender-cell percent-cell custom-column-cell'
                        : 'gender-cell editable-cell custom-column-cell';
                    td.dataset.barangay = barangay;
                    td.dataset.vaccine = key;
                    td.dataset.type = type;
                    td.dataset.original = '0';
                    td.textContent = type === 'percentage' ? '0.00%' : '0';
                    if (isEditMode && barangay !== 'TOTAL' && type !== 'percentage') {
                        td.style.cursor = 'pointer';
                        td.style.backgroundColor = '#fef3c7';
                        td.title = 'Click to edit';
                    }
                    row.appendChild(td);
                });
            });

            // Track the new column as a modification so save works
            modifiedCells.add('__new_column__' + key);
        }

        function editCustomColumnHeader(btn) {
            const th = btn.closest('th');
            const oldTitle = th.dataset.customTitle;
            const oldKey = '__custom__' + oldTitle;
            document.getElementById('renameColumnInput').value = oldTitle;
            document.getElementById('renameColumnOldKey').value = oldKey;
            document.getElementById('renameColumnModal').style.display = 'flex';
            document.getElementById('renameColumnInput').focus();
        }

        function closeRenameColumnModal() {
            document.getElementById('renameColumnModal').style.display = 'none';
        }

        function confirmRenameColumn() {
            const oldKey = document.getElementById('renameColumnOldKey').value;
            const newTitle = document.getElementById('renameColumnInput').value.trim();
            if (!newTitle) return;
            const oldTitle = oldKey.replace('__custom__', '');
            if (newTitle === oldTitle) { closeRenameColumnModal(); return; }
            const newKey = '__custom__' + newTitle;
            closeRenameColumnModal();

            // Update header
            const th = document.querySelector(`th[data-custom-title="${CSS.escape(oldTitle)}"]`);
            if (th) {
                th.dataset.customTitle = newTitle;
                th.querySelector('.custom-header-text').textContent = newTitle;
                th.querySelector('[onclick*="showDeleteColumnModal"]').setAttribute('onclick', "showDeleteColumnModal('" + newKey + "')");
            }

            // Update all body cells
            document.querySelectorAll(`[data-vaccine="${CSS.escape(oldKey)}"]`).forEach(cell => {
                cell.dataset.vaccine = newKey;
                modifiedCells.add(`${cell.dataset.barangay}|${newKey}|${cell.dataset.type}`);
            });

            logCustomColumnAction('rename', newTitle, oldTitle);
        }

        function showDeleteColumnModal(key) {
            const title = key.replace('__custom__', '');
            document.getElementById('deleteColumnTitle').textContent = title;
            document.getElementById('deleteColumnKey').value = key;
            document.getElementById('deleteColumnModal').style.display = 'flex';
        }

        function closeDeleteColumnModal() {
            document.getElementById('deleteColumnModal').style.display = 'none';
        }

        function confirmDeleteColumn() {
            const key = document.getElementById('deleteColumnKey').value;
            closeDeleteColumnModal();

            const title = key.replace('__custom__', '');
            const headerRow = document.querySelector('#vaccinationReportTable thead tr:first-child');
            const subRow = document.querySelector('#vaccinationReportTable thead tr:nth-child(2)');

            // Find the position of the header being removed (count only non-rowspan headers)
            const allHeaders = Array.from(headerRow.querySelectorAll('th'));
            let headerIndex = 0;
            let position = -1;
            for (const th of allHeaders) {
                if (th.rowSpan === 2) continue; // skip rowspan headers (Area, Pop columns)
                if (th.dataset.customTitle === title) {
                    position = headerIndex;
                    th.remove();
                    break;
                }
                headerIndex++;
            }

            // Remove the 4 sub-headers at the same position
            if (position >= 0) {
                const subHeaders = Array.from(subRow.querySelectorAll('th'));
                for (let i = 0; i < 4; i++) {
                    const idx = position * 4; // each header has 4 sub-headers
                    if (subRow.children[idx]) subRow.children[idx].remove();
                }
            }

            // Remove all data cells
            document.querySelectorAll(`[data-vaccine="${CSS.escape(key)}"]`).forEach(cell => cell.remove());

            modifiedCells.add('__remove__' + key);

            logCustomColumnAction('delete', title);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Handle Enter key in add column modal
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('customColumnTitle');
            if (titleInput) {
                titleInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        confirmAddColumn();
                    }
                });
            }
        });
        
        // Show Edit & Save New Version Modal
        function showEditNewVersionModal() {
            const modal = document.getElementById('editNewVersionModal');
            modal.style.display = 'flex';
        }

        // Close Edit & Save New Version Modal
        function closeEditNewVersionModal() {
            const modal = document.getElementById('editNewVersionModal');
            modal.style.display = 'none';
        }

        // Confirm Edit - Enable edit mode instead of redirecting
        function confirmEditNewVersion() {
            closeEditNewVersionModal();
            enableEditMode();
        }
        
        // Toggle Edit Mode (wrapper for button)
        function toggleEditMode() {
            showEditNewVersionModal();
        }
        
        // Enable Edit Mode
        function enableEditMode() {
            isEditMode = true;

            // Toggle button visibility
            document.getElementById('toggleEditBtn').classList.add('hidden');
            document.getElementById('saveEditBtn').classList.remove('hidden');
            document.getElementById('saveEditBtn').classList.add('inline-flex');
            document.getElementById('cancelEditBtn').classList.remove('hidden');
            document.getElementById('cancelEditBtn').classList.add('inline-flex');

            // Show Add Column button
            const addColumnBtn = document.getElementById('addColumnBtn');
            if (addColumnBtn) {
                addColumnBtn.classList.remove('hidden');
                addColumnBtn.classList.add('inline-flex');
            }

            // Show custom column header actions via inline style (bypasses CSS specificity)
            document.querySelectorAll('.custom-header-actions').forEach(el => {
                el.style.display = 'inline-flex';
                el.style.alignItems = 'center';
            });

            // Toggle helper text
            document.getElementById('editModeHelper').classList.add('hidden');
            document.getElementById('editModeActive').classList.remove('hidden');

            // Store original data and apply visual feedback
            const editableCells = document.querySelectorAll('.editable-cell');
            editableCells.forEach(cell => {
                const barangay = cell.dataset.barangay;
                const vaccine = cell.dataset.vaccine;
                const type = cell.dataset.type;

                if (barangay === 'TOTAL') return;
                if (type === 'percentage') return;

                const key = `${barangay}|${vaccine}|${type}`;
                originalData[key] = cell.textContent.trim();

                cell.style.cursor = 'pointer';
                cell.style.backgroundColor = '#fef3c7';
                cell.title = 'Click to edit';
            });

            // Event delegation: single click handler on document for all editable cells
            if (!window._editListenerAttached) {
                window._editListenerAttached = true;
                document.addEventListener('click', function(e) {
                    if (!isEditMode) return;
                    const cell = e.target.closest('.editable-cell');
                    if (!cell) return;
                    if (cell.dataset.barangay === 'TOTAL') return;
                    if (cell.dataset.type === 'percentage') return;
                    if (cell.querySelector('input')) return;
                    makeEditable(cell);
                });
            }
        }
        
        // Make a cell editable
        function makeEditable(cell) {
            if (cell.querySelector('input')) {
                return; // Already editing
            }
            
            const currentValue = cell.textContent.trim();
            const input = document.createElement('input');
            input.type = 'number';
            input.min = '0';
            input.value = currentValue;
            input.className = 'w-full px-2 py-1 border-2 border-purple-500 rounded text-center';
            
            cell.textContent = '';
            cell.appendChild(input);
            input.focus();
            input.select();
            
            // Save on blur or Enter
            const saveEdit = () => {
                const newValue = parseInt(input.value) || 0;
                cell.textContent = newValue;
                
                // Track modification
                const key = `${cell.dataset.barangay}|${cell.dataset.vaccine}|${cell.dataset.type}`;
                if (originalData[key] !== newValue.toString()) {
                    modifiedCells.add(key);
                    cell.style.backgroundColor = '#dbeafe'; // blue-100 - indicates modified
                } else {
                    modifiedCells.delete(key);
                    cell.style.backgroundColor = '#fef3c7'; // back to amber-100
                }
                
                // Recalculate totals and percentages for this row
                recalculateRow(cell);
            };
            
            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    saveEdit();
                }
                if (e.key === 'Escape') {
                    cell.textContent = currentValue;
                }
            });
        }
        
        // Recalculate totals and percentages for a row
        function recalculateRow(cell) {
            const barangay = cell.dataset.barangay;
            const vaccine = cell.dataset.vaccine;
            const row = cell.parentElement;

            // Find male, female, total cells for this vaccine
            const cells = row.querySelectorAll(`[data-vaccine="${CSS.escape(vaccine)}"]`);
            let maleCell, femaleCell, totalCell, percentCell;

            cells.forEach(c => {
                if (c.dataset.type === 'male') maleCell = c;
                if (c.dataset.type === 'female') femaleCell = c;
                if (c.dataset.type === 'total') totalCell = c;
                if (c.dataset.type === 'percentage') percentCell = c;
            });

            if (!maleCell || !femaleCell || !totalCell || !percentCell) return;

            const male = parseInt(maleCell.textContent) || 0;
            const female = parseInt(femaleCell.textContent) || 0;
            const total = male + female;

            totalCell.textContent = total;

            const eligiblePop = getEligiblePopulation(row, vaccine);
            const percentage = eligiblePop > 0 ? (total / eligiblePop * 100) : 0;
            percentCell.textContent = percentage.toFixed(2) + '%';

            recalculateTotalRow();
        }
        
        // Get eligible population for a vaccine based on its age group
        function getEligiblePopulation(row, vaccine) {
            // Determine age group from vaccine name
            const vaccineConfig = @json($vaccineConfig ?? []);
            const baseVaccineName = vaccine.split('|')[0];
            const targetAgeGroup = vaccineConfig[baseVaccineName]?.target_age_group || 'under_1_year';
            
            // Get the appropriate eligible population column
            const cells = row.children;
            if (targetAgeGroup === 'under_1_year') {
                return parseInt(cells[1].textContent.replace(/,/g, '')) || 0; // Column 2
            } else if (targetAgeGroup === '0_12_months') {
                return parseInt(cells[2].textContent.replace(/,/g, '')) || 0; // Column 3
            } else if (targetAgeGroup === '13_23_months') {
                return parseInt(cells[3].textContent.replace(/,/g, '')) || 0; // Column 4
            }
            return 0;
        }
        
        // Recalculate TOTAL row by summing all barangay rows
        // Recalculate TOTAL row by summing all barangay rows
        function recalculateTotalRow() {
            const table = document.querySelector('table tbody');
            if (!table) {
                return;
            }
            
            // Try multiple ways to find TOTAL row
            let totalRow = table.querySelector('tr[data-is-total="true"]');
            if (!totalRow) {
                totalRow = table.querySelector('.total-row');
            }
            if (!totalRow) {
                // Last resort: find row where area-column contains "TOTAL"
                const rows = table.querySelectorAll('tr');
                rows.forEach(row => {
                    const areaCell = row.querySelector('.area-column');
                    if (areaCell && areaCell.textContent.trim() === 'TOTAL') {
                        totalRow = row;
                    }
                });
            }
            
            if (!totalRow) {
                return;
            }
            
            const allRows = table.querySelectorAll('tbody tr:not(.total-row):not([data-is-total="true"])');
            
            // Get all vaccine cells in the TOTAL row to process
            const totalRowVaccineCells = totalRow.querySelectorAll('[data-vaccine]');
            const processedVaccines = new Set();
            
            totalRowVaccineCells.forEach(cell => {
                const vaccine = cell.dataset.vaccine;
                if (processedVaccines.has(vaccine)) return;
                processedVaccines.add(vaccine);
                
                let totalMale = 0, totalFemale = 0, totalTotal = 0;
                
                // Sum up all barangay rows for this vaccine
                allRows.forEach(row => {
                    const cells = Array.from(row.querySelectorAll('[data-vaccine]'));
                    cells.forEach(c => {
                        if (c.dataset.vaccine !== vaccine) return;
                        
                        // Get text content, handling input fields if present
                        let textValue = c.textContent.trim();
                        if (c.querySelector('input')) {
                            textValue = c.querySelector('input').value;
                        }
                        const value = parseInt(textValue) || 0;
                        
                        if (c.dataset.type === 'male') totalMale += value;
                        if (c.dataset.type === 'female') totalFemale += value;
                        if (c.dataset.type === 'total') totalTotal += value;
                    });
                });
                
                // Update TOTAL row cells for this vaccine
                const totalRowCells = Array.from(totalRow.querySelectorAll('[data-vaccine]'));
                totalRowCells.forEach(c => {
                    if (c.dataset.vaccine !== vaccine) return;
                    
                    if (c.dataset.type === 'male') c.textContent = totalMale;
                    if (c.dataset.type === 'female') c.textContent = totalFemale;
                    if (c.dataset.type === 'total') c.textContent = totalTotal;
                    
                    if (c.dataset.type === 'percentage') {
                        const eligiblePop = getEligiblePopulation(totalRow, vaccine);
                        const percentage = eligiblePop > 0 ? (totalTotal / eligiblePop * 100) : 0;
                        c.textContent = percentage.toFixed(2) + '%';
                    }
                });
            });
        }
        
        // Cancel Edit Mode
        function cancelEditMode() {
            showConfirm(
                'Discard Changes?',
                'Are you sure you want to cancel? All unsaved changes will be lost.',
                function() {
                    performCancel();
                }
            );
        }
        
        // Perform the actual cancel operation
        function performCancel() {
            // Restore original values
            const editableCells = document.querySelectorAll('.editable-cell');
            editableCells.forEach(cell => {
                const key = `${cell.dataset.barangay}|${cell.dataset.vaccine}|${cell.dataset.type}`;
                if (originalData[key]) {
                    cell.textContent = originalData[key];
                }
                cell.style.cursor = '';
                cell.style.backgroundColor = '';
                cell.title = '';
            });
            
            // Reset state
            isEditMode = false;
            originalData = {};
            modifiedCells.clear();
            
            // Toggle button visibility
            document.getElementById('toggleEditBtn').classList.remove('hidden');
            document.getElementById('saveEditBtn').classList.add('hidden');
            document.getElementById('cancelEditBtn').classList.add('hidden');

            // Hide Add Column button
            const addColumnBtn = document.getElementById('addColumnBtn');
            if (addColumnBtn) {
                addColumnBtn.classList.add('hidden');
                addColumnBtn.classList.remove('inline-flex');
            }

            // Hide custom column header actions via inline style
            document.querySelectorAll('.custom-header-actions').forEach(el => {
                el.style.display = 'none';
            });
            
            // Toggle helper text
            document.getElementById('editModeHelper').classList.remove('hidden');
            document.getElementById('editModeActive').classList.add('hidden');
            
            // Recalculate TOTAL row
            recalculateTotalRow();
        }
        
        // Save Edited Report
        function saveEditedReport() {
            if (modifiedCells.size === 0) {
                showToast('No changes were made.', 'warning');
                return;
            }
            
            showConfirm(
                'Confirm Save',
                `Save ${modifiedCells.size} change(s) as Version {{ ($version ?? 1) + 1 }}? This will create a new version of the report.`,
                function() {
                    performSave();
                }
            );
        }
        
        // Perform the actual save operation
        function performSave() {
            
            // Collect all data from the table
            const reportData = [];
            const table = document.querySelector('table tbody');
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const areaCell = row.querySelector('.area-column');
                if (!areaCell) return;
                
                // Use data attribute if available, otherwise use text content
                const barangay = areaCell.dataset.barangayName || areaCell.textContent.trim();
                const vaccines = {};
                
                const vaccineCells = row.querySelectorAll('[data-vaccine]');
                const processedVaccines = new Set();
                
                vaccineCells.forEach(cell => {
                    const vaccine = cell.dataset.vaccine;
                    if (processedVaccines.has(vaccine)) return;
                    processedVaccines.add(vaccine);
                    
                    const vaccineCellsForThis = Array.from(row.querySelectorAll('[data-vaccine]')).filter(c => c.dataset.vaccine === vaccine);
                    let male = 0, female = 0, total = 0;
                    
                    vaccineCellsForThis.forEach(c => {
                        const value = parseInt(c.textContent.trim()) || 0;
                        if (c.dataset.type === 'male') male = value;
                        if (c.dataset.type === 'female') female = value;
                        if (c.dataset.type === 'total') total = value;
                    });
                    
                    vaccines[vaccine] = { male, female, total };
                });
                
                reportData.push({ barangay, vaccines });
            });
            
            // Show loading state
            const saveBtn = document.getElementById('saveEditBtn');
            const originalBtnText = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';
            
            // Send to backend
            fetch('{{ route("reports.saveEdited") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    year: {{ $year }},
                    quarter_start: {{ $quarterStart }},
                    quarter_end: {{ $quarterEnd }},
                    month_start: {{ $monthStart ?? 'null' }},
                    month_end: {{ $monthEnd ?? 'null' }},
                    current_version: {{ $version ?? 1 }},
                    report_data: reportData
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(`Successfully saved as Version ${data.new_version}! Redirecting...`, 'success', 2000);
                    // Redirect to the new version
                    setTimeout(() => {
                        window.location.href = `{{ route('reports.show') }}?year={{ $year }}&quarter_start={{ $quarterStart }}&quarter_end={{ $quarterEnd }}&version=${data.new_version}`;
                    }, 2000);
                } else {
                    showToast('Error: ' + (data.message || 'Failed to save report'), 'error', 0);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalBtnText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while saving. Please try again.', 'error', 0);
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnText;
            });
        }

        // Close modal when clicking outside
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editNewVersionModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeEditNewVersionModal();
                    }
                });
            }
            const renameModal = document.getElementById('renameColumnModal');
            if (renameModal) {
                renameModal.addEventListener('click', function(e) {
                    if (e.target === renameModal) closeRenameColumnModal();
                });
            }
            const deleteModal = document.getElementById('deleteColumnModal');
            if (deleteModal) {
                deleteModal.addEventListener('click', function(e) {
                    if (e.target === deleteModal) closeDeleteColumnModal();
                });
            }
        });
        
        // PDF Export - Landscape 8.5" x 13" (Long Bond Paper) - Same format as current report
        function exportToPDF() {
            try {
                const { jsPDF } = window.jspdf;
                
                // Long Bond Paper Landscape: 13" x 8.5" = 330.2mm x 215.9mm
                const doc = new jsPDF({
                    orientation: 'landscape',
                    unit: 'mm',
                    format: [215.9, 330.2]
                });
                
                const pageWidth = doc.internal.pageSize.getWidth(); // 330.2mm
                const pageHeight = doc.internal.pageSize.getHeight(); // 215.9mm
                const margin = 8;
                const usableWidth = pageWidth - (margin * 2); // ~314mm
                
                // Get table data
                const table = document.getElementById('vaccinationReportTable');
                const headerRow1 = table.querySelector('thead tr:first-child');
                const bodyRows = table.querySelectorAll('tbody tr');
                
                // Parse vaccine names
                const vaccineColumns = [];
                headerRow1.querySelectorAll('th').forEach((th) => {
                    const colspan = parseInt(th.getAttribute('colspan') || 1);
                    const rowspan = parseInt(th.getAttribute('rowspan') || 1);
                    if (rowspan === 1 && colspan === 4) {
                        vaccineColumns.push({
                            name: th.textContent.trim().replace(/\s+/g, ' '),
                            dataStartCol: 5 + (vaccineColumns.length * 4)
                        });
                    }
                });
                
                // Parse body data
                const bodyData = [];
                bodyRows.forEach((tr) => {
                    const row = [];
                    tr.querySelectorAll('td').forEach((td) => {
                        row.push(td.textContent.trim());
                    });
                    bodyData.push(row);
                });
                
                // 5 vaccines per page - good balance for readability on landscape long bond
                const vaccinesPerPage = 5;
                const totalPages = Math.ceil(vaccineColumns.length / vaccinesPerPage);
                
                // Column widths
                const areaWidth = 32;
                const popWidth = 14;
                const fixedColsWidth = areaWidth + (popWidth * 4); // 88mm
                const remainingWidth = usableWidth - fixedColsWidth; // ~226mm
                const vaccineGroupWidth = remainingWidth / vaccinesPerPage; // ~45mm
                const subColWidth = vaccineGroupWidth / 4; // ~11mm
                
                // Generate pages
                for (let pageIndex = 0; pageIndex < totalPages; pageIndex++) {
                    if (pageIndex > 0) {
                        doc.addPage([215.9, 330.2], 'landscape');
                    }
                    
                    const startVaccine = pageIndex * vaccinesPerPage;
                    const endVaccine = Math.min(startVaccine + vaccinesPerPage, vaccineColumns.length);
                    const pageVaccines = vaccineColumns.slice(startVaccine, endVaccine);
                    
                    // Adjust column widths if fewer vaccines on last page
                    const actualVaccineGroupWidth = (pageVaccines.length < vaccinesPerPage) 
                        ? remainingWidth / pageVaccines.length 
                        : vaccineGroupWidth;
                    const actualSubColWidth = actualVaccineGroupWidth / 4;
                    
                    // === HEADER ===
                    doc.setFontSize(14);
                    doc.setFont(undefined, 'bold');
                    doc.text('CHILD CARE PROGRAM - Vaccination Report', pageWidth / 2, 12, { align: 'center' });
                    
                    doc.setFontSize(10);
                    doc.setFont(undefined, 'normal');
                    doc.text('{{ $dateRange }} | CALAUAN, LAGUNA', pageWidth / 2, 19, { align: 'center' });
                    
                    doc.setFontSize(8);
                    doc.setFont(undefined, 'italic');
                    doc.text('Version {{ $version ?? 1 }} - Saved: {{ $savedAt ? \Carbon\Carbon::parse($savedAt)->format("M d, Y h:i A") : "N/A" }}', pageWidth / 2, 25, { align: 'center' });
                    
                    doc.setFont(undefined, 'normal');
                    doc.setFontSize(9);
                    doc.text(`Page ${pageIndex + 1} of ${totalPages}`, pageWidth - margin, 12, { align: 'right' });
                    
                    // === TABLE HEADERS ===
                    const headers1 = [
                        { content: 'BARANGAY', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontStyle: 'bold', fontSize: 8 } },
                        { content: 'Pop\n(U1)', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontSize: 7 } },
                        { content: 'Pop\n(0-12)', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontSize: 7 } },
                        { content: 'Pop\n(13-23)', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontSize: 7 } },
                        { content: 'Pop\n(0-23)', rowSpan: 2, styles: { halign: 'center', valign: 'middle', fontSize: 7 } }
                    ];
                    
                    const headers2 = [];
                    
                    // Add vaccine headers
                    pageVaccines.forEach(v => {
                        let shortName = v.name
                            .replace('Human Papillomavirus', 'HPV')
                            .replace('Inactivated Polio', 'IPV')
                            .replace('Pentavalent', 'Penta')
                            .replace('Measles, Mumps, Rubella', 'MMR')
                            .replace('Hepatitis B', 'HepB')
                            .replace('(Dose ', 'D').replace('Dose ', 'D').replace(')', '')
                            .replace('Catch-up', 'CU').replace('Routine', 'Rt')
                            .replace('(Grade ', 'G').replace('Grade ', 'G');
                        
                        headers1.push({ 
                            content: shortName, 
                            colSpan: 4, 
                            styles: { halign: 'center', fontSize: 7, fontStyle: 'bold', fillColor: [220, 220, 240] } 
                        });
                        
                        headers2.push({ content: 'M', styles: { halign: 'center', fontSize: 7 } });
                        headers2.push({ content: 'F', styles: { halign: 'center', fontSize: 7 } });
                        headers2.push({ content: 'T', styles: { halign: 'center', fontSize: 7, fontStyle: 'bold' } });
                        headers2.push({ content: '%', styles: { halign: 'center', fontSize: 7 } });
                    });
                    
                    // === TABLE BODY ===
                    const pageBody = bodyData.map(row => {
                        const pageRow = [row[0], row[1], row[2], row[3], row[4]];
                        pageVaccines.forEach(v => {
                            for (let c = 0; c < 4; c++) {
                                let val = row[v.dataStartCol + c] || '0';
                                if (c === 3) val = val.replace('.00%', '%');
                                pageRow.push(val);
                            }
                        });
                        return pageRow;
                    });
                    
                    // === COLUMN STYLES ===
                    const colStyles = {
                        0: { cellWidth: areaWidth, fontStyle: 'bold', fontSize: 8, halign: 'left' }
                    };
                    for (let i = 1; i <= 4; i++) {
                        colStyles[i] = { cellWidth: popWidth, halign: 'center', fontSize: 7 };
                    }
                    for (let i = 5; i < 5 + pageVaccines.length * 4; i++) {
                        colStyles[i] = { cellWidth: actualSubColWidth, halign: 'center', fontSize: 7 };
                    }
                    
                    // === DRAW TABLE ===
                    doc.autoTable({
                        head: [headers1, headers2],
                        body: pageBody,
                        startY: 30,
                        styles: { 
                            fontSize: 7, 
                            cellPadding: 1.5, 
                            lineWidth: 0.2,
                            lineColor: [120, 120, 120]
                        },
                        headStyles: { 
                            fillColor: [200, 200, 220], 
                            textColor: [0, 0, 0], 
                            fontStyle: 'bold'
                        },
                        columnStyles: colStyles,
                        alternateRowStyles: { fillColor: [250, 250, 255] },
                        theme: 'grid',
                        margin: { left: margin, right: margin, bottom: 18 },
                        tableWidth: usableWidth,
                        willDrawCell: (data) => {
                            if (data.row.index === pageBody.length - 1 && data.section === 'body') {
                                doc.setFillColor(255, 250, 205);
                            }
                        }
                    });
                    
                    // === FOOTER ===
                    const finalY = doc.lastAutoTable.finalY;
                    
                    // Legend on last page
                    if (pageIndex === totalPages - 1 && finalY < pageHeight - 25) {
                        doc.setFontSize(7);
                        doc.setFont(undefined, 'bold');
                        doc.text('LEGEND:', margin, finalY + 5);
                        doc.setFont(undefined, 'normal');
                        doc.setFontSize(6);
                        doc.text('Pop(U1)=Under 1yr | Pop(0-12)=0-12 mos | Pop(13-23)=13-23 mos | Pop(0-23)=0-23 mos | M=Male | F=Female | T=Total | %=Vaccination Rate', margin, finalY + 9);
                        doc.text('HPV=Human Papillomavirus | IPV=Inactivated Polio | Penta=Pentavalent | HepB=Hepatitis B | MMR=Measles,Mumps,Rubella | D=Dose | CU=Catch-up | Rt=Routine | G=Grade', margin, finalY + 13);
                    }
                    
                    doc.setFontSize(7);
                    doc.text('Generated: ' + new Date().toLocaleString(), margin, pageHeight - 5);
                }
                
                // Save with proper naming
                const dateRange = '{{ $dateRange }}';
                const year = '{{ $year }}';
                const version = '{{ $version ?? 1 }}';
                
                let startMonth = 'Jan';
                let endMonth = 'Dec';
                const dateMatch = dateRange.match(/([A-Za-z]+)\s+\d+.*to\s+([A-Za-z]+)\s+\d+/);
                if (dateMatch) {
                    startMonth = dateMatch[1];
                    endMonth = dateMatch[2];
                }
                
                doc.save(`ChildCare_VaccinationReport_Calauan_${year}_${startMonth}-${endMonth}_v${version}.pdf`);
                
            } catch (error) {
                console.error('PDF Export Error:', error);
                alert('Failed to export PDF. Error: ' + error.message);
            }
        }
        
        // Export to Excel function
        function exportToExcel() {
            const table = document.getElementById('vaccinationReportTable');
            const wb = XLSX.utils.table_to_book(table, {sheet: 'Vaccination Report'});
            
            // Add title rows
            const ws = wb.Sheets['Vaccination Report'];
            XLSX.utils.sheet_add_aoa(ws, [
                ['CHILD CARE PROGRAM'],
                ['Vaccine Preventable Diseases Surveillance Report'],
                ['{{ $dateRange }} - CALAUAN, LAGUNA'],
                ['Archived Version {{ $version ?? 1 }} - Saved: {{ $savedAt ? \Carbon\Carbon::parse($savedAt)->format("M d, Y h:i A") : "N/A" }}'],
                [''],
            ], {origin: 'A1'});
            
            // Adjust column widths
            const colWidths = [
                { wch: 20 }, // Area
                { wch: 15 }, // Population
            ];
            ws['!cols'] = colWidths;
            
            // Use proper naming consistent with PDF - extract from dateRange
            const dateRange = '{{ $dateRange }}';
            const year = '{{ $year }}';
            const version = '{{ $version ?? 1 }}';
            
            let startMonth = 'Jan';
            let endMonth = 'Dec';
            const dateMatch = dateRange.match(/([A-Za-z]+)\s+\d+.*to\s+([A-Za-z]+)\s+\d+/);
            if (dateMatch) {
                startMonth = dateMatch[1];
                endMonth = dateMatch[2];
            }
            
            XLSX.writeFile(wb, `ChildCare_VaccinationReport_Calauan_${year}_${startMonth}-${endMonth}_v${version}.xlsx`);
        }
    </script>
    
    <!-- Toast Notification Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Confirmation Modal -->
    <div id="confirmModal" style="display: none; position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 400px; width: 90%; animation: fadeIn 0.2s ease-out;">
            <div style="padding: 24px; border-bottom: 1px solid #e5e7eb;">
                <h3 id="confirmTitle" style="font-size: 18px; font-weight: 700; color: #111827; margin: 0;"></h3>
            </div>
            <div style="padding: 24px;">
                <p id="confirmMessage" style="font-size: 14px; color: #6b7280; line-height: 1.6; margin: 0;"></p>
            </div>
            <div style="padding: 16px 24px 24px; display: flex; gap: 12px; justify-content: flex-end;">
                <button id="confirmCancelBtn" style="padding: 10px 20px; border: 1px solid #d1d5db; background: white; color: #374151; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Cancel</button>
                <button id="confirmOkBtn" style="padding: 10px 20px; border: none; background: #7c3aed; color: white; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s;">Confirm</button>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        #confirmCancelBtn:hover { background: #f3f4f6; }
        #confirmOkBtn:hover { background: #6d28d9; }
    </style>
    
    <script>
        // Toast Notification System
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" style="stroke: #10b981;"/></svg>',
                error: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" style="stroke: #ef4444;"/></svg>',
                warning: '<svg class="toast-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" style="stroke: #f59e0b;"/></svg>'
            };
            
            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning'
            };
            
            toast.innerHTML = `
                ${icons[type]}
                <div class="toast-content">
                    <div class="toast-title">${titles[type]}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <div class="toast-close">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Close button handler
            toast.querySelector('.toast-close').addEventListener('click', () => {
                closeToast(toast);
            });
            
            // Auto-dismiss
            if (duration > 0) {
                setTimeout(() => {
                    closeToast(toast);
                }, duration);
            }
        }
        
        function closeToast(toast) {
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
        
        // Confirmation Modal System
        function showConfirm(title, message, onConfirm) {
            const modal = document.getElementById('confirmModal');
            const titleEl = document.getElementById('confirmTitle');
            const messageEl = document.getElementById('confirmMessage');
            const okBtn = document.getElementById('confirmOkBtn');
            const cancelBtn = document.getElementById('confirmCancelBtn');
            
            titleEl.textContent = title;
            messageEl.textContent = message;
            modal.style.display = 'flex';
            
            // Remove old listeners
            const newOkBtn = okBtn.cloneNode(true);
            const newCancelBtn = cancelBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(newOkBtn, okBtn);
            cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
            
            // Add new listeners
            newOkBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                if (onConfirm) onConfirm();
            });
            
            newCancelBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });
            
            // Close on backdrop click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
@endsection
