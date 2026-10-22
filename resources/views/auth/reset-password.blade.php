<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    
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
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Reset Password</h2>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="reset-password-form" method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email Field (Read-only) -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <div class="relative">
                    <input type="email" name="email" id="email" value="{{ old('email', request()->email) }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                           readonly>
                </div>
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <!-- New Password Field with Eye Icon -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                    <i class="fas fa-eye eye-icon" id="toggle-password"></i>
                </div>
                @error('password')
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
                            <i class="fas fa-circle unmet" id="special-unmet"></i> At least 1 special character (@#$%^&*()_+-=[]{}|;:,.)</li>
                    </ul>
                </div>
            </div>

            <!-- Confirm Password Field with Eye Icon -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                           required>
                    <i class="fas fa-eye eye-icon" id="toggle-confirm-password"></i>
                </div>
                <span class="error" id="confirm-error"></span>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </a>
                <button type="submit" id="reset-password-button"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Reset Password
                </button>
            </div>
        </form>
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

        togglePassword('password', 'toggle-password');
        togglePassword('password_confirmation', 'toggle-confirm-password');

        // Debounce function to optimize performance
        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Real-time password validation
        const form = document.getElementById('reset-password-form');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const confirmError = document.getElementById('confirm-error');
        let confirmStarted = false; // Flag to track if typing started in confirm field

        const validatePassword = () => {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;

            // Validation checks
            const lengthValid = password.length >= 8;
            document.getElementById('length-check').style.display = lengthValid ? 'inline' : 'none';
            document.getElementById('length-unmet').style.display = lengthValid ? 'none' : 'inline';

            const uppercaseValid = /[A-Z]/.test(password);
            document.getElementById('uppercase-check').style.display = uppercaseValid ? 'inline' : 'none';
            document.getElementById('uppercase-unmet').style.display = uppercaseValid ? 'none' : 'inline';

            const lowercaseValid = /[a-z]/.test(password);
            document.getElementById('lowercase-check').style.display = lowercaseValid ? 'inline' : 'none';
            document.getElementById('lowercase-unmet').style.display = lowercaseValid ? 'none' : 'inline';

            const numberValid = /\d/.test(password);
            document.getElementById('number-check').style.display = numberValid ? 'inline' : 'none';
            document.getElementById('number-unmet').style.display = numberValid ? 'none' : 'inline';

            const specialValid = /[@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password);
            document.getElementById('special-check').style.display = specialValid ? 'inline' : 'none';
            document.getElementById('special-unmet').style.display = specialValid ? 'none' : 'inline';

            // Check confirmation match
            if (confirmPassword.length > 0) {
                confirmStarted = true;
            }
            if (confirmStarted && confirmPassword.length > 0) {
                const matchValid = password === confirmPassword;
                confirmError.textContent = matchValid ? '' : 'The new password and confirmation do not match.';
            } else {
                confirmError.textContent = '';
            }
        };

        // Form submission validation
        form.addEventListener('submit', (event) => {
            const password = passwordInput.value;
            const confirmPassword = confirmInput.value;

            const lengthValid = password.length >= 8;
            const uppercaseValid = /[A-Z]/.test(password);
            const lowercaseValid = /[a-z]/.test(password);
            const numberValid = /\d/.test(password);
            const specialValid = /[@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password);
            const matchValid = !confirmStarted || (confirmStarted && confirmPassword === password);

            if (!(lengthValid && uppercaseValid && lowercaseValid && numberValid && specialValid && matchValid)) {
                event.preventDefault();
            }
        });

        // Apply debounced validation
        passwordInput.addEventListener('input', debounce(validatePassword, 100));
        confirmInput.addEventListener('input', debounce(validatePassword, 100));
    </script>
</body>
</html>