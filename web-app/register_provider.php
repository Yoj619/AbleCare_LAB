<?php
// ============================================================
//  AbleCare – Healthcare Provider Registration (multi-step)
//  Submits via JS (multipart/form-data) to:
//    POST /backend/api/auth/register-provider.php
// ============================================================
session_start();

// Already logged in → go straight to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'lgu_admin' ? 'dashboard_admin.php' : 'dashboard_provider.php'));
    exit;
}

$BARANGAYS             = require __DIR__ . '/../backend/constants/barangays.php';
$DISABILITY_CATEGORIES = require __DIR__ . '/../backend/constants/disability_categories.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>AbleCare – Healthcare Provider Registration</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="css/register_provider.css">
</head>
<body>

<div class="wizard-wrapper">

  <div class="wizard-brand" style="display:flex;align-items:center;justify-content:space-between;">
    <div style="display:flex;align-items:center;gap:10px;">
      <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:48px;height:48px;object-fit:contain;">
      <span>AbleCare</span>
    </div>
    <button type="button" id="exitRegBtn" onclick="exitRegistration()"
      style="background:none;border:1px solid #ccc;border-radius:6px;padding:6px 14px;font-size:13px;color:#666;cursor:pointer;font-family:inherit;line-height:1;"
      onmouseover="this.style.borderColor='#d95f3b';this.style.color='#d95f3b';"
      onmouseout="this.style.borderColor='#ccc';this.style.color='#666';">
      ✕ Exit
    </button>
  </div>

  <div class="wizard-card">

    <h1 class="wizard-title">Healthcare Provider Registration</h1>
    <p class="wizard-subtitle">Register your clinic or practice to join the AbleCare provider network</p>

    <!-- Step progress indicator -->
    <div class="step-progress" id="stepProgress">
      <div class="step-progress-bar"><div class="step-progress-fill" id="stepProgressFill"></div></div>
      <div class="step-progress-labels">
        <span class="step-dot active" data-step-dot="1"><span class="dot">1</span>Account</span>
        <span class="step-dot" data-step-dot="2"><span class="dot">2</span>Professional</span>
        <span class="step-dot" data-step-dot="3"><span class="dot">3</span>Location</span>
        <span class="step-dot" data-step-dot="4"><span class="dot">4</span>Specializations</span>
        <span class="step-dot" data-step-dot="5"><span class="dot">5</span>Review</span>
      </div>
    </div>

    <div id="formErrorBanner" class="alert alert-error" style="display:none;"></div>

    <form id="providerForm" novalidate>

      <!-- ══ STEP 1 — Account Information ══ -->
      <section class="wizard-step" data-step="1">
        <h2 class="step-heading">Step 1 of 5 — Account Information</h2>

        <div class="field-row">
          <div class="field">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" placeholder="Juan">
            <div class="field-error" data-error-for="first_name"></div>
          </div>
          <div class="field">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" placeholder="Dela Cruz">
            <div class="field-error" data-error-for="last_name"></div>
          </div>
        </div>

        <div class="field">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="you@example.com">
          <div class="field-error" data-error-for="email"></div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="At least 8 characters">
            <div class="field-error" data-error-for="password"></div>
          </div>
          <div class="field">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password">
            <div class="field-error" data-error-for="confirm_password"></div>
          </div>
        </div>

        <div class="field">
          <label for="phone_number">Phone Number</label>
          <input type="tel" id="phone_number" name="phone_number" placeholder="09XX XXX XXXX">
          <div class="field-error" data-error-for="phone_number"></div>
        </div>

        <div class="field">
          <label for="profile_photo">Profile Photo <span class="optional-tag">(optional)</span></label>
          <input type="file" id="profile_photo" name="profile_photo" accept="image/png,image/jpeg">
          <div class="field-error" data-error-for="profile_photo"></div>
        </div>
      </section>

      <!-- ══ STEP 2 — Professional Information ══ -->
      <section class="wizard-step" data-step="2" hidden>
        <h2 class="step-heading">Step 2 of 5 — Professional Information</h2>

        <div class="field">
          <label for="clinic_name">Full Name of Clinic / Practice</label>
          <input type="text" id="clinic_name" name="clinic_name" placeholder="e.g., Nasugbu Rehabilitation Center">
          <div class="field-error" data-error-for="clinic_name"></div>
        </div>

        <div class="field">
          <label for="license_number">License Number</label>
          <input type="text" id="license_number" name="license_number" placeholder="PRC License Number">
          <div class="field-error" data-error-for="license_number"></div>
        </div>

        <div class="field">
          <label for="prc_id">PRC ID Upload <span class="optional-tag">(image or PDF)</span></label>
          <input type="file" id="prc_id" name="prc_id" accept="image/png,image/jpeg,application/pdf">
          <div class="field-error" data-error-for="prc_id"></div>
        </div>
      </section>

      <!-- ══ STEP 3 — Clinic Location & Accessibility ══ -->
      <section class="wizard-step" data-step="3" hidden>
        <h2 class="step-heading">Step 3 of 5 — Clinic Location &amp; Accessibility</h2>

        <div class="field">
          <label for="address">Complete Address</label>
          <textarea id="address" name="address" rows="2" placeholder="Street, Barangay, Nasugbu, Batangas"></textarea>
          <div class="field-error" data-error-for="address"></div>
        </div>

        <div class="field">
          <label for="barangay">Barangay</label>
          <select id="barangay" name="barangay">
            <option value="" disabled selected>Select barangay</option>
            <?php foreach ($BARANGAYS as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="field-error" data-error-for="barangay"></div>
        </div>

        <p class="form-note">📍 Coordinates will be determined from your address — no manual entry needed.</p>

        <div class="field">
          <label for="operating_hours">Operating Hours</label>
          <input type="text" id="operating_hours" name="operating_hours" placeholder="e.g., Mon-Fri 8AM-5PM">
          <div class="field-error" data-error-for="operating_hours"></div>
        </div>

        <div class="checkbox-group">
          <label class="checkbox-row">
            <input type="checkbox" id="accepts_walk_ins" name="accepts_walk_ins">
            Accepts Walk-in Patients
          </label>
          <label class="checkbox-row">
            <input type="checkbox" id="wheelchair_accessible" name="wheelchair_accessible">
            Wheelchair Accessible
          </label>
          <label class="checkbox-row">
            <input type="checkbox" id="ground_floor_access" name="ground_floor_access">
            Ground Floor Access
          </label>
        </div>
      </section>

      <!-- ══ STEP 4 — Specializations ══ -->
      <section class="wizard-step" data-step="4" hidden>
        <h2 class="step-heading">Step 4 of 5 — Specializations</h2>
        <p class="form-note">Add at least one disability category and the specific conditions you treat.</p>

        <div id="specializationsList"></div>

        <button type="button" id="addSpecializationBtn" class="btn-secondary">+ Add Another Specialization</button>
        <div class="field-error" data-error-for="specializations"></div>
      </section>

      <!-- ══ STEP 5 — Review & Submit ══ -->
      <section class="wizard-step" data-step="5" hidden>
        <h2 class="step-heading">Step 5 of 5 — Review &amp; Submit</h2>

        <div id="reviewSummary" class="review-summary"></div>

        <label class="checkbox-row" style="margin-top:16px;">
          <input type="checkbox" id="confirm_accurate" name="confirm_accurate">
          I confirm all information is accurate
        </label>
        <div class="field-error" data-error-for="confirm_accurate"></div>
      </section>

      <!-- ══ SUCCESS STATE ══ -->
      <section class="wizard-step" data-step="success" hidden>
        <div class="success-box">
          <div class="success-icon">✓</div>
          <h2>Registration Submitted</h2>
          <p>Your registration is under review. We'll notify you once your account has been approved.</p>
          <a href="login.php" class="btn-primary" style="display:inline-block;text-decoration:none;margin-top:12px;">Back to Login</a>
        </div>
      </section>

      <!-- Navigation -->
      <div class="wizard-nav" id="wizardNav">
        <button type="button" id="backBtn" class="btn-secondary" disabled>Back</button>
        <button type="button" id="nextBtn" class="btn-primary">Next</button>
        <button type="button" id="submitBtn" class="btn-primary" hidden>Submit Registration</button>
      </div>

    </form>

    <div class="login-row" id="loginRow">
      Already have an account? <a href="login.php">Login</a>
    </div>

  </div>
</div>

<script>
  window.ABLECARE_DISABILITY_CATEGORIES = <?= json_encode($DISABILITY_CATEGORIES, JSON_HEX_TAG) ?>;

  function exitRegistration() {
    const successSection = document.querySelector('[data-step="success"]');
    if (successSection && !successSection.hidden) {
      window.location.href = 'index.php';
      return;
    }
    if (confirm('Are you sure you want to exit? Your progress will not be saved.')) {
      window.location.href = 'index.php';
    }
  }
</script>
<script src="js/register-provider.js"></script>

</body>
</html>
