<?php
// ============================================================
//  AbleCare – Caregiver Registration (multi-step + map pin)
//  Submits via JS (JSON) to:
//    POST /backend/api/auth/register-caregiver.php
// ============================================================
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$BARANGAYS = require __DIR__ . '/../backend/constants/barangays.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>AbleCare – Caregiver Registration</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
<!-- Leaflet CSS — must load before the map container is shown -->
<link rel="stylesheet" href="node_modules/leaflet/dist/leaflet.css"/>
<link rel="stylesheet" href="css/register_caregiver.css"/>
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

    <h1 class="wizard-title">Caregiver Registration</h1>
    <p class="wizard-subtitle">Create your account to start using AbleCare</p>

    <!-- Progress bar -->
    <div class="step-progress" id="stepProgress">
      <div class="step-progress-bar">
        <div class="step-progress-fill" id="stepProgressFill"></div>
      </div>
      <div class="step-progress-labels">
        <span class="step-dot active" data-step-dot="1"><span class="dot">1</span>Account</span>
        <span class="step-dot"        data-step-dot="2"><span class="dot">2</span>Address</span>
        <span class="step-dot"        data-step-dot="3"><span class="dot">3</span>Location</span>
        <span class="step-dot"        data-step-dot="4"><span class="dot">4</span>Review</span>
      </div>
    </div>

    <div id="formErrorBanner" class="alert alert-error" style="display:none;"></div>

    <form id="caregiverForm" novalidate>

      <!-- ══ STEP 1 — Account Information ══ -->
      <section class="wizard-step" data-step="1">
        <h2 class="step-heading">Step 1 of 4 — Account Information</h2>

        <div class="field-row">
          <div class="field">
            <label for="fullName">Full Name</label>
            <input type="text" id="fullName" name="fullName" placeholder="Juan Dela Cruz">
            <div class="field-error" data-error-for="fullName"></div>
          </div>
        </div>

        <div class="field">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="you@example.com">
          <div class="field-error" data-error-for="email"></div>
        </div>

        <div class="field">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" placeholder="09XX XXX XXXX">
          <div class="field-error" data-error-for="phone"></div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="At least 8 characters">
            <div class="field-error" data-error-for="password"></div>
          </div>
          <div class="field">
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password">
            <div class="field-error" data-error-for="confirmPassword"></div>
          </div>
        </div>
      </section>

      <!-- ══ STEP 2 — Address ══ -->
      <section class="wizard-step" data-step="2" hidden>
        <h2 class="step-heading">Step 2 of 4 — Address</h2>

        <div class="field">
          <label for="address">Complete Address</label>
          <textarea id="address" name="address" rows="2"
            placeholder="Street number, street name, Nasugbu, Batangas"></textarea>
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
      </section>

      <!-- ══ STEP 3 — Pin Your Location ══ -->
      <section class="wizard-step" data-step="3" hidden>
        <h2 class="step-heading">Step 3 of 4 — Pin Your Location</h2>

        <p class="map-instructions">
          Tap on the map to pin your home or primary location.
          This will be used to locate you in case of an emergency.
        </p>

        <!-- Leaflet map renders here — height set in CSS (.map-container) -->
        <div id="leafletMap" class="map-container"></div>

        <div id="coordsDisplay" class="coords-display"></div>
        <div class="field-error" data-error-for="mapPin"></div>
      </section>

      <!-- ══ STEP 4 — Review & Submit ══ -->
      <section class="wizard-step" data-step="4" hidden>
        <h2 class="step-heading">Step 4 of 4 — Review &amp; Submit</h2>

        <div id="reviewSummary" class="review-summary"></div>

        <label class="checkbox-row" style="margin-top:16px;">
          <input type="checkbox" id="confirmAccurate" name="confirmAccurate">
          I confirm all information is accurate
        </label>
        <div class="field-error" data-error-for="confirmAccurate"></div>
      </section>

      <!-- ══ SUCCESS ══ -->
      <section class="wizard-step" data-step="success" hidden>
        <div class="success-box">
          <div class="success-icon">✓</div>
          <h2>Registration Submitted</h2>
          <p>Your registration is under review. We'll notify you once your account has been approved.</p>
          <a href="login.php" class="btn-primary"
             style="display:inline-block;text-decoration:none;margin-top:16px;">
            Back to Login
          </a>
        </div>
      </section>

      <!-- Navigation -->
      <div class="wizard-nav" id="wizardNav">
        <button type="button" id="backBtn"   class="btn-secondary" disabled>Back</button>
        <button type="button" id="nextBtn"   class="btn-primary">Next</button>
        <button type="button" id="submitBtn" class="btn-primary" hidden>Create Account</button>
      </div>

    </form>

    <div class="login-row" id="loginRow">
      Already have an account? <a href="login.php">Login</a>
    </div>

  </div><!-- /wizard-card -->
</div><!-- /wizard-wrapper -->

<!-- Leaflet JS — must load before our wizard script -->
<script src="node_modules/leaflet/dist/leaflet.js"></script>
<script>
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
<script src="js/register-caregiver.js"></script>

</body>
</html>
