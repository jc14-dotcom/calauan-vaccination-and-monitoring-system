@extends('layouts.responsive-layout')

@section('title', 'Backup & Restore')

@section('additional-styles')
<style>
    .hw-container { width:100%; max-width:100%; margin-left:auto; margin-right:auto; padding-left:1rem; padding-right:1rem; }
    @media (min-width: 640px){ .hw-container { padding-left:2rem; padding-right:2rem; } }
    @media (min-width: 1280px){ .hw-container { padding-left:2.5rem; padding-right:2.5rem; } }
    .hw-no-overflow-x { overflow-x:hidden; }
    /* Fade-in animation for header */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    [data-animate] { animation: fadeInUp 0.6s ease-out forwards; }
</style>
@endsection

@section('content')
<div class="hw-container hw-no-overflow-x flex flex-col pb-8 min-w-0">
    <!-- Page Banner -->
    <section class="relative overflow-hidden rounded-2xl mb-6 ring-1 ring-primary-300/40 bg-gradient-to-r from-primary-600 to-primary-800">
        <div class="relative px-6 py-7 text-white flex flex-col md:flex-row md:items-center md:justify-between gap-4" data-animate>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-white/15 ring-1 ring-white/25">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </span>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold leading-tight">Backup & Restore</h1>
                    <p class="text-sm md:text-base text-white/90 mt-1">Protect your data with automated backups and easy restoration</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Database Info Card -->
    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-2">Database Information</h2>
                <div class="flex flex-wrap gap-3 sm:gap-6">
                    <div class="flex items-center">
                        <span class="text-xs sm:text-sm text-gray-600">Database:</span>
                        <span class="font-semibold text-gray-900 ml-1 sm:ml-2 text-sm sm:text-base truncate max-w-[120px] sm:max-w-none">{{ $dbInfo['name'] }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-xs sm:text-sm text-gray-600">Size:</span>
                        <span class="font-semibold text-gray-900 ml-1 sm:ml-2 text-sm sm:text-base">{{ $dbInfo['size'] }}</span>
                    </div>
                </div>
            </div>
            <button onclick="createBackup()" id="createBackupBtn" class="bg-primary-600 hover:bg-primary-700 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg font-semibold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg w-full sm:w-auto text-sm sm:text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Backup Now</span>
            </button>
        </div>
    </div>

    <!-- Auto Backup Settings -->
    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 mb-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Automatic Backup Settings</h2>
        <div class="space-y-4">
            <!-- Schedule Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Backup Schedule</label>
                <p class="text-xs text-gray-600 mb-3">Select how often automatic backups should be created</p>
                <select id="backupSchedule" class="block w-full md:w-80 text-sm font-semibold text-gray-900 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-primary-600 p-2.5 sm:p-3 bg-white">
                    <option value="daily">Daily (Every day at midnight)</option>
                    <option value="weekly" selected>Weekly (Every Monday at midnight)</option>
                    <option value="monthly">Monthly (1st day of month)</option>
                    <option value="quarterly">Quarterly (Every 3 months)</option>
                    <option value="yearly">Yearly (January 1st)</option>
                </select>
            </div>
            
            <!-- Next Backup Info + Save Button aligned -->
            <div class="bg-gray-50 rounded-lg p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="flex flex-wrap items-center gap-1 sm:gap-2 text-xs sm:text-sm">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-gray-700">Next backup:</span>
                    <span id="nextBackupDate" class="font-semibold text-gray-900">Loading...</span>
                </div>
                <button onclick="saveScheduleSettings()" id="saveSettingsBtn" class="bg-primary-600 hover:bg-primary-700 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg w-full sm:w-auto text-sm">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Settings
                </button>
            </div>
        </div>
    </div>

    <!-- Restore Section -->
    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 mb-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Restore from Backup</h2>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 sm:p-4 mb-4">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs sm:text-sm text-yellow-800 font-semibold">
                    Warning: Restoring will replace all current data. A safety backup will be created automatically before restore. You will need to login again after restore.
                </p>
            </div>
        </div>
        <form id="restoreForm" class="flex flex-col sm:flex-row gap-4 sm:items-end">
            <div class="w-full sm:flex-1 sm:min-w-[200px]">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Backup File (.zip)</label>
                <input type="file" name="backup_file" id="backupFile" accept=".zip" class="block w-full text-xs sm:text-sm font-semibold text-gray-900 border-2 border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:border-primary-500 p-2 sm:p-2.5 bg-white">
            </div>
            <button type="button" onclick="showRestoreConfirmation()" style="background-color: #ea580c;" class="hover:bg-orange-700 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-lg font-bold flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg w-full sm:w-auto text-sm sm:text-base">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="font-bold">Restore</span>
            </button>
        </form>
    </div>

    <!-- Existing Backups -->
    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Existing Backups ({{ count($backups) }})</h2>
        
        @if(count($backups) > 0)
            <div class="grid gap-3 sm:gap-4" id="backupsList">
                @foreach($backups as $backup)
                <div class="backup-card border border-gray-200 rounded-lg p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                        <div class="bg-purple-100 rounded-lg p-2 sm:p-3 flex-shrink-0">
                            @if($backup['is_safety'])
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $backup['name'] }}</h3>
                            <div class="flex flex-wrap gap-1 sm:gap-4 text-xs sm:text-sm text-gray-600 mt-1">
                                <span>{{ $backup['date'] }}</span>
                                <span class="hidden sm:inline">•</span>
                                <span>{{ $backup['size'] }}</span>
                                @if($backup['is_safety'])
                                    <span class="bg-orange-100 text-orange-800 px-1.5 sm:px-2 py-0.5 rounded text-[10px] sm:text-xs font-semibold">Safety</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 w-full sm:w-auto sm:flex sm:gap-2">
                        <button onclick="showServerRestoreConfirmation('{{ $backup['name'] }}')" style="background-color: #ea580c;" class="hover:bg-orange-700 text-white px-2 sm:px-4 py-2 rounded-lg font-semibold transition-colors inline-flex items-center justify-center gap-1 sm:gap-2 text-xs sm:text-sm">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span class="hidden sm:inline">Restore</span>
                        </button>
                        <a href="{{ route('backup.download', $backup['name']) }}" style="background-color: #2563eb;" class="hover:bg-blue-700 text-white px-2 sm:px-4 py-2 rounded-lg font-semibold transition-colors inline-flex items-center justify-center gap-1 sm:gap-2 text-xs sm:text-sm">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="hidden sm:inline">Download</span>
                        </a>
                        <button onclick="deleteBackup('{{ $backup['name'] }}')" style="background-color: #dc2626;" class="hover:bg-red-700 text-white px-2 sm:px-4 py-2 rounded-lg font-semibold transition-colors inline-flex items-center justify-center gap-1 sm:gap-2 text-xs sm:text-sm">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span class="hidden sm:inline">Delete</span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-lg font-medium">No backups available</p>
                <p class="text-sm mt-1">Create your first backup to get started</p>
            </div>
        @endif
    </div>
