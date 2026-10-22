@extends('layouts.responsive-layout')

@section('title', 'Create Account')

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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Create New Account</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Add a new health worker account</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('health_worker.accounts.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </section>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Create Form -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-primary-700">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Account Information
            </h2>
        </div>

        <form action="{{ route('health_worker.accounts.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Account Type -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Account Type <span class="text-red-500">*</span></label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="account_type" value="rhu" {{ old('account_type', 'barangay') === 'rhu' ? 'checked' : '' }}
                            class="w-5 h-5 text-primary-600 focus:ring-primary-500" onchange="toggleBarangayField()">
                        <span class="text-gray-700">
                            <span class="font-medium">RHU Admin</span>
                            <span class="text-sm text-gray-500 block">Access to all barangays</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="radio" name="account_type" value="barangay" {{ old('account_type', 'barangay') === 'barangay' ? 'checked' : '' }}
                            class="w-5 h-5 text-primary-600 focus:ring-primary-500" onchange="toggleBarangayField()">
                        <span class="text-gray-700">
                            <span class="font-medium">Barangay Worker</span>
                            <span class="text-sm text-gray-500 block">Access to assigned barangay only</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Barangay Selection (shown only for Barangay Worker) -->
            <div id="barangayField" class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Assigned Barangay <span class="text-red-500">*</span></label>
                <select name="barangay_id" id="barangay_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Select Barangay</option>
                    @foreach($barangays as $barangay)
                        @php
                            $count = $workerCounts[$barangay->id] ?? 0;
                            $isFull = $count >= 5;
                        @endphp
                        <option value="{{ $barangay->id }}" 
                            {{ old('barangay_id') == $barangay->id ? 'selected' : '' }}
                            {{ $isFull ? 'disabled' : '' }}
                            class="{{ $isFull ? 'text-gray-400' : '' }}">
                            {{ $barangay->name }} ({{ $count }}/5 workers)
                            {{ $isFull ? '- FULL' : '' }}
                        </option>
                    @endforeach
                </select>
                <p id="workerCountInfo" class="mt-1 text-xs text-gray-500">Maximum 5 active workers per barangay</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Username -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="e.g., dayap_worker1" pattern="[a-z0-9_]+" title="Only lowercase letters, numbers, and underscores">
                    <p id="usernameError" class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and underscores only</p>
                </div>

                <!-- Display Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="displayName" value="{{ old('name') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="e.g., Maria Santos">
                    <p class="mt-1 text-xs text-gray-500">Full name shown in activity logs</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="e.g., maria@example.com">
                    <p id="emailError" class="mt-1 text-xs text-gray-500">For password recovery</p>
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <input type="text" name="contact_number" id="contactNumber" value="{{ old('contact_number') }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="e.g., 09123456789" maxlength="11" inputmode="numeric">
                    <p id="contactError" class="mt-1 text-xs text-gray-500">Must start with 09 and be exactly 11 digits</p>
                </div>
            </div>

            <!-- Password Notice -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Password will be auto-generated</p>
                        <p class="text-xs text-blue-600 mt-1">A secure 12-character password will be created and displayed after account creation. Make sure to save it!</p>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('health_worker.accounts.index') }}" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Account
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('additional-scripts')
<script>
    function toggleBarangayField() {
        const accountType = document.querySelector('input[name="account_type"]:checked').value;
        const barangayField = document.getElementById('barangayField');
        const barangaySelect = document.getElementById('barangay_id');
        
        if (accountType === 'rhu') {
            barangayField.classList.add('hidden');
            barangaySelect.removeAttribute('required');
        } else {
            barangayField.classList.remove('hidden');
            barangaySelect.setAttribute('required', 'required');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleBarangayField();
        
        // DOM Elements
        const usernameInput = document.getElementById('username');
        const usernameError = document.getElementById('usernameError');
        const emailInput = document.getElementById('email');
        const emailError = document.getElementById('emailError');
        const contactInput = document.getElementById('contactNumber');
        const contactError = document.getElementById('contactError');
        const displayNameInput = document.getElementById('displayName');
        
        /**
         * Helper function to format name with proper title case
         * Each word's first letter is capitalized
         */
        function formatProperName(name) {
            if (!name || name.trim() === '') return name;
            
            // Convert to lowercase first
            name = name.toLowerCase().trim();
            
            // Capitalize first letter of each word
            name = name.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
            
            // Handle apostrophes (e.g., O'brien -> O'Brien)
            name = name.replace(/'([a-z])/g, function(match, char) {
                return "'" + char.toUpperCase();
            });
            
            // Handle hyphenated names (e.g., Anne-marie -> Anne-Marie)
            name = name.replace(/-([a-z])/g, function(match, char) {
                return '-' + char.toUpperCase();
            });
            
            return name;
        }

        // ===== DISPLAY NAME AUTO-CAPITALIZE =====
        if (displayNameInput) {
            displayNameInput.addEventListener('blur', function(e) {
                this.value = formatProperName(this.value);
            });
        }
        
        // ===== USERNAME VALIDATION =====
        // Only allow lowercase letters, numbers, and underscores
        usernameInput.addEventListener('input', function(e) {
            // Convert to lowercase and remove invalid characters
            let value = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
            this.value = value;
            validateUsername();
        });
        
        function validateUsername() {
            const value = usernameInput.value;
            if (value === '') {
                usernameError.textContent = 'Lowercase letters, numbers, and underscores only';
                usernameError.classList.remove('text-red-600');
                usernameError.classList.add('text-gray-500');
                usernameInput.classList.remove('border-red-400');
                return false;
            }
            if (value.length < 3) {
                usernameError.textContent = 'Username must be at least 3 characters';
                usernameError.classList.add('text-red-600');
                usernameError.classList.remove('text-gray-500');
                usernameInput.classList.add('border-red-400');
                return false;
            }
            const rx = /^[a-z0-9_]+$/;
            if (!rx.test(value)) {
                usernameError.textContent = 'Only lowercase letters, numbers, and underscores allowed';
                usernameError.classList.add('text-red-600');
                usernameError.classList.remove('text-gray-500');
                usernameInput.classList.add('border-red-400');
                return false;
            }
            usernameError.textContent = 'Lowercase letters, numbers, and underscores only';
            usernameError.classList.remove('text-red-600');
            usernameError.classList.add('text-gray-500');
            usernameInput.classList.remove('border-red-400');
            return true;
        }
        
        // ===== EMAIL VALIDATION =====
        emailInput.addEventListener('input', function(e) {
            validateEmail();
        });
        
        function validateEmail() {
            const value = emailInput.value.trim();
            if (value === '') {
                emailError.textContent = 'For password recovery';
                emailError.classList.remove('text-red-600');
                emailError.classList.add('text-gray-500');
                emailInput.classList.remove('border-red-400');
                return true; // Email is optional
            }
            const rx = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!rx.test(value)) {
                emailError.textContent = 'Enter a valid email (e.g., name@gmail.com)';
                emailError.classList.add('text-red-600');
                emailError.classList.remove('text-gray-500');
                emailInput.classList.add('border-red-400');
                return false;
            }
            emailError.textContent = 'For password recovery';
            emailError.classList.remove('text-red-600');
            emailError.classList.add('text-gray-500');
            emailInput.classList.remove('border-red-400');
            return true;
        }
        
        // ===== CONTACT NUMBER VALIDATION =====
        // Only allow digits, must start with 09, exactly 11 digits
        contactInput.addEventListener('input', function(e) {
            // Remove any non-digit characters
            let value = this.value.replace(/\D/g, '');
            // Limit to 11 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            this.value = value;
            validateContact();
        });
        
        function validateContact() {
            const value = contactInput.value;
            if (value === '') {
                contactError.textContent = 'Must start with 09 and be exactly 11 digits';
                contactError.classList.remove('text-red-600');
                contactError.classList.add('text-gray-500');
                contactInput.classList.remove('border-red-400');
                return true; // Contact is optional
            }
            const rx = /^09\d{9}$/;
            if (!rx.test(value)) {
                contactError.textContent = 'Must start with 09 and be exactly 11 digits';
                contactError.classList.add('text-red-600');
                contactError.classList.remove('text-gray-500');
                contactInput.classList.add('border-red-400');
                return false;
            }
            contactError.textContent = 'Must start with 09 and be exactly 11 digits';
            contactError.classList.remove('text-red-600');
            contactError.classList.add('text-gray-500');
            contactInput.classList.remove('border-red-400');
            return true;
        }
        
        // ===== FORM SUBMISSION VALIDATION =====
        document.querySelector('form').addEventListener('submit', function(e) {
            const usernameValid = validateUsername();
            const emailValid = validateEmail();
            const contactValid = validateContact();
            
            if (!usernameValid || !emailValid || !contactValid) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endsection
