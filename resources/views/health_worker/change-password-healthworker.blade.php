<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error {
            color: #e3342f;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #666;
        }
        .password-requirements li {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
        }
        .password-requirements .check {
            color: #10b981;
            margin-right: 0.5rem;
        }
        .password-requirements .unmet {
            color: #d1d5db;
            margin-right: 0.5rem;
        }
        .relative {
            position: relative;
        }
        .eye-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 py-8 max-w-md">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Change Password</h2>
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            <form id="change-password-form" action="{{ route('health_worker.update-password') }}" method="POST" class="space-y-6">
                @csrf
                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <div class="relative">
                        <input type="password" name="current_password" id="current_password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <i class="fas fa-eye eye-icon" id="toggle-current-password"></i>
                    </div>
                    @error('current_password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
                <!-- New Password -->
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <div class="relative">
                        <input type="password" name="new_password" id="new_password" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <i class="fas fa-eye eye-icon" id="toggle-new-password"></i>
                    </div>
                    @error('new_password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    <!-- Password Requirements -->
                    <div class="password-requirements">
                        <p class="font-semibold">Password must contain:</p>
                        <ul>
                            <li><i class="fas fa-check check" id="length-check" style="display: none;"></i>
                                <i class="fas fa-circle unmet" id="length-unmet"></i> At least 8 characters</li>
                            <li><i class="fas fa-check check" id="uppercase-check" style="display: none;"></i>
                                <i class="fas fa-circle unmet" id="uppercase-unmet"></i> At least 1 uppercase letter</li>
                            <li><i class="fas fa-check check" id="lowercase-check" style="display: none;"></i>
                                <i class="fas fa-circle unmet" id="lowercase-unmet"></i> At least 1 lowercase letter</li>
                            <li><i class="fas fa-check check" id="number-check" style="display: none;"></i>
                                <i class="fas fa-circle unmet" id="number-unmet"></i> At least 1 number</li>
                            <li><i class="fas fa-check check" id="special-check" style="display: none;"></i>
                                <i class="fas fa-circle unmet" id="special-unmet"></i> At least 1 special character (@#$%^&*()_+-=[]{}|;:,.<>?)</li>
                        </ul>
                    </div>
                </div>
                <!-- Confirm New Password -->
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <i class="fas fa-eye eye-icon" id="toggle-confirm-password"></i>
                    </div>
                    <span class="error" id="confirm-error"></span>
                </div>
                <!-- Form Actions -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('health_worker.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Cancel
                    </a>
                    <button type="submit" id="change-password-button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Toggle password visibility
        const togglePassword = (inputId, toggleId) => {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);
            toggle.addEventListener('click', () => {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                toggle.classList.toggle('fa-eye');
                toggle.classList.toggle('fa-eye-slash');
            });
        };
        togglePassword('current_password', 'toggle-current-password');
        togglePassword('new_password', 'toggle-new-password');
        togglePassword('new_password_confirmation', 'toggle-confirm-password');
        // Debounce function to optimize performance
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        // Validate password requirements and mismatch in real-time
        const form = document.getElementById('change-password-form');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('new_password_confirmation');
        const confirmError = document.getElementById('confirm-error');
        let confirmStarted = false; // Flag to track if typing started in confirm field
        const validatePassword = () => {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            // At least 8 characters
            const lengthCheck = password.length >= 8;
            document.getElementById('length-check').style.display = lengthCheck ? 'inline' : 'none';
            document.getElementById('length-unmet').style.display = lengthCheck ? 'none' : 'inline';
            // At least 1 uppercase letter
            const uppercaseCheck = /[A-Z]/.test(password);
            document.getElementById('uppercase-check').style.display = uppercaseCheck ? 'inline' : 'none';
            document.getElementById('uppercase-unmet').style.display = uppercaseCheck ? 'none' : 'inline';
            // At least 1 lowercase letter
            const lowercaseCheck = /[a-z]/.test(password);
            document.getElementById('lowercase-check').style.display = lowercaseCheck ? 'inline' : 'none';
            document.getElementById('lowercase-unmet').style.display = lowercaseCheck ? 'none' : 'inline';
            // At least 1 number
            const numberCheck = /\d/.test(password);
            document.getElementById('number-check').style.display = numberCheck ? 'inline' : 'none';
            document.getElementById('number-unmet').style.display = numberCheck ? 'none' : 'inline';
            // At least 1 special character
            const specialCheck = /[@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password);
            document.getElementById('special-check').style.display = specialCheck ? 'inline' : 'none';
            document.getElementById('special-unmet').style.display = specialCheck ? 'none' : 'inline';
            // Check if passwords match only after typing starts in confirm field
            if (confirmPassword.length > 0) {
                confirmStarted = true;
            }
            if (confirmStarted && confirmPassword.length > 0) {
                const matchCheck = password === confirmPassword;
                confirmError.textContent = matchCheck ? '' : 'The new password and confirmation do not match.';
            } else {
                confirmError.textContent = '';
            }
        };
        // Form submission validation
        form.addEventListener('submit', (event) => {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const lengthCheck = password.length >= 8;
            const uppercaseCheck = /[A-Z]/.test(password);
            const lowercaseCheck = /[a-z]/.test(password);
            const numberCheck = /\d/.test(password);
            const specialCheck = /[@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password);
            const matchCheck = !confirmStarted || (confirmStarted && confirmPassword === password);
            if (!(lengthCheck && uppercaseCheck && lowercaseCheck && numberCheck && specialCheck && matchCheck)) {
                event.preventDefault();
            }
        });
        // Apply debounced validation with a 100ms delay
        newPasswordInput.addEventListener('input', debounce(validatePassword, 100));
        confirmPasswordInput.addEventListener('input', debounce(validatePassword, 100));
    </script>
</body>
</html>
