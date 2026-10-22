<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') - Vaccination System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <!-- Full Tailwind CSS -->
    <link href="{{ versioned_asset('css/tailwind-full.css') }}" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @yield('additional-styles')
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="absolute top-10 left-10 w-20 h-20 bg-primary-300 rounded-full"></div>
            <div class="absolute top-32 right-20 w-16 h-16 bg-primary-400 rounded-full"></div>
            <div class="absolute bottom-20 left-20 w-24 h-24 bg-primary-200 rounded-full"></div>
            <div class="absolute bottom-40 right-10 w-12 h-12 bg-primary-500 rounded-full"></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-md">
        <!-- Logo/Brand Section -->
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-primary-600 rounded-full flex items-center justify-center shadow-lg">
                <i class="fas fa-baby text-white text-2xl"></i>
            </div>
            <h1 class="mt-4 text-3xl font-bold text-gray-900">
                Vaccination System
            </h1>
            <p class="mt-2 text-gray-600">
                @yield('subtitle', 'Secure access to infant health monitoring')
            </p>
        </div>

        <!-- Card Container -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <!-- Card Header -->
            @hasSection('card-header')
            <div class="bg-primary-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white text-center">
                    @yield('card-header')
                </h2>
            </div>
            @endif

            <!-- Card Body -->
            <div class="px-6 py-8">
                <!-- Flash Messages -->
                @if(session('status'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span class="text-sm">{{ session('status') }}</span>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <span class="text-sm">{{ session('error') }}</span>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                        <div class="text-sm">
                            <p class="font-medium">Please correct the following errors:</p>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Main Content -->
                @yield('content')
            </div>

            <!-- Card Footer -->
            @hasSection('card-footer')
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                @yield('card-footer')
            </div>
            @endif
        </div>

        <!-- Additional Links -->
        @hasSection('auth-links')
        <div class="mt-6 text-center">
            @yield('auth-links')
        </div>
        @endif

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>&copy; {{ date('Y') }} Vaccination Monitoring System</p>
            <p class="mt-1">Designed for infant health monitoring</p>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Password visibility toggle
        function togglePasswordVisibility(inputId, buttonId) {
            const input = document.getElementById(inputId);
            const button = document.getElementById(buttonId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash text-gray-400';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye text-gray-400';
            }
        }

        // Form validation helpers
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return true;
            
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-300');
                    field.classList.remove('border-gray-300');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-300');
                    field.classList.add('border-gray-300');
                }
            });
            
            return isValid;
        }

        // Auto-hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s ease';
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        });

        // Loading state for form submissions
        function setLoadingState(buttonId, isLoading = true) {
            const button = document.getElementById(buttonId);
            if (!button) return;
            
            if (isLoading) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
                button.classList.add('opacity-75');
            } else {
                button.disabled = false;
                button.classList.remove('opacity-75');
            }
        }
    </script>

    @yield('additional-scripts')
</body>
</html>
