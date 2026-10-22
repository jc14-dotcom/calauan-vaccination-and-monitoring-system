<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>First Login - Change Password</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}" />
    <style>
        [data-animate]{opacity:0;transform:translateY(18px);transition:.55s cubic-bezier(.4,.7,.2,1);} [data-animate].in{opacity:1;transform:none;}
        @media(prefers-reduced-motion:reduce){[data-animate]{opacity:1!important;transform:none!important;transition:none!important;}}
    </style>
    <script>
        document.addEventListener('DOMContentLoaded',()=>{
            if('IntersectionObserver' in window){
                const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}}),{threshold:.15});
                document.querySelectorAll('[data-animate]').forEach(el=>io.observe(el));
            } else { document.querySelectorAll('[data-animate]').forEach(el=>el.classList.add('in')); }
        });
    </script>
</head>
<body class="min-h-full bg-gradient-to-br from-primary-50 via-white to-primary-50 text-gray-800 font-sans antialiased flex flex-col">
    <!-- Header -->
    <header class="w-full bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow-lg">
        <div class="max-w-xl mx-auto px-5 h-20 flex items-center justify-center">
            <div class="text-center">
                <p class="text-xs uppercase tracking-wider opacity-80 font-medium">Welcome!</p>
                <h1 class="text-lg font-semibold tracking-tight">Complete Your Setup</h1>
            </div>
        </div>
    </header>

    <main class="flex-1 w-full max-w-xl mx-auto px-5 py-10">
        <!-- Info Alert -->
        @if (session('info'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl bg-blue-50 text-blue-800 ring-1 ring-blue-200 px-5 py-4" role="alert" data-animate>
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                </div>
                <div class="flex-1 leading-relaxed text-sm">
                    <strong class="font-semibold">{{ session('info') }}</strong>
                </div>
            </div>
        @endif

        <!-- Important Notice -->
        <div class="mb-8 rounded-2xl bg-amber-50 ring-1 ring-amber-200 p-6 text-amber-900" data-animate>
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-base mb-2">Security Requirement</h3>
                    <p class="text-sm leading-relaxed mb-3">
                        You are using a default password. For your account security, you must change it to a personal password before accessing the system.
                    </p>
                    <ul class="text-sm space-y-1 ml-4 list-disc list-inside opacity-90">
                        <li>Choose a password you'll remember</li>
                        <li>Don't share it with anyone</li>
                        <li>This is a one-time requirement</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="rounded-3xl bg-white ring-1 ring-gray-200 shadow-lg p-8 relative overflow-hidden" data-animate>
            <div class="absolute -top-16 -right-16 w-56 h-56 bg-primary-200/40 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-primary-100/50 rounded-full blur-2xl pointer-events-none"></div>
            
            <h2 class="text-xl font-bold tracking-tight mb-6 flex items-center gap-2 text-gray-800 relative">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Change Your Password
            </h2>

            <form id="firstLoginChangePasswordForm" action="{{ route('parents.first-login-update-password') }}" method="POST" novalidate class="space-y-7 relative">
                @csrf
                
                <!-- Current Password -->
                <div class="space-y-2">
                    <label for="current_password" class="text-sm font-medium text-gray-700 flex items-center gap-2">
                        Current Password (Default)
                        <span class="text-xs text-gray-500 font-normal">(provided by health worker)</span>
                    </label>
                    <div class="relative group">
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none pr-12" placeholder="Enter your current password" />
                        <button type="button" data-toggle="current_password" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-600 transition" aria-label="Show password">
                            <svg class="w-5 h-5 toggle-icon-hide" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="w-5 h-5 hidden toggle-icon-show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.81 21.81 0 014.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.83 21.83 0 01-2.16 3.19M14.12 14.12a3 3 0 01-4.24-4.24"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs font-medium text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="space-y-2">
                    <label for="new_password" class="text-sm font-medium text-gray-700">New Password</label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password" required autocomplete="new-password" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none pr-12" placeholder="Create a strong password" aria-describedby="passwordHelp" />
                        <button type="button" data-toggle="new_password" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-600 transition" aria-label="Show password">
                            <svg class="w-5 h-5 toggle-icon-hide" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="w-5 h-5 hidden toggle-icon-show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.81 21.81 0 014.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.83 21.83 0 01-2.16 3.19M14.12 14.12a3 3 0 01-4.24-4.24"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                    @error('new_password')
                        <p class="text-xs font-medium text-red-600 mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Requirements -->
                    <div id="passwordHelp" class="mt-4 bg-gray-50 rounded-xl p-4 ring-1 ring-gray-200">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 mb-3">Password must include:</p>
                        <ul class="space-y-2 text-xs" id="requirementsList">
                            <li data-req="length" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 8 characters</span>
                            </li>
                            <li data-req="uppercase" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 uppercase letter (A-Z)</span>
                            </li>
                            <li data-req="lowercase" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 lowercase letter (a-z)</span>
                            </li>
                            <li data-req="number" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 number (0-9)</span>
                            </li>
                            <li data-req="special" class="flex items-start gap-2 text-gray-500">
                                <span class="status w-5 h-5 flex items-center justify-center rounded-full ring-1 ring-gray-300 bg-white flex-shrink-0">
                                    <svg class="w-3.5 h-3.5 opacity-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                                <span class="leading-tight">At least 1 special character (@#$%^&*()_+-=[]{}|;:,.<>?)</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Confirm -->
                <div class="space-y-2">
                    <label for="new_password_confirmation" class="text-sm font-medium text-gray-700">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" required autocomplete="new-password" class="peer w-full rounded-xl border border-gray-300 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-4 h-12 text-sm transition outline-none pr-12" placeholder="Re-enter your new password" />
                        <button type="button" data-toggle="new_password_confirmation" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-primary-600 transition" aria-label="Show password">
                            <svg class="w-5 h-5 toggle-icon-hide" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="w-5 h-5 hidden toggle-icon-show" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.81 21.81 0 014.06-5.94M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 8 11 8a21.83 21.83 0 01-2.16 3.19M14.12 14.12a3 3 0 01-4.24-4.24"/><path stroke-linecap="round" stroke-linejoin="round" d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                    <p id="confirmError" class="text-xs font-medium text-red-600 min-h-[1rem]"></p>
                </div>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-end gap-4">
                    <button type="submit" id="changePasswordBtn" disabled class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-primary-700 active:scale-[.97] text-white text-sm font-semibold px-8 h-12 shadow-md transition relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Continue to Dashboard
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Text -->
        <div class="mt-6 text-center text-sm text-gray-600" data-animate>
            <p>Need help? Contact your health worker for assistance.</p>
        </div>
    </main>

    <footer class="py-8 text-center text-xs text-gray-500">&copy; RHU Calauan {{ date('Y') }}</footer>

    <script>
        // Prevent back button navigation
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Password visibility toggles
        document.querySelectorAll('[data-toggle]').forEach(btn=>{
            btn.addEventListener('click',()=>{
                const id=btn.getAttribute('data-toggle');
                const input=document.getElementById(id);
                if(!input) return;
                const show=btn.querySelector('.toggle-icon-show');
                const hide=btn.querySelector('.toggle-icon-hide');
                const isHidden=input.type==='password';
                input.type=isHidden?'text':'password';
                show.classList.toggle('hidden',!isHidden);
                hide.classList.toggle('hidden',isHidden);
                btn.setAttribute('aria-label', isHidden? 'Hide password':'Show password');
            });
        });

        const form=document.getElementById('firstLoginChangePasswordForm');
        const newPw=document.getElementById('new_password');
        const confirmPw=document.getElementById('new_password_confirmation');
        const confirmError=document.getElementById('confirmError');
        const submitBtn=document.getElementById('changePasswordBtn');
        const reqItems=[...document.querySelectorAll('#requirementsList [data-req]')];

        function evaluate(){
            const v=newPw.value;
            const checks={
                length: v.length>=8,
                uppercase: /[A-Z]/.test(v),
                lowercase: /[a-z]/.test(v),
                number: /\d/.test(v),
                special: /[@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(v)
            };
            reqItems.forEach(li=>{
                const key=li.getAttribute('data-req');
                const ok=!!checks[key];
                li.classList.toggle('text-gray-500',!ok);
                li.classList.toggle('text-emerald-600',ok);
                const icon=li.querySelector('svg');
                if(icon) icon.classList.toggle('opacity-0',!ok);
                li.querySelector('.status').classList.toggle('ring-emerald-300',ok);
                li.querySelector('.status').classList.toggle('bg-emerald-50',ok);
            });
            const match = confirmPw.value.length? v===confirmPw.value : true;
            confirmError.textContent = confirmPw.value.length && !match ? 'Passwords do not match.' : '';
            const allOk = Object.values(checks).every(Boolean) && match && confirmPw.value.length > 0;
            submitBtn.disabled = !allOk;
            return allOk;
        }

        const debounce=(fn,ms=120)=>{let t;return (...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),ms);}};
        newPw.addEventListener('input',debounce(evaluate));
        confirmPw.addEventListener('input',debounce(evaluate));
        form.addEventListener('submit',e=>{ if(!evaluate()) e.preventDefault(); });
    </script>
</body>
</html>
