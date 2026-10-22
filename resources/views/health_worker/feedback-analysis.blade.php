@extends('layouts.responsive-layout')

@section('title', 'Feedback Analysis')

@section('additional-styles')
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
<style>
    /* Feedback Analysis responsive guards */
    .hw-container { width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width: 640px){ .hw-container { padding-left:2rem; padding-right:2rem; } }
    @media (min-width: 1280px){ .hw-container { padding-left:2.5rem; padding-right:2.5rem; } }
    
    #feedbackChart {
        display:block; max-width:100% !important;
    }
    .hw-no-overflow-x { overflow-x:hidden; }
    
    /* Fade-in animation */
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
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner - Exact copy from dashboard -->
    <section class="hw-section relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="hw-flex flex items-center gap-4" data-animate>
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                </span>
                <div class="hw-title">
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Feedback Analysis</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Patient satisfaction and service feedback insights</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Feedback Analysis Chart -->
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
            <div class="flex flex-wrap items-center gap-3 md:gap-4">
                <label for="feedbackMonth" class="text-sm md:text-base font-semibold text-white">Month:</label>
                <select id="feedbackMonth" class="rounded-lg border-2 border-white/30 bg-white px-3 md:px-4 py-2 md:py-2.5 text-sm md:text-base text-gray-900 font-semibold focus:border-white focus:ring-2 focus:ring-white/20 min-w-[140px] md:min-w-[160px]" style="color: #1f2937 !important;">
                    <option value="" style="color: #1f2937;">All Months</option>
                    <option value="01" style="color: #1f2937;">January</option>
                    <option value="02" style="color: #1f2937;">February</option>
                    <option value="03" style="color: #1f2937;">March</option>
                    <option value="04" style="color: #1f2937;">April</option>
                    <option value="05" style="color: #1f2937;">May</option>
                    <option value="06" style="color: #1f2937;">June</option>
                    <option value="07" style="color: #1f2937;">July</option>
                    <option value="08" style="color: #1f2937;">August</option>
                    <option value="09" style="color: #1f2937;">September</option>
                    <option value="10" style="color: #1f2937;">October</option>
                    <option value="11" style="color: #1f2937;">November</option>
                    <option value="12" style="color: #1f2937;">December</option>
                </select>
                <label for="feedbackYear" class="text-sm md:text-base font-semibold text-white">Year:</label>
                <select id="feedbackYear" class="rounded-lg border-2 border-white/30 bg-white px-3 md:px-4 py-2 md:py-2.5 text-sm md:text-base text-gray-900 font-semibold focus:border-white focus:ring-2 focus:ring-white/20 min-w-[100px] md:min-w-[120px]" style="color: #1f2937 !important;">
                </select>
                <label for="feedbackBarangay" class="text-sm md:text-base font-semibold text-white">Barangay:</label>
                <select id="feedbackBarangay" class="rounded-lg border-2 border-white/30 bg-white px-3 md:px-4 py-2 md:py-2.5 text-sm md:text-base text-gray-900 font-semibold focus:border-white focus:ring-2 focus:ring-white/20 min-w-[140px] md:min-w-[180px]" style="color: #1f2937 !important;">
                    @if(isset($healthWorker) && $healthWorker && !$healthWorker->isRHU())
                        {{-- Barangay worker - show only their barangay, pre-selected --}}
                        <option value="{{ $healthWorker->getAssignedBarangayName() }}" selected style="color: #1f2937;">{{ $healthWorker->getAssignedBarangayName() }}</option>
                    @else
                        {{-- RHU admin - show all barangays with "All" option --}}
                        <option value="" style="color: #1f2937;">All Barangays</option>
                        @foreach($accessibleBarangays ?? [] as $barangay)
                            <option value="{{ $barangay }}" style="color: #1f2937;">{{ $barangay }}</option>
                        @endforeach
                    @endif
                </select>
                <button id="resetFilters" disabled title="Reset Filters" class="rounded-lg bg-white/20 hover:bg-white/30 disabled:bg-white/10 disabled:cursor-not-allowed disabled:opacity-50 p-2.5 transition-colors ring-1 ring-white/30">
                    <img src="{{ asset('images/undo.png') }}" alt="Reset" class="w-5 h-5 invert">
                </button>
            </div>
        </div>
        <div class="p-6 md:p-8">
            <div class="mb-4 md:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="text-base md:text-lg font-semibold text-gray-800" id="feedbackTitle">Feedback Analysis for <span id="currentPeriod" class="text-primary-700">November 2025</span></h3>
                <p id="totalResponses" class="text-base md:text-lg text-gray-600 font-medium">Total Responses: 0</p>
            </div>
            <div class="h-[300px] sm:h-[400px] md:h-[500px]"><canvas id="feedbackChart"></canvas></div>
        </div>
    </section>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get current date
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0');
        
        // Populate year dropdown (2020 to current year + 1)
        const yearSelect = document.getElementById('feedbackYear');
        for (let year = 2020; year <= currentYear + 1; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            option.style.color = '#1f2937';
            if (year === currentYear) {
                option.selected = true;
            }
            yearSelect.appendChild(option);
        }
        
        // Set current month as selected
        const monthSelect = document.getElementById('feedbackMonth');
        monthSelect.value = currentMonth;
        
        // Month names mapping
        const monthNames = {
            '': 'All Months',
            '01': 'January',
            '02': 'February',
            '03': 'March',
            '04': 'April',
            '05': 'May',
            '06': 'June',
            '07': 'July',
            '08': 'August',
            '09': 'September',
            '10': 'October',
            '11': 'November',
            '12': 'December'
        };
        
        // Initialize feedback chart with empty data
        const feedbackCtx = document.getElementById('feedbackChart');
        
        // Clean up any existing custom tooltip from previous page loads
        const existingTooltip = document.getElementById('custom-tooltip');
        if (existingTooltip) existingTooltip.remove();
        
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
                            },
                            font: {
                                size: window.innerWidth < 640 ? 10 : 14
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: window.innerWidth < 640 ? 10 : 12
                            }
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
                        },
                        titleFont: {
                            size: 15
                        },
                        bodyFont: {
                            size: 14
                        },
                        padding: 12
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15,
                            padding: window.innerWidth < 640 ? 10 : 20,
                            font: {
                                size: window.innerWidth < 640 ? 12 : 14
                            }
                        }
                    }
                },
                onHover: (event, elements, chart) => {
                    if (event && event.native && event.native.target) {
                        event.native.target.style.cursor = elements.length ? 'pointer' : 'default';
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
            
            feedbackChart.data.datasets[0].data = positiveData;
            feedbackChart.data.datasets[1].data = negativeData;
            feedbackChart.update();
            
            // Update display text based on filters
            const monthEl = document.getElementById('feedbackMonth');
            const yearEl = document.getElementById('feedbackYear');
            const barangayEl = document.getElementById('feedbackBarangay');
            
            const selectedMonth = monthEl.value;
            const selectedYear = yearEl.value;
            const selectedBarangay = barangayEl.value;
            
            let displayText = '';
            
            // Build display text based on selections
            if (selectedMonth === '') {
                displayText += 'All Months';
            } else {
                displayText += monthNames[selectedMonth];
            }
            
            displayText += ' ' + selectedYear;
            
            if (selectedBarangay !== '') {
                displayText += ' - ' + selectedBarangay;
            } else {
                displayText += ' - All Barangays';
            }
            
            const currentPeriod = document.getElementById('currentPeriod');
            const totalResponses = document.getElementById('totalResponses');
            if (currentPeriod) currentPeriod.textContent = displayText;
            if (totalResponses) totalResponses.textContent = 'Total Responses: ' + (data.total_responses || 0);
        }
        
        // Handle filter changes
        function onFiltersChange() {
            const monthEl = document.getElementById('feedbackMonth');
            const yearEl = document.getElementById('feedbackYear');
            const barangayEl = document.getElementById('feedbackBarangay');
            
            const selectedMonth = monthEl ? monthEl.value : null;
            const selectedYear = yearEl ? yearEl.value : currentYear;
            const selectedBarangay = barangayEl ? barangayEl.value : '';
            
            fetchFeedbackAnalytics(selectedMonth, selectedYear, selectedBarangay);
        }
        
        const monthEl = document.getElementById('feedbackMonth');
        const yearEl = document.getElementById('feedbackYear');
        const barangayEl = document.getElementById('feedbackBarangay');
        const resetBtn = document.getElementById('resetFilters');
        
        // Store default values
        const defaultMonth = currentMonth;
        const defaultYear = String(currentYear);
        const defaultBarangay = barangayEl ? barangayEl.options[0].value : '';
        
        // Function to update reset button disabled state
        function updateResetButtonState() {
            const isDefault = 
                monthEl.value === defaultMonth &&
                yearEl.value === defaultYear &&
                barangayEl.value === defaultBarangay;
            resetBtn.disabled = isDefault;
        }
        
        // Handle filter changes with reset button state update
        function handleFilterChange() {
            onFiltersChange();
            updateResetButtonState();
        }
        
        if (monthEl) monthEl.addEventListener('change', handleFilterChange);
        if (yearEl) yearEl.addEventListener('change', handleFilterChange);
        if (barangayEl) barangayEl.addEventListener('change', handleFilterChange);
        
        // Reset filters button click handler
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                monthEl.value = defaultMonth;
                yearEl.value = defaultYear;
                barangayEl.value = defaultBarangay;
                onFiltersChange();
                updateResetButtonState();
            });
        }
        
        // Initialize by loading current month's data
        fetchFeedbackAnalytics(currentMonth, currentYear, defaultBarangay);
    });
</script>
@endsection