</div>

<!-- Loading Modal -->
<div id="loadingModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-8 max-w-md mx-4">
        <div class="text-center">
            <div class="mx-auto mb-4 border-4 border-gray-200 border-t-primary-600 rounded-full w-12 h-12 animate-spin"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2" id="loadingTitle">Processing...</h3>
            <p class="text-gray-600 font-medium" id="loadingMessage">Please wait while we process your request.</p>
            <div class="mt-4 bg-gray-200 rounded-full h-2 overflow-hidden">
                <div id="progressBar" class="bg-primary-600 h-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal with RESTORE typing -->
<div id="restoreConfirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-orange-100 rounded-full p-3">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Confirm Restore</h3>
        </div>
        <p class="text-gray-700 font-medium mb-4">
            Are you sure you want to restore from this backup? This will:
        </p>
        <ul class="list-disc list-inside text-sm text-gray-700 mb-4 space-y-1 font-medium">
            <li>Replace all current database data</li>
            <li>Create a safety backup of current state</li>
            <li>Restore configuration settings</li>
            <li>Clear all caches</li>
            <li class="text-orange-700 font-semibold">Require you to login again (like a system restart)</li>
        </ul>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-red-700 font-semibold mb-2">
                ⚠️ This action is irreversible. Type <span class="font-mono bg-red-100 px-1 rounded">RESTORE</span> to confirm:
            </p>
            <input type="text" id="restoreConfirmInput" 
                   class="w-full border-2 border-red-300 rounded-lg p-2 text-center font-mono text-lg focus:outline-none focus:border-red-500"
                   placeholder="Type RESTORE here"
                   oninput="checkRestoreConfirmation()">
        </div>
        <p id="restoreFileName" class="text-sm text-gray-600 mb-4 text-center font-medium"></p>
        <div class="flex gap-3">
            <button onclick="closeRestoreConfirmation()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg font-bold transition-colors">
                Cancel
            </button>
            <button id="confirmRestoreBtn" onclick="performRestore()" style="background-color: #ea580c;" 
                    class="flex-1 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-bold transition-colors opacity-50 cursor-not-allowed" disabled>
                Restore Now
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4">
        <div class="flex items-center gap-3 mb-4">
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Delete Backup</h3>
        </div>
        <p class="text-gray-700 font-medium mb-4">
            Are you sure you want to delete this backup file?
        </p>
        <p class="text-sm text-gray-600 mb-6">
            <strong id="deleteFileName" class="text-gray-900"></strong>
        </p>
        <p class="text-sm text-red-600 font-semibold mb-6">
            ⚠️ This action cannot be undone.
        </p>
        <div class="flex gap-3">
            <button onclick="closeDeleteConfirmation()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 px-4 py-2 rounded-lg font-bold transition-colors">
                Cancel
            </button>
            <button id="confirmDeleteBtn" onclick="confirmDelete()" style="background-color: #dc2626;" class="flex-1 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-bold transition-colors">
                Delete Backup
            </button>
        </div>
    </div>
