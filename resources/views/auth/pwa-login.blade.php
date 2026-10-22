<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - IVS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#7a5bbd">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="IVS">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192x192.png') }}">
    
    <link href="{{ versioned_asset('css/tailwind-full.css') }}" rel="stylesheet">
    <style>
        /* Ensure full height on mobile including safe areas */
        html, body {
            height: 100%;
            height: -webkit-fill-available;
        }
        body {
            min-height: 100vh;
            min-height: -webkit-fill-available;
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
            padding-left: env(safe-area-inset-left);
            padding-right: env(safe-area-inset-right);
        }
        /* Disable pull-to-refresh on this page */
        body {
            overscroll-behavior-y: contain;
        }
        /* Custom loading spinner */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            animation: spin 1s linear infinite;
        }
        /* Input focus animation */
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(122, 91, 189, 0.2);
        }
        /* Modal animation */
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-backdrop {
            animation: fadeIn 0.2s ease;
        }
        .modal-content {
            animation: slideUp 0.3s ease;
        }
    </style>
</head>
<body class="bg-primary-600 flex flex-col font-sans antialiased min-h-screen">
    
    <!-- Main Container - Centered vertically and horizontally -->
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-8">
        
        <!-- Logo & App Name -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto mb-5 bg-white rounded-3xl shadow-xl flex items-center justify-center p-2">
                <img src="{{ asset('images/icon-192x192.png') }}" alt="IVS" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight drop-shadow-sm">Infant Vaccination</h1>
            <p class="text-white/80 text-base mt-2 font-medium">Sistema ng Pagbabakuna</p>
        </div>

        <!-- Login Card -->
        <div class="w-full max-w-sm">
            <div class="bg-white rounded-3xl shadow-2xl p-7 space-y-1">
                
                <!-- Error Message -->
                @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-red-700 text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                @if(session('info'))
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-blue-700 text-sm font-medium">{{ session('info') }}</p>
                    </div>
                </div>
                @endif
                
                <form action="{{ route('login.post') }}" method="POST" id="pwaLoginForm" autocomplete="off">
                    @csrf
                    <!-- Hidden field to indicate PWA mode -->
                    <input type="hidden" name="pwa_mode" value="1">
                    
                    <!-- Username -->
                    <div class="mb-5">
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value=""
                            autocomplete="off"
                            autocapitalize="none"
                            required
                            class="input-field w-full px-4 py-3.5 rounded-xl border-2 border-gray-200 bg-gray-50 focus:bg-white focus:border-primary-500 outline-none transition-all duration-200 text-gray-900 text-base placeholder-gray-400"
                            placeholder="Enter username"
                        >
                        @error('username')
                        <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                autocomplete="off"
                                required
                                class="input-field w-full px-4 py-3.5 pr-14 rounded-xl border-2 border-gray-200 bg-gray-50 focus:bg-white focus:border-primary-500 outline-none transition-all duration-200 text-gray-900 text-base placeholder-gray-400"
                                placeholder="Enter password"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword()"
                                class="absolute right-0 top-0 h-full px-4 flex items-center justify-center text-gray-400 hover:text-gray-600 active:text-primary-600 transition-colors"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="eyeIconHidden" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                                <svg id="eyeIconVisible" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                        <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="flex justify-end mb-6">
                        <a href="{{ route('password.request') }}" class="text-sm text-primary-600 hover:text-primary-700 font-semibold">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        id="loginBtn"
                        class="w-full bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-primary-500/30 transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-2 text-base"
                    >
                        <span id="loginBtnText">Sign In</span>
                        <svg id="loginSpinner" class="hidden w-5 h-5 spinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Footer Links -->
            <div class="mt-6 text-center">
                <p class="text-white/70 text-sm">
                    By signing in, you agree to our 
                    <button onclick="openPrivacyPolicy()" class="text-white font-semibold underline underline-offset-2 hover:text-white/90">Privacy Policy</button>
                </p>
            </div>
        </div>
    </div>

    <!-- Version Info -->
    <div class="text-center pb-6 text-white/50 text-sm font-medium">
        Vaccination Monitoring System v1.0
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacyModal" class="hidden fixed inset-0 z-50 modal-backdrop" style="background: rgba(0,0,0,0.6);">
        <div class="min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div class="modal-content bg-white w-full sm:max-w-lg sm:rounded-2xl rounded-t-3xl max-h-[90vh] flex flex-col shadow-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
                    <h3 class="text-lg font-bold text-gray-900">Patakaran sa Privacy ng Data</h3>
                    <button onclick="closePrivacyPolicy()" class="p-2 -mr-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto px-5 py-5">
                    <div class="space-y-5 text-gray-700 text-sm leading-relaxed">
                        <!-- Section 1 -->
                        <div>
                            <h4 class="font-bold text-gray-900 mb-2">1. Layunin ng Pagkolekta ng Impormasyon</h4>
                            <p class="mb-2">Ang iyong personal na impormasyon at ang impormasyon ng iyong anak ay kinukulekta at ginagamit para sa mga sumusunod na layunin:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>Pagtala ng kasaysayan ng pagbabakuna ng iyong anak</li>
                                <li>Pag-iskedyul ng mga susunod na bakuna</li>
                                <li>Pagsubaybay at pag-uulat ng pampublikong kalusugan</li>
                                <li>Pakikipag-ugnayan sa inyo kaugnay ng kalusugan ng iyong anak</li>
                            </ul>
                        </div>

                        <!-- Section 2 -->
                        <div>
                            <h4 class="font-bold text-gray-900 mb-2">2. Proteksyon ng Iyong Datos</h4>
                            <p class="mb-2">Ang iyong datos ay ligtas na itinatago sa aming sistema at may mga sumusunod na proteksyon:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>Limitadong access lamang para sa mga awtorisadong kawani ng kalusugan</li>
                                <li>Secure storage gamit ang encryption technology</li>
                                <li>Regular security audits at monitoring</li>
                                <li>Pagsunod sa Data Privacy Act of 2012</li>
                            </ul>
                        </div>

                        <!-- Section 3 -->
                        <div>
                            <h4 class="font-bold text-gray-900 mb-2">3. Pagbabahagi ng Impormasyon</h4>
                            <p class="mb-2">Ang iyong impormasyon ay maaaring ibahagi sa mga sumusunod na ahensya para sa layunin ng pagsubaybay ng pampublikong kalusugan:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>Department of Health (DOH)</li>
                                <li>Local Government Unit (LGU)</li>
                                <li>Mga kaugnay na ahensya ng kalusugan</li>
                            </ul>
                            <p class="mt-2">Hindi namin ibabahagi ang iyong impormasyon sa ibang partido nang walang iyong pahintulot, maliban kung kinakailangan ng batas.</p>
                        </div>

                        <!-- Section 4 -->
                        <div>
                            <h4 class="font-bold text-gray-900 mb-2">4. Ang Iyong mga Karapatan</h4>
                            <p class="mb-2">Sa ilalim ng Data Privacy Act of 2012, mayroon kang mga sumusunod na karapatan:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li><strong>Karapatan na malaman:</strong> Maaari mong tingnan ang iyong personal na impormasyon</li>
                                <li><strong>Karapatan na itama:</strong> Maaari mong hilingin na itama ang anumang hindi tamang impormasyon</li>
                                <li><strong>Karapatan na magtanong:</strong> Maaari kang magtanong tungkol sa kung paano ginagamit ang iyong datos</li>
                                <li><strong>Karapatan na magreklamo:</strong> Maaari kang magreklamo kung may alalahanin ka sa privacy</li>
                            </ul>
                        </div>

                        <!-- Section 5 -->
                        <div>
                            <h4 class="font-bold text-gray-900 mb-2">5. Makipag-ugnayan sa Amin</h4>
                            <p class="mb-2">Kung mayroon kang mga katanungan o alalahanin kaugnay ng iyong personal na impormasyon:</p>
                            <div class="bg-gray-50 rounded-xl p-4 mt-2">
                                <p class="font-semibold text-gray-800">RHU Data Protection Officer</p>
                                <p class="text-gray-600 mt-1">Rural Health Unit - Calauan, Laguna</p>
                                <p class="text-gray-600">Email: rhucalauan@yahoo.com</p>
                                <p class="text-gray-600">Telepono: (049) 543-6638</p>
                            </div>
                        </div>

                        <!-- Version -->
                        <div class="pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-500">
                                <strong>Version:</strong> 1.0 | <strong>Petsa:</strong> December 2025
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="p-4 border-t border-gray-100 flex-shrink-0">
                    <button onclick="closePrivacyPolicy()" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white rounded-xl font-bold text-base transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const input = document.getElementById('password');
            const iconHidden = document.getElementById('eyeIconHidden');
            const iconVisible = document.getElementById('eyeIconVisible');
            
            if (input.type === 'password') {
                input.type = 'text';
                iconHidden.classList.add('hidden');
                iconVisible.classList.remove('hidden');
            } else {
                input.type = 'password';
                iconHidden.classList.remove('hidden');
                iconVisible.classList.add('hidden');
            }
        }

        // Privacy modal
        function openPrivacyPolicy() {
            document.getElementById('privacyModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closePrivacyPolicy() {
            document.getElementById('privacyModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close modal on backdrop click
        document.getElementById('privacyModal')?.addEventListener('click', function(e) {
            if (e.target === this || e.target === this.firstElementChild) {
                closePrivacyPolicy();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePrivacyPolicy();
            }
        });

        // Show loading on form submit
        document.getElementById('pwaLoginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            const text = document.getElementById('loginBtnText');
            const spinner = document.getElementById('loginSpinner');
            
            btn.disabled = true;
            btn.classList.add('opacity-75');
            text.textContent = 'Signing in...';
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>
