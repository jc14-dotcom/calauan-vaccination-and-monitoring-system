@extends('layouts.responsive-layout')

@section('title', 'Feedback Management')

@section('additional-styles')
<!-- Add viewport meta tag to ensure proper mobile scaling -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
@endsection

@section('content')
    <div class="tw-p-3 md:tw-p-6 tw-max-h-screen tw-overflow-y-auto">
        <h1 class="tw-text-xl md:tw-text-2xl tw-font-bold tw-mb-4 md:tw-mb-8">Feedback Management</h1>

        <!-- Sorting Section - Improved for mobile view -->
        <div class="tw-bg-white tw-p-3 md:tw-p-6 tw-rounded-lg tw-shadow tw-mb-4 md:tw-mb-6">
            <div class="tw-flex tw-flex-col tw-gap-3">
                <div class="tw-w-full">
                    <label for="barangay" class="tw-font-medium tw-text-xs md:tw-text-sm tw-mb-1 tw-block">Filter by Barangay:</label>
                    <select id="barangay" class="tw-border tw-border-gray-300 tw-rounded tw-p-2 tw-w-full focus:tw-ring-purple-500 focus:tw-border-purple-500 tw-text-xs md:tw-text-sm">
                        <option value="">All</option>
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
                
                <div class="tw-flex tw-items-center tw-gap-3 tw-flex-wrap tw-justify-between">
                    <div class="tw-flex-1 tw-min-w-[150px]">
                        <label for="order" class="tw-font-medium tw-text-xs md:tw-text-sm tw-mb-1 tw-block">Sort by:</label>
                        <select id="order" class="tw-border tw-border-gray-300 tw-rounded tw-p-2 tw-w-full focus:tw-ring-purple-500 focus:tw-border-purple-500 tw-text-xs md:tw-text-sm">
                            <option value="desc">Newest</option>
                            <option value="asc">Oldest</option>
                        </select>
                    </div>
                    <button id="resetFilters" title="Reset Filters" class="tw-bg-purple-600 tw-p-2 tw-rounded tw-text-white hover:tw-bg-purple-700 tw-transition tw-h-[38px] tw-self-end">
                        <img src="{{ asset('images/undo.png') }}" alt="Reset" class="tw-w-5 tw-h-5">
                    </button>
                </div>
            </div>
        </div>

        <!-- Feedback Content - Now with card-based mobile view -->
        <div class="tw-bg-white tw-rounded-lg tw-shadow tw-overflow-hidden">
            <!-- Desktop view -->
            <div class="tw-hidden md:tw-block">
                <div class="tw-overflow-x-auto tw-max-h-[calc(100vh-240px)]">
                    <table class="tw-min-w-full tw-divide-y tw-divide-gray-200">
                        <thead class="tw-bg-purple-700 tw-sticky tw-top-0">
                            <tr>
                                <th scope="col" class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-white tw-uppercase tw-tracking-wider">Name</th>
                                <th scope="col" class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-white tw-uppercase tw-tracking-wider">Barangay</th>
                                <th scope="col" class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-white tw-uppercase tw-tracking-wider">Feedback</th>
                                <th scope="col" class="tw-px-4 md:tw-px-6 tw-py-3 tw-text-left tw-text-xs tw-font-medium tw-text-white tw-uppercase tw-tracking-wider">Submitted On</th>
                            </tr>
                        </thead>
                        <tbody id="feedbackTableBody" class="tw-bg-white tw-divide-y tw-divide-gray-200">
                            @forelse ($feedbacks->take(10) as $feedback)
                                <tr class="clickable-row hover:tw-bg-purple-50 tw-cursor-pointer">
                                    <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4 tw-whitespace-nowrap">{{ $feedback->patient ? $feedback->patient->name : 'Unknown Patient' }}</td>
                                    <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4 tw-whitespace-nowrap">{{ $feedback->patient ? $feedback->patient->barangay : 'Unknown Barangay' }}</td>
                                    <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4">{{ $feedback->content }}</td>
                                    <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4 tw-whitespace-nowrap">{{ $feedback->created_at->format('F j, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-italic">No feedback available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Mobile card view -->
            <div class="md:tw-hidden">
                <div class="tw-divide-y tw-divide-gray-200 tw-max-h-[60vh] tw-overflow-y-auto" id="mobileFeedbackCards">
                    @forelse ($feedbacks->take(10) as $feedback)
                        <div class="tw-p-3 tw-cursor-pointer hover:tw-bg-purple-50 mobile-feedback-card">
                            <div class="tw-flex tw-justify-between tw-items-start">
                                <h3 class="tw-font-medium tw-text-sm tw-text-purple-700">{{ $feedback->patient ? $feedback->patient->name : 'Unknown Patient' }}</h3>
                                <span class="tw-text-xs tw-text-gray-500 tw-whitespace-nowrap">{{ $feedback->created_at->format('M j, Y') }}</span>
                            </div>
                            <p class="tw-text-xs tw-text-gray-700 tw-mt-1 tw-mb-2">{{ $feedback->patient ? $feedback->patient->barangay : 'Unknown Barangay' }}</p>
                            <div class="tw-text-xs tw-bg-gray-50 tw-p-2 tw-rounded tw-line-clamp-2 tw-text-gray-600">
                                @php
                                    try {
                                        $content = json_decode($feedback->content, true);
                                        echo "Q1: " . ($content['question1'] ?? 'N/A');
                                    } catch (\Exception $e) {
                                        echo substr($feedback->content, 0, 100) . (strlen($feedback->content) > 100 ? '...' : '');
                                    }
                                @endphp
                            </div>
                        </div>
                    @empty
                        <div class="tw-p-4 tw-text-center tw-text-gray-500 tw-italic tw-text-sm">No feedback available.</div>
                    @endforelse
                </div>
            </div>
            
            <!-- Loading indicator -->
            <div id="loadingIndicator" class="tw-hidden tw-p-4 tw-text-center tw-text-gray-600">
                <div class="tw-inline-block tw-w-5 tw-h-5 tw-border-2 tw-border-gray-200 tw-rounded-full tw-border-t-purple-600 tw-animate-spin tw-mr-2"></div> 
                <span class="tw-text-xs md:tw-text-sm">Loading more feedback...</span>
            </div>
            
            <!-- No results message -->
            <div id="noResultsMessage" class="tw-hidden tw-p-4 tw-text-center tw-text-gray-500 tw-text-xs md:tw-text-sm">
                No more feedback available.
            </div>
        </div>

        <!-- Feedback Details Modal - Mobile optimized -->
        <div id="feedbackModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-justify-center tw-items-center tw-z-50 tw-p-4">
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-w-full tw-max-w-3xl tw-mx-auto">
                <div class="tw-flex tw-justify-between tw-items-center tw-bg-purple-700 tw-text-white tw-px-3 md:tw-px-6 tw-py-2 md:tw-py-4 tw-rounded-t-lg">
                    <h2 class="tw-text-base md:tw-text-xl tw-font-bold">Feedback Details</h2>
                    <span class="tw-text-xl md:tw-text-2xl tw-cursor-pointer">&times;</span>
                </div>
                <div class="tw-p-3 md:tw-p-6 tw-overflow-y-auto tw-max-h-[70vh]">
                    <div class="tw-mb-4">
                        <div class="tw-flex tw-flex-col md:tw-flex-row tw-mb-2">
                            <div class="tw-font-semibold tw-text-xs md:tw-text-sm tw-text-purple-700 tw-w-full md:tw-w-1/3">Patient Name:</div>
                            <div id="patientName" class="tw-text-xs md:tw-text-sm"></div>
                        </div>
                        <div class="tw-flex tw-flex-col md:tw-flex-row">
                            <div class="tw-font-semibold tw-text-xs md:tw-text-sm tw-text-purple-700 tw-w-full md:tw-w-1/3">Barangay:</div>
                            <div id="patientBarangay" class="tw-text-xs md:tw-text-sm"></div>
                        </div>
                    </div>
                    
                    <div class="tw-bg-gray-50 tw-rounded tw-p-3 md:tw-p-4">
                        <div id="feedbackDetails" class="tw-text-xs md:tw-text-sm"></div>
                    </div>
                    
                    <div class="tw-mt-3 tw-text-center tw-text-gray-600 tw-italic tw-text-xs">
                        <p>Submitted on: <span id="submissionDate"></span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Confirmation Modal -->
        <div id="logoutModal" class="tw-hidden tw-fixed tw-inset-0 tw-bg-black tw-bg-opacity-50 tw-flex tw-justify-center tw-items-center tw-z-50 tw-p-4">
            <div class="tw-bg-white tw-rounded-lg tw-shadow tw-p-6 tw-max-w-sm tw-mx-auto tw-text-center">
                <p class="tw-mb-6 tw-text-sm">Are you sure you want to log out?</p>
                <div class="tw-flex tw-justify-center tw-gap-4">
                    <button id="confirmLogoutBtn" class="tw-bg-purple-600 tw-text-white tw-py-2 tw-px-4 tw-rounded hover:tw-bg-purple-700 tw-text-sm">Yes</button>
                    <button id="cancelLogoutBtn" class="tw-bg-gray-300 tw-text-gray-800 tw-py-2 tw-px-4 tw-rounded hover:tw-bg-gray-400 tw-text-sm">No</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the CSRF token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Global variables
        let page = 1;
        const pageSize = 10;
        let isLoading = false;
        let hasMoreData = true;
        let barangayFilter = '';
        let orderFilter = 'desc';

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we already have data from initial Laravel render
            const initialRows = document.querySelectorAll('#feedbackTableBody tr');
            const initialCards = document.querySelectorAll('#mobileFeedbackCards .mobile-feedback-card');
            
            if ((initialRows.length === 0 && window.innerWidth >= 768) || 
                (initialCards.length === 0 && window.innerWidth < 768)) {
                loadFeedback();
            } else {
                // Setup for more lazy loading
                initializeClickableRows();
            }
            
            setupEventListeners();
            
            // Get URL parameters and set filters accordingly
            const urlParams = new URLSearchParams(window.location.search);
            barangayFilter = urlParams.get('barangay') || '';
            orderFilter = urlParams.get('order') || 'desc';
            
            if (barangayFilter) document.getElementById('barangay').value = barangayFilter;
            if (orderFilter) document.getElementById('order').value = orderFilter;
        });

        // Setup all event listeners
        function setupEventListeners() {
            // Scroll event for infinite loading
            window.addEventListener('scroll', function() {
                if (isScrollNearBottom() && !isLoading && hasMoreData) {
                    loadMoreFeedback();
                }
            });
            
            // Mobile view specific scroll handling
            const mobileContainer = document.getElementById('mobileFeedbackCards');
            if (mobileContainer) {
                mobileContainer.addEventListener('scroll', function() {
                    const scrollPosition = this.scrollTop + this.clientHeight;
                    const scrollHeight = this.scrollHeight - 50;
                    
                    if (scrollPosition >= scrollHeight && !isLoading && hasMoreData) {
                        loadMoreFeedback();
                    }
                });
            }
            
            // Filter by barangay
            document.getElementById('barangay').addEventListener('change', function() {
                barangayFilter = this.value;
                resetAndReload();
            });
            
            // Sort by order
            document.getElementById('order').addEventListener('change', function() {
                orderFilter = this.value;
                resetAndReload();
            });
            
            // Reset filters
            document.getElementById('resetFilters').addEventListener('click', function() {
                document.getElementById('barangay').value = '';
                document.getElementById('order').value = 'desc';
                barangayFilter = '';
                orderFilter = 'desc';
                resetAndReload();
            });
            
            // Close feedback modal
            document.querySelector('#feedbackModal .tw-cursor-pointer').addEventListener('click', function() {
                document.getElementById('feedbackModal').classList.add('tw-hidden');
            });
            
            // Close feedback modal when clicking outside of it
            window.addEventListener('click', function(event) {
                const feedbackModal = document.getElementById('feedbackModal');
                if (event.target == feedbackModal) {
                    feedbackModal.classList.add('tw-hidden');
                }
            });

            // Init mobile card click events
            const mobileCards = document.querySelectorAll('.mobile-feedback-card');
            mobileCards.forEach(function(card, index) {
                card.addEventListener('click', function() {
                    const feedback = {
                        patient: {
                            name: this.querySelector('h3').innerText,
                            barangay: this.querySelector('p').innerText
                        },
                        formatted_date: this.querySelector('span').innerText,
                        content: '' // This will be parsed from the backend data when actually clicked
                    };
                    showFeedbackDetails(feedback, index);
                });
            });

            // Close logout modal when clicking cancel
            if (document.getElementById('cancelLogoutBtn')) {
                document.getElementById('cancelLogoutBtn').addEventListener('click', function() {
                    document.getElementById('logoutModal').classList.add('tw-hidden');
                });
            }

            // Confirm logout
            if (document.getElementById('confirmLogoutBtn')) {
                document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
                    window.location.href = '{{ route('welcome') }}';
                });
            }
        }

        // Check if scroll is near bottom of page
        function isScrollNearBottom() {
            const scrollPosition = window.innerHeight + window.scrollY;
            const documentHeight = document.body.offsetHeight - 100;
            return scrollPosition >= documentHeight;
        }

        // Reset pagination and reload data
        function resetAndReload() {
            page = 1;
            hasMoreData = true;
            
            // Update URL with filter parameters
            let url = '{{ route('health_worker.feedback') }}';
            let params = new URLSearchParams();
            
            if (barangayFilter) params.append('barangay', barangayFilter);
            if (orderFilter) params.append('order', orderFilter);
            
            window.location.href = `${url}?${params.toString()}`;
        }

        // Load more feedback when scrolling
        function loadMoreFeedback() {
            page++;
            loadFeedback();
        }

        // Load feedback data
        function loadFeedback() {
            if (isLoading || !hasMoreData) return;
            
            isLoading = true;
            document.getElementById('loadingIndicator').classList.remove('tw-hidden');
            document.getElementById('noResultsMessage').classList.add('tw-hidden');
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Prepare the URL with parameters
            let apiUrl = '{{ route('health_worker.feedback.load_more') }}';
            let params = new URLSearchParams();
            params.append('page', page);
            if (barangayFilter) params.append('barangay', barangayFilter);
            if (orderFilter) params.append('order', orderFilter);
            
            // Fetch data from server
            fetch(`${apiUrl}?${params.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                document.getElementById('loadingIndicator').classList.add('tw-hidden');
                
                if (!data.feedbacks || data.feedbacks.length === 0) {
                    hasMoreData = false;
                    if (page === 1) {
                        // No data at all
                        const tableBody = document.getElementById('feedbackTableBody');
                        const mobileContainer = document.getElementById('mobileFeedbackCards');
                        
                        if (tableBody) {
                            tableBody.innerHTML = '<tr><td colspan="4" class="tw-px-6 tw-py-8 tw-text-center tw-text-gray-500 tw-italic tw-text-sm">No feedback available.</td></tr>';
                        }
                        
                        if (mobileContainer) {
                            mobileContainer.innerHTML = '<div class="tw-p-4 tw-text-center tw-text-gray-500 tw-italic tw-text-sm">No feedback available.</div>';
                        }
                    } else {
                        // No more data to load
                        document.getElementById('noResultsMessage').classList.remove('tw-hidden');
                    }
                    isLoading = false;
                    return;
                }
                
                renderFeedback(data.feedbacks);
                hasMoreData = data.hasMore === true;
                
                if (!hasMoreData) {
                    document.getElementById('noResultsMessage').classList.remove('tw-hidden');
                }
                
                isLoading = false;
            })
            .catch(error => {
                console.error('Error fetching feedback:', error);
                document.getElementById('loadingIndicator').classList.add('tw-hidden');
                hasMoreData = false;
                isLoading = false;
            });
        }

        // Render feedback data to the table and mobile view
        function renderFeedback(feedbacks) {
            const tableBody = document.getElementById('feedbackTableBody');
            const mobileContainer = document.getElementById('mobileFeedbackCards');
            const isMobile = window.innerWidth < 768;
            
            // Clear "No feedback available" message if present
            if (page === 1) {
                if (tableBody) {
                    const noDataRow = tableBody.querySelector('.no-data');
                    if (noDataRow) {
                        tableBody.innerHTML = '';
                    }
                }
                
                if (mobileContainer) {
                    const noDataDiv = mobileContainer.querySelector('.tw-italic');
                    if (noDataDiv) {
                        mobileContainer.innerHTML = '';
                    }
                }
            }
            
            feedbacks.forEach(function(feedback, index) {
                // Add to desktop table view
                if (tableBody) {
                    const row = document.createElement('tr');
                    row.className = 'clickable-row hover:tw-bg-purple-50 tw-cursor-pointer';
                    
                    row.innerHTML = `
                        <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4 tw-whitespace-nowrap">${feedback.patient ? feedback.patient.name : 'Unknown Patient'}</td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4 tw-whitespace-nowrap">${feedback.patient ? feedback.patient.barangay : 'Unknown Barangay'}</td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4">${feedback.content}</td>
                        <td class="tw-px-4 md:tw-px-6 tw-py-3 md:tw-py-4 tw-whitespace-nowrap">${feedback.formatted_date}</td>
                    `;
                    
                    // Add click event for viewing details
                    row.addEventListener('click', function() {
                        showFeedbackDetails(feedback);
                    });
                    
                    tableBody.appendChild(row);
                }
                
                // Add to mobile card view
                if (mobileContainer) {
                    const card = document.createElement('div');
                    card.className = 'tw-p-3 tw-cursor-pointer hover:tw-bg-purple-50 mobile-feedback-card';
                    
                    // Try to parse content as JSON for better display
                    let contentPreview = '';
                    try {
                        const contentObj = typeof feedback.content === 'string' ? 
                            JSON.parse(feedback.content) : feedback.content;
                        contentPreview = "Q1: " + (contentObj.question1 || 'N/A');
                    } catch (e) {
                        const contentStr = typeof feedback.content === 'string' ? 
                            feedback.content : JSON.stringify(feedback.content);
                        contentPreview = contentStr.substring(0, 100) + 
                            (contentStr.length > 100 ? '...' : '');
                    }
                    
                    card.innerHTML = `
                        <div class="tw-flex tw-justify-between tw-items-start">
                            <h3 class="tw-font-medium tw-text-sm tw-text-purple-700">${feedback.patient ? feedback.patient.name : 'Unknown Patient'}</h3>
                            <span class="tw-text-xs tw-text-gray-500 tw-whitespace-nowrap">${feedback.formatted_date}</span>
                        </div>
                        <p class="tw-text-xs tw-text-gray-700 tw-mt-1 tw-mb-2">${feedback.patient ? feedback.patient.barangay : 'Unknown Barangay'}</p>
                        <div class="tw-text-xs tw-bg-gray-50 tw-p-2 tw-rounded tw-line-clamp-2 tw-text-gray-600">
                            ${contentPreview}
                        </div>
                    `;
                    
                    // Add click event for viewing details
                    card.addEventListener('click', function() {
                        showFeedbackDetails(feedback);
                    });
                    
                    mobileContainer.appendChild(card);
                }
            });
        }

        // Initialize click events for the first batch of rows
        function initializeClickableRows() {
            const rows = document.querySelectorAll('.clickable-row');
            const cards = document.querySelectorAll('.mobile-feedback-card');
            
            rows.forEach(row => {
                row.addEventListener('click', function() {
                    // Check if content is already parsed JSON or a string
                    let content;
                    try {
                        content = JSON.parse(this.cells[2].textContent);
                    } catch (e) {
                        // If parsing fails, assume it's already an object or has a different format
                        console.warn('Could not parse feedback content as JSON, using as-is');
                        content = {
                            question1: 'N/A',
                            question2: 'N/A',
                            question3: 'N/A',
                            question4: 'N/A'
                        };
                    }
                    
                    // Update patient details
                    document.getElementById('patientName').textContent = this.cells[0].textContent;
                    document.getElementById('patientBarangay').textContent = this.cells[1].textContent;
                    document.getElementById('submissionDate').textContent = this.cells[3].textContent;
                    
                    // Create headers and feedback details
                    const feedbackContainer = document.createElement('div');
                    
                    // Add headers - simpler on mobile
                    const headers = document.createElement('div');
                    headers.className = 'tw-grid tw-grid-cols-2 tw-font-semibold tw-text-purple-700 tw-pb-2 tw-border-b tw-border-purple-300 tw-mb-3 tw-text-xs md:tw-text-sm';
                    headers.innerHTML = `
                        <div>Questions</div>
                        <div class="tw-text-right">Answers</div>
                    `;
                    feedbackContainer.appendChild(headers);
                    
                    // Create feedback details
                    const feedbackDetails = document.createElement('div');
                    feedbackDetails.className = 'tw-space-y-3 tw-text-xs md:tw-text-sm';
                    [
                        ['MADALI BA NINYONG NAHANAP ANG HEALTH CENTER AT MAY NAKITA BA KAYONG KARATULA NG DIREKSYON?', content.question1 || 'N/A'],
                        ['MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?', content.question2 || 'N/A'],
                        ['MAGALANG, MABAIT, AT MAAYOS BA ANG SERBISYO NG HEALTH WORKER NA NAGBIGAY NG BAKUNA?', content.question3 || 'N/A'],
                        ['NAIPALIWANAG BA SA INYO NG HEALTH WORKER ANG TUNGKOL SA BAKUNA AT KAILAN ANG SUSUNOD NA SCHEDULE?', content.question4 || 'N/A'],
                        ['NASIYAHAN BA KAYO SA KABUUANG SERBISYO NG HEALTH CENTER SA ARAW NA ITO?', content.question5 || 'N/A']
                    ].forEach((item, index) => {
                        const questionDiv = document.createElement('div');
                        questionDiv.className = 'tw-grid tw-grid-cols-2 tw-gap-2';
                        questionDiv.innerHTML = `
                            <div class="tw-text-purple-700 tw-text-xs md:tw-text-sm">${index + 1}. ${item[0]}</div>
                            <div class="tw-text-right tw-font-medium tw-text-xs md:tw-text-sm">${item[1]}</div>
                        `;
                        feedbackDetails.appendChild(questionDiv);
                    });
                    
                    feedbackContainer.appendChild(feedbackDetails);
                    document.getElementById('feedbackDetails').innerHTML = '';
                    document.getElementById('feedbackDetails').appendChild(feedbackContainer);
                    document.getElementById('feedbackModal').classList.remove('tw-hidden');
                });
            });
        }

        // Show feedback details for dynamically loaded rows
        function showFeedbackDetails(feedback, cardIndex) {
            // Check if content is already parsed JSON or a string
            let content;
            try {
                content = typeof feedback.content === 'string' ? JSON.parse(feedback.content) : feedback.content;
            } catch (e) {
                // If parsing fails, assume it's already an object or has a different format
                console.warn('Could not parse feedback content as JSON, using as-is');
                content = {
                    question1: 'N/A',
                    question2: 'N/A',
                    question3: 'N/A',
                    question4: 'N/A'
                };
            }
            
            // Update patient details
            document.getElementById('patientName').textContent = feedback.patient ? feedback.patient.name : 'Unknown Patient';
            document.getElementById('patientBarangay').textContent = feedback.patient ? feedback.patient.barangay : 'Unknown Barangay';
            document.getElementById('submissionDate').textContent = feedback.formatted_date;
            
            // Create headers and feedback details
            const feedbackContainer = document.createElement('div');
            
            // Add headers - simpler for mobile
            const headers = document.createElement('div');
            headers.className = 'tw-grid tw-grid-cols-2 tw-font-semibold tw-text-purple-700 tw-pb-2 tw-border-b tw-border-purple-300 tw-mb-3 tw-text-xs md:tw-text-sm';
            headers.innerHTML = `
                <div>Questions</div>
                <div class="tw-text-right">Answers</div>
            `;
            feedbackContainer.appendChild(headers);
            
            // Create feedback details
            const feedbackDetails = document.createElement('div');
            feedbackDetails.className = 'tw-space-y-3';
            [
                ['MADALI BA NINYONG NAHANAP ANG HEALTH CENTER AT MAY NAKITA BA KAYONG KARATULA NG DIREKSYON?', content.question1 || 'N/A'],
                ['MALINIS AT MAAYOS BA ANG TANGGAPANG ITO?', content.question2 || 'N/A'],
                ['MAGALANG, MABAIT, AT MAAYOS BA ANG SERBISYO NG HEALTH WORKER NA NAGBIGAY NG BAKUNA?', content.question3 || 'N/A'],
                ['NAIPALIWANAG BA SA INYO NG HEALTH WORKER ANG TUNGKOL SA BAKUNA AT KAILAN ANG SUSUNOD NA SCHEDULE?', content.question4 || 'N/A'],
                ['NASIYAHAN BA KAYO SA KABUUANG SERBISYO NG HEALTH CENTER SA ARAW NA ITO?', content.question5 || 'N/A']
            ].forEach((item, index) => {
                const questionDiv = document.createElement('div');
                questionDiv.className = 'tw-grid tw-grid-cols-2 tw-gap-2';
                questionDiv.innerHTML = `
                    <div class="tw-text-purple-700 tw-text-xs md:tw-text-sm">${index + 1}. ${item[0]}</div>
                    <div class="tw-text-right tw-font-medium tw-text-xs md:tw-text-sm">${item[1]}</div>
                `;
                feedbackDetails.appendChild(questionDiv);
            });
            
            feedbackContainer.appendChild(feedbackDetails);
            document.getElementById('feedbackDetails').innerHTML = '';
            document.getElementById('feedbackDetails').appendChild(feedbackContainer);
            document.getElementById('feedbackModal').classList.remove('tw-hidden');
        }
    </script>
@endsection