</div>

<script>
// Get CSRF token
const backupCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Toast queue to prevent spam
let toastTimeout = null;
let activeToast = null;

// Settings state
let currentSettings = {
    schedule: 'weekly',
    next_backup_at: null
};

// Track original value to detect changes
let originalSchedule = 'weekly';

// Restore state
let pendingRestoreFile = null;
let isRestoreFromServer = false;

// Load settings on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBackupSettings();
    
    // Add change listener to schedule dropdown
    const scheduleSelect = document.getElementById('backupSchedule');
    scheduleSelect.addEventListener('change', function() {
        checkForChanges();
    });
});

// Check if settings have changed and update button state
function checkForChanges() {
    const scheduleSelect = document.getElementById('backupSchedule');
    const saveBtn = document.getElementById('saveSettingsBtn');
    
    const hasChanged = scheduleSelect.value !== originalSchedule;
    
    if (hasChanged) {
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

// Load backup settings from server
async function loadBackupSettings() {
    const saveBtn = document.getElementById('saveSettingsBtn');
    // Initially disable the button until settings are loaded and changed
    saveBtn.disabled = true;
    saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
    
    try {
        const response = await fetch('/backup/settings', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': backupCsrfToken
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            const settings = data.settings || data;
            currentSettings = {
                schedule: settings.schedule,
                next_backup_at: settings.next_backup_at
            };
            // Store original value for comparison
            originalSchedule = settings.schedule;
            applySettingsToUI(settings);
        }
    } catch (error) {
        console.error('Failed to load backup settings:', error);
        originalSchedule = 'weekly';
        applySettingsToUI({ schedule: 'weekly', next_backup_at: null });
    }
}

// Apply settings to UI elements
function applySettingsToUI(settings) {
    const scheduleSelect = document.getElementById('backupSchedule');
    const nextBackupDate = document.getElementById('nextBackupDate');
    
    scheduleSelect.value = settings.schedule;
    
    if (settings.next_backup_at) {
        // The API returns formatted date string like "December 08, 2025 12:00 AM"
        nextBackupDate.textContent = settings.next_backup_at;
    } else {
        nextBackupDate.textContent = 'Not scheduled yet - save settings to schedule';
    }
}

// Save schedule settings
async function saveScheduleSettings() {
    const scheduleSelect = document.getElementById('backupSchedule');
    const saveBtn = document.getElementById('saveSettingsBtn');
    
    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
    saveBtn.innerHTML = '<div class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Saving...';
    
    currentSettings.schedule = scheduleSelect.value;
    
    try {
        const response = await fetch('/backup/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': backupCsrfToken
            },
            body: JSON.stringify({
                schedule: currentSettings.schedule,
                enabled: true
            })
        });
        
        if (!response.ok) {
            throw new Error('Failed to save settings');
        }
        
        const data = await response.json();
        const settings = data.settings || data;
        
        // Update next backup date display
        if (settings.next_backup_at) {
            document.getElementById('nextBackupDate').textContent = settings.next_backup_at;
        }
        
        // Update original value since save was successful
        originalSchedule = scheduleSelect.value;
        
        // Show toast notification only
        showToast('Backup schedule saved successfully!', 'success');
        
    } catch (error) {
        console.error('Failed to save backup settings:', error);
        showToast('Failed to save settings. Please try again.', 'error');
        // Re-enable button on error so user can try again
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } finally {
        // Restore button text
        saveBtn.innerHTML = '<svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Save Settings';
        // Check if button should stay disabled (when no changes)
        checkForChanges();
    }
}

