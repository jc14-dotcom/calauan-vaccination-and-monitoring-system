@extends('layouts.responsive-layout')

@section('title', 'Patients')

@section('additional-styles')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
<style>
    /* Page container matches dashboard spacing and full-width desktop */
    .hw-container{ width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width:640px){ .hw-container{ padding-left:2rem; padding-right:2rem; } }
    @media (min-width:1280px){ .hw-container{ padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x{ overflow-x:hidden; }
    .break-words{ overflow-wrap:anywhere; word-break:break-word; }
    /* Spinner animation (kept) */
    .spinner{ display:inline-block; width:20px; height:20px; border:3px solid rgba(0,0,0,0.1); border-radius:50%; border-top-color:#7a5bbd; animation:spin 1s ease-in-out infinite; margin-right:10px; }
    @keyframes spin{ to{ transform:rotate(360deg); } }
    /* Floating action button */
    .floating-btn{ position:fixed; bottom:30px; right:30px; width:60px; height:60px; border-radius:50%; background:linear-gradient(90deg,#7a5bbd 0%,#5a3f99 100%); color:#fff; display:flex; align-items:center; justify-content:center; box-shadow:0 8px 24px rgba(90,63,153,.25); transition:transform .2s ease, box-shadow .2s ease; z-index:10; text-decoration:none; }
    .floating-btn:hover{ transform:translateY(-4px); box-shadow:0 12px 28px rgba(90,63,153,.3); }
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
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                            <circle cx="12" cy="8" r="4" />
                        </svg>
                    </span>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold leading-tight">Patient List</h1>
                        <p class="text-sm md:text-base text-white/90 mt-1">Search, filter and manage patient records</p>
                    </div>
                </div>
            </section>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" action="{{ route('health_worker.patients') }}" id="filterForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <!-- Barangay Filter -->
                        <div class="space-y-2">
                            <label for="barangay" class="block text-base font-semibold text-gray-800">
                                Barangay
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <select id="barangay" name="barangay" class="w-full h-12 text-base rounded-lg border-2 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all pl-11">
                                    <option value="">All Barangays</option>
                                <option value="Balayhangin" {{ ($filters['barangay'] ?? '') == 'Balayhangin' ? 'selected' : '' }}>Balayhangin</option>
                                <option value="Bangyas" {{ ($filters['barangay'] ?? '') == 'Bangyas' ? 'selected' : '' }}>Bangyas</option>
                                <option value="Dayap" {{ ($filters['barangay'] ?? '') == 'Dayap' ? 'selected' : '' }}>Dayap</option>
                                <option value="Hanggan" {{ ($filters['barangay'] ?? '') == 'Hanggan' ? 'selected' : '' }}>Hanggan</option>
                                <option value="Imok" {{ ($filters['barangay'] ?? '') == 'Imok' ? 'selected' : '' }}>Imok</option>
                                <option value="Kanluran" {{ ($filters['barangay'] ?? '') == 'Kanluran' ? 'selected' : '' }}>Kanluran</option>
                                <option value="Lamot 1" {{ ($filters['barangay'] ?? '') == 'Lamot 1' ? 'selected' : '' }}>Lamot 1</option>
                                <option value="Lamot 2" {{ ($filters['barangay'] ?? '') == 'Lamot 2' ? 'selected' : '' }}>Lamot 2</option>
                                <option value="Limao" {{ ($filters['barangay'] ?? '') == 'Limao' ? 'selected' : '' }}>Limao</option>
                                <option value="Mabacan" {{ ($filters['barangay'] ?? '') == 'Mabacan' ? 'selected' : '' }}>Mabacan</option>
                                <option value="Masiit" {{ ($filters['barangay'] ?? '') == 'Masiit' ? 'selected' : '' }}>Masiit</option>
                                <option value="Paliparan" {{ ($filters['barangay'] ?? '') == 'Paliparan' ? 'selected' : '' }}>Paliparan</option>
                                <option value="Perez" {{ ($filters['barangay'] ?? '') == 'Perez' ? 'selected' : '' }}>Perez</option>
                                <option value="Prinza" {{ ($filters['barangay'] ?? '') == 'Prinza' ? 'selected' : '' }}>Prinza</option>
                                <option value="San Isidro" {{ ($filters['barangay'] ?? '') == 'San Isidro' ? 'selected' : '' }}>San Isidro</option>
                                <option value="Santo Tomas" {{ ($filters['barangay'] ?? '') == 'Santo Tomas' ? 'selected' : '' }}>Santo Tomas</option>
                                <option value="Silangan" {{ ($filters['barangay'] ?? '') == 'Silangan' ? 'selected' : '' }}>Silangan</option>
                                </select>
                            </div>
                        </div>

                        <!-- Search Input -->
                        <div class="space-y-2 lg:col-span-2">
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
                                    <input type="text" id="searchBar" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Type patient name or contact number..." class="w-full h-12 text-base rounded-lg border-2 border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-200/50 hover:border-gray-400 transition-all placeholder:text-gray-400 pl-11">
                                </div>
                                
                                <!-- Clear Filters Button -->
                                <a href="{{ route('health_worker.patients') }}" id="clearFiltersBtn" class="h-12 w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-base font-semibold px-6 shadow-sm transition-all {{ !(($filters['barangay'] ?? '') || ($filters['search'] ?? '')) ? 'opacity-50 pointer-events-none' : '' }}">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span>Clear Filters</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Patients Table -->
            <div class="overflow-x-auto w-full shadow-md rounded-lg bg-white mb-6">
                <table class="min-w-full" id="patientsTable">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Age</th>
                            <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Barangay</th>
                            <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Contact Number</th>
                        </tr>
                    </thead>
                    <tbody id="patientsTableBody" class="divide-y divide-gray-200">
                        @forelse($patients as $patient)
                            <tr class="patient-row hover:bg-primary-50 cursor-pointer transition-colors" data-id="{{ $patient->id }}" onclick="window.location.href='{{ route('health_worker.patient_view', $patient->id) }}?return={{ urlencode(route('health_worker.patients')) }}'">
                                <td class="px-6 py-4 text-base font-semibold text-gray-900">{{ $patient->display_name ?? $patient->formatted_name }}</td>
                                <td class="px-6 py-4 text-base text-gray-700">{{ $patient->display_age ?? $patient->formatted_age }}</td>
                                <td class="px-6 py-4 text-base text-gray-700">{{ $patient->barangay }}</td>
                                <td class="px-6 py-4 text-base text-gray-600">{{ $patient->contact_no }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-12 text-center text-gray-500">
                                    <svg class="w-20 h-20 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                    </svg>
                                    <p class="text-xl font-semibold mb-2 text-gray-700">No patients found</p>
                                    <p class="text-base text-gray-500">Try adjusting your search or filters</p>
                                </td>
                            </tr>
                        @endforelse
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
                <div class="text-center text-base text-gray-600 mb-6 font-medium">
                    Showing <strong class="text-primary-700">{{ $patients->count() }}</strong> of <strong class="text-primary-700">{{ $patients->total() }}</strong> patients
                    @if($filters['search'] ?? false)
                        matching "<strong class="text-gray-900">{{ $filters['search'] }}</strong>"
                    @endif
                    @if($filters['barangay'] ?? false)
                        in <strong class="text-gray-900">{{ $filters['barangay'] }}</strong>
                    @endif
                </div>
            @endif
    </div>

    <!-- Plus Button (Floating Action Button) -->
    <a href="{{ route('health_worker.vaccination_form') }}" class="floating-btn" aria-label="Add vaccination record">
        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
        </svg>
    </a>
@endsection

@section('additional-scripts')
    <script>
        let currentPage = 1;
        let hasMore = {{ $patients->hasMorePages() ? 'true' : 'false' }};
        let isLoading = false;
        let searchDebounceTimer = null;

        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
        });

        function setupEventListeners() {
            const searchInput = document.getElementById('searchBar');
            const barangaySelect = document.getElementById('barangay');
            const clearBtn = document.getElementById('clearFiltersBtn');

            // Function to update clear button state
            function updateClearButtonState() {
                const hasFilters = (searchInput && searchInput.value.trim() !== '') || 
                                   (barangaySelect && barangaySelect.value !== '');
                if (clearBtn) {
                    if (hasFilters) {
                        clearBtn.classList.remove('opacity-50', 'pointer-events-none');
                    } else {
                        clearBtn.classList.add('opacity-50', 'pointer-events-none');
                    }
                }
            }

            // Debounced search input (300ms) - triggers on ANY change including clearing
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    updateClearButtonState();
                    clearTimeout(searchDebounceTimer);
                    searchDebounceTimer = setTimeout(() => {
                        // Always trigger search, even if empty (to show all patients)
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

            // Barangay filter - triggers immediate search
            if (barangaySelect) {
                barangaySelect.addEventListener('change', function() {
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
            const barangay = document.getElementById('barangay').value;

            const params = new URLSearchParams();
            params.append('page', 1);
            if (searchQuery) params.append('search', searchQuery);
            if (barangay) params.append('barangay', barangay);

            showLoading(true);

            fetch(`/patients/load?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    replaceTableRows(data.patients);
                    hasMore = data.has_more || false;
                    updateLoadMoreButton();
                    showLoading(false);
                })
                .catch(error => {
                    console.error('Error searching patients:', error);
                    showLoading(false);
                });
        }

        function loadMorePatients() {
            if (isLoading || !hasMore) return;

            currentPage++;
            const searchQuery = document.getElementById('searchBar').value.trim();
            const barangay = document.getElementById('barangay').value;

            const params = new URLSearchParams();
            params.append('page', currentPage);
            if (searchQuery) params.append('search', searchQuery);
            if (barangay) params.append('barangay', barangay);

            showLoading(true);

            fetch(`/patients/load?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    appendTableRows(data.patients);
                    hasMore = data.has_more || false;
                    updateLoadMoreButton();
                    showLoading(false);
                })
                .catch(error => {
                    console.error('Error loading more patients:', error);
                    showLoading(false);
                    currentPage--; // Revert page increment on error
                });
        }

        function replaceTableRows(patients) {
            const tbody = document.getElementById('patientsTableBody');
            if (!tbody) return;

            if (patients.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                            <p class="text-lg font-semibold mb-1">No patients found</p>
                            <p class="text-sm">Try adjusting your search or filters</p>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = patients.map(patient => createPatientRow(patient)).join('');
            }
        }

        function appendTableRows(patients) {
            const tbody = document.getElementById('patientsTableBody');
            if (!tbody) return;

            // Remove "no patients" message if it exists
            const noResultsRow = tbody.querySelector('td[colspan="4"]');
            if (noResultsRow) {
                noResultsRow.parentElement.remove();
            }

            const rows = patients.map(patient => createPatientRow(patient)).join('');
            tbody.insertAdjacentHTML('beforeend', rows);
        }

        function createPatientRow(patient) {
            const returnUrl = encodeURIComponent('{{ route("health_worker.patients") }}');
            const patientViewUrl = `/health_worker/patient_view/${patient.id}?return=${returnUrl}`;
            return `
                <tr class="patient-row hover:bg-primary-50 cursor-pointer transition-colors" data-id="${patient.id}" onclick="window.location.href='${patientViewUrl}'">
                    <td class="px-6 py-4 text-base font-semibold text-gray-900">${patient.display_name}</td>
                    <td class="px-6 py-4 text-base text-gray-700">${patient.display_age || 'N/A'}</td>
                    <td class="px-6 py-4 text-base text-gray-700">${patient.barangay}</td>
                    <td class="px-6 py-4 text-base text-gray-600">${patient.contact_no || 'N/A'}</td>
                </tr>
            `;
        }

        function updateLoadMoreButton() {
            const loadMoreBtn = document.getElementById('loadMoreBtn');
            if (loadMoreBtn) {
                loadMoreBtn.style.display = hasMore ? 'inline-flex' : 'none';
            }
        }

        function showLoading(show) {
            isLoading = show;
            const loadingIndicator = document.getElementById('loadingIndicator');
            const loadMoreBtn = document.getElementById('loadMoreBtn');

            if (loadingIndicator) {
                loadingIndicator.classList.toggle('hidden', !show);
            }
            if (loadMoreBtn) {
                loadMoreBtn.style.display = (show || !hasMore) ? 'none' : 'inline-flex';
            }
        }
    </script>
@endsection