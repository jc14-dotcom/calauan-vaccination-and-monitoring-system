@extends('layouts.responsive-layout')

@section('title', 'Infants Vaccination Records')

@section('additional-styles')
<!-- Add viewport meta tag to ensure proper mobile scaling -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<!-- FontAwesome CDN for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
@endsection

@section('content')
    <div class="tw-bg-gray-50 tw-min-h-screen">
        <div class="tw-p-3 md:tw-p-6">
            <h1 class="tw-text-xl md:tw-text-2xl tw-font-bold tw-mb-4 md:tw-mb-6">Infants Vaccination Records</h1>
            
            <!-- Search and Filter Controls - Mobile optimized -->
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-3 md:tw-p-6 tw-mb-4 md:tw-mb-6">
                <div class="tw-flex tw-flex-wrap tw-gap-3">
                    <!-- Search Input -->
                    <div class="tw-w-full md:tw-w-1/3">
                        <label for="search" class="tw-block tw-text-xs md:tw-text-sm tw-font-medium tw-mb-1">Search:</label>
                        <input type="text" id="search" placeholder="Search by name..." class="tw-text-xs md:tw-text-sm tw-border tw-border-gray-300 tw-rounded tw-p-2 tw-w-full focus:tw-ring-purple-500 focus:tw-border-purple-500">
                    </div>
                    
                    <!-- Barangay Filter -->
                    <div class="tw-w-full md:tw-w-1/3">
                        <label for="barangay" class="tw-block tw-text-xs md:tw-text-sm tw-font-medium tw-mb-1">Barangay:</label>
                        <select id="barangay" class="tw-text-xs md:tw-text-sm tw-border tw-border-gray-300 tw-rounded tw-p-2 tw-w-full focus:tw-ring-purple-500 focus:tw-border-purple-500">
                            <option value="">All Barangays</option>
                            <option value="Balayhangin">Balayhangin</option>
                            <option value="Bangyas">Bangyas</option>
                            <option value="Dayap">Dayap</option>
                            <option value="Hanggan">Hanggan</option>
                            <option value="Imok">Imok</option>
                            <option value="Kanluran">Kanluran</option>
                            <option value="Lamot 1">Lamot 1</option>
                            <option value="Lamot 2">Lamot 2</option>
                            <option value="Limao">Limao</option>
                            <option value="Mabacan">Mabacan</option>
                            <option value="Masiit">Masiit</option>
                            <option value="Paliparan">Paliparan</option>
                            <option value="Perez">Perez</option>
                            <option value="Prinza">Prinza</option>
                            <option value="San Isidro">San Isidro</option>
                            <option value="Santo Tomas">Santo Tomas</option>
                            <option value="Silangan">Silangan</option>
                        </select>
                    </div>
                    
                    <!-- Vaccine Status Filter -->
                    <div class="tw-w-full md:tw-w-1/4">
                        <label for="status" class="tw-block tw-text-xs md:tw-text-sm tw-font-medium tw-mb-1">Status:</label>
                        <select id="status" class="tw-text-xs md:tw-text-sm tw-border tw-border-gray-300 tw-rounded tw-p-2 tw-w-full focus:tw-ring-purple-500 focus:tw-border-purple-500">
                            <option value="">All Status</option>
                            <option value="complete">Complete</option>
                            <option value="incomplete">Incomplete</option>
                        </select>
                    </div>
                    
                    <!-- Reset Button -->
                    <div class="tw-flex tw-items-end tw-w-full md:tw-w-auto">
                        <button id="resetFilters" title="Reset Filters" class="tw-bg-purple-600 tw-py-1 tw-px-2 md:tw-px-3 tw-rounded tw-text-white hover:tw-bg-purple-700 tw-transition tw-text-xs md:tw-text-sm tw-w-full md:tw-w-auto tw-flex tw-items-center tw-justify-center tw-gap-1">
                            <img src="{{ asset('images/undo.png') }}" alt="Reset" class="tw-w-4 tw-h-4 tw-align-middle" />
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Desktop Table View - Hidden on mobile -->
            <div class="tw-hidden md:tw-block tw-overflow-hidden tw-bg-white tw-shadow tw-rounded-lg">
                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                        <thead class="tw-bg-purple-700 tw-text-white">
                            <tr>
                                <th scope="col" class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-uppercase tw-tracking-wider">Name</th>
                                <th scope="col" class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-uppercase tw-tracking-wider">Birthdate</th>
                                <th scope="col" class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-uppercase tw-tracking-wider">Barangay</th>
                                <th scope="col" class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-uppercase tw-tracking-wider">Status</th>
                                <th scope="col" class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-uppercase tw-tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="tw-bg-white tw-divide-y tw-divide-gray-200" id="patientsTableBody">
                            @foreach ($patients as $patient)
                                <tr class="hover:tw-bg-purple-50">
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap">
                                        <div class="tw-flex tw-items-center">
                                            <div>
                                                <div class="tw-text-sm tw-font-medium tw-text-gray-900">{{ $patient->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                        {{ \Carbon\Carbon::parse($patient->birthdate)->format('M d, Y') }}
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-text-gray-500">
                                        {{ $patient->barangay }}
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap">
                                        @php
                                            $recordCount = $patient->vaccineRecords->count();
                                            $completeCount = $patient->vaccineRecords->where('status', 'complete')->count();
                                            $status = ($recordCount > 0 && $completeCount === $recordCount) ? 'complete' : 'incomplete';
                                        @endphp
                                        
                                        <span class="tw-px-2 tw-inline-flex tw-text-xs tw-leading-5 tw-font-semibold tw-rounded-full {{ $status === 'complete' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-yellow-100 tw-text-yellow-800' }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="tw-px-4 tw-py-4 tw-whitespace-nowrap tw-text-sm tw-font-medium">
                                        <a href="{{ route('health_worker.patient.records', $patient->id) }}" class="tw-bg-purple-600 hover:tw-bg-purple-700 tw-py-1 tw-px-2 tw-text-white tw-rounded tw-text-xs">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Mobile Card View - Shown only on mobile -->
            <div class="md:tw-hidden">
                <div class="tw-space-y-3" id="patientsMobileCards">
                    @foreach ($patients as $patient)
                        @php
                            $recordCount = $patient->vaccineRecords->count();
                            $completeCount = $patient->vaccineRecords->where('status', 'complete')->count();
                            $status = ($recordCount > 0 && $completeCount === $recordCount) ? 'complete' : 'incomplete';
                        @endphp
                        
                        <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-3 patient-card" 
                             data-name="{{ strtolower($patient->name) }}"
                             data-barangay="{{ strtolower($patient->barangay) }}"
                             data-status="{{ $status }}">
                            <div class="tw-flex tw-justify-between tw-items-center tw-mb-2">
                                <h2 class="tw-text-sm tw-font-medium tw-text-gray-900">{{ $patient->name }}</h2>
                                <span class="tw-px-2 tw-py-1 tw-text-xs tw-font-semibold tw-rounded-full {{ $status === 'complete' ? 'tw-bg-green-100 tw-text-green-800' : 'tw-bg-yellow-100 tw-text-yellow-800' }}">
                                    {{ ucfirst($status) }}
                                </span>
                            </div>
                            <div class="tw-text-xs tw-text-gray-500 tw-mb-3">
                                <div class="tw-flex tw-justify-between">
                                    <span>Birthdate: {{ \Carbon\Carbon::parse($patient->birthdate)->format('M d, Y') }}</span>
                                    <span>{{ $patient->barangay }}</span>
                                </div>
                            </div>
                            <div class="tw-flex tw-justify-end">
                                <a href="{{ route('health_worker.patient.records', $patient->id) }}" class="tw-bg-purple-600 hover:tw-bg-purple-700 tw-py-1 tw-px-3 tw-text-white tw-rounded tw-text-xs">
                                    View
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Mobile No Results Message -->
                <div id="noResultsMobile" class="tw-hidden tw-bg-white tw-rounded-lg tw-shadow tw-p-4 tw-text-center tw-text-gray-500">
                    No matching records found.
                </div>
            </div>
            
            <!-- No Results Message for Desktop -->
            <div id="noResultsDesktop" class="tw-hidden tw-bg-white tw-rounded-lg tw-shadow tw-p-6 tw-text-center tw-text-gray-500">
                No matching records found.
            </div>
            
            <!-- Pagination for both views -->
            <div class="tw-mt-4 tw-flex tw-justify-center">
                {{ $patients->links('pagination::tailwind') }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const barangaySelect = document.getElementById('barangay');
            const statusSelect = document.getElementById('status');
            const resetButton = document.getElementById('resetFilters');
            
            // Desktop table elements
            const tableRows = document.querySelectorAll('#patientsTableBody tr');
            const noResultsDesktop = document.getElementById('noResultsDesktop');
            
            // Mobile card elements
            const mobileCards = document.querySelectorAll('.patient-card');
            const noResultsMobile = document.getElementById('noResultsMobile');
            
            // Filter function
            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                const barangayFilter = barangaySelect.value.toLowerCase();
                const statusFilter = statusSelect.value.toLowerCase();
                
                let desktopMatches = 0;
                let mobileMatches = 0;
                
                // Filter desktop table
                tableRows.forEach(row => {
                    const name = row.querySelector('td:first-child').textContent.toLowerCase();
                    const barangay = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase();
                    const status = row.querySelector('td:nth-child(4) span').textContent.trim().toLowerCase();
                    
                    const matchesSearch = name.includes(searchTerm);
                    const matchesBarangay = !barangayFilter || barangay === barangayFilter;
                    const matchesStatus = !statusFilter || status === statusFilter;
                    
                    const isVisible = matchesSearch && matchesBarangay && matchesStatus;
                    row.style.display = isVisible ? '' : 'none';
                    
                    if (isVisible) desktopMatches++;
                });
                
                // Filter mobile cards
                mobileCards.forEach(card => {
                    const name = card.dataset.name;
                    const barangay = card.dataset.barangay;
                    const status = card.dataset.status;
                    
                    const matchesSearch = name.includes(searchTerm);
                    const matchesBarangay = !barangayFilter || barangay === barangayFilter;
                    const matchesStatus = !statusFilter || status === statusFilter;
                    
                    const isVisible = matchesSearch && matchesBarangay && matchesStatus;
                    card.style.display = isVisible ? '' : 'none';
                    
                    if (isVisible) mobileMatches++;
                });
                
                // Show/hide no results message
                noResultsDesktop.style.display = desktopMatches === 0 ? 'block' : 'none';
                noResultsMobile.style.display = mobileMatches === 0 ? 'block' : 'none';
            }
            
            // Event listeners
            searchInput.addEventListener('input', applyFilters);
            barangaySelect.addEventListener('change', applyFilters);
            statusSelect.addEventListener('change', applyFilters);
            
            // Reset filters
            resetButton.addEventListener('click', function() {
                searchInput.value = '';
                barangaySelect.value = '';
                statusSelect.value = '';
                applyFilters();
            });
        });
    </script>
@endsection