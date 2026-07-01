"use strict";
// ============================================================
//  AbleCare – Healthcare Provider Registration Wizard
//  Compiled to ../js/register-provider.js (see web-app/tsconfig.json)
// ============================================================
(function init() {
    const TOTAL_STEPS = 5;
    const disabilityCategories = window.ABLECARE_DISABILITY_CATEGORIES;
    let currentStep = 1;
    let specializations = [{ disabilityCategory: '', specificCondition: '' }];
    const form = document.getElementById('providerForm');
    const backBtn = document.getElementById('backBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressFill = document.getElementById('stepProgressFill');
    const formErrorBanner = document.getElementById('formErrorBanner');
    const specializationsList = document.getElementById('specializationsList');
    const addSpecializationBtn = document.getElementById('addSpecializationBtn');
    const reviewSummary = document.getElementById('reviewSummary');
    const loginRow = document.getElementById('loginRow');
    function byId(id) {
        const el = document.getElementById(id);
        if (!el)
            throw new Error(`Missing element #${id}`);
        return el;
    }
    function setError(field, message) {
        const el = document.querySelector(`[data-error-for="${field}"]`);
        if (el)
            el.textContent = message;
    }
    function clearErrors(step) {
        const section = document.querySelector(`.wizard-step[data-step="${step}"]`);
        if (!section)
            return;
        section.querySelectorAll('[data-error-for]').forEach((el) => {
            el.textContent = '';
        });
    }
    function clearFormBanner() {
        formErrorBanner.style.display = 'none';
        formErrorBanner.innerHTML = '';
    }
    function showFormErrors(errors) {
        formErrorBanner.innerHTML = '<ul>' + errors.map((e) => `<li>${escapeHtml(e)}</li>`).join('') + '</ul>';
        formErrorBanner.style.display = '';
    }
    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value;
        return div.innerHTML;
    }
    // ── Step navigation ─────────────────────────────────────────────────────
    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach((section) => {
            section.hidden = section.dataset.step !== String(step);
        });
        const wizardNav = byId('wizardNav');
        if (step === 'success') {
            wizardNav.style.display = 'none';
            loginRow.style.display = 'none';
            return;
        }
        document.querySelectorAll('[data-step-dot]').forEach((dot) => {
            const dotStep = Number(dot.dataset.stepDot);
            dot.classList.remove('active', 'done');
            if (dotStep < step)
                dot.classList.add('done');
            if (dotStep === step)
                dot.classList.add('active');
        });
        progressFill.style.width = `${((step - 1) / (TOTAL_STEPS - 1)) * 100}%`;
        backBtn.disabled = step === 1;
        nextBtn.hidden = step === TOTAL_STEPS;
        submitBtn.hidden = step !== TOTAL_STEPS;
        if (step === TOTAL_STEPS)
            renderReview();
    }
    // ── Step 4: specializations ─────────────────────────────────────────────
    function renderSpecializations() {
        specializationsList.innerHTML = '';
        specializations.forEach((spec, index) => {
            const row = document.createElement('div');
            row.className = 'specialization-row';
            const categoryField = document.createElement('div');
            categoryField.className = 'field';
            const categoryLabel = document.createElement('label');
            categoryLabel.textContent = 'Disability Category';
            const categorySelect = document.createElement('select');
            categorySelect.dataset.specIndex = String(index);
            categorySelect.dataset.specField = 'category';
            const placeholderOpt = document.createElement('option');
            placeholderOpt.value = '';
            placeholderOpt.textContent = 'Select category';
            placeholderOpt.disabled = true;
            placeholderOpt.selected = spec.disabilityCategory === '';
            categorySelect.appendChild(placeholderOpt);
            Object.keys(disabilityCategories).forEach((key) => {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = disabilityCategories[key];
                opt.selected = spec.disabilityCategory === key;
                categorySelect.appendChild(opt);
            });
            categorySelect.addEventListener('change', () => {
                specializations[index].disabilityCategory = categorySelect.value;
            });
            categoryField.appendChild(categoryLabel);
            categoryField.appendChild(categorySelect);
            const conditionField = document.createElement('div');
            conditionField.className = 'field';
            const conditionLabel = document.createElement('label');
            conditionLabel.textContent = 'Specific Condition';
            const conditionInput = document.createElement('input');
            conditionInput.type = 'text';
            conditionInput.placeholder = 'e.g., Cerebral Palsy, Low Vision';
            conditionInput.value = spec.specificCondition;
            conditionInput.dataset.specIndex = String(index);
            conditionInput.dataset.specField = 'condition';
            conditionInput.addEventListener('input', () => {
                specializations[index].specificCondition = conditionInput.value;
            });
            conditionField.appendChild(conditionLabel);
            conditionField.appendChild(conditionInput);
            row.appendChild(categoryField);
            row.appendChild(conditionField);
            if (specializations.length > 1) {
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'remove-specialization-btn';
                removeBtn.textContent = '✕';
                removeBtn.addEventListener('click', () => {
                    specializations.splice(index, 1);
                    renderSpecializations();
                });
                row.appendChild(removeBtn);
            }
            specializationsList.appendChild(row);
        });
    }
    addSpecializationBtn.addEventListener('click', () => {
        specializations.push({ disabilityCategory: '', specificCondition: '' });
        renderSpecializations();
    });
    // ── Collecting form state ───────────────────────────────────────────────
    function collectState() {
        return {
            account: {
                firstName: byId('first_name').value.trim(),
                lastName: byId('last_name').value.trim(),
                email: byId('email').value.trim(),
                password: byId('password').value,
                confirmPassword: byId('confirm_password').value,
                phoneNumber: byId('phone_number').value.trim(),
            },
            professional: {
                clinicName: byId('clinic_name').value.trim(),
                licenseNumber: byId('license_number').value.trim(),
            },
            location: {
                address: byId('address').value.trim(),
                barangay: byId('barangay').value,
                operatingHours: byId('operating_hours').value.trim(),
                acceptsWalkIns: byId('accepts_walk_ins').checked,
                wheelchairAccessible: byId('wheelchair_accessible').checked,
                groundFloorAccess: byId('ground_floor_access').checked,
            },
            specializations,
            confirmAccurate: byId('confirm_accurate').checked,
        };
    }
    // ── Validation ───────────────────────────────────────────────────────────
    function validateStep(step) {
        clearErrors(step);
        const state = collectState();
        let valid = true;
        function fail(field, message) {
            setError(field, message);
            valid = false;
        }
        if (step === 1) {
            if (state.account.firstName === '')
                fail('first_name', 'First name is required.');
            if (state.account.lastName === '')
                fail('last_name', 'Last name is required.');
            if (state.account.email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.account.email)) {
                fail('email', 'A valid email address is required.');
            }
            if (state.account.password.length < 8) {
                fail('password', 'Password must be at least 8 characters.');
            }
            if (state.account.password !== state.account.confirmPassword) {
                fail('confirm_password', 'Passwords do not match.');
            }
        }
        if (step === 2) {
            if (state.professional.clinicName === '')
                fail('clinic_name', 'Clinic / practice name is required.');
            if (state.professional.licenseNumber === '')
                fail('license_number', 'License number is required.');
            const prcFile = byId('prc_id').files;
            if (!prcFile || prcFile.length === 0)
                fail('prc_id', 'PRC ID upload is required.');
        }
        if (step === 3) {
            if (state.location.address === '')
                fail('address', 'Complete address is required.');
            if (state.location.barangay === '')
                fail('barangay', 'Please select a barangay.');
        }
        if (step === 4) {
            const validRows = specializations.filter((s) => s.disabilityCategory !== '' && s.specificCondition.trim() !== '');
            if (validRows.length === 0) {
                fail('specializations', 'At least one complete specialization is required.');
            }
        }
        if (step === 5) {
            if (!state.confirmAccurate) {
                fail('confirm_accurate', 'Please confirm that all information is accurate.');
            }
        }
        return valid;
    }
    // ── Review summary (step 5) ─────────────────────────────────────────────
    function renderReview() {
        const state = collectState();
        function row(label, value) {
            return `<div class="review-row"><span class="review-label">${escapeHtml(label)}</span><span class="review-value">${escapeHtml(value || '—')}</span></div>`;
        }
        const specRows = specializations
            .filter((s) => s.disabilityCategory !== '' && s.specificCondition.trim() !== '')
            .map((s) => row(disabilityCategories[s.disabilityCategory], s.specificCondition))
            .join('');
        reviewSummary.innerHTML = `
      <div class="review-section">
        <h3>Account Information</h3>
        ${row('Name', `${state.account.firstName} ${state.account.lastName}`)}
        ${row('Email', state.account.email)}
        ${row('Phone Number', state.account.phoneNumber)}
      </div>
      <div class="review-section">
        <h3>Professional Information</h3>
        ${row('Clinic / Practice', state.professional.clinicName)}
        ${row('License Number', state.professional.licenseNumber)}
      </div>
      <div class="review-section">
        <h3>Clinic Location &amp; Accessibility</h3>
        ${row('Address', state.location.address)}
        ${row('Barangay', state.location.barangay)}
        ${row('Operating Hours', state.location.operatingHours)}
        ${row('Walk-in Patients', state.location.acceptsWalkIns ? 'Yes' : 'No')}
        ${row('Wheelchair Accessible', state.location.wheelchairAccessible ? 'Yes' : 'No')}
        ${row('Ground Floor Access', state.location.groundFloorAccess ? 'Yes' : 'No')}
      </div>
      <div class="review-section">
        <h3>Specializations</h3>
        ${specRows || row('Specializations', 'None')}
      </div>
    `;
    }
    // ── Submission ───────────────────────────────────────────────────────────
    async function submitRegistration() {
        clearFormBanner();
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        const state = collectState();
        const validSpecializations = specializations.filter((s) => s.disabilityCategory !== '' && s.specificCondition.trim() !== '');
        const data = new FormData();
        data.append('first_name', state.account.firstName);
        data.append('last_name', state.account.lastName);
        data.append('email', state.account.email);
        data.append('password', state.account.password);
        data.append('confirm_password', state.account.confirmPassword);
        data.append('phone_number', state.account.phoneNumber);
        data.append('clinic_name', state.professional.clinicName);
        data.append('license_number', state.professional.licenseNumber);
        data.append('address', state.location.address);
        data.append('barangay', state.location.barangay);
        data.append('operating_hours', state.location.operatingHours);
        data.append('accepts_walk_ins', state.location.acceptsWalkIns ? '1' : '0');
        data.append('wheelchair_accessible', state.location.wheelchairAccessible ? '1' : '0');
        data.append('ground_floor_access', state.location.groundFloorAccess ? '1' : '0');
        data.append('specializations', JSON.stringify(validSpecializations.map((s) => ({
            disability_category: s.disabilityCategory,
            specific_condition: s.specificCondition,
        }))));
        const profilePhoto = byId('profile_photo').files;
        if (profilePhoto && profilePhoto.length > 0) {
            data.append('profile_photo', profilePhoto[0]);
        }
        const prcId = byId('prc_id').files;
        if (prcId && prcId.length > 0) {
            data.append('prc_id', prcId[0]);
        }
        try {
            const response = await fetch('../backend/api/auth/register-provider.php', {
                method: 'POST',
                body: data,
            });
            const result = (await response.json());
            if (result.success) {
                showStep('success');
            }
            else {
                showFormErrors(result.errors);
            }
        }
        catch {
            showFormErrors(['Unable to reach the server. Please check your connection and try again.']);
        }
        finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Registration';
        }
    }
    // ── Event wiring ─────────────────────────────────────────────────────────
    nextBtn.addEventListener('click', () => {
        if (!validateStep(currentStep))
            return;
        currentStep = Math.min(currentStep + 1, TOTAL_STEPS);
        showStep(currentStep);
    });
    backBtn.addEventListener('click', () => {
        currentStep = Math.max(currentStep - 1, 1);
        showStep(currentStep);
    });
    submitBtn.addEventListener('click', () => {
        if (!validateStep(TOTAL_STEPS))
            return;
        void submitRegistration();
    });
    form.addEventListener('submit', (e) => e.preventDefault());
    renderSpecializations();
    showStep(currentStep);
})();
