<!DOCTYPE html>
<html lang="tl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patakaran sa Privacy ng Data</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}">
    <style>
        .brand-gradient { background: linear-gradient(135deg, #7a5bbd 0%, #5a3f99 45%, #402d73 100%); }
        .scroll-indicator { 
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(10px); }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="brand-gradient text-white py-6 shadow-lg">
            <div class="max-w-4xl mx-auto px-4">
                <div class="flex items-center justify-center gap-4">
                    <img src="{{ asset('images/todoligtass.png') }}" alt="Logo" class="h-14 w-auto drop-shadow-lg">
                    <div class="text-center">
                        <h1 class="text-2xl font-bold">RHU Infant Vaccination System</h1>
                        <p class="text-sm text-white/90 mt-1">Patakaran sa Privacy ng Data</p>
                    </div>
                    <img src="{{ asset('images/doh-logo.png') }}" alt="DOH" class="h-14 w-auto drop-shadow-lg">
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-8">
            <div class="max-w-4xl mx-auto px-4">
                <!-- Welcome Message -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-3">Maligayang pagdating, {{ auth('parents')->user()?->patients->first()?->mother_name ?? auth('parents')->user()?->username ?? 'Magulang' }}!</h2>
                    <p class="text-gray-700 leading-relaxed">
                        Bago mo magamit ang iyong account, mahalaga na maunawaan mo kung paano namin pinoprotektahan 
                        at ginagamit ang iyong personal na impormasyon at ang impormasyon ng iyong anak. Pakibasa ang 
                        sumusunod na patakaran sa privacy ng data.
                    </p>
                </div>

                <!-- Privacy Policy Content -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <!-- Scrollable Area -->
                    <div id="policyContent" class="p-6 max-h-[500px] overflow-y-auto">
                        <h3 class="text-lg font-bold text-primary-700 mb-4">PATAKARAN SA PRIVACY NG DATA</h3>
                        
                        <div class="space-y-5 text-gray-700 leading-relaxed">
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
                                    Ang iyong datos ay ligtas na itinatago sa aming sistema at may mga sumusunod na 
                                    proteksyon:
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
                                <p class="mt-2">
                                    Hindi namin ibabahagi ang iyong impormasyon sa ibang partido nang walang iyong pahintulot, 
                                    maliban kung kinakailangan ng batas.
                                </p>
                            </div>

                            <!-- Section 4: Your Rights -->
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">4. Ang Iyong mga Karapatan</h4>
                                <p>
                                    Sa ilalim ng Data Privacy Act of 2012, mayroon kang mga sumusunod na karapatan:
                                </p>
                                <ul class="list-disc list-inside ml-4 mt-2 space-y-1">
                                    <li><strong>Karapatan na malaman:</strong> Maaari mong tingnan ang iyong personal na impormasyon na nakaimbak sa sistema</li>
                                    <li><strong>Karapatan na itama:</strong> Maaari mong hilingin na itama ang anumang hindi tamang impormasyon</li>
                                    <li><strong>Karapatan na magtanong:</strong> Maaari kang magtanong tungkol sa kung paano ginagamit ang iyong datos</li>
                                    <li><strong>Karapatan na magreklamo:</strong> Maaari kang magreklamo kung may alalahanin ka sa privacy ng iyong datos</li>
                                </ul>
                            </div>

                            <!-- Section 5: Contact Information -->
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">5. Makipag-ugnayan sa Amin</h4>
                                <p>
                                    Kung mayroon kang mga katanungan, alalahanin, o nais magamit ang iyong mga karapatan 
                                    kaugnay ng iyong personal na impormasyon, maaari kang makipag-ugnayan sa:
                                </p>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-3">
                                    <p class="font-semibold">RHU Data Protection Officer</p>
                                    <p class="text-sm mt-1">Rural Health Unit - Calauan, Laguna</p>
                                    <p class="text-sm">Email: rhucalauan@yahoo.com</p>
                                    <p class="text-sm">Telepono: (049) 543-6638</p>
                                </div>
                            </div>

                            <!-- Section 6: Changes to Policy -->
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">6. Pagbabago sa Patakaran</h4>
                                <p>
                                    Maaari naming baguhin ang patakarang ito sa hinaharap. Kung may malaking pagbabago, 
                                    aabisuhan ka namin at hihilingin ang iyong muling pahintulot.
                                </p>
                            </div>

                            <!-- Version Info -->
                            <div class="border-t pt-4 mt-6">
                                <p class="text-sm text-gray-600">
                                    <strong>Version:</strong> 1.0<br>
                                    <strong>Petsa ng Pagsasapanahon:</strong> Nobyembre 20, 2025
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Scroll Indicator (shown if not scrolled to bottom) -->
                    <div id="scrollIndicator" class="bg-yellow-50 border-t border-yellow-200 px-6 py-3 flex items-center justify-center gap-2 text-yellow-800">
                        <svg class="w-5 h-5 scroll-indicator" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <span class="text-sm font-medium">Mag-scroll pababa upang makita ang buong patakaran</span>
                    </div>

                    <!-- Consent Section -->
                    <div class="bg-gray-50 border-t border-gray-200 p-6">
                        <form action="{{ route('parent.privacy.accept') }}" method="POST" id="consentForm">
                            @csrf
                            <div class="mb-4">
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <input type="checkbox" id="consentCheckbox" name="privacy_consent" 
                                           class="mt-1 h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                           required>
                                    <span class="text-sm text-gray-700 leading-relaxed group-hover:text-gray-900 transition">
                                        Nabasa ko at naiintindihan ko ang Patakaran sa Privacy ng Data. Sumasang-ayon ako 
                                        na gamitin ang aking personal na impormasyon at ang impormasyon ng aking anak 
                                        ayon sa mga nakalagay sa itaas.
                                    </span>
                                </label>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" id="acceptButton"
                                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-300 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Sumasang-ayon ako at Magpatuloy</span>
                                </button>
                            </div>

                            <p class="mt-4 text-xs text-gray-600 text-center">
                                Hindi mo maaaring gamitin ang system nang hindi ka sumasang-ayon sa patakarang ito.
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="mt-6 text-center text-sm text-gray-600">
                    <p>May mga katanungan? Makipag-ugnayan sa RHU Data Protection Officer</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t py-4">
            <div class="max-w-4xl mx-auto px-4 text-center text-xs text-gray-600">
                <p>&copy; 2025 RHU Infant Vaccination System - Calauan, Laguna. Lahat ng karapatan ay nakalaan.</p>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const policyContent = document.getElementById('policyContent');
            const scrollIndicator = document.getElementById('scrollIndicator');
            const consentCheckbox = document.getElementById('consentCheckbox');
            const acceptButton = document.getElementById('acceptButton');
            let hasScrolledToBottom = false;

            // Check if user has scrolled to bottom
            policyContent.addEventListener('scroll', function() {
                const scrollTop = policyContent.scrollTop;
                const scrollHeight = policyContent.scrollHeight;
                const clientHeight = policyContent.clientHeight;
                
                // Check if scrolled to bottom (with 10px tolerance)
                if (scrollTop + clientHeight >= scrollHeight - 10) {
                    hasScrolledToBottom = true;
                    scrollIndicator.style.display = 'none';
                }
            });

            // Enable checkbox only after scrolling to bottom
            consentCheckbox.addEventListener('click', function(e) {
                if (!hasScrolledToBottom) {
                    e.preventDefault();
                    alert('Pakibasa muna ang buong patakaran sa privacy bago ka sumasang-ayon.');
                    // Scroll to bottom automatically
                    policyContent.scrollTo({
                        top: policyContent.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            });

            // Handle submit button click
            acceptButton.addEventListener('click', function(e) {
                // If not scrolled to bottom, prevent and scroll
                if (!hasScrolledToBottom) {
                    e.preventDefault();
                    alert('Pakibasa muna ang buong patakaran sa privacy bago ka sumasang-ayon.');
                    policyContent.scrollTo({
                        top: policyContent.scrollHeight,
                        behavior: 'smooth'
                    });
                    return;
                }
                
                // If checkbox is not checked, trigger animation
                if (!consentCheckbox.checked) {
                    e.preventDefault();
                    const checkboxLabel = consentCheckbox.parentElement;
                    checkboxLabel.classList.add('shake-animation', 'border-2', 'border-red-500', 'bg-red-50', 'rounded-lg', 'p-2', '-m-2');
                    setTimeout(() => {
                        checkboxLabel.classList.remove('shake-animation', 'border-2', 'border-red-500', 'bg-red-50', 'rounded-lg', 'p-2', '-m-2');
                    }, 3000);
                    return;
                }
                
                // If everything is valid, allow form submission
                document.getElementById('consentForm').submit();
            });

            // Check initial scroll position (in case content is short)
            if (policyContent.scrollHeight <= policyContent.clientHeight) {
                hasScrolledToBottom = true;
                scrollIndicator.style.display = 'none';
            }

            // Prevent back button navigation
            history.pushState(null, null, location.href);
            window.addEventListener('popstate', function() {
                history.pushState(null, null, location.href);
                alert('Hindi ka maaaring bumalik. Pakibasa at tanggapin muna ang patakaran sa privacy.');
            });
        });
    </script>

    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .shake-animation {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</body>
</html>
