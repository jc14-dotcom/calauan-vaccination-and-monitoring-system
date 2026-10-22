@php
    // Expects: $patient (child record or related model holding contact/address),
    //          auth('parents')->user() for parent email.
    // Re-uses existing update route: route('updateProfile') expecting PUT.
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <title>Parent Profile</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/icon-72x72.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/icon-96x96.png') }}">
    
    <link rel="stylesheet" href="{{ versioned_asset('css/tailwind-full.css') }}" />
    <style>
        [data-animate]{opacity:0;transform:translateY(18px);transition:.55s cubic-bezier(.4,.7,.2,1);}
        [data-animate].in{opacity:1;transform:none;}
        @media(prefers-reduced-motion:reduce){[data-animate]{opacity:1!important;transform:none!important;transition:none!important;}}
    </style>
    <script>
        // Simple intersection observer for subtle fade.
        document.addEventListener('DOMContentLoaded',()=>{
            if('IntersectionObserver' in window){
                const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}}),{threshold:.15});
                document.querySelectorAll('[data-animate]').forEach(el=>io.observe(el));
            } else { document.querySelectorAll('[data-animate]').forEach(el=>el.classList.add('in')); }
        });
    </script>
</head>
<body class="min-h-full bg-gray-50 text-gray-800 font-sans antialiased flex flex-col">

    <!-- Top Bar -->
    <header class="w-full bg-gradient-to-r from-primary-700 to-primary-600 text-white shadow">
        <div class="max-w-5xl mx-auto px-4 sm:px-5 h-16 sm:h-20 flex items-center justify-between">
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                <a href="{{ route('parent.dashboard') }}" class="group inline-flex items-center justify-center h-9 w-9 sm:h-11 sm:w-11 rounded-full bg-white/15 hover:bg-white/25 transition ring-1 ring-white/30 flex-shrink-0" title="Back">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="min-w-0">
                    <p class="text-[10px] sm:text-xs uppercase tracking-wider opacity-70 font-medium">Profile</p>
                    <h1 class="text-sm sm:text-lg font-semibold tracking-tight truncate">{{ $patient->mother_name }}'s Profile</h1>
                </div>
            </div>
            {{-- <div class="hidden sm:flex items-center gap-3">
                <div class="px-4 py-2 text-xs rounded-full bg-white/15 ring-1 ring-white/20">Secure Area</div>
            </div> --}}
        </div>
    </header>

    <!-- Main -->
    <main class="flex-1 w-full max-w-3xl mx-auto px-4 sm:px-5 py-6 sm:py-10">
        <form id="profileForm" action="{{ route('updateProfile') }}" method="POST" class="space-y-6 sm:space-y-8" novalidate>
            @csrf
            @method('PUT')

            <div class="rounded-2xl sm:rounded-3xl bg-white ring-1 ring-gray-200 shadow-sm p-5 sm:p-8 relative overflow-hidden" data-animate>
                <div class="absolute -top-16 -right-16 w-40 sm:w-56 h-40 sm:h-56 bg-primary-200/40 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-10 -left-10 w-32 sm:w-40 h-32 sm:h-40 bg-primary-100/50 rounded-full blur-2xl pointer-events-none"></div>
                <h2 class="text-lg sm:text-xl font-bold tracking-tight mb-5 sm:mb-6 flex items-center gap-2 text-gray-800">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12c2.761 0 5-2.462 5-5.5S14.761 1 12 1 7 3.462 7 6.5 9.239 12 12 12z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 21c0-3.313 3.582-6 8-6s8 2.687 8 6"/></svg>
                    Personal Information
                </h2>

                <div class="grid sm:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Contact -->
                    <div class="flex flex-col gap-1.5 sm:gap-2">
                        <label for="contact_no" class="text-xs sm:text-sm font-medium text-gray-700">Contact No <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="tel" id="contact_no" name="contact_no" value="{{ auth('parents')->user()->contact_number ?? '' }}" disabled
                                   pattern="09\d{9}" maxlength="11" inputmode="numeric"
                                   class="peer w-full rounded-lg sm:rounded-xl border border-gray-300 disabled:bg-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-3 sm:px-4 h-10 sm:h-12 text-sm transition outline-none" placeholder="09xxxxxxxxx" />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400 peer-focus:text-primary-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 5l6 3 4 8 6 3 2-4-6-3-4-8-6-3-2 4z"/></svg>
                            </span>
                        </div>
                        <p id="contactError" class="text-xs font-medium text-red-600 min-h-[1rem]"></p>
                    </div>
                    <!-- Email -->
                    <div class="flex flex-col gap-1.5 sm:gap-2">
                        <label for="email" class="text-xs sm:text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ auth('parents')->user()->email ?? '' }}" disabled required
                                   class="peer w-full rounded-lg sm:rounded-xl border border-gray-300 disabled:bg-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-3 sm:px-4 h-10 sm:h-12 text-sm transition outline-none" placeholder="name@example.com" />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-gray-400 peer-focus:text-primary-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h16v16H4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16"/></svg>
                            </span>
                        </div>
                        <p id="emailError" class="text-xs font-medium text-red-600 min-h-[1rem]"></p>
                    </div>
                    <!-- Address -->
                    <div class="flex flex-col gap-1.5 sm:gap-2 sm:col-span-2">
                        <label for="address" class="text-xs sm:text-sm font-medium text-gray-700">Address</label>
                        <input type="text" id="address" name="address" value="{{ auth('parents')->user()->address ?? '' }}" disabled
                               class="w-full rounded-lg sm:rounded-xl border border-gray-300 disabled:bg-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-3 sm:px-4 h-10 sm:h-12 text-sm transition outline-none" placeholder="Street / Sitio / Purok" />
                    </div>
                    <!-- Barangay -->
                    <div class="flex flex-col gap-1.5 sm:gap-2 sm:col-span-2">
                        <label for="barangay" class="text-xs sm:text-sm font-medium text-gray-700">Barangay</label>
                        <select id="barangay" name="barangay" disabled required
                                class="w-full rounded-lg sm:rounded-xl border border-gray-300 disabled:bg-gray-100 focus:border-primary-500 focus:ring-4 focus:ring-primary-200/40 px-3 sm:px-4 h-10 sm:h-12 text-sm transition outline-none">
                            <option value="" disabled>Select Barangay</option>
                            @foreach(['Balayhangin','Bangyas','Dayap','Hanggan','Imok','Kanluran','Lamot 1','Lamot 2','Limao','Mabacan','Masiit','Paliparan','Perez','Prinza','San Isidro','Santo Tomas','Silangan'] as $b)
                                <option value="{{ $b }}">{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 sm:mt-10 flex flex-wrap gap-2 sm:gap-4">
                    <button type="button" id="editButton" class="inline-flex items-center gap-2 rounded-lg sm:rounded-xl bg-primary-600 hover:bg-primary-700 active:scale-[.97] text-white text-xs sm:text-sm font-semibold px-4 sm:px-6 h-9 sm:h-11 shadow-sm transition">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-7"/><path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </button>
                    <button type="submit" id="saveButton" disabled class="inline-flex items-center gap-2 rounded-lg sm:rounded-xl bg-gray-300 text-gray-500 text-xs sm:text-sm font-semibold px-4 sm:px-6 h-9 sm:h-11 shadow-sm cursor-not-allowed transition-all duration-200" style="pointer-events: none;">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save
                    </button>
                    <button type="reset" id="cancelEdit" disabled class="inline-flex items-center gap-2 rounded-lg sm:rounded-xl border border-gray-300 text-gray-400 bg-gray-100 text-xs sm:text-sm font-semibold px-3 sm:px-5 h-9 sm:h-11 shadow-sm cursor-not-allowed transition-all duration-200" style="pointer-events: none;">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </main>

    <!-- Toast -->
    <div id="toast" class="hidden fixed top-5 right-5 w-72 rounded-xl bg-white shadow-xl ring-1 ring-gray-200 p-4 flex items-start gap-3 text-sm text-gray-700">
        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div class="flex-1">
            <p class="font-semibold">Profile updated</p>
            <p class="text-xs mt-0.5">Your changes were saved successfully.</p>
        </div>
        <button id="closeToast" class="mt-1 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <footer class="py-8 text-center text-xs text-gray-500">&copy; RHU Calauan {{ date('Y') }}</footer>

    <script>
        // Populate barangay
        document.addEventListener('DOMContentLoaded',()=>{
            const barangaySelect=document.getElementById('barangay');
            if(barangaySelect) barangaySelect.value = @json(auth('parents')->user()->barangay);
        });

        const editBtn=document.getElementById('editButton');
        const saveBtn=document.getElementById('saveButton');
        const cancelBtn=document.getElementById('cancelEdit');
        const form=document.getElementById('profileForm');
        const contact=document.getElementById('contact_no');
        const email=document.getElementById('email');
        const address=document.getElementById('address');
        const barangay=document.getElementById('barangay');
        const contactError=document.getElementById('contactError');
        const emailError=document.getElementById('emailError');
        const toast=document.getElementById('toast');
        const closeToast=document.getElementById('closeToast');
        let originalValues={};

        function enableEditing(){
            originalValues={contact:contact.value,email:email.value,address:address.value,barangay:barangay.value};
            [contact,email,address,barangay].forEach(i=>i.disabled=false);
            
            // Enable Save button with active styling
            saveBtn.disabled=false;
            saveBtn.style.pointerEvents = 'auto';
            saveBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            saveBtn.classList.add('bg-primary-600', 'hover:bg-primary-700', 'text-white', 'active:scale-[.97]');
            
            // Enable Cancel button with active styling
            cancelBtn.disabled=false;
            cancelBtn.style.pointerEvents = 'auto';
            cancelBtn.classList.remove('border-gray-300', 'text-gray-400', 'bg-gray-100', 'cursor-not-allowed');
            cancelBtn.classList.add('border-primary-300', 'text-primary-700', 'bg-white', 'hover:bg-primary-50', 'hover:border-primary-400', 'active:scale-[.97]');
            
            // Disable Edit button
            editBtn.disabled=true; 
            editBtn.classList.add('opacity-60','cursor-not-allowed');
        }
        function disableEditing(){
            [contact,email,address,barangay].forEach(i=>i.disabled=true);
            
            // Disable Save button with grayed out styling
            saveBtn.disabled=true;
            saveBtn.style.pointerEvents = 'none';
            saveBtn.classList.remove('bg-primary-600', 'hover:bg-primary-700', 'text-white', 'active:scale-[.97]');
            saveBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            
            // Disable Cancel button with grayed out styling
            cancelBtn.disabled=true;
            cancelBtn.style.pointerEvents = 'none';
            cancelBtn.classList.remove('border-primary-300', 'text-primary-700', 'bg-white', 'hover:bg-primary-50', 'hover:border-primary-400', 'active:scale-[.97]');
            cancelBtn.classList.add('border-gray-300', 'text-gray-400', 'bg-gray-100', 'cursor-not-allowed');
            
            // Enable Edit button
            editBtn.disabled=false; 
            editBtn.classList.remove('opacity-60','cursor-not-allowed');
        }
        function cancelEditing(){
            contact.value=originalValues.contact; email.value=originalValues.email; address.value=originalValues.address; barangay.value=originalValues.barangay; disableEditing(); clearErrors();
        }
        function clearErrors(){contactError.textContent=''; emailError.textContent=''; email.classList.remove('border-red-400'); contact.classList.remove('border-red-400');}
        function validateContact(){
            const rx=/^09\d{9}$/; if(!rx.test(contact.value)){contactError.textContent='Must start with 09 and be 11 digits'; return false;} contactError.textContent=''; return true;
        }
        function validateEmail(){
            // Stricter email regex: requires valid format with proper domain (at least 2 chars after last dot)
            const rx=/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if(!rx.test(email.value)){
                emailError.textContent='Enter a valid email (e.g., name@gmail.com)';
                email.classList.add('border-red-400');
                email.classList.remove('border-gray-300');
                return false;
            }
            emailError.textContent='';
            email.classList.remove('border-red-400');
            email.classList.add('border-gray-300');
            return true;
        }
        
        // Block non-digit input for contact number (only allow numbers)
        contact.addEventListener('input', function(e) {
            // Remove any non-digit characters
            let value = this.value.replace(/\D/g, '');
            // Limit to 11 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            this.value = value;
            // Validate after filtering
            validateContact();
        });
        
        // Email validation with visual feedback
        email.addEventListener('input', function() {
            validateEmail();
        });
        
        editBtn.addEventListener('click', enableEditing);
        cancelBtn.addEventListener('click', (e)=>{e.preventDefault(); cancelEditing();});
        closeToast.addEventListener('click', ()=> toast.classList.add('hidden'));

        form.addEventListener('submit',e=>{
            e.preventDefault();
            const okC=validateContact(); const okE=validateEmail(); if(!(okC && okE)) return;
            const fd=new FormData(form);
            fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:fd})
                .then(r=>r.json())
                .then(j=>{
                    if(j.success){
                        disableEditing();
                        toast.classList.remove('hidden');
                        toast.classList.add('animate-slideIn');
                        setTimeout(()=>toast.classList.add('hidden'),4000);
                    } else {
                        alert('Update failed.');
                    }
                })
                .catch(()=>alert('Network error.'));
        });
    </script>
</body>
</html>