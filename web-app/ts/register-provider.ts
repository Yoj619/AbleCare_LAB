// ============================================================
//  AbleCare – Healthcare Provider Registration Wizard
//  Compiled to ../js/register-provider.js (see web-app/tsconfig.json)
// ============================================================

type DisabilityCategoryKey = 'physical' | 'sensory_visual' | 'sensory_hearing' | 'cognitive';

interface SpecializationEntry {
  disabilityCategory: DisabilityCategoryKey | '';
  specificCondition: string;
}

interface AccountInfo {
  firstName: string;
  lastName: string;
  email: string;
  password: string;
  confirmPassword: string;
  phoneNumber: string;
}

interface ProfessionalInfo {
  clinicName: string;
  licenseNumber: string;
}

interface LocationInfo {
  address: string;
  barangay: string;
  operatingHours: string;
  acceptsWalkIns: boolean;
  wheelchairAccessible: boolean;
  groundFloorAccess: boolean;
}

interface ProviderRegistrationState {
  account: AccountInfo;
  professional: ProfessionalInfo;
  location: LocationInfo;
  specializations: SpecializationEntry[];
  confirmAccurate: boolean;
}

interface RegisterProviderSuccessResponse {
  success: true;
  message: string;
}

interface RegisterProviderErrorResponse {
  success: false;
  errors: string[];
}

type RegisterProviderResponse = RegisterProviderSuccessResponse | RegisterProviderErrorResponse;

interface Window {
  ABLECARE_DISABILITY_CATEGORIES: Record<DisabilityCategoryKey, string>;
}

