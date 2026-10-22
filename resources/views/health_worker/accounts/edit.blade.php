@extends('layouts.responsive-layout')

@section('title', 'Edit Account')

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
    
    /* Button States */
    .btn-enabled {
        background-color: #6a4baa !important;
        color: white !important;
        cursor: pointer !important;
        pointer-events: auto !important;
    }
    .btn-enabled:hover {
        background-color: #553c9a !important;
    }
    .btn-disabled {
        background-color: #d1d5db !important;
        color: #6b7280 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
    }
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Edit Account</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Editing: {{ $account->username }}</p>
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

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-primary-700">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Account Information
            </h2>
        </div>

        <form action="{{ route('health_worker.accounts.update', $account->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <!-- Username (Read-only) -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input type="text" value="{{ $account->username }}" disabled
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-500">Username cannot be changed</p>
            </div>

            <!-- Account Type -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Account Type <span class="text-red-500">*</span></label>
                @if($isOwnAccount)
                    {{-- Own account: Show read-only account type --}}
                    <div class="flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100">
                        @if($account->isRHU())
                            <span class="inline-flex items-center gap-2 text-purple-700 font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                RHU Admin
                            </span>
                        @else
                            <span class="inline-flex items-center gap-2 text-blue-700 font-medium">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Barangay Worker - {{ $account->barangay->name ?? 'Unknown' }}
                            </span>
                        @endif
                    </div>
                    <input type="hidden" name="account_type" value="{{ $account->isRHU() ? 'rhu' : 'barangay' }}">
                    @if(!$account->isRHU())
                        <input type="hidden" name="barangay_id" value="{{ $account->barangay_id }}">
                    @endif
                    <p class="mt-1 text-xs text-amber-600">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        You cannot change your own account type. Another RHU Admin must do this.
                    </p>
                @else
                    {{-- Other accounts: Allow account type change --}}
                    <div class="flex gap-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="account_type" value="rhu" id="accountTypeRhu"
                                {{ old('account_type', $account->isRHU() ? 'rhu' : 'barangay') === 'rhu' ? 'checked' : '' }}
                                class="w-5 h-5 text-primary-600 focus:ring-primary-500" onchange="toggleBarangayField(); checkFormChanges();">
                            <span class="text-gray-700">
                                <span class="font-medium">RHU Admin</span>
                                <span class="text-sm text-gray-500 block">Access to all barangays</span>
                            </span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="account_type" value="barangay" id="accountTypeBarangay"
                                {{ old('account_type', $account->isRHU() ? 'rhu' : 'barangay') === 'barangay' ? 'checked' : '' }}
                                class="w-5 h-5 text-primary-600 focus:ring-primary-500" onchange="toggleBarangayField(); checkFormChanges();">
                            <span class="text-gray-700">
                                <span class="font-medium">Barangay Worker</span>
                                <span class="text-sm text-gray-500 block">Access to assigned barangay only</span>
                            </span>
                        </label>
                    </div>
                @endif
            </div>

            <!-- Barangay Selection (only show if not own account and is barangay worker) -->
            @if(!$isOwnAccount)
            <div id="barangayField" class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Assigned Barangay <span class="text-red-500">*</span></label>
                <select name="barangay_id" id="barangay_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    data-original="{{ $account->barangay_id }}" onchange="checkFormChanges();">
                    <option value="">Select Barangay</option>
                    @foreach($barangays as $barangay)
                        @php
                            $count = $workerCounts[$barangay->id] ?? 0;
                            $isFull = $count >= 5;
                            $isCurrentBarangay = $account->barangay_id == $barangay->id;
                        @endphp
                        <option value="{{ $barangay->id }}" 
                            {{ old('barangay_id', $account->barangay_id) == $barangay->id ? 'selected' : '' }}
                            {{ $isFull && !$isCurrentBarangay ? 'disabled' : '' }}
                            class="{{ $isFull && !$isCurrentBarangay ? 'text-gray-400' : '' }}">
                            {{ $barangay->name }} ({{ $count }}/5 workers)
                            {{ $isFull && !$isCurrentBarangay ? '- FULL' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Maximum 5 active workers per barangay</p>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Display Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editName" value="{{ old('name', $account->name) }}" 
                        {{ $isOwnAccount ? 'disabled' : 'required' }}
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg {{ $isOwnAccount ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'focus:ring-2 focus:ring-purple-500 focus:border-transparent' }}"
                        placeholder="e.g., Maria Santos"
                        data-original="{{ $account->name }}">
                    @if($isOwnAccount)
                        <input type="hidden" name="name" value="{{ $account->name }}">
                        <p class="mt-1 text-xs text-amber-600">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Edit your name in Profile Settings
                        </p>
                    @else
                        <p class="mt-1 text-xs text-gray-500">Full name shown in activity logs</p>
                    @endif
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <input type="email" name="email" id="editEmail" value="{{ old('email', $account->email) }}"
                        {{ $isOwnAccount ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg {{ $isOwnAccount ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'focus:ring-2 focus:ring-purple-500 focus:border-transparent' }}"
                        placeholder="e.g., maria@example.com"
                        data-original="{{ $account->email }}">
                    @if($isOwnAccount)
                        <input type="hidden" name="email" value="{{ $account->email }}">
                        <p class="mt-1 text-xs text-amber-600">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Edit your email in Profile Settings
                        </p>
                    @else
                        <p id="emailError" class="mt-1 text-xs font-medium text-red-600 h-4">&nbsp;</p>
                        <p class="text-xs text-gray-500">For password recovery</p>
                    @endif
                </div>

                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number <span class="text-gray-400 text-xs font-normal">(Optional)</span></label>
                    <input type="text" name="contact_number" id="editContactNumber" value="{{ old('contact_number', $account->contact_number) }}"
                        {{ $isOwnAccount ? 'disabled' : '' }}
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg {{ $isOwnAccount ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : 'focus:ring-2 focus:ring-purple-500 focus:border-transparent' }}"
                        placeholder="e.g., 09123456789"
                        pattern="09\d{9}" maxlength="11" inputmode="numeric"
                        data-original="{{ $account->contact_number }}">
                    @if($isOwnAccount)
                        <input type="hidden" name="contact_number" value="{{ $account->contact_number }}">
                        <p class="mt-1 text-xs text-amber-600">
                            <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Edit your contact number in Profile Settings
                        </p>
                    @else
                        <p id="contactError" class="mt-1 text-xs font-medium text-red-600 h-4">&nbsp;</p>
                    @endif
                </div>

                <!-- Account Status (Read-only) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <div class="flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50">
                        @if($account->is_active)
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            <span class="text-green-700 font-medium">Active</span>
                        @else
                            <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                            <span class="text-red-700 font-medium">Inactive</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Change status from the account list</p>
                </div>
            </div>

            <!-- Account Info -->
            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Created:</span>
                        <span class="font-medium text-gray-700 ml-1">{{ $account->created_at ? $account->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') : 'Unknown' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Created by:</span>
                        <span class="font-medium text-gray-700 ml-1">{{ $account->creator ? ($account->creator->name ?? $account->creator->username ?? 'Unknown') : 'System' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Last login:</span>
                        <span class="font-medium text-gray-700 ml-1">{{ $account->last_login_at ? $account->last_login_at->timezone('Asia/Manila')->format('M d, Y h:i A') : 'Never' }}</span>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('health_worker.accounts.index') }}" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" id="saveChangesBtn" disabled class="px-6 py-2.5 rounded-lg font-medium transition-colors flex items-center gap-2 btn-disabled">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('additional-scripts')
<script>
    // Check if this is the user's own account
    const isOwnAccount = {{ $isOwnAccount ? 'true' : 'false' }};
    
    // Store original values for change detection
    const originalValues = {
        accountType: '{{ $account->isRHU() ? 'rhu' : 'barangay' }}',
        barangayId: '{{ $account->barangay_id ?? '' }}',
        name: '{{ $account->name }}',
        email: '{{ $account->email ?? '' }}',
        contactNumber: '{{ $account->contact_number ?? '' }}'
    };
    
    // Get form elements
    const saveBtn = document.getElementById('saveChangesBtn');
    const editName = document.getElementById('editName');
    const editEmail = document.getElementById('editEmail');
    const editContact = document.getElementById('editContactNumber');
    const barangaySelect = document.getElementById('barangay_id'); // May be null for own account
    const emailError = document.getElementById('emailError');
    const contactError = document.getElementById('contactError');
    
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

    // Auto-capitalize display name on blur
    if (editName) {
        editName.addEventListener('blur', function(e) {
            this.value = formatProperName(this.value);
        });
    }
    
    // Enable/disable button helpers
    function enableButton(btn) {
        btn.disabled = false;
        btn.classList.remove('btn-disabled');
        btn.classList.add('btn-enabled');
    }
    
    function disableButton(btn) {
        btn.disabled = true;
        btn.classList.remove('btn-enabled');
        btn.classList.add('btn-disabled');
    }

    function toggleBarangayField() {
        // Only run if not own account (own account has no radio buttons)
        if (isOwnAccount) return;
        
        const accountTypeRadio = document.querySelector('input[name="account_type"]:checked');
        if (!accountTypeRadio) return;
        
        const accountType = accountTypeRadio.value;
        const barangayField = document.getElementById('barangayField');
        const barangaySelectEl = document.getElementById('barangay_id');
        
        if (!barangayField || !barangaySelectEl) return;
        
        if (accountType === 'rhu') {
            barangayField.classList.add('hidden');
            barangaySelectEl.removeAttribute('required');
        } else {
            barangayField.classList.remove('hidden');
            barangaySelectEl.setAttribute('required', 'required');
        }
    }
    
    // Validate email format
    function validateEmail() {
        const value = editEmail.value.trim();
        if (value === '') {
            emailError.innerHTML = '&nbsp;';
            editEmail.classList.remove('border-red-400');
            editEmail.classList.add('border-gray-300');
            return true; // Email is optional
        }
        const rx = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!rx.test(value)) {
            emailError.textContent = 'Enter a valid email (e.g., name@gmail.com)';
            editEmail.classList.add('border-red-400');
            editEmail.classList.remove('border-gray-300');
            return false;
        }
        emailError.innerHTML = '&nbsp;';
        editEmail.classList.remove('border-red-400');
        editEmail.classList.add('border-gray-300');
        return true;
    }
    
    // Validate contact number format
    function validateContactNumber() {
        const value = editContact.value;
        if (value === '') {
            contactError.innerHTML = '&nbsp;';
            editContact.classList.remove('border-red-400');
            editContact.classList.add('border-gray-300');
            return true; // Contact is optional
        }
        const rx = /^09\d{9}$/;
        if (!rx.test(value)) {
            contactError.textContent = 'Must start with 09 and be exactly 11 digits';
            editContact.classList.add('border-red-400');
            editContact.classList.remove('border-gray-300');
            return false;
        }
        contactError.innerHTML = '&nbsp;';
        editContact.classList.remove('border-red-400');
        editContact.classList.add('border-gray-300');
        return true;
    }
    
    // Check if form has valid changes
    function checkFormChanges() {
        let accountTypeChanged = false;
        let barangayChanged = false;
        
        // Only check account type/barangay changes if NOT own account
        if (!isOwnAccount) {
            const currentAccountType = document.querySelector('input[name="account_type"]:checked')?.value || '';
            const currentBarangayId = barangaySelect ? barangaySelect.value : '';
            accountTypeChanged = currentAccountType !== originalValues.accountType;
            barangayChanged = currentBarangayId !== originalValues.barangayId;
        }
        
        const currentName = editName.value;
        const currentEmail = editEmail.value.trim();
        const currentContact = editContact.value;
        
        // Check for changes
        const nameChanged = currentName !== originalValues.name;
        const emailChanged = currentEmail !== originalValues.email;
        const contactChanged = currentContact !== originalValues.contactNumber;
        
        const hasChanges = accountTypeChanged || barangayChanged || nameChanged || emailChanged || contactChanged;
        
        // Validate fields
        const emailValid = validateEmail();
        const contactValid = validateContactNumber();
        const nameValid = currentName.trim() !== '';
        
        // Enable button only if there are changes AND all validations pass
        if (hasChanges && emailValid && contactValid && nameValid) {
            enableButton(saveBtn);
        } else {
            disableButton(saveBtn);
        }
    }
    
    // Block non-digit input for contact number
    editContact.addEventListener('input', function(e) {
        // Remove any non-digit characters
        let value = this.value.replace(/\D/g, '');
        // Limit to 11 digits
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        this.value = value;
        checkFormChanges();
    });
    
    // Add event listeners for change detection
    editName.addEventListener('input', checkFormChanges);
    editEmail.addEventListener('input', checkFormChanges);
    
    // Form submission validation
    document.querySelector('form').addEventListener('submit', function(e) {
        if (!validateEmail() || !validateContactNumber()) {
            e.preventDefault();
            return false;
        }
        if (editName.value.trim() === '') {
            e.preventDefault();
            return false;
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleBarangayField();
        // Initial state: button disabled since no changes yet
        disableButton(saveBtn);
    });
</script>
@endsection