// Create backup
function createBackup() {
    const btn = document.getElementById('createBackupBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<div class="inline-block w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span class="ml-2">Creating...</span>';
    
    showLoading('Creating Backup', 'Exporting database and compressing files...');
    
    fetch('{{ route('backup.create') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': backupCsrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('Backup created successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to create backup', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        hideLoading();
        showToast('An error occurred: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Delete backup
function deleteBackup(filename) {
    // Store filename in button's data attribute for reliability
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    confirmBtn.setAttribute('data-filename', filename);
    
    // Display filename in modal
    document.getElementById('deleteFileName').textContent = filename;
    
    // Show modal
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

function closeDeleteConfirmation() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
}

function confirmDelete() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const filename = confirmBtn.getAttribute('data-filename');
    
    if (!filename) {
        showToast('Error: No file selected', 'error');
        return;
    }
    
    closeDeleteConfirmation();
    
    fetch(`{{ url('/backup/delete') }}/${filename}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': backupCsrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Backup deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to delete backup', 'error');
        }
    })
    .catch(error => {
        showToast('An error occurred: ' + error.message, 'error');
    });
}

// Check RESTORE confirmation input
function checkRestoreConfirmation() {
    const input = document.getElementById('restoreConfirmInput');
    const confirmBtn = document.getElementById('confirmRestoreBtn');
    
    if (input.value === 'RESTORE') {
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        confirmBtn.disabled = true;
        confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

// Show restore confirmation (from file upload)
function showRestoreConfirmation() {
    const fileInput = document.getElementById('backupFile');
    
    if (!fileInput.files.length) {
        showToast('Please select a backup file first', 'error');
        return;
    }
    
    pendingRestoreFile = fileInput.files[0];
    isRestoreFromServer = false;
    
    document.getElementById('restoreFileName').textContent = 'File: ' + pendingRestoreFile.name;
    document.getElementById('restoreConfirmInput').value = '';
    document.getElementById('confirmRestoreBtn').disabled = true;
    document.getElementById('confirmRestoreBtn').classList.add('opacity-50', 'cursor-not-allowed');
    document.getElementById('restoreConfirmModal').classList.remove('hidden');
}

// Show restore confirmation from server backup
function showServerRestoreConfirmation(filename) {
    pendingRestoreFile = filename;
    isRestoreFromServer = true;
    
    document.getElementById('restoreFileName').textContent = 'Server backup: ' + filename;
    document.getElementById('restoreConfirmInput').value = '';
    document.getElementById('confirmRestoreBtn').disabled = true;
    document.getElementById('confirmRestoreBtn').classList.add('opacity-50', 'cursor-not-allowed');
    document.getElementById('restoreConfirmModal').classList.remove('hidden');
}

// Close restore confirmation
function closeRestoreConfirmation() {
    document.getElementById('restoreConfirmModal').classList.add('hidden');
    document.getElementById('restoreConfirmInput').value = '';
    pendingRestoreFile = null;
    isRestoreFromServer = false;
}

// Perform restore
function performRestore() {
    if (!pendingRestoreFile) {
        showToast('No backup file selected', 'error');
        return;
    }
    
    // IMPORTANT: Save values BEFORE closing modal (which resets them)
    const restoreFile = pendingRestoreFile;
    const isServerRestore = isRestoreFromServer;
    
    closeRestoreConfirmation();
    showLoading('Restoring Backup', 'This may take a few minutes. Please do not close this window...');
    
    if (isServerRestore) {
        // Restore from server backup
        const restoreUrl = `{{ url('/backup/restore-from-server') }}/${restoreFile}`;
        
        fetch(restoreUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': backupCsrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error. Please try again.');
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Show success message with login redirect notice
                showToast(data.message || 'Restore completed successfully!', 'success');
                
                // Redirect to login page since restore requires re-authentication
                setTimeout(() => {
                    window.location.href = '{{ route('login') }}';
                }, 2500);
            } else {
                showToast(data.message || 'Failed to restore backup', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showToast('An error occurred: ' + error.message, 'error');
        });
    } else {
        // Restore from uploaded file
        const formData = new FormData();
        formData.append('backup_file', restoreFile);
        
        fetch('{{ route('backup.restore') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': backupCsrfToken
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error. Please try again.');
            }
            return response.json();
        })
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Show success message with login redirect notice
                showToast(data.message || 'Restore completed successfully!', 'success');
                
                // Redirect to login page since restore requires re-authentication
                setTimeout(() => {
                    window.location.href = '{{ route('login') }}';
                }, 2500);
            } else {
                showToast(data.message || 'Failed to restore backup', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            showToast('An error occurred: ' + error.message, 'error');
        });
    }
}

// Show loading modal
function showLoading(title, message) {
    document.getElementById('loadingTitle').textContent = title;
    document.getElementById('loadingMessage').textContent = message;
    document.getElementById('loadingModal').classList.remove('hidden');
    
    // Simulate progress
    let progress = 0;
    const progressBar = document.getElementById('progressBar');
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        progressBar.style.width = progress + '%';
    }, 500);
    
    // Store interval ID to clear later
    window.backupLoadingInterval = interval;
}

// Hide loading modal
function hideLoading() {
    if (window.backupLoadingInterval) {
        clearInterval(window.backupLoadingInterval);
    }
    document.getElementById('progressBar').style.width = '100%';
    setTimeout(() => {
        document.getElementById('loadingModal').classList.add('hidden');
        document.getElementById('progressBar').style.width = '0%';
    }, 300);
}

// Show toast notification (styled like system notifications)
function showToast(message, type = 'info') {
    // Remove any existing toast first
    if (activeToast) {
        activeToast.remove();
        activeToast = null;
    }
    
    // Clear any existing timeout
    if (toastTimeout) {
        clearTimeout(toastTimeout);
        toastTimeout = null;
    }
    
    // Determine icon and colors based on type
    let icon, bgColor, borderColor, iconBg;
    if (type === 'success') {
        bgColor = 'bg-white';
        borderColor = 'border-green-500';
        iconBg = 'bg-green-100';
        icon = `<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`;
    } else if (type === 'error') {
        bgColor = 'bg-white';
        borderColor = 'border-red-500';
        iconBg = 'bg-red-100';
        icon = `<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`;
    } else {
        bgColor = 'bg-white';
        borderColor = 'border-blue-500';
        iconBg = 'bg-blue-100';
        icon = `<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>`;
    }
    
    const title = type === 'success' ? 'Success' : type === 'error' ? 'Error' : 'Info';
    
    const toast = document.createElement('div');
    toast.className = `fixed top-6 right-6 ${bgColor} border-l-4 ${borderColor} rounded-lg shadow-xl z-50 transition-all duration-300 max-w-sm`;
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    
    toast.innerHTML = `
        <div class="p-4 flex items-start gap-3">
            <div class="${iconBg} rounded-full p-2 flex-shrink-0">
                ${icon}
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-gray-900 text-sm">${title}</h4>
                <p class="text-gray-600 text-sm mt-0.5">${message}</p>
            </div>
            <button onclick="this.closest('div.fixed').remove()" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    activeToast = toast;
    
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    toastTimeout = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            toast.remove();
            if (activeToast === toast) {
                activeToast = null;
            }
        }, 300);
    }, 4000);
}
</script>
</div>
@endsection