(function init(): void {
  const TOTAL_STEPS = 5;
  const disabilityCategories: Record<DisabilityCategoryKey, string> = window.ABLECARE_DISABILITY_CATEGORIES;

  let currentStep = 1;
  let specializations: SpecializationEntry[] = [{ disabilityCategory: '', specificCondition: '' }];

  const form = document.getElementById('providerForm') as HTMLFormElement;
  const backBtn = document.getElementById('backBtn') as HTMLButtonElement;
  const nextBtn = document.getElementById('nextBtn') as HTMLButtonElement;
  const submitBtn = document.getElementById('submitBtn') as HTMLButtonElement;
  const progressFill = document.getElementById('stepProgressFill') as HTMLDivElement;
  const formErrorBanner = document.getElementById('formErrorBanner') as HTMLDivElement;
  const specializationsList = document.getElementById('specializationsList') as HTMLDivElement;
  const addSpecializationBtn = document.getElementById('addSpecializationBtn') as HTMLButtonElement;
  const reviewSummary = document.getElementById('reviewSummary') as HTMLDivElement;
  const loginRow = document.getElementById('loginRow') as HTMLDivElement;

  function byId<T extends HTMLElement>(id: string): T {
    const el = document.getElementById(id);
    if (!el) throw new Error(`Missing element #${id}`);
    return el as T;
  }

  function setError(field: string, message: string): void {
    const el = document.querySelector(`[data-error-for="${field}"]`);
    if (el) el.textContent = message;
  }

  function clearErrors(step: number): void {
    const section = document.querySelector(`.wizard-step[data-step="${step}"]`);
    if (!section) return;
    section.querySelectorAll('[data-error-for]').forEach((el) => {
      el.textContent = '';
    });
  }

  function clearFormBanner(): void {
    formErrorBanner.style.display = 'none';
    formErrorBanner.innerHTML = '';
  }

  function showFormErrors(errors: string[]): void {
    formErrorBanner.innerHTML = '<ul>' + errors.map((e) => `<li>${escapeHtml(e)}</li>`).join('') + '</ul>';
    formErrorBanner.style.display = '';
  }

  function escapeHtml(value: string): string {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
  }

  // ── Step navigation ─────────────────────────────────────────────────────
  function showStep(step: number | 'success'): void {
    document.querySelectorAll<HTMLElement>('.wizard-step').forEach((section) => {
      section.hidden = section.dataset.step !== String(step);
    });

    const wizardNav = byId<HTMLDivElement>('wizardNav');

    if (step === 'success') {
      wizardNav.style.display = 'none';
      loginRow.style.display = 'none';
      return;
    }

    document.querySelectorAll<HTMLElement>('[data-step-dot]').forEach((dot) => {
      const dotStep = Number(dot.dataset.stepDot);
      dot.classList.remove('active', 'done');
      if (dotStep < step) dot.classList.add('done');
      if (dotStep === step) dot.classList.add('active');
    });

    progressFill.style.width = `${((step - 1) / (TOTAL_STEPS - 1)) * 100}%`;

    backBtn.disabled = step === 1;
    nextBtn.hidden = step === TOTAL_STEPS;
    submitBtn.hidden = step !== TOTAL_STEPS;

    if (step === TOTAL_STEPS) renderReview();
  }

  // ── Step 4: specializations ─────────────────────────────────────────────
  function renderSpecializations(): void {
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

      (Object.keys(disabilityCategories) as DisabilityCategoryKey[]).forEach((key) => {
        const opt = document.createElement('option');
        opt.value = key;
        opt.textContent = disabilityCategories[key];
        opt.selected = spec.disabilityCategory === key;
        categorySelect.appendChild(opt);
      });

      categorySelect.addEventListener('change', () => {
        specializations[index].disabilityCategory = categorySelect.value as DisabilityCategoryKey;
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
  function collectState(): ProviderRegistrationState {
    return {
      account: {
        firstName: byId<HTMLInputElement>('first_name').value.trim(),
        lastName: byId<HTMLInputElement>('last_name').value.trim(),
        email: byId<HTMLInputElement>('email').value.trim(),
        password: byId<HTMLInputElement>('password').value,
        confirmPassword: byId<HTMLInputElement>('confirm_password').value,
        phoneNumber: byId<HTMLInputElement>('phone_number').value.trim(),
      },
      professional: {
        clinicName: byId<HTMLInputElement>('clinic_name').value.trim(),
        licenseNumber: byId<HTMLInputElement>('license_number').value.trim(),
      },
      location: {
        address: byId<HTMLTextAreaElement>('address').value.trim(),
        barangay: byId<HTMLSelectElement>('barangay').value,
        operatingHours: byId<HTMLInputElement>('operating_hours').value.trim(),
        acceptsWalkIns: byId<HTMLInputElement>('accepts_walk_ins').checked,
        wheelchairAccessible: byId<HTMLInputElement>('wheelchair_accessible').checked,
        groundFloorAccess: byId<HTMLInputElement>('ground_floor_access').checked,
      },
      specializations,
      confirmAccurate: byId<HTMLInputElement>('confirm_accurate').checked,
    };
  }

  // ── Validation ───────────────────────────────────────────────────────────
  function validateStep(step: number): boolean {
    clearErrors(step);
    const state = collectState();
    let valid = true;

    function fail(field: string, message: string): void {
      setError(field, message);
      valid = false;
    }

    if (step === 1) {
      if (state.account.firstName === '') fail('first_name', 'First name is required.');
      if (state.account.lastName === '') fail('last_name', 'Last name is required.');
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
      if (state.professional.clinicName === '') fail('clinic_name', 'Clinic / practice name is required.');
      if (state.professional.licenseNumber === '') fail('license_number', 'License number is required.');
      const prcFile = byId<HTMLInputElement>('prc_id').files;
      if (!prcFile || prcFile.length === 0) fail('prc_id', 'PRC ID upload is required.');
    }

    if (step === 3) {
      if (state.location.address === '') fail('address', 'Complete address is required.');
      if (state.location.barangay === '') fail('barangay', 'Please select a barangay.');
    }

    if (step === 4) {
      const validRows = specializations.filter(
        (s) => s.disabilityCategory !== '' && s.specificCondition.trim() !== ''
      );
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
  function renderReview(): void {
    const state = collectState();

    function row(label: string, value: string): string {
      return `<div class="review-row"><span class="review-label">${escapeHtml(label)}</span><span class="review-value">${escapeHtml(value || '—')}</span></div>`;
    }

    const specRows = specializations
      .filter((s) => s.disabilityCategory !== '' && s.specificCondition.trim() !== '')
      .map((s) => row(disabilityCategories[s.disabilityCategory as DisabilityCategoryKey], s.specificCondition))
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
  async function submitRegistration(): Promise<void> {
    clearFormBanner();
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    const state = collectState();
    const validSpecializations = specializations.filter(
      (s) => s.disabilityCategory !== '' && s.specificCondition.trim() !== ''
    );

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
    data.append(
      'specializations',
      JSON.stringify(
        validSpecializations.map((s) => ({
          disability_category: s.disabilityCategory,
          specific_condition: s.specificCondition,
        }))
      )
    );

    const profilePhoto = byId<HTMLInputElement>('profile_photo').files;
    if (profilePhoto && profilePhoto.length > 0) {
      data.append('profile_photo', profilePhoto[0]);
    }
    const prcId = byId<HTMLInputElement>('prc_id').files;
    if (prcId && prcId.length > 0) {
      data.append('prc_id', prcId[0]);
    }

    try {
      const response = await fetch('../backend/api/auth/register-provider.php', {
        method: 'POST',
        body: data,
      });
      const result = (await response.json()) as RegisterProviderResponse;

      if (result.success) {
        showStep('success');
      } else {
        showFormErrors(result.errors);
      }
    } catch {
      showFormErrors(['Unable to reach the server. Please check your connection and try again.']);
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit Registration';
    }
  }

  // ── Event wiring ─────────────────────────────────────────────────────────
  nextBtn.addEventListener('click', () => {
    if (!validateStep(currentStep)) return;
    currentStep = Math.min(currentStep + 1, TOTAL_STEPS);
    showStep(currentStep);
  });

  backBtn.addEventListener('click', () => {
    currentStep = Math.max(currentStep - 1, 1);
    showStep(currentStep);
  });

  submitBtn.addEventListener('click', () => {
    if (!validateStep(TOTAL_STEPS)) return;
    void submitRegistration();
  });

  form.addEventListener('submit', (e) => e.preventDefault());

  renderSpecializations();
  showStep(currentStep);
})();
