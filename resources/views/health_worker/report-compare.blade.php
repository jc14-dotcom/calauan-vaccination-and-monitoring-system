@extends('layouts.responsive-layout')

@section('title', 'Compare Report Versions')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Compare Report Versions</h1>
                <p class="text-gray-600">{{ $dateRange }} - Comparing Version {{ $version1 }} vs Version {{ $version2 }}</p>
            </div>
            <a href="{{ route('reports.history') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to History
            </a>
        </div>
    </div>

    <!-- Version Headers -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <!-- Version 1 Info -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg p-4 text-white">
            <h2 class="text-xl font-bold mb-2">Version {{ $version1 }}</h2>
            <p class="text-sm opacity-90">Saved: {{ $report1['saved_at'] ? \Carbon\Carbon::parse($report1['saved_at'])->format('M d, Y h:i A') : 'N/A' }}</p>
            <p class="text-sm opacity-90">Source: {{ ucfirst($report1['source']) }}</p>
        </div>

        <!-- Version 2 Info -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-4 text-white">
            <h2 class="text-xl font-bold mb-2">Version {{ $version2 }}</h2>
            <p class="text-sm opacity-90">Saved: {{ $report2['saved_at'] ? \Carbon\Carbon::parse($report2['saved_at'])->format('M d, Y h:i A') : 'N/A' }}</p>
            <p class="text-sm opacity-90">Source: {{ ucfirst($report2['source']) }}</p>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h3 class="font-semibold text-gray-800 mb-3">Legend:</h3>
        <div class="flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                <span class="text-gray-700">Increased</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
                <span class="text-gray-700">Decreased</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-gray-50 border border-gray-200 rounded"></div>
                <span class="text-gray-700">No Change</span>
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 sticky top-0">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 text-left font-semibold text-gray-700 border-r border-gray-300">Barangay</th>
                        <th rowspan="2" class="px-4 py-3 text-left font-semibold text-gray-700 border-r border-gray-300">Vaccine</th>
                        <th colspan="3" class="px-4 py-2 text-center font-semibold text-purple-700 border-r border-gray-300 bg-purple-50">Version {{ $version1 }}</th>
                        <th colspan="3" class="px-4 py-2 text-center font-semibold text-blue-700 bg-blue-50">Version {{ $version2 }}</th>
                    </tr>
                    <tr>
                        <th class="px-2 py-2 text-center font-semibold text-gray-600 bg-purple-50">M</th>
                        <th class="px-2 py-2 text-center font-semibold text-gray-600 bg-purple-50">F</th>
                        <th class="px-2 py-2 text-center font-semibold text-gray-600 border-r border-gray-300 bg-purple-50">Total</th>
                        <th class="px-2 py-2 text-center font-semibold text-gray-600 bg-blue-50">M</th>
                        <th class="px-2 py-2 text-center font-semibold text-gray-600 bg-blue-50">F</th>
                        <th class="px-2 py-2 text-center font-semibold text-gray-600 bg-blue-50">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report1['data'] as $barangay => $data1)
                        @php
                            $data2 = $report2['data'][$barangay] ?? null;
                            $vaccines1 = $data1['vaccines'] ?? [];
                            $vaccines2 = $data2['vaccines'] ?? [];
                            $allVaccines = array_unique(array_merge(array_keys($vaccines1), array_keys($vaccines2)));
                        @endphp
                        
                        @foreach($allVaccines as $vaccine)
                            @php
                                $v1 = $vaccines1[$vaccine] ?? ['male_count' => 0, 'female_count' => 0, 'total_count' => 0];
                                $v2 = $vaccines2[$vaccine] ?? ['male_count' => 0, 'female_count' => 0, 'total_count' => 0];
                                
                                // Calculate differences
                                $maleDiff = $v2['male_count'] - $v1['male_count'];
                                $femaleDiff = $v2['female_count'] - $v1['female_count'];
                                $totalDiff = $v2['total_count'] - $v1['total_count'];
                                
                                // Determine background colors
                                $maleClass = $maleDiff > 0 ? 'bg-green-100' : ($maleDiff < 0 ? 'bg-red-100' : 'bg-gray-50');
                                $femaleClass = $femaleDiff > 0 ? 'bg-green-100' : ($femaleDiff < 0 ? 'bg-red-100' : 'bg-gray-50');
                                $totalClass = $totalDiff > 0 ? 'bg-green-100' : ($totalDiff < 0 ? 'bg-red-100' : 'bg-gray-50');
                            @endphp
                            
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $barangay }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $vaccine }}</td>
                                
                                <!-- Version 1 Data -->
                                <td class="px-2 py-3 text-center text-gray-700">{{ $v1['male_count'] }}</td>
                                <td class="px-2 py-3 text-center text-gray-700">{{ $v1['female_count'] }}</td>
                                <td class="px-2 py-3 text-center font-semibold text-gray-800 border-r border-gray-300">{{ $v1['total_count'] }}</td>
                                
                                <!-- Version 2 Data (with highlighting) -->
                                <td class="px-2 py-3 text-center text-gray-700 {{ $maleClass }}">
                                    <div class="flex items-center justify-center gap-1">
                                        <span>{{ $v2['male_count'] }}</span>
                                        @if($maleDiff != 0)
                                            <span class="text-xs {{ $maleDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ({{ $maleDiff > 0 ? '+' : '' }}{{ $maleDiff }})
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-center text-gray-700 {{ $femaleClass }}">
                                    <div class="flex items-center justify-center gap-1">
                                        <span>{{ $v2['female_count'] }}</span>
                                        @if($femaleDiff != 0)
                                            <span class="text-xs {{ $femaleDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ({{ $femaleDiff > 0 ? '+' : '' }}{{ $femaleDiff }})
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-3 text-center font-semibold text-gray-800 {{ $totalClass }}">
                                    <div class="flex items-center justify-center gap-1">
                                        <span>{{ $v2['total_count'] }}</span>
                                        @if($totalDiff != 0)
                                            <span class="text-xs {{ $totalDiff > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ({{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }})
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
