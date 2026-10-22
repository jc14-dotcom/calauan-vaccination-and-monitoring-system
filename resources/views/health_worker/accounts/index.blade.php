@extends('layouts.responsive-layout')

@section('title', 'Account Management')

@section('additional-styles')
<style>
    .hw-container { width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width: 640px){ .hw-container { padding-left:2rem; padding-right:2rem; } }
    @media (min-width: 1280px){ .hw-container { padding-left:2.5rem; padding-right:2.5rem; } }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
@endsection

@section('content')
<div class="hw-container flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4" data-animate>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Account Management</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Manage health worker accounts</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('health_worker.accounts.create') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Account
                </a>
            </div>
        </div>
    </section>

    <!-- Password Display Modal -->
    @if(session('generated_password'))
    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 bg-green-600">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @if(session('created_username'))
                        Account Created Successfully
                    @else
                        Password Reset Successfully
                    @endif
                </h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">
                    @if(session('created_username'))
                        The account <strong>{{ session('created_username') }}</strong> has been created.
                    @else
                        Password has been reset for <strong>{{ session('reset_username') }}</strong>.
                    @endif
                </p>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-yellow-800 font-medium mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Save this password! It will not be shown again.
                    </p>
                    <div class="flex items-center gap-2">
                        <input type="text" id="generatedPassword" value="{{ session('generated_password') }}" readonly
                            class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-lg font-mono text-lg text-center tracking-wider">
                        <button onclick="copyPassword()" class="px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors" title="Copy to clipboard">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                    <p id="copyFeedback" class="text-xs text-green-600 mt-2 hidden">✓ Copied to clipboard!</p>
                </div>

                <button onclick="closePasswordModal()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    I've Saved the Password
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Success/Error Messages -->
    @if(session('success') && !session('generated_password'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Accounts</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Active</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">RHU Admins</p>
                    <p class="text-2xl font-bold text-primary-600">{{ $stats['rhu_admins'] }}</p>
                </div>
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Barangay Workers</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['barangay_workers'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <form id="filterForm" method="GET" action="{{ route('health_worker.accounts.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Account Type</label>
                    <select name="type" id="filterType" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">All Types</option>
                        <option value="rhu" {{ request('type') === 'rhu' ? 'selected' : '' }}>RHU Admin</option>
                        <option value="barangay" {{ request('type') === 'barangay' ? 'selected' : '' }}>Barangay Worker</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="filterStatus" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Barangay Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Barangay</label>
                    <select name="barangay_id" id="filterBarangay" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">All Barangays</option>
                        @foreach($barangays as $barangay)
                            <option value="{{ $barangay->id }}" {{ request('barangay_id') == $barangay->id ? 'selected' : '' }}>
                                {{ $barangay->name }} ({{ $workerCounts[$barangay->id] ?? 0 }}/5)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                    <div class="relative flex items-center">
                        <input type="text" name="search" id="filterSearch" value="{{ request('search') }}" placeholder="Username, name, or email..."
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
            </div>
            
            <!-- Reset button (always visible) -->
            <div class="flex justify-end mt-4">
                <a href="{{ route('health_worker.accounts.index') }}" id="resetFiltersBtn" class="px-5 py-2.5 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2 {{ !(request('type') || request('status') || request('barangay_id') || request('search')) ? 'opacity-50 pointer-events-none' : '' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Accounts Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Username</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Name</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Account Type</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Last Login</th>
                        <th class="px-6 py-4 text-left text-sm font-bold bg-primary-700 text-white uppercase tracking-wide">Actions</th>
                    </tr>
                </thead>
                <tbody id="accountsTableBody" class="bg-white divide-y divide-gray-200">
                    @include('health_worker.accounts._table_rows', ['accounts' => $accounts])
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="accountsPagination">
            @include('health_worker.accounts._pagination', ['accounts' => $accounts])
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full overflow-hidden">
        <div id="confirmModalHeader" class="px-6 py-4">
            <h3 id="confirmModalTitle" class="text-lg font-semibold text-white"></h3>
        </div>
        <div class="p-6">
            <p id="confirmModalMessage" class="text-gray-600 mb-6"></p>
            <div class="flex justify-end gap-3">
                <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
                <form id="confirmForm" method="POST">
                    @csrf
                    <button type="submit" id="confirmButton" class="px-4 py-2 text-white rounded-lg font-medium transition-colors">
                        Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional-scripts')
<script>
    // Auto-apply filters functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filterForm');
        const filterType = document.getElementById('filterType');
        const filterStatus = document.getElementById('filterStatus');
        const filterBarangay = document.getElementById('filterBarangay');
        const filterSearch = document.getElementById('filterSearch');
        const tableBody = document.getElementById('accountsTableBody');
        const paginationContainer = document.getElementById('accountsPagination');
        const resetBtn = document.getElementById('resetFiltersBtn');
        
        let searchTimeout = null;
        let currentPage = 1;

        function updateResetButtonState() {
            const hasFilters = filterSearch.value.trim() !== '' ||
                               filterType.value !== '' ||
                               filterStatus.value !== '' ||
                               filterBarangay.value !== '';
            if (resetBtn) {
                if (hasFilters) {
                    resetBtn.classList.remove('opacity-50', 'pointer-events-none');
                } else {
                    resetBtn.classList.add('opacity-50', 'pointer-events-none');
                }
            }
        }

        function loadAccounts(page) {
            page = page || 1;
            currentPage = page;

            const params = new URLSearchParams();
            if (filterSearch.value.trim()) params.append('search', filterSearch.value.trim());
            if (filterType.value) params.append('type', filterType.value);
            if (filterStatus.value) params.append('status', filterStatus.value);
            if (filterBarangay.value) params.append('barangay_id', filterBarangay.value);
            if (page > 1) params.append('page', page);

            fetch(`/health_worker/accounts/load?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = data.tableHtml;
                    paginationContainer.innerHTML = data.paginationHtml;
                    bindPaginationLinks();
                    updateResetButtonState();

                    // Update URL without reloading
                    const url = new URL(window.location);
                    url.search = params.toString();
                    window.history.replaceState({}, '', url);
                })
                .catch(error => {
                    console.error('Error loading accounts:', error);
                });
        }

        function bindPaginationLinks() {
            document.querySelectorAll('.pagination-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('data-page');
                    loadAccounts(parseInt(page));
                });
            });
        }

        // Bind initial pagination links
        bindPaginationLinks();

        // Initialize reset button state
        updateResetButtonState();
        
        // Auto-submit when dropdown filters change
        [filterType, filterStatus, filterBarangay].forEach(function(select) {
            select.addEventListener('change', function() {
                loadAccounts(1);
            });
        });
        
        // Debounced search - wait 300ms after user stops typing
        filterSearch.addEventListener('input', function() {
            updateResetButtonState();
            
            // Clear previous timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Set new timeout
            searchTimeout = setTimeout(function() {
                loadAccounts(1);
            }, 300);
        });
        
        // Submit on Enter key in search
        filterSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                loadAccounts(1);
            }
        });
    });

    function copyPassword() {
        const input = document.getElementById('generatedPassword');
        input.select();
        document.execCommand('copy');
        document.getElementById('copyFeedback').classList.remove('hidden');
    }

    function closePasswordModal() {
        document.getElementById('passwordModal').remove();
    }

    function confirmResetPassword(id, username) {
        const modal = document.getElementById('confirmModal');
        const header = document.getElementById('confirmModalHeader');
        const title = document.getElementById('confirmModalTitle');
        const message = document.getElementById('confirmModalMessage');
        const form = document.getElementById('confirmForm');
        const button = document.getElementById('confirmButton');

        header.className = 'px-6 py-4 bg-yellow-600';
        title.textContent = 'Reset Password';
        message.textContent = `Are you sure you want to reset the password for "${username}"? A new password will be generated.`;
        form.action = `/health_worker/accounts/${id}/reset-password`;
        button.className = 'px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-colors';
        button.textContent = 'Reset Password';

        modal.classList.remove('hidden');
    }

    function confirmToggleStatus(id, username, isActive) {
        const modal = document.getElementById('confirmModal');
        const header = document.getElementById('confirmModalHeader');
        const title = document.getElementById('confirmModalTitle');
        const message = document.getElementById('confirmModalMessage');
        const form = document.getElementById('confirmForm');
        const button = document.getElementById('confirmButton');

        if (isActive) {
            header.className = 'px-6 py-4 bg-red-600';
            title.textContent = 'Deactivate Account';
            message.textContent = `Are you sure you want to deactivate "${username}"? They will no longer be able to login.`;
            button.className = 'px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors';
            button.textContent = 'Deactivate';
        } else {
            header.className = 'px-6 py-4 bg-green-600';
            title.textContent = 'Activate Account';
            message.textContent = `Are you sure you want to activate "${username}"? They will be able to login again.`;
            button.className = 'px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors';
            button.textContent = 'Activate';
        }

        form.action = `/health_worker/accounts/${id}/toggle-status`;

        modal.classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });
</script>
@endsection
