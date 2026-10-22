// Inventory Manager - bottle-focused workflow
(function () {
    const InventoryManager = {
        currentVaccineId: null,
        currentVaccineName: null,
        isModalOpen: false,
        isManualOverride: false,
        bottleInput: null,
        perBottleInput: null,
        totalDosesInput: null,
        currentStockDisplay: null,
        bottlesPreview: null,
        perBottlePreview: null,
        calculatedPreview: null,
        openModalCount: 0,

        init() {
            this.cacheElements();
            this.hideModalsOnLoad();
            this.registerEvents();
        },

        cacheElements() {
            this.updateModal = document.getElementById('updateModal');
            this.addVaccineModal = document.getElementById('addVaccineModal');
            this.bottleInput = document.getElementById('bottleCountInput');
            this.perBottleInput = document.getElementById('dosesPerBottleInput');
            this.totalDosesInput = document.getElementById('totalDosesInput');
            this.currentStockDisplay = document.getElementById('currentStockDisplay');
            this.bottlesPreview = document.getElementById('bottlesPreview');
            this.perBottlePreview = document.getElementById('perBottleDisplay');
            this.calculatedPreview = document.getElementById('calculatedTotalDisplay');
        },

        hideModalsOnLoad() {
            this.updateModal?.classList.add('hidden');
            this.addVaccineModal?.classList.add('hidden');
        },

        registerEvents() {
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && this.isModalOpen) {
                    this.closeModal();
                }
            });

            this.bottleInput?.addEventListener('input', () => this.handleBottleOrPerBottleChange());
            this.perBottleInput?.addEventListener('input', () => this.handleBottleOrPerBottleChange());
            this.totalDosesInput?.addEventListener('input', () => this.updatePreview());
        },

        showModal(vaccineId, vaccineName) {
            if (!vaccineId || !this.updateModal) {
                return;
            }

            const row = this.getRowByVaccineId(vaccineId);
            const availableDoses = row ? parseInt(row.dataset.availableDoses || '0', 10) : 0;
            const availableBottles = row ? parseInt(row.dataset.availableBottles || '0', 10) : 0;
            const dosesPerBottle = row ? parseInt(row.dataset.dosesPerBottle || '10', 10) : 10;

            this.currentVaccineId = vaccineId;
            this.currentVaccineName = vaccineName;
            this.isModalOpen = true;
            this.isManualOverride = false;
            this.lockBodyScroll();

            const manualOverrideToggle = document.getElementById('manualOverrideToggle');
            if (manualOverrideToggle) {
                manualOverrideToggle.checked = false;
            }

            if (this.currentStockDisplay) {
                this.currentStockDisplay.textContent = availableDoses;
            }

            if (this.bottleInput) {
                this.bottleInput.value = availableBottles;
            }

            if (this.perBottleInput) {
                this.perBottleInput.value = dosesPerBottle;
            }

            const defaultTotal = availableBottles * dosesPerBottle;
            if (this.totalDosesInput) {
                this.totalDosesInput.value = availableDoses || defaultTotal;
            }

            this.updateModalTitle();
            this.updatePreview();
            this.updateModal.classList.remove('hidden');
        },

        updateModalTitle() {
            const titleEl = document.getElementById('modalTitle');
            if (!titleEl) {
                return;
            }
            titleEl.textContent = `Update ${this.currentVaccineName || ''}`;
        },

        closeModal() {
            this.updateModal?.classList.add('hidden');
            this.isModalOpen = false;
            this.currentVaccineId = null;
            this.currentVaccineName = null;
            this.unlockBodyScroll();
        },

        handleBottleOrPerBottleChange() {
            const bottles = this.getBottleCount();
            const perBottle = this.getDosesPerBottle();
            
            if (!this.isManualOverride && this.totalDosesInput) {
                this.totalDosesInput.value = bottles * perBottle;
            }
            
            this.updatePreview();
        },

        updatePreview() {
            const bottles = this.getBottleCount();
            const perBottle = this.getDosesPerBottle();
            const total = this.getTotalDoses();

            if (this.bottlesPreview) {
                this.bottlesPreview.textContent = bottles;
            }
            if (this.perBottlePreview) {
                this.perBottlePreview.textContent = perBottle;
            }
            if (this.calculatedPreview) {
                this.calculatedPreview.textContent = total;
            }
        },

        getBottleCount() {
            return Math.max(0, parseInt(this.bottleInput?.value || '0', 10));
        },

        getDosesPerBottle() {
            return Math.max(1, parseInt(this.perBottleInput?.value || '10', 10));
        },

        getTotalDoses() {
            return Math.max(0, parseInt(this.totalDosesInput?.value || '0', 10));
        },

        saveStock() {
            if (!this.currentVaccineId) {
                this.showToast('Error', 'No vaccine selected.', 'error');
                return;
            }

            const availableBottles = this.getBottleCount();
            const dosesPerBottle = this.getDosesPerBottle();
            const totalDoses = this.getTotalDoses();

            const saveButton = document.getElementById('saveStockBtn');
            const originalText = saveButton?.innerHTML;

            if (saveButton) {
                saveButton.innerHTML = '<svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
                saveButton.disabled = true;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch(`/inventory/update/${this.currentVaccineId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    available_bottles: availableBottles,
                    doses_per_bottle: dosesPerBottle,
                    available_doses: totalDoses
                })
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        this.updateRowDisplay(data);
                        this.closeModal();
                        this.showToast('Success', `Stock updated from ${data.old_stock} to ${data.new_stock}!`, 'success');
                    } else {
                        this.showToast('Error', data.message || 'Failed to update stock.', 'error');
                    }
                })
                .catch((error) => {
                    this.showToast('Error', 'An error occurred while updating stock.', 'error');
                })
                .finally(() => {
                    if (saveButton) {
                        saveButton.innerHTML = originalText || 'Update Stock';
                        saveButton.disabled = false;
                    }
                });
        },

        updateRowDisplay(data) {
            const row = this.getRowByVaccineId(data.vaccine_id);
            if (!row) {
                return;
            }

            row.dataset.availableDoses = data.available_doses;
            row.dataset.availableBottles = data.available_bottles;
            row.dataset.dosesPerBottle = data.doses_per_bottle;

            const dosesCell = row.querySelector('.available-doses-cell');
            if (dosesCell) {
                dosesCell.innerHTML = `
                    <div class="text-xl font-bold text-primary-700">${data.available_doses} doses</div>
                `;
            }

            const bottlesCell = row.querySelector('.available-bottles-cell');
            if (bottlesCell) {
                bottlesCell.innerHTML = `
                    <div class="text-lg font-semibold">${data.available_bottles} bottles</div>
                `;
            }

            const dosesPerBottleCell = row.querySelector('.doses-per-bottle-cell');
            if (dosesPerBottleCell) {
                dosesPerBottleCell.innerHTML = `
                    <div class="text-lg font-semibold">${data.doses_per_bottle}</div>
                `;
            }

            const statusChip = row.querySelector('.status-chip');
            if (statusChip) {
                statusChip.outerHTML = this.buildStatusChipHtml(data.status);
            }

            const updateBtn = row.querySelector('.update-stock-btn');
            if (updateBtn) {
                updateBtn.setAttribute('onclick', `showUpdateModal(${data.vaccine_id}, '${(data.vaccine_name || '').replace(/'/g, "\\'")}')`);
            }
        },

        buildStatusChipHtml(status) {
            const iconClass = 'w-5 h-5';
            if (status === 'out') {
                return `
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-red-100 text-red-700 status-chip">
                    <svg class="${iconClass}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    Out of Stock
                </span>`;
            }

            if (status === 'low') {
                return `
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-yellow-100 text-yellow-700 status-chip">
                    <svg class="${iconClass}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Low Stock
                </span>`;
            }

            if (status === 'medium') {
                return `
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-blue-100 text-blue-700 status-chip">
                    <svg class="${iconClass}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                    </svg>
                    Medium Stock
                </span>`;
            }

            return `
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-base font-bold bg-green-100 text-green-700 status-chip">
                <svg class="${iconClass}" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                High Stock
            </span>`;
        },

        getRowByVaccineId(vaccineId) {
            return document.querySelector(`.inventory-row[data-vaccine-id="${vaccineId}"]`);
        },

        showToast(title, message, type) {
            const container = document.getElementById('toastContainer');
            if (!container) {
                alert(`${title}: ${message}`);
                return;
            }

            const toast = document.createElement('div');
            toast.className = `flex items-start gap-3 rounded-md bg-white px-4 py-3 shadow-lg ring-1 ${type === 'success' ? 'ring-green-400/40' : 'ring-red-400/40'}`;
            const icon = type === 'success'
                ? '<svg class="w-5 h-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
                : '<svg class="w-5 h-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            toast.innerHTML = `
                <div class="mt-0.5">${icon}</div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-gray-900">${title}</div>
                    <div class="text-sm text-gray-600">${message}</div>
                </div>
                <button class="text-gray-400 hover;text-gray-600" onclick="this.parentElement.remove()">✕</button>
            `;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        },

        showAddVaccineModal() {
            document.getElementById('vaccineNameInput').value = '';
            document.getElementById('vaccineDescriptionInput').value = '';
            const acronymEl = document.getElementById('vaccineAcronymInput');
            if (acronymEl) acronymEl.value = '';
            const totalDosesEl = document.getElementById('addTotalDosesScheduleInput');
            if (totalDosesEl) totalDosesEl.value = '1';
            const ageGroupEl = document.getElementById('addTargetAgeGroupInput');
            if (ageGroupEl) ageGroupEl.value = 'under_1_year';
            const isStandardEl = document.getElementById('addIsStandardInput');
            if (isStandardEl) isStandardEl.checked = false;
            const isActiveEl = document.getElementById('addIsActiveReportsInput');
            if (isActiveEl) isActiveEl.checked = false;
            document.getElementById('initialBottlesInput').value = '0';
            document.getElementById('addDosesPerBottleInput').value = '10';
            this.renderAddDoseWindows(1);
            this.updateAddPreview();
            this.addVaccineModal?.classList.remove('hidden');
            this.isModalOpen = true;
            this.lockBodyScroll();
        },

        closeAddVaccineModal() {
            this.addVaccineModal?.classList.add('hidden');
            this.isModalOpen = false;
            this.unlockBodyScroll();
        },

        changeInitialBottles(amount) {
            const bottlesInput = document.getElementById('initialBottlesInput');
            let currentBottles = parseInt(bottlesInput.value || '0', 10);
            currentBottles = Math.max(0, currentBottles + amount);
            bottlesInput.value = currentBottles;
            this.updateAddPreview();
        },

        updateAddPreview() {
            const bottles = Math.max(0, parseInt(document.getElementById('initialBottlesInput')?.value || '0', 10));
            const perBottle = Math.max(1, parseInt(document.getElementById('addDosesPerBottleInput')?.value || '10', 10));
            const total = bottles * perBottle;

            const bottlesPreview = document.getElementById('addBottlesPreview');
            const perBottlePreview = document.getElementById('addPerBottlePreview');
            const totalPreview = document.getElementById('addTotalPreview');

            if (bottlesPreview) bottlesPreview.textContent = bottles;
            if (perBottlePreview) perBottlePreview.textContent = perBottle;
            if (totalPreview) totalPreview.textContent = total;
        },

        renderAddDoseWindows(totalDoses) {
            const container = document.getElementById('addDoseWindowsContainer');
            if (!container) return;
            container.innerHTML = '';
            for (let i = 1; i <= totalDoses; i++) {
                container.insertAdjacentHTML('beforeend', `
                    <div class="grid grid-cols-3 gap-3 items-end">
                        <div class="text-sm font-semibold text-gray-700 pb-2">Dose ${i}</div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Min age (months)</label>
                            <input type="number" min="0" value="0" id="addDoseMin_${i}"
                                   class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-200" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Max age (months)</label>
                            <input type="number" min="0" value="0" id="addDoseMax_${i}"
                                   class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-200" />
                        </div>
                    </div>
                `);
            }
        },

        saveNewVaccine() {
            const vaccineName = document.getElementById('vaccineNameInput').value.trim();
            const vaccineDescription = document.getElementById('vaccineDescriptionInput').value.trim();
            const acronym = document.getElementById('vaccineAcronymInput')?.value.trim() || '';
            const totalDosesSchedule = Math.max(1, Math.min(10, parseInt(document.getElementById('addTotalDosesScheduleInput')?.value || '1', 10)));
            const targetAgeGroup = document.getElementById('addTargetAgeGroupInput')?.value || 'under_1_year';
            const isStandard = document.getElementById('addIsStandardInput')?.checked || false;
            const isActiveReports = document.getElementById('addIsActiveReportsInput')?.checked || false;
            const initialBottles = parseInt(document.getElementById('initialBottlesInput').value || '0', 10);
            const dosesPerBottle = parseInt(document.getElementById('addDosesPerBottleInput').value || '10', 10);
            const totalDoses = initialBottles * dosesPerBottle;

            // Collect dose age windows
            const doseMin = {};
            const doseMax = {};
            for (let i = 1; i <= totalDosesSchedule; i++) {
                doseMin[i] = parseFloat(document.getElementById(`addDoseMin_${i}`)?.value || '0');
                doseMax[i] = parseFloat(document.getElementById(`addDoseMax_${i}`)?.value || '0');
            }

            if (!vaccineName) {
                this.showToast('Error', 'Vaccine name is required.', 'error');
                return;
            }

            if (initialBottles < 0) {
                this.showToast('Error', 'Initial bottles cannot be negative.', 'error');
                return;
            }

            if (dosesPerBottle < 1) {
                this.showToast('Error', 'Doses per bottle must be at least 1.', 'error');
                return;
            }

            const saveButton = document.getElementById('addVaccineBtn');
            const originalText = saveButton?.textContent;
            if (saveButton) {
                saveButton.innerHTML = '<svg class="animate-spin w-5 h-5 inline" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adding...';
                saveButton.disabled = true;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            fetch('/inventory/add-vaccine', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    vaccine_name: vaccineName,
                    description: vaccineDescription,
                    acronym: acronym,
                    total_doses: totalDosesSchedule,
                    target_age_group: targetAgeGroup,
                    is_standard: isStandard,
                    is_active_in_reports: isActiveReports,
                    dose_min: doseMin,
                    dose_max: doseMax,
                    available_bottles: initialBottles,
                    doses_per_bottle: dosesPerBottle,
                    stocks: totalDoses
                })
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        this.showToast('Success', `${vaccineName} has been added to the inventory.`, 'success');
                        this.closeAddVaccineModal();
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        this.showToast('Error', data.message || 'Failed to add vaccine.', 'error');
                    }
                })
                .catch((error) => {
                    this.showToast('Error', 'An error occurred while adding the vaccine.', 'error');
                })
                .finally(() => {
                    if (saveButton) {
                        saveButton.textContent = originalText || 'Add Vaccine';
                        saveButton.disabled = false;
                    }
                });
        },

        lockBodyScroll() {
            if (this.openModalCount === 0) {
                document.body.classList.add('modal-open');
            }
            this.openModalCount += 1;
        },

        unlockBodyScroll() {
            this.openModalCount = Math.max(0, this.openModalCount - 1);
            if (this.openModalCount === 0) {
                document.body.classList.remove('modal-open');
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        InventoryManager.init();
    });

    window.showUpdateModal = (vaccineId, vaccineName) => InventoryManager.showModal(vaccineId, vaccineName);
    window.closeUpdateModal = () => InventoryManager.closeModal();
    window.saveStock = () => InventoryManager.saveStock();

    window.showAddVaccineModal = () => InventoryManager.showAddVaccineModal();
    window.closeAddVaccineModal = () => InventoryManager.closeAddVaccineModal();
    window.changeInitialBottles = (amount) => InventoryManager.changeInitialBottles(amount);
    window.saveNewVaccine = () => InventoryManager.saveNewVaccine();

    window.updateAddDoseWindows = () => {
        const totalDoses = Math.max(1, Math.min(10, parseInt(document.getElementById('addTotalDosesScheduleInput')?.value || '1', 10)));
        InventoryManager.renderAddDoseWindows(totalDoses);
    };

    window.setDosesPerBottle = (value) => {
        const input = document.getElementById('dosesPerBottleInput');
        if (input) {
            input.value = value;
            InventoryManager.handleBottleOrPerBottleChange();
            
            // Update preset button styles
            document.querySelectorAll('.preset-btn').forEach(btn => {
                btn.classList.remove('border-primary-500', 'bg-primary-50', 'text-primary-700');
                btn.classList.add('border-gray-300', 'bg-white');
            });
            event.target.classList.remove('border-gray-300', 'bg-white');
            event.target.classList.add('border-primary-500', 'bg-primary-50', 'text-primary-700');
        }
    };

    window.setAddDosesPerBottle = (value) => {
        const input = document.getElementById('addDosesPerBottleInput');
        if (input) {
            input.value = value;
            InventoryManager.updateAddPreview();
            
            // Update preset button styles
            document.querySelectorAll('.add-preset-btn').forEach(btn => {
                btn.classList.remove('border-primary-500', 'bg-primary-50', 'text-primary-700');
                btn.classList.add('border-gray-300', 'bg-white');
            });
            event.target.classList.remove('border-gray-300', 'bg-white');
            event.target.classList.add('border-primary-500', 'bg-primary-50', 'text-primary-700');
        }
    };

    window.toggleManualOverride = () => {
        const toggle = document.getElementById('manualOverrideToggle');
        const input = document.getElementById('totalDosesInput');
        const warning = document.getElementById('overrideWarning');
        
        if (toggle && input && warning) {
            InventoryManager.isManualOverride = toggle.checked;
            
            if (toggle.checked) {
                // Enable manual mode
                input.removeAttribute('readonly');
                input.classList.remove('bg-gray-50', 'text-gray-700', 'cursor-not-allowed');
                input.classList.add('bg-white', 'focus:border-primary-500', 'focus:ring-2', 'focus:ring-primary-500');
                warning.classList.remove('hidden');
            } else {
                // Disable manual mode - recalculate
                input.setAttribute('readonly', 'readonly');
                input.classList.add('bg-gray-50', 'text-gray-700', 'cursor-not-allowed');
                input.classList.remove('bg-white', 'focus:border-primary-500', 'focus:ring-2', 'focus:ring-primary-500');
                warning.classList.add('hidden');
                
                // Recalculate from bottles × per bottle
                const bottles = InventoryManager.getBottleCount();
                const perBottle = InventoryManager.getDosesPerBottle();
                input.value = bottles * perBottle;
                InventoryManager.updatePreview();
            }
        }
    };

    // Add event listeners for Add Vaccine modal inputs
    document.addEventListener('DOMContentLoaded', () => {
        const addBottlesInput = document.getElementById('initialBottlesInput');
        const addPerBottleInput = document.getElementById('addDosesPerBottleInput');
        
        if (addBottlesInput) {
            addBottlesInput.addEventListener('input', () => InventoryManager.updateAddPreview());
        }
        if (addPerBottleInput) {
            addPerBottleInput.addEventListener('input', () => InventoryManager.updateAddPreview());
        }
    });
})();
