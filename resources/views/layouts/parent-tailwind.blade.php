<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Vaccination System</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#7a5bbd">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Infant Vaccination">
    <link rel="apple-touch-icon" href="{{ asset('images/todoligtass.png') }}">
    
    <!-- Full Tailwind CSS -->
    <link href="{{ versioned_asset('css/tailwind-full.css') }}" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @yield('additional-styles')
</head>
<body class="bg-gray-50 font-sans antialiased">
    <!-- Each section defines its own navigation -->
    @yield('navigation')

    <!-- Each section defines its own mobile menu -->
    @yield('mobile-menu')

    <!-- Main Content -->
    <main class="min-h-screen">
        <!-- Page Header -->
        @hasSection('page-header')
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                @yield('page-header')
            </div>
        </div>
        @endif

        <!-- Content Area -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle mr-2 mt-0.5"></i>
                    <div>
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
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-300">
                    © {{ date('Y') }} Vaccination Monitoring System. All rights reserved.
                </div>
                <div class="mt-2 md:mt-0 text-sm text-gray-300">
                    Designed for infant health monitoring
                </div>
            </div>
        </div>
    </footer>

    <!-- Base JavaScript -->
    <script>
        // Common JavaScript functions that all sections can use
        
        // Generic dropdown toggle
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('hidden');
        }

        // Generic mobile menu toggle
        function toggleMobileMenu(mobileMenuId, iconId) {
            const mobileMenu = document.getElementById(mobileMenuId);
            const menuIcon = document.getElementById(iconId);
            
            mobileMenu.classList.toggle('hidden');
            
            if (mobileMenu.classList.contains('hidden')) {
                menuIcon.className = 'fas fa-bars text-xl';
            } else {
                menuIcon.className = 'fas fa-times text-xl';
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            // Each section can add their own dropdown close logic
        });

        // Responsive table handling for mobile
        function makeTablesResponsive() {
            const tables = document.querySelectorAll('table');
            tables.forEach(table => {
                if (!table.classList.contains('responsive-handled')) {
                    table.classList.add('responsive-handled');
                    
                    // Add responsive wrapper
                    const wrapper = document.createElement('div');
                    wrapper.className = 'overflow-x-auto shadow rounded-lg';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                    
                    // Add responsive classes to table
                    table.className = 'min-w-full divide-y divide-gray-200';
                }
            });
        }

        // Initialize responsive tables on page load
        document.addEventListener('DOMContentLoaded', makeTablesResponsive);
    </script>

    <!-- Notification System -->
    <script src="{{ asset('javascript/notifications.js') }}?v={{ time() }}"></script>

    <!-- PWA Service Worker Registration (for app installability) -->
    <script src="{{ asset('javascript/pwa.js') }}?v={{ time() }}"></script>
    
    <!-- Firebase Cloud Messaging (FCM) - NEW -->
    <script src="{{ asset('javascript/fcm.js') }}?v={{ time() }}"></script>

    @yield('additional-scripts')
</body>
</html>
