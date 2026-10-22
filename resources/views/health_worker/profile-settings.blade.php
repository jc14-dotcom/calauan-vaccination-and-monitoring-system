@extends('layouts.responsive-layout')

@section('title', 'Profile Settings')

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
    
    /* Button States - matches header bg-primary-700 (#6a4baa) */
    .btn-enabled {
        background-color: #6a4baa !important; /* primary-700 from tailwind config */
        color: white !important;
        cursor: pointer !important;
        pointer-events: auto !important;
    }
    .btn-enabled:hover {
        background-color: #553c9a !important; /* primary-800 from tailwind config */
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Profile Settings</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Manage your account information and security</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Success/Error Messages -->
    @if(session('success'))
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Information Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-primary-700">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile Information
                </h2>
            </div>
            <form action="{{ route('health_worker.profile.update') }}" method="POST" class="p-6 space-y-4" id="profileForm">
                @csrf
                @method('PUT')
                
                <!-- Username and Account Type (Side by Side) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Username (Read-only) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <input type="text" value="{{ $user->username }}" disabled
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                        <p class="mt-1 text-xs text-gray-500">Username cannot be changed</p>
                    </div>

                    <!-- Account Type (Read-only) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Account Type</label>
                        <input type="text" value="{{ $user->isRHU() ? 'RHU Admin' : 'Barangay Worker - ' . ($user->barangay->name ?? 'Unknown') }}" disabled
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                    </div>
                </div>

                <!-- Display Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="profileName" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Enter your full name"
                        data-original="{{ $user->name }}">
                    <p class="mt-1 text-xs text-gray-500">This name will appear in activity logs</p>
                </div>

                <!-- Contact Number -->
                <div class="mb-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                    <input type="tel" name="contact_number" id="profileContactNumber" 
                        value="{{ old('contact_number', $user->contact_number) }}"
                        pattern="09\d{9}" maxlength="11" inputmode="numeric"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="09xxxxxxxxx"
                        data-original="{{ $user->contact_number }}">
                    <p id="contactError" class="mt-1 text-xs font-medium text-red-600 h-4">&nbsp;</p>
                </div>

                <div class="pt-2">
                    <button type="submit" id="saveProfileBtn" disabled 
                        class="w-full px-6 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 btn-disabled">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Email Settings Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-primary-700">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email Settings
                </h2>
            </div>
            <form action="{{ route('health_worker.profile.email') }}" method="POST" class="p-6 space-y-4" id="emailForm">
                @csrf
                @method('PUT')
                
                <!-- Current Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Email</label>
                        <input type="text" value="{{ $user->email ?? 'Not set' }}" disabled
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                    </div>

                    <!-- New Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="newEmail" value="{{ old('email') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter new email address">
                        <p id="emailError" class="mt-1 text-xs font-medium text-red-600 h-4">&nbsp;</p>
                    </div>

                    <!-- Current Password -->
                    <div class="mb-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" id="emailCurrentPassword" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter your current password">
                        <p class="mt-1 text-xs text-gray-500 h-4">Required to confirm email change</p>
                    </div>

                <div class="pt-2">
                    <button type="submit" id="updateEmailBtn" disabled 
                        class="w-full px-6 py-3 rounded-lg font-semibold transition-all flex items-center justify-center gap-2 btn-disabled">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Update Email
                    </button>
                </div>
            </form>
        </div>

        <!-- Password Settings Card -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden lg:col-span-2">
            <div class="px-6 py-4 bg-primary-700">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Change Password
                </h2>
            </div>
            <form action="{{ route('health_worker.profile.password') }}" method="POST" class="p-6" id="passwordForm">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" name="current_password" id="pwdCurrent" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter current password">
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">New Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="pwdNew" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Enter new password">
                        <p class="mt-1 text-xs text-gray-500">Min 8 chars, mixed case, numbers</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" id="pwdConfirm" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            placeholder="Confirm new password">
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" id="updatePasswordBtn" disabled 
                        class="px-8 py-3 rounded-lg font-semibold transition-all flex items-center gap-2 shadow-md btn-disabled">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const profileName = document.getElementById('profileName');
    const profileContact = document.getElementById('profileContactNumber');
    const contactError = document.getElementById('contactError');
    const saveProfileBtn = document.getElementById('saveProfileBtn');
    
    const newEmail = document.getElementById('newEmail');
    const emailCurrentPassword = document.getElementById('emailCurrentPassword');
    const emailError = document.getElementById('emailError');
    const updateEmailBtn = document.getElementById('updateEmailBtn');
    
    const pwdCurrent = document.getElementById('pwdCurrent');
    const pwdNew = document.getElementById('pwdNew');
    const pwdConfirm = document.getElementById('pwdConfirm');
    const updatePasswordBtn = document.getElementById('updatePasswordBtn');
    
    // Enable/Disable button helper functions
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

    // Auto-capitalize profile name on blur
    if (profileName) {
        profileName.addEventListener('blur', function(e) {
            this.value = formatProperName(this.value);
        });
    }
    
    // ===== PROFILE FORM =====
    
    // Contact number validation - only allow digits, must start with 09, max 11 digits
    function validateContactNumber() {
        const value = profileContact.value;
        if (value === '') {
            contactError.textContent = '';
            profileContact.classList.remove('border-red-400');
            return true; // Contact is optional
        }
        const rx = /^09\d{9}$/;
        if (!rx.test(value)) {
            contactError.textContent = 'Must start with 09 and be exactly 11 digits';
            profileContact.classList.add('border-red-400');
            profileContact.classList.remove('border-gray-300');
            return false;
        }
        contactError.textContent = '';
        profileContact.classList.remove('border-red-400');
        profileContact.classList.add('border-gray-300');
        return true;
    }
    
    // Block non-digit input for contact number
    profileContact.addEventListener('input', function(e) {
        // Remove any non-digit characters
        let value = this.value.replace(/\D/g, '');
        // Limit to 11 digits
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        this.value = value;
        // Validate after filtering
        validateContactNumber();
        checkProfileFormChanges();
    });
    
    // Check if profile form has changes
    function checkProfileFormChanges() {
        const nameOriginal = profileName.getAttribute('data-original') || '';
        const contactOriginal = profileContact.getAttribute('data-original') || '';
        
        const nameChanged = profileName.value !== nameOriginal;
        const contactChanged = profileContact.value !== contactOriginal;
        const contactValid = validateContactNumber();
        
        if ((nameChanged || contactChanged) && contactValid) {
            enableButton(saveProfileBtn);
        } else {
            disableButton(saveProfileBtn);
        }
    }
    
    profileName.addEventListener('input', checkProfileFormChanges);
    
    // Form submission validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        if (!validateContactNumber()) {
            e.preventDefault();
            return false;
        }
    });
    
    // ===== EMAIL FORM =====
    
    // Email validation
    function validateEmail() {
        const value = newEmail.value.trim();
        if (value === '') {
            emailError.textContent = '';
            newEmail.classList.remove('border-red-400');
            return false;
        }
        const rx = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (!rx.test(value)) {
            emailError.textContent = 'Enter a valid email (e.g., name@gmail.com)';
            newEmail.classList.add('border-red-400');
            newEmail.classList.remove('border-gray-300');
            return false;
        }
        emailError.textContent = '';
        newEmail.classList.remove('border-red-400');
        newEmail.classList.add('border-gray-300');
        return true;
    }
    
    function checkEmailFormChanges() {
        const emailValid = validateEmail();
        const passwordFilled = emailCurrentPassword.value.trim() !== '';
        
        if (emailValid && passwordFilled) {
            enableButton(updateEmailBtn);
        } else {
            disableButton(updateEmailBtn);
        }
    }
    
    newEmail.addEventListener('input', checkEmailFormChanges);
    emailCurrentPassword.addEventListener('input', checkEmailFormChanges);
    
    // ===== PASSWORD FORM =====
    
    function checkPasswordFormChanges() {
        const currentFilled = pwdCurrent.value.trim() !== '';
        const newFilled = pwdNew.value.trim() !== '';
        const confirmFilled = pwdConfirm.value.trim() !== '';
        
        if (currentFilled && newFilled && confirmFilled) {
            enableButton(updatePasswordBtn);
        } else {
            disableButton(updatePasswordBtn);
        }
    }
    
    pwdCurrent.addEventListener('input', checkPasswordFormChanges);
    pwdNew.addEventListener('input', checkPasswordFormChanges);
    pwdConfirm.addEventListener('input', checkPasswordFormChanges);
});
</script>

@endsection
