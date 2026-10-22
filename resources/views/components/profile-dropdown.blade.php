<!-- FontAwesome Offline -->
<link rel="stylesheet" href="{{ asset('css/fontawesome-all.min.css') }}">
<!-- Tailwind Offline -->
<link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">

<div class="relative inline-block ml-auto">
    <!-- Profile Button -->
    <div class="flex items-center cursor-pointer bg-transparent border-none px-3 py-2 rounded-full hover:bg-gray-100 transition-colors duration-200" id="profileToggle">
        <img src="{{ asset('images/user (1).png') }}" alt="Profile" class="w-9 h-9 rounded-full object-cover shadow-sm border border-gray-200">
        <svg class="w-5 h-5 ml-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </div>
    
    <!-- Profile Dropdown -->
    <div class="absolute right-0 top-12 w-64 bg-white rounded-lg shadow-lg z-30 overflow-hidden hidden transition-all duration-200" id="profileDropdown">
        <div class="px-4 py-3 border-b border-gray-100 text-left">
            <h6 class="m-0 text-base font-semibold text-gray-800">{{ Auth::user()->username }}</h6>
            <p class="mt-1 mb-0 text-sm text-gray-500">{{ Auth::user()->email }}</p>
        </div>
        <ul class="py-1">
            <li>
                <a href="#" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition" id="changePasswordBtn">
                    <i class="fas fa-key mr-2 text-purple-600"></i> Change Password
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Password Modal and Backdrop -->
<div class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" id="passwordBackdrop"></div>
<div class="fixed inset-0 items-center justify-center z-50 hidden" id="passwordModal">
    <div class="w-full max-w-md bg-white rounded-xl shadow-2xl mx-auto my-20 sm:my-auto max-h-[90vh] overflow-y-auto">
        <div class="bg-purple-100 p-6 rounded-t-xl border-b border-purple-200">
            <h2 class="text-2xl font-bold text-center text-purple-700">Change Password</h2>
        </div>
        <div class="p-8">
        <form class="space-y-6" id="passwordForm" action="{{ route('health_worker.update-password') }}" method="POST">
            @csrf
            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <div class="relative mt-1">
                    <input type="password" id="current_password" name="current_password" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm pr-10">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye-slash cursor-pointer text-gray-500 hover:text-gray-700 transition-colors" id="toggle-current-password"></i>
                    </div>
                </div>
            </div>
            
            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <div class="relative mt-1">
                    <input type="password" id="new_password" name="new_password" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm pr-10">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye-slash cursor-pointer text-gray-500 hover:text-gray-700 transition-colors" id="toggle-new-password"></i>
                    </div>
                </div>
                <div class="mt-3 text-sm text-gray-600 bg-gray-50 p-4 rounded-md border border-gray-100">
                    <p class="font-semibold text-gray-700 mb-2">Password must contain:</p>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <div id="length-circle" class="flex items-center justify-center w-5 h-5 rounded-full border-2 border-gray-300 mr-2">
                                <i class="fas fa-check text-xs text-green-500 hidden" id="length-check"></i>
                            </div>
                            <span>At least 8 characters</span>
                        </li>
                        <li class="flex items-center">
                            <div id="uppercase-circle" class="flex items-center justify-center w-5 h-5 rounded-full border-2 border-gray-300 mr-2">
                                <i class="fas fa-check text-xs text-green-500 hidden" id="uppercase-check"></i>
                            </div>
                            <span>At least 1 uppercase letter</span>
                        </li>
                        <li class="flex items-center">
                            <div id="lowercase-circle" class="flex items-center justify-center w-5 h-5 rounded-full border-2 border-gray-300 mr-2">
                                <i class="fas fa-check text-xs text-green-500 hidden" id="lowercase-check"></i>
                            </div>
                            <span>At least 1 lowercase letter</span>
                        </li>
                        <li class="flex items-center">
                            <div id="number-circle" class="flex items-center justify-center w-5 h-5 rounded-full border-2 border-gray-300 mr-2">
                                <i class="fas fa-check text-xs text-green-500 hidden" id="number-check"></i>
                            </div>
                            <span>At least 1 number</span>
                        </li>
                        <li class="flex items-center">
                            <div id="special-circle" class="flex items-center justify-center w-5 h-5 rounded-full border-2 border-gray-300 mr-2">
                                <i class="fas fa-check text-xs text-green-500 hidden" id="special-check"></i>
                            </div>
                            <span>At least 1 special character (@#$%^&*()_+-=[]{}|;:,.&lt;&gt;?)</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <div class="relative mt-1">
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm pr-10">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye-slash cursor-pointer text-gray-500 hover:text-gray-700 transition-colors" id="toggle-confirm-password"></i>
                    </div>
                </div>
                <span class="text-xs text-red-500 mt-1 block" id="confirm-error"></span>
            </div>
            
            <div class="flex justify-end gap-4 mt-8">
                <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500" id="cancelPasswordBtn">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">Update Password</button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Profile dropdown toggle
        const profileToggle = document.getElementById('profileToggle');
        const profileDropdown = document.getElementById('profileDropdown');
        
        // Make sure dropdown is initially hidden
        profileDropdown.classList.add('hidden');
        
        profileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking elsewhere
        document.addEventListener('click', function() {
            profileDropdown.classList.add('hidden');
        });
        
        // Change password modal
        const changePasswordBtn = document.getElementById('changePasswordBtn');
        const passwordModal = document.getElementById('passwordModal');
        const passwordBackdrop = document.getElementById('passwordBackdrop');
        const cancelPasswordBtn = document.getElementById('cancelPasswordBtn');
        const passwordForm = document.getElementById('passwordForm');
        
        // Make sure modals are hidden on page load
        passwordModal.classList.add('hidden');
        passwordModal.classList.remove('flex');
        passwordBackdrop.classList.add('hidden');

        function showModal() {
            passwordBackdrop.classList.remove('hidden');
            passwordModal.classList.remove('hidden');
            passwordModal.classList.add('flex');
            
            // Reset form
            passwordForm.reset();
            
            // Reset validation UI
            document.getElementById('length-check').classList.add('hidden');
            document.getElementById('uppercase-check').classList.add('hidden');
            document.getElementById('lowercase-check').classList.add('hidden');
            document.getElementById('number-check').classList.add('hidden');
            document.getElementById('special-check').classList.add('hidden');
            
            document.getElementById('length-circle').classList.add('border-gray-300');
            document.getElementById('length-circle').classList.remove('border-green-500', 'bg-green-50');
            document.getElementById('uppercase-circle').classList.add('border-gray-300');
            document.getElementById('uppercase-circle').classList.remove('border-green-500', 'bg-green-50');
            document.getElementById('lowercase-circle').classList.add('border-gray-300');
            document.getElementById('lowercase-circle').classList.remove('border-green-500', 'bg-green-50');
            document.getElementById('number-circle').classList.add('border-gray-300');
            document.getElementById('number-circle').classList.remove('border-green-500', 'bg-green-50');
            document.getElementById('special-circle').classList.add('border-gray-300');
            document.getElementById('special-circle').classList.remove('border-green-500', 'bg-green-50');
            
            if (confirmError) {
                confirmError.textContent = '';
            }
        }
        
        function hideModal() {
            passwordBackdrop.classList.add('hidden');
            passwordModal.classList.add('hidden');
            passwordModal.classList.remove('flex');
        }
        
        changePasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showModal();
            profileDropdown.classList.add('hidden');
        });
        
        cancelPasswordBtn.addEventListener('click', hideModal);
        passwordBackdrop.addEventListener('click', hideModal);
        
        // Password validation
        if (passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('new_password_confirmation').value;
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New password and confirmation password do not match.');
                }
            });
        }
        
        // Toggle password visibility (Tailwind style)
        function togglePassword(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);
            
            if (!input || !toggle) {
                console.error(`Could not find elements: input=${inputId}, toggle=${toggleId}`);
                return;
            }
            
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                toggle.classList.toggle('fa-eye-slash');
                toggle.classList.toggle('fa-eye');
            });
        }
        
        // Initialize password toggle functionality
        togglePassword('current_password', 'toggle-current-password');
        togglePassword('new_password', 'toggle-new-password');
        togglePassword('new_password_confirmation', 'toggle-confirm-password');

        // Password requirements validation for strong password (Tailwind style)
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('new_password_confirmation');
        const confirmError = document.getElementById('confirm-error');
        
        if (!newPasswordInput || !confirmPasswordInput) {
            return;
        }
        
        let confirmStarted = false;
        
        function validatePassword() {
            const password = newPasswordInput?.value || '';
            const confirmPassword = confirmPasswordInput?.value || '';
            
            // At least 8 characters
            const lengthCheck = password.length >= 8;
            const lengthCheckIcon = document.getElementById('length-check');
            const lengthCircle = document.getElementById('length-circle');
            
            if (lengthCheckIcon && lengthCircle) {
                if (lengthCheck) {
                    lengthCheckIcon.classList.remove('hidden');
                    lengthCircle.classList.remove('border-gray-300');
                    lengthCircle.classList.add('border-green-500', 'bg-green-50');
                } else {
                    lengthCheckIcon.classList.add('hidden');
                    lengthCircle.classList.add('border-gray-300');
                    lengthCircle.classList.remove('border-green-500', 'bg-green-50');
                }
            }
            
            // At least 1 uppercase letter
            const uppercaseCheck = /[A-Z]/.test(password);
            const uppercaseCheckIcon = document.getElementById('uppercase-check');
            const uppercaseCircle = document.getElementById('uppercase-circle');
            
            if (uppercaseCheckIcon && uppercaseCircle) {
                if (uppercaseCheck) {
                    uppercaseCheckIcon.classList.remove('hidden');
                    uppercaseCircle.classList.remove('border-gray-300');
                    uppercaseCircle.classList.add('border-green-500', 'bg-green-50');
                } else {
                    uppercaseCheckIcon.classList.add('hidden');
                    uppercaseCircle.classList.add('border-gray-300');
                    uppercaseCircle.classList.remove('border-green-500', 'bg-green-50');
                }
            }
            
            // At least 1 lowercase letter
            const lowercaseCheck = /[a-z]/.test(password);
            const lowercaseCheckIcon = document.getElementById('lowercase-check');
            const lowercaseCircle = document.getElementById('lowercase-circle');
            
            if (lowercaseCheckIcon && lowercaseCircle) {
                if (lowercaseCheck) {
                    lowercaseCheckIcon.classList.remove('hidden');
                    lowercaseCircle.classList.remove('border-gray-300');
                    lowercaseCircle.classList.add('border-green-500', 'bg-green-50');
                } else {
                    lowercaseCheckIcon.classList.add('hidden');
                    lowercaseCircle.classList.add('border-gray-300');
                    lowercaseCircle.classList.remove('border-green-500', 'bg-green-50');
                }
            }
            
            // At least 1 number
            const numberCheck = /\d/.test(password);
            const numberCheckIcon = document.getElementById('number-check');
            const numberCircle = document.getElementById('number-circle');
            
            if (numberCheckIcon && numberCircle) {
                if (numberCheck) {
                    numberCheckIcon.classList.remove('hidden');
                    numberCircle.classList.remove('border-gray-300');
                    numberCircle.classList.add('border-green-500', 'bg-green-50');
                } else {
                    numberCheckIcon.classList.add('hidden');
                    numberCircle.classList.add('border-gray-300');
                    numberCircle.classList.remove('border-green-500', 'bg-green-50');
                }
            }
            
            // At least 1 special character
            const specialCheck = /[@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password);
            const specialCheckIcon = document.getElementById('special-check');
            const specialCircle = document.getElementById('special-circle');
            
            if (specialCheckIcon && specialCircle) {
                if (specialCheck) {
                    specialCheckIcon.classList.remove('hidden');
                    specialCircle.classList.remove('border-gray-300');
                    specialCircle.classList.add('border-green-500', 'bg-green-50');
                } else {
                    specialCheckIcon.classList.add('hidden');
                    specialCircle.classList.add('border-gray-300');
                    specialCircle.classList.remove('border-green-500', 'bg-green-50');
                }
            }
            
            // Password match feedback
            if (confirmPassword.length > 0) {
                confirmStarted = true;
            }
            
            if (confirmError) {
                if (confirmStarted && confirmPassword.length > 0) {
                    const matchCheck = password === confirmPassword;
                    confirmError.textContent = matchCheck ? '' : 'The new password and confirmation do not match.';
                } else {
                    confirmError.textContent = '';
                }
            }
        }
        
        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        
        if (newPasswordInput && confirmPasswordInput) {
            newPasswordInput.addEventListener('input', debounce(validatePassword, 100));
            confirmPasswordInput.addEventListener('input', debounce(validatePassword, 100));
        }
    });
</script>
