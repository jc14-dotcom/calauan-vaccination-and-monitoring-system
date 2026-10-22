{{-- filepath: c:\laragon\www\infantsSystem\resources\views\auth\login_tailwind.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
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
    <meta name="apple-mobile-web-app-title" content="Infant Vaccination">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192x192.png') }}">
    
    <link href="{{ versioned_asset('css/tailwind-full.css') }}" rel="stylesheet">
    <style>
        /* Custom checkbox styles for Remember Me */
        input#remember:checked + div {
            background-color: rgb(122, 91, 189); /* primary-600 */
            border-color: rgb(122, 91, 189);
        }
        input#remember:checked + div svg {
            opacity: 1;
        }
        input#remember:focus + div {
            box-shadow: 0 0 0 4px rgba(122, 91, 189, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-primary-600/95 bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.12),rgba(122,91,189,0.95))] font-sans antialiased selection:bg-white/30 selection:text-primary-700">
    <!-- Back -->
    <a href="{{ url('/') }}" class="absolute top-4 left-4 group">
        <span class="inline-flex items-center justify-center w-11 h-11 rounded-full bg-white shadow-md ring-1 ring-black/5 transition group-hover:scale-105 group-active:scale-95">
            <!-- Arrow Left -->
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </span>
    </a>

    <!-- Card -->
    <div class="w-[940px] max-w-[95%] flex flex-col md:flex-row rounded-2xl shadow-2xl bg-white/95 backdrop-blur-md ring-1 ring-black/5 overflow-hidden">
        <!-- Visual / Brand Panel -->
        <div class="relative hidden md:flex md:w-2/5 bg-gradient-to-br from-primary-700 via-primary-600 to-primary-500">
            <img src="{{ asset('images/systembg.jpg') }}"
                 alt="Vaccination"
                 class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-90">
            <div class="relative z-10 p-8 flex flex-col justify-between text-white">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight drop-shadow-sm">Welcome Back</h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/90">Sign in to continue managing vaccination records and monitoring infant health.</p>
                </div>
                <div class="mt-10 space-y-3 text-xs text-white/80">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/15">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.657 0 3-1.79 3-4s-1.343-4-3-4-3 1.79-3 4 1.343 4 3 4z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21c0-3.313 3.582-6 8-6s8 2.687 8 6"/>
                            </svg>
                        </span>
                        <span>Secure role‑based access</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/15">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/>
                            </svg>
                        </span>
                        <span>Centralized records</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/15">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l3.5 3.5"/>
                            </svg>
                        </span>
                        <span>Real‑time status tracking</span>
                    </div>
                </div>
            </div>
            <div class="absolute -right-24 -bottom-24 w-72 h-72 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Form Panel -->
        <div class="flex-1 px-8 py-10 md:px-14 md:py-12 flex flex-col">
            <!-- Mobile heading -->
            <div class="md:hidden mb-8">
                <h1 class="text-2xl font-bold text-primary-700">Sign In</h1>
                <p class="text-sm mt-1 text-primary-600/80">Access your dashboard.</p>
            </div>

            {{-- Login form - simplified, no "continue as..." feature --}}
            <form id="loginForm" action="{{ route('login.post') }}" method="POST" autocomplete="off" class="flex flex-col gap-7 flex-1 justify-center">
                @csrf

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-primary-700 mb-2">Username</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary-500 pointer-events-none">
                            <!-- User Icon -->
                            <svg class="w-5 h-5 opacity-70 group-focus-within:opacity-100 transition" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 12c2.761 0 5-2.462 5-5.5S14.761 1 12 1 7 3.462 7 6.5 9.239 12 12 12z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 21c0-3.313 3.582-6 8-6s8 2.687 8 6"/>
                            </svg>
                        </span>
                        <input id="username" name="username" type="text" autocomplete="off" required
                               value=""
                               class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-300 bg-white/70 backdrop-blur-sm shadow-sm focus:border-primary-500 focus:ring-4 focus:ring-primary-300/50 outline-none transition placeholder:text-gray-400 lowercase"
                               placeholder="Enter your username"
                               style="text-transform: lowercase;"
                               oninput="this.value = this.value.toLowerCase()">
                    </div>
                    @error('username')
                        <p class="mt-2 text-xs font-medium text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-primary-700 mb-2">Password</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary-500 pointer-events-none">
                            <!-- Lock Icon -->
                            <svg class="w-5 h-5 opacity-70 group-focus-within:opacity-100 transition" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="4" y="11" width="16" height="11" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0v4"/>
                            </svg>
                        </span>
                        <input id="password" name="password" type="password" autocomplete="off" required
                               class="w-full pl-11 pr-12 py-3 rounded-lg border border-gray-300 bg-white/70 backdrop-blur-sm shadow-sm focus:border-primary-500 focus:ring-4 focus:ring-primary-300/50 outline-none transition placeholder:text-gray-400"
                               placeholder="••••••••">
                        <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-primary-600 focus:outline-none"
                                aria-label="Toggle password">
                            <!-- Eye (dynamic) -->
                            <svg id="eyeClosed" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6A1.5 1.5 0 0112 10.5a2 2 0 012 2c0 .4-.1.8-.3 1.1M9.5 5.5a9.5 9.5 0 017 1.3c1.6.9 3 2.3 4 4-1 1.8-2.4 3.2-4 4a9.5 9.5 0 01-4.4 1.2M6.2 6.2C4.7 7.1 3.3 8.5 2.2 10.5c1 1.8 2.4 3.2 4 4a9.5 9.5 0 004.2 1.2"/>
                            </svg>
                            <svg id="eyeOpen" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-xs font-medium text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded">{{ $message }}</p>
                    @enderror
                    <p id="credentialsError" class="hidden mt-3 text-xs font-medium text-red-700 bg-red-100 border border-red-300 px-3 py-2 rounded"></p>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="relative flex items-center gap-3 cursor-pointer group select-none">
                        <input type="checkbox" name="remember" id="remember" class="peer sr-only">
                        <!-- Custom checkbox box -->
                        <div class="flex items-center justify-center w-5 h-5 rounded border-2 border-gray-300 bg-white/80 
                                    transition-all duration-200
                                    peer-checked:bg-primary-600 peer-checked:border-primary-600
                                    peer-focus:ring-4 peer-focus:ring-primary-300/50
                                    group-hover:border-primary-400 group-hover:shadow-sm">
                            <!-- Checkmark icon -->
                            <svg class="w-3 h-3 text-white opacity-0 transition-opacity duration-200" 
                                 fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-600 group-hover:text-primary-700 transition-colors duration-200">
                            Remember me
                        </span>
                    </label>
                    <button type="button" id="openForgot"
                            class="text-sm font-medium text-primary-600 hover:text-primary-700 focus:outline-none focus:underline transition-colors duration-200">
                        Forgot password?
                    </button>
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white shadow-sm ring-1 ring-primary-600/50 focus:outline-none focus:ring-4 focus:ring-primary-300 transition hover:bg-primary-700 active:scale-[.98]">
                    <span class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-120%] group-hover:translate-x-[120%] transition duration-700"></span>
                    {{-- <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7"/>
                    </svg> --}}
                    <span>Login</span>
                </button>

                <!-- Privacy Policy Link -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-center text-gray-600 leading-relaxed">
                        Sa paggamit ng system, sumasang-ayon ka sa aming
                        <a href="#" id="viewPrivacyLink" class="text-primary-600 hover:text-primary-700 font-medium underline">
                            Patakaran sa Privacy ng Data
                        </a>
                        at Terms of Service.
                    </p>
                </div>
            </form>

            @if(session('error'))
                <script>window.addEventListener('DOMContentLoaded',()=>showCredErr(@json(session('error'))));</script>
            @endif
            {{-- Success message removed per user request --}}
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacyPolicyModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" id="privacyModalBackdrop"></div>
        <div class="relative w-full max-w-3xl bg-white rounded-2xl shadow-xl ring-1 ring-black/10 overflow-hidden animate-[fadeIn_.25s_ease]">
            <div class="bg-primary-600 text-white px-6 py-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold">Patakaran sa Privacy ng Data</h2>
                <button id="closePrivacyModal" class="text-white/80 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 80px);">
                <div class="space-y-5 text-gray-700 text-sm leading-relaxed">
                    <!-- Section 1: Purpose -->
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">1. Layunin ng Pagkolekta ng Impormasyon</h4>
                        <p>
                            Ang iyong personal na impormasyon at ang impormasyon ng iyong anak ay kinukulekta at 
                            ginagamit para sa mga sumusunod na layunin:
                        </p>
                        <ul class="list-disc list-inside ml-4 mt-2 space-y-1">
                            <li>Pagtala ng kasaysayan ng pagbabakuna ng iyong anak</li>
                            <li>Pag-iskedyul ng mga susunod na bakuna</li>
                            <li>Pagsubaybay at pag-uulat ng pampublikong kalusugan</li>
                            <li>Pakikipag-ugnayan sa inyo kaugnay ng kalusugan ng iyong anak</li>
                        </ul>
                    </div>

                    <!-- Section 2: Data Security -->
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">2. Proteksyon ng Iyong Datos</h4>
                        <p>
                            Ang iyong datos ay ligtas na itinatago sa aming sistema at may mga sumusunod na proteksyon:
                        </p>
                        <ul class="list-disc list-inside ml-4 mt-2 space-y-1">
                            <li>Limitadong access lamang para sa mga awtorisadong kawani ng kalusugan</li>
                            <li>Secure storage gamit ang encryption technology</li>
                            <li>Regular security audits at monitoring</li>
                            <li>Pagsunod sa Data Privacy Act of 2012</li>
                        </ul>
                    </div>

                    <!-- Section 3: Data Sharing -->
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">3. Pagbabahagi ng Impormasyon</h4>
                        <p>
                            Ang iyong impormasyon ay maaaring ibahagi sa mga sumusunod na ahensya para sa 
                            layunin ng pagsubaybay ng pampublikong kalusugan:
                        </p>
                        <ul class="list-disc list-inside ml-4 mt-2 space-y-1">
                            <li>Department of Health (DOH)</li>
                            <li>Local Government Unit (LGU)</li>
                            <li>Mga kaugnay na ahensya ng kalusugan</li>
                        </ul>
                    </div>

                    <!-- Section 4: Your Rights -->
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">4. Ang Iyong mga Karapatan</h4>
                        <p>
                            Sa ilalim ng Data Privacy Act of 2012, mayroon kang mga sumusunod na karapatan:
                        </p>
                        <ul class="list-disc list-inside ml-4 mt-2 space-y-1">
                            <li><strong>Karapatan na malaman:</strong> Maaari mong tingnan ang iyong personal na impormasyon</li>
                            <li><strong>Karapatan na itama:</strong> Maaari mong hilingin na itama ang hindi tamang impormasyon</li>
                            <li><strong>Karapatan na magtanong:</strong> Maaari kang magtanong tungkol sa kung paano ginagamit ang iyong datos</li>
                            <li><strong>Karapatan na magreklamo:</strong> Maaari kang magreklamo kung may alalahanin ka</li>
                        </ul>
                    </div>

                    <!-- Section 5: Contact -->
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">5. Makipag-ugnayan sa Amin</h4>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-3">
                            <p class="font-semibold">RHU Data Protection Officer</p>
                            <p class="text-sm mt-1">Rural Health Unit - Calauan, Laguna</p>
                            <p class="text-sm">Email: rhucalauan@yahoo.com</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-600 text-center">
                        Kapag nag-login ka sa system, kakailanganin mong tanggapin ang buong patakaran sa privacy.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" data-close></div>
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl ring-1 ring-black/10 p-8 animate-[fadeIn_.25s_ease]">
            <button id="closeForgot" class="absolute -top-4 -right-4 bg-primary-600 hover:bg-primary-700 text-white w-10 h-10 rounded-full shadow flex items-center justify-center">
                <span class="sr-only">Close</span>&times;
            </button>
            <h2 class="text-xl font-semibold text-primary-700 mb-2 text-center">Reset Password</h2>
            <p class="text-xs text-primary-600/80 mb-6 text-center">Enter the account email to receive a reset link.</p>
            <form id="resetForm" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-medium text-primary-700 mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-primary-500 pointer-events-none">
                            <svg class="w-5 h-5 opacity-80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6l8 7 8-7M4 6h16v12H4z"/>
                            </svg>
                        </span>
                        <input id="email" name="email" type="email" required
                               class="w-full pl-11 pr-3 py-3 rounded-lg border border-gray-300 bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-300/50 outline-none transition placeholder:text-gray-400"
                               placeholder="name@example.com">
                    </div>
                </div>
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold py-3 shadow-sm ring-1 ring-primary-600/50 focus:outline-none focus:ring-4 focus:ring-primary-300 transition disabled:opacity-60">
                    <svg class="w-5 h-5" id="resetSpinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3a9 9 0 019 9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9"/>
                    </svg>
                    <span id="resetBtnText">Send Reset Link</span>
                </button>
            </form>
            <div id="successMessage" class="hidden mt-5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-3 py-2 rounded text-center"></div>
            <div id="errorMessage" class="hidden mt-5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-3 py-2 rounded text-center"></div>
        </div>
    </div>

    <script>
        // Password toggle - only if login form is shown
        const pwField = document.getElementById('password');
        const eyeClosed = document.getElementById('eyeClosed');
        const eyeOpen = document.getElementById('eyeOpen');
        const togglePasswordBtn = document.getElementById('togglePassword');
        
        if (togglePasswordBtn && pwField) {
            togglePasswordBtn.addEventListener('click', () => {
                const show = pwField.type === 'password';
                pwField.type = show ? 'text' : 'password';
                if (eyeClosed) eyeClosed.classList.toggle('hidden', show);
                if (eyeOpen) eyeOpen.classList.toggle('hidden', !show);
            });
        }

        // Forgot Password Modal - only if login form is shown
        const modal = document.getElementById('forgotModal');
        const openForgotBtn = document.getElementById('openForgot');
        const closeForgotBtn = document.getElementById('closeForgot');
        const resetForm = document.getElementById('resetForm');
        
        if (openForgotBtn && modal) {
            openForgotBtn.addEventListener('click', () => modal.classList.remove('hidden'));
        }
        if (closeForgotBtn) {
            closeForgotBtn.addEventListener('click', closeModal);
        }
        if (modal) {
            modal.addEventListener('click', e => { if (e.target.dataset.close !== undefined) closeModal(); });
        }

        function closeModal() {
            if (modal) modal.classList.add('hidden');
            if (resetForm) resetForm.reset();
            hideMsg('successMessage'); hideMsg('errorMessage');
        }

        // Privacy Policy Modal
        const privacyModal = document.getElementById('privacyPolicyModal');
        const viewPrivacyLink = document.getElementById('viewPrivacyLink');
        const viewPrivacyLinkAuth = document.getElementById('viewPrivacyLinkAuth');
        const closePrivacyBtn = document.getElementById('closePrivacyModal');
        const privacyBackdrop = document.getElementById('privacyModalBackdrop');

        // Normal login form privacy link
        if (viewPrivacyLink) {
            viewPrivacyLink.addEventListener('click', (e) => {
                e.preventDefault();
                if (privacyModal) privacyModal.classList.remove('hidden');
            });
        }
        
        // Authenticated view privacy link
        if (viewPrivacyLinkAuth) {
            viewPrivacyLinkAuth.addEventListener('click', (e) => {
                e.preventDefault();
                if (privacyModal) privacyModal.classList.remove('hidden');
            });
        }

        // Close button for privacy modal
        if (closePrivacyBtn) {
            closePrivacyBtn.addEventListener('click', () => {
                if (privacyModal) privacyModal.classList.add('hidden');
            });
        }

        // Backdrop click for privacy modal
        if (privacyBackdrop) {
            privacyBackdrop.addEventListener('click', () => {
                if (privacyModal) privacyModal.classList.add('hidden');
            });
        }

        // Credential error (server)
        function showCredErr(msg){
            const el = document.getElementById('credentialsError');
            if (el) {
                el.textContent = msg;
                el.classList.remove('hidden');
            }
        }

        // Reset form (AJAX) - only if form exists
        if (resetForm) {
            resetForm.addEventListener('submit', async e => {
                e.preventDefault();
                const btn = e.target.querySelector('button[type="submit"]');
                const spinner = document.getElementById('resetSpinner');
                const text = document.getElementById('resetBtnText');
                btn.disabled = true;
                if (spinner) spinner.classList.add('animate-spin');
                if (text) text.textContent = 'Sending...';
                hideMsg('successMessage'); hideMsg('errorMessage');

                try {
                    const r = await fetch('{{ route("password.email") }}', {
                        method:'POST',
                        headers:{
                            'Content-Type':'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ email: document.getElementById('email').value })
                    });
                    const data = await r.json();
                    if (data.status || data.success) {
                        showMsg('successMessage','Password reset link sent!');
                        setTimeout(closeModal, 2500);
                    } else {
                        showMsg('errorMessage', data.message || 'Unable to send link. Try again.');
                    }
                } catch {
                    showMsg('errorMessage','Network error. Please retry.');
                } finally {
                    btn.disabled = false;
                    if (spinner) spinner.classList.remove('animate-spin');
                    if (text) text.textContent = 'Send Reset Link';
                }
            });
        }

        function showMsg(id,msg){
            const el = document.getElementById(id);
            if (el) {
                el.textContent = msg;
                el.classList.remove('hidden');
            }
        }
        function hideMsg(id){
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        }

        // Prevent navigation back to authenticated pages after logout
        // This runs on the login page to clear browser history
        (function() {
            // Clear forward navigation
            if (window.history && window.history.replaceState) {
                // Replace the current history entry
                window.history.replaceState(null, null, window.location.href);
            }
            
            // Prevent forward button from working
            window.addEventListener('popstate', function() {
                window.history.replaceState(null, null, window.location.href);
            });
            
            // If page was loaded from cache, force reload
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
            
            // Clear any session storage that might remain
            try {
                sessionStorage.clear();
            } catch(e) {}
        })();

        // PWA Standalone Detection - Redirect to PWA login if running as installed app
        (function() {
            const isStandalone = window.matchMedia('(display-mode: standalone)').matches 
                || window.navigator.standalone === true 
                || document.referrer.includes('android-app://');
            
            if (isStandalone) {
                // Redirect to PWA-optimized login page
                window.location.href = '{{ route("pwa.login") }}';
            }
        })();
    </script>
    
    <!-- PWA Install Prompt Handler -->
    <script src="{{ asset('javascript/pwa.js') }}?v={{ time() }}"></script>
</body>
</html>