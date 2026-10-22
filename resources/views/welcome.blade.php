<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Vaccination Monitoring System</title>
    <meta name="description" content="Infant Vaccination & Growth Monitoring System - Calauan" />
    <meta name="color-scheme" content="light" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    
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
    
    <!-- Background image loaded via CSS, prefetch instead of preload to avoid unused preload warning -->
    <link rel="prefetch" as="image" href="{{ versioned_asset('images/systembg.jpg') }}" />
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}" />
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Dark mode variants temporarily disabled (kept below for quick restoration) */
        .brand-gradient {
            background: linear-gradient(135deg, #7a5bbd 0%, #5a3f99 45%, #402d73 100%);
        }

        .hero-overlay {
            background: linear-gradient(to right, rgba(64, 45, 115, 0.85), rgba(90, 63, 153, 0.65), rgba(122, 91, 189, 0.55));
        }

        /* Scroll animation base */
        [data-animate] {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .75s cubic-bezier(.4, .7, .2, 1), transform .75s cubic-bezier(.4, .7, .2, 1);
            will-change: opacity, transform;
        }

        [data-animate].in {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion:reduce) {
            [data-animate] {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }
    </style>
    <!--
    ===== Dark Mode (Temporarily Disabled) =====
    Original early theme init script kept here:
    <script>
        (() => {
            const stored = localStorage.getItem('theme');
            const prefers = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefers)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    To re-enable: move the script above out of this comment, restore the commented body class variant, and uncomment the toggle button in the nav.
    ===========================================
    -->
</head>
<!-- Original body with dark classes (disabled):
<body class="antialiased text-gray-800 dark:text-gray-200 flex flex-col min-h-screen bg-gray-50 dark:bg-[#16161d]">
-->

<body class="antialiased text-gray-800 flex flex-col min-h-screen bg-gray-50">

    <!-- Hero -->
    <header class="relative isolate">
        <div class="absolute inset-0 -z-10 brand-gradient"></div>
        <div class="absolute inset-0 -z-10 hero-overlay mix-blend-multiply"></div>
        <div class="absolute inset-0 -z-10 opacity-25 dark:opacity-20"
            style="background:url('{{ asset('images/systembg.jpg') }}') center/cover"></div>

        <nav class="relative z-10 px-4 sm:px-8 py-5 flex items-center justify-between">
            <div class="flex items-center gap-5" data-animate>
                <img src="{{ asset('images/doh-logo.png') }}" alt="DOH" class="h-20 sm:h-24 lg:h-28 w-auto drop-shadow-lg" loading="lazy"
                    decoding="async" />
                <img src="{{ asset('images/calauanlogo.png') }}" alt="Calauan" class="h-20 sm:h-24 lg:h-28 w-auto drop-shadow-lg"
                    loading="lazy" decoding="async" />
                <span class="hidden sm:inline-block h-14 w-px bg-white/40"></span>
                <span class="text-white font-bold tracking-wide text-lg sm:text-3xl lg:text-4xl drop-shadow-lg">
                    Vaccination & Growth Monitoring
                </span>
            </div>
        </nav>


        <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-8 pt-6 pb-20 md:pt-8 lg:pt-12 lg:pb-32">
            <div class="grid lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-6 flex flex-col gap-6 text-white">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight drop-shadow-xl" data-animate>
                        Protektadong Kinabukasan Para sa Bawat Bata
                    </h1>
                    <p class="text-lg sm:text-xl text-white/90 font-medium leading-relaxed" data-animate>
                        "Pangangalaga sa kalusugan at kinabukasan ng bawat bata sa pamamagitan ng
                        mabisa, napapanahong pagbabakuna at masusing pagsubaybay."
                    </p>
                    <div class="flex flex-wrap gap-4 pt-2" data-animate>
                        <a href="{{ route('login') }}"
                            class="group inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-[#7a5bbd] to-[#5a3f99] text-white font-semibold px-6 py-3 shadow-lg shadow-purple-600/30 border border-white/40 transition-all duration-300 hover:from-[#8b6dce] hover:to-[#6b4faa] hover:shadow-2xl hover:shadow-purple-500/50 hover:-translate-y-1 hover:scale-110 active:translate-y-0 active:scale-100">
                            <svg class="h-5 w-5 transition-transform duration-300 group-hover:rotate-12" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M3 4.5A1.5 1.5 0 014.5 3h6A1.5 1.5 0 0112 4.5V6h-1V4.5a.5.5 0 00-.5-.5h-6a.5.5 0 00-.5.5v11a.5.5 0 00.5.5h6a.5.5 0 00.5-.5V14h1v1.5A1.5 1.5 0 0110.5 17h-6A1.5 1.5 0 013 15.5v-11z" />
                                <path
                                    d="M9.293 12.95l.707.707L13.657 10l-3.657-3.657-.707.707L11.793 9.5H5v1h6.793l-2.5 2.45z" />
                            </svg>
                            Login
                        </a>
                        <a href="#learn-more"
                            class="inline-flex items-center gap-2 rounded-lg bg-white/10 backdrop-blur text-white font-medium px-6 py-3 border-2 border-white/60 transition-all duration-300 hover:-translate-y-1 hover:scale-110 hover:shadow-2xl hover:border-white hover:bg-white/20 active:translate-y-0 active:scale-100">
                            Learn More
                        </a>
                    </div>
                    <div class="grid grid-cols-3 gap-6 pt-10" data-animate>
                        <div class="text-center">
                            <p class="text-3xl font-bold">99%+</p>
                            <p class="text-xs uppercase tracking-wider text-white/70 font-medium">Coverage Goal</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold">24/7</p>
                            <p class="text-xs uppercase tracking-wider text-white/70 font-medium">Monitoring</p>
                        </div>
                        <div class="text-center">
                            <p class="text-3xl font-bold">100%</p>
                            <p class="text-xs uppercase tracking-wider text-white/70 font-medium">Commitment</p>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-6" data-animate>
                    <div class="relative mx-auto max-w-xl">
                        <div
                            class="absolute -inset-4 bg-gradient-to-tr from-[#7a5bbd] via-[#5a3f99] to-[#402d73] rounded-3xl blur-2xl opacity-40 animate-pulse">
                        </div>
                        <div
                            class="relative glass rounded-3xl p-6 sm:p-8 shadow-2xl ring-1 ring-white/40 transition duration-300">
                            <img src="{{ asset('images/todoligtass.png') }}" alt="TODO LIGTAS"
                                class="w-full max-w-md mx-auto drop-shadow" loading="lazy" decoding="async" />
                            <p class="mt-6 text-center text-sm font-semibold text-[#5a3f99]">
                                RHU CALAUAN: (049) 543-6638<br><br>
                                DOH HOTLINE: (02) 651 7800<br>
                                CENTER FOR HEALTH DEVELOPMENT IV-CALABARZON<br>
                                8-249-2000 loc. 4400
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <svg class="absolute bottom-0 left-0 w-full text-gray-50 dark:text-[#16161d]" viewBox="0 0 1440 100" fill="none"
            preserveAspectRatio="none">
            <path
                d="M0 67L60 63.7C120 60 240 53.7 360 36.3C480 19 600 -10 720 2.7C840 16 960 80 1080 96.7C1200 113 1320 83 1380 67L1440 50V100H1380C1320 100 1200 100 1080 100C960 100 840 100 720 100C600 100 480 100 360 100C240 100 120 100 60 100H0V67Z"
                fill="currentColor" />
        </svg>
    </header>

    <!-- Features -->
    <section id="learn-more" class="relative py-20 lg:py-28 bg-gray-50 dark:bg-secondary-900">
        <div
            class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-primary-300/40 to-transparent dark:via-primary-500/30">
        </div>
        <div class="mx-auto max-w-7xl px-4 sm:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14" data-animate>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-800 dark:text-gray-100">Bakit Mahalaga ang Bakuna?
                </h2>
                <p class="mt-4 text-gray-600 dark:text-gray-300 leading-relaxed">
                    Ang bakuna ay mahalaga dahil pinapalakas nito ang immune system upang maiwasan ang malulubhang sakit. 
                    Itinuturo ng bakuna sa katawan kung paano kilalanin at labanan ang mga mikrobyo o virus bago pa magdulot ng impeksyon. 
                    Sa pamamagitan nito, napoprotektahan hindi lamang ang sanggol, kundi pati ang buong komunidad.
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-10">
                @php
                    $features = [
                        ['icon' => 'M4 6h16M4 12h16M4 18h16', 'title' => 'Organized Records', 'desc' => 'Sentralisadong digital record ng lahat ng bakuna ng sanggol para mas madali, mas mabilis, at mas tumpak ang pag-access ng impormasyon.'],
                        ['icon' => 'M12 4v16m8-8H4', 'title' => 'Timely Reminders', 'desc' => 'Automated notifications para sa susunod na iskedyul ng bakuna upang masiguro na walang makakaligtaan.'],
                        ['icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z', 'title' => 'Real-time Monitoring', 'desc' => 'Pagtatala ng timbang, taas, at iba pang growth indicators para makita ang development ng sanggol kasabay ng kaniyang vaccination milestones.'],
                    ];
                @endphp
                @foreach($features as $f)
                    <div class="group p-6 rounded-2xl bg-white dark:bg-secondary-800 shadow-sm ring-1 ring-primary-200 hover:ring-primary-400 hover:shadow-md transition flex flex-col"
                        data-animate>
                        <div
                            class="h-14 w-14 rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 text-white flex items-center justify-center shadow-md mb-5">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-lg mb-2 text-gray-800 dark:text-white">{{ $f['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Process -->
    <section class="relative py-20 bg-white">
        <div class="mx-auto max-w-6xl px-4 sm:px-8">
            <div class="grid lg:grid-cols-2 gap-14 items-center">
                <div class="order-2 lg:order-1 space-y-8">
                    <h2 class="text-3xl font-bold text-gray-800" data-animate>Paano Ito Gumagana</h2>
                    <ul class="space-y-6">
                        @php
                            $steps = [
                                ['n' => 1, 't' => 'Registration & Profile Setup', 'd' => 'Health worker o parent ay nag-eencode ng detalye ng bata at initial health metrics.'],
                                ['n' => 2, 't' => 'Vaccination Scheduling', 'd' => 'System ay nagbibigay ng recommended schedule batay sa edad at national guidelines.'],
                                ['n' => 3, 't' => 'Monitoring & Updates', 'd' => 'Real-time logging ng nabigay na bakuna at growth measurements.'],
                                ['n' => 4, 't' => 'Reports & Insights', 'd' => 'Automated summaries para mas madali ang decision-making at intervention.'],
                            ];
                        @endphp
                        @foreach($steps as $s)
                            <li class="flex gap-4" data-animate>
                                <span
                                    class="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-gradient-to-br from-[#7a5bbd] to-[#5a3f99] text-white font-semibold">{{ $s['n'] }}</span>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $s['t'] }}</p>
                                    <p class="text-sm text-gray-600 leading-relaxed">{{ $s['d'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="order-1 lg:order-2 relative" data-animate>
                    <div
                        class="absolute -inset-4 bg-gradient-to-tr from-[#7a5bbd] via-[#5a3f99] to-[#402d73] rounded-3xl blur-2xl opacity-30">
                    </div>
                    <div class="relative glass rounded-3xl p-8 shadow-2xl ring-1 ring-purple-200/40">
                        <h3 class="text-xl font-semibold text-[#5a3f99] mb-4" data-animate>Mission Statement</h3>
                        <p class="text-sm leading-relaxed text-gray-700 font-medium" data-animate>
                            "Pangangalaga sa kalusugan at kinabukasan ng bawat bata sa pamamagitan ng mabisa,
                            napapanahong pagbabakuna at masusing pagsubaybay."
                        </p>
                        <div class="mt-8 grid grid-cols-2 gap-6 text-center" data-animate>
                            <div>
                                <p class="text-2xl font-bold text-[#5a3f99]">500+</p>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Children</p>
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-[#5a3f99]">100%</p>
                                <p class="text-xs uppercase tracking-wide text-gray-500">Goal</p>
                            </div>
                        </div>
                        <div class="mt-10 text-center" data-animate>
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-full
          bg-white text-primary-800 font-semibold px-8 py-3
          border border-primary-300 shadow-soft
          hover:bg-primary-50 hover:text-primary-900
          hover:scale-105 transition-transform duration-200
          focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                                Get Started
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="relative py-24 bg-gray-50">
        <div class="mx-auto max-w-5xl px-4 sm:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-800" data-animate>
                Handa Ka Na Bang Magsimula?
            </h2>
            <p class="mt-4 text-gray-600 max-w-2xl mx-auto" data-animate>
                Tumulong sa pagprotekta sa kalusugan ng mga bata sa komunidad. Panahon na para sa mas modernong paraan
                ng pagsubaybay.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4" data-animate>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl
          bg-gradient-to-r from-primary-600 to-primary-800
          text-white font-semibold px-8 py-3
          shadow-medium hover:shadow-strong
          hover:from-primary-700 hover:to-primary-900
          hover:scale-105 transition-transform duration-200
          focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400">
                    {{-- <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15l-3 3m0 0l-3-3m3 3V3" />
                    </svg> --}}
                    Login Now
                </a>
                <a href="#learn-more"
                    class="inline-flex items-center gap-2 rounded-lg bg-white text-[#5a3f99] border-2 border-[#5a3f99] hover:bg-[#f5f3ff] hover:border-[#7a5bbd] hover:translate-y-[-2px] hover:scale-105 hover:shadow-lg active:translate-y-0 active:scale-100 px-8 py-3 font-semibold shadow transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#7a5bbd]/50">
                    Learn More
                </a>
            </div>
        </div>
        <div
            class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-purple-300/40 to-transparent dark:via-purple-500/30">
        </div>
    </section>

    <!-- Back to Top Button - Fixed at bottom right, above footer -->
    <button id="backToTop"
        class="fixed bottom-24 right-6 z-50 h-12 w-12 rounded-full bg-white flex items-center justify-center shadow-lg ring-2 ring-[#7a5bbd] opacity-0 invisible translate-y-4 transition-all duration-300 hover:bg-[#f5f3ff] hover:shadow-xl hover:scale-110 active:scale-100 focus:outline-none focus-visible:ring-4 focus-visible:ring-purple-400"
        aria-label="Back to top">
        <svg class="h-6 w-6 text-[#7a5bbd]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>

    <!-- Footer -->
    <footer class="mt-auto bg-gray-900 text-gray-300 pt-12 pb-10 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
            style="background:url('{{ asset('images/systembg.jpg') }}') center/cover;"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-8 grid md:grid-cols-4 gap-10">
            <div class="space-y-4 md:col-span-2" data-animate>
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/doh-logo.png') }}" alt="DOH" class="h-12 w-auto drop-shadow"
                        loading="lazy" decoding="async" />
                    <img src="{{ asset('images/calauanlogo.png') }}" alt="Calauan" class="h-12 w-auto drop-shadow"
                        loading="lazy" decoding="async" />
                </div>
                <p class="text-sm leading-relaxed max-w-md text-gray-400">Digitally empowering local health units to
                    track vaccination progress and growth milestones with accuracy and confidence.</p>
            </div>
            <div data-animate>
                <h3 class="font-semibold text-gray-100 mb-4">Contact</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><span class="text-gray-300">RHU CALAUAN:</span> (049) 543-6638</li>
                    <li><span class="text-gray-300">DOH HOTLINE:</span> (02) 651 7800</li>
                    <li><span class="text-gray-300">CHD IV-CALABARZON:</span> 8-249-2000 loc. 4400</li>
                </ul>
            </div>
            <div data-animate>
                <h3 class="font-semibold text-gray-100 mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="{{ route('login') }}" class="hover:text-gray-200">Login</a></li>
                    <li><a href="#learn-more" class="hover:text-gray-200">Learn More</a></li>
                </ul>
            </div>
        </div>
        <div class="relative mt-12 pt-6 border-t border-white/10 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} Vaccination Monitoring System. All rights reserved.
        </div>
    </footer>

    <script>
        // Simple and effective cache prevention - no flickering
        (function() {
            // Only reload if page was restored from bfcache (back button)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
        })();

        // Back to Top button functionality
        (function() {
            const backToTopBtn = document.getElementById('backToTop');
            if (!backToTopBtn) return;

            // Show/hide button based on scroll position
            function toggleBackToTop() {
                // Show when scrolled more than 500px from top
                if (window.scrollY > 500) {
                    backToTopBtn.classList.remove('opacity-0', 'invisible', 'translate-y-4');
                    backToTopBtn.classList.add('opacity-100', 'visible', 'translate-y-0');
                } else {
                    backToTopBtn.classList.add('opacity-0', 'invisible', 'translate-y-4');
                    backToTopBtn.classList.remove('opacity-100', 'visible', 'translate-y-0');
                }
            }

            // Smooth scroll to top
            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Listen for scroll events (throttled)
            let ticking = false;
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        toggleBackToTop();
                        ticking = false;
                    });
                    ticking = true;
                }
            });

            // Check on page load
            toggleBackToTop();
        })();

        // Scroll reveal
        const animated = [...document.querySelectorAll('[data-animate]')];
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('in');
                        io.unobserve(e.target);
                    }
                });
            }, { threshold: .15, rootMargin: '0px 0px -5% 0px' });
            animated.forEach(el => io.observe(el));
        } else {
            animated.forEach(el => el.classList.add('in'));
        }
        
        // PWA Standalone Detection - Redirect to minimal PWA login if running as installed app
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
