<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>How to Use AbleCare</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/index.css">
  <style>
    /* ── Page-specific styles ── */
    .help-hero {
      background: linear-gradient(135deg, #1e2a2a 0%, #2b9e98 100%);
      padding: 64px 48px 56px;
      text-align: center;
      color: #fff;
    }
    .help-hero img {
      width: 72px;
      height: 72px;
      object-fit: contain;
      background: #fff;
      border-radius: 50%;
      padding: 6px;
      margin-bottom: 20px;
      box-shadow: 0 0 0 3px rgba(58,175,169,0.5);
    }
    .help-hero h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 12px;
    }
    .help-hero p {
      font-size: 1rem;
      color: rgba(255,255,255,0.85);
      max-width: 520px;
      margin: 0 auto;
      line-height: 1.6;
    }

    .help-content {
      max-width: 820px;
      margin: 0 auto;
      padding: 56px 24px 80px;
    }

    .help-section {
      margin-bottom: 52px;
    }

    .help-section-header {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 20px;
      padding-bottom: 14px;
      border-bottom: 2px solid var(--teal-light);
    }

    .help-section-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: var(--teal-light);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .help-section-icon svg {
      width: 24px;
      height: 24px;
      stroke: var(--teal-dark);
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .help-section-number {
      font-family: 'Poppins', sans-serif;
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--teal);
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 2px;
    }
    .help-section-title {
      font-family: 'Poppins', sans-serif;
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--dark);
    }

    .help-steps {
      list-style: none;
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .help-steps li {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      background: var(--bg-light);
      border-radius: 10px;
      padding: 14px 18px;
      font-size: 0.95rem;
      color: var(--text);
      line-height: 1.55;
    }

    .step-num {
      width: 26px;
      height: 26px;
      border-radius: 50%;
      background: var(--teal);
      color: #fff;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 0.78rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }

    .help-note {
      margin-top: 10px;
      padding: 10px 16px;
      background: #fff8e1;
      border-left: 3px solid #f0a500;
      border-radius: 4px;
      font-size: 0.88rem;
      color: #6b5000;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 36px;
      color: var(--teal);
      font-weight: 600;
      font-size: 0.9rem;
      text-decoration: none;
    }
    .back-link:hover { text-decoration: underline; }
    .back-link svg {
      width: 16px; height: 16px;
      stroke: currentColor; fill: none;
      stroke-width: 2.5;
      stroke-linecap: round; stroke-linejoin: round;
    }
  </style>
</head>
<body>

<!-- ── NAVBAR (same as index.php) ── -->
<nav>
  <a href="index.php" class="nav-brand">
    <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:48px;height:48px;object-fit:contain;">
    AbleCare
  </a>
  <ul class="nav-links">
    <li><a href="index.php#home">Home</a></li>
    <li><a href="index.php#about">About</a></li>
    <li><a href="index.php#features">Features</a></li>
    <li><a href="index.php#contact">Contact</a></li>
  </ul>
  <div class="nav-actions">
    <a href="login.php" class="btn-outline">Login</a>
    <a href="register_provider.php" class="btn-solid">Register</a>
  </div>
</nav>

<!-- ── HERO ── -->
<div class="help-hero">
  <img src="image/ablecarelogo.png" alt="AbleCare Logo">
  <h1>How to Use AbleCare</h1>
  <p>Step-by-step guides for caregivers and healthcare providers to get the most out of the AbleCare platform.</p>
</div>

<!-- ── CONTENT ── -->
<div class="help-content">

  <a href="index.php" class="back-link">
    <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Home
  </a>

  <!-- 1. Register as Caregiver -->
  <div class="help-section">
    <div class="help-section-header">
      <div class="help-section-icon">
        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
      </div>
      <div>
        <div class="help-section-number">Guide 1</div>
        <div class="help-section-title">How to Register as a Caregiver</div>
      </div>
    </div>
    <ol class="help-steps">
      <li><span class="step-num">1</span>Download the AbleCare mobile app from the App Store or Google Play and open it.</li>
      <li><span class="step-num">2</span>On the Landing screen, tap <strong>Get Started</strong>.</li>
      <li><span class="step-num">3</span>Fill in your personal information: full name, email address, and a secure password.</li>
      <li><span class="step-num">4</span>Tap <strong>Create Account</strong>. Your account is created immediately — no approval needed for caregivers.</li>
      <li><span class="step-num">5</span>Log in using your email and password to access the caregiver dashboard.</li>
    </ol>
  </div>

  <!-- 2. Register as Healthcare Provider -->
  <div class="help-section">
    <div class="help-section-header">
      <div class="help-section-icon">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </div>
      <div>
        <div class="help-section-number">Guide 2</div>
        <div class="help-section-title">How to Register as a Healthcare Provider</div>
      </div>
    </div>
    <ol class="help-steps">
      <li><span class="step-num">1</span>On this website, click <strong>Register</strong> in the top navigation bar.</li>
      <li><span class="step-num">2</span>Complete the multi-step registration form: enter your personal details, professional credentials (license number, specialty), and clinic information.</li>
      <li><span class="step-num">3</span>Upload your PRC ID when prompted.</li>
      <li><span class="step-num">4</span>Review and submit your application.</li>
      <li><span class="step-num">5</span>Wait for the LGU Admin to review and approve your account. You will receive a notification once approved.</li>
      <li><span class="step-num">6</span>Once approved, log in at <a href="login.php">login.php</a> using your credentials to access the Healthcare Provider dashboard.</li>
    </ol>
    <div class="help-note">⚠ Healthcare provider accounts require LGU Admin approval before login is allowed. Pending accounts will see a message when attempting to log in.</div>
  </div>

  <!-- 3. Add a Patient -->
  <div class="help-section">
    <div class="help-section-header">
      <div class="help-section-icon">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="12" y1="3" x2="12" y2="7"/></svg>
      </div>
      <div>
        <div class="help-section-number">Guide 3</div>
        <div class="help-section-title">How to Add a Patient</div>
      </div>
    </div>
    <ol class="help-steps">
      <li><span class="step-num">1</span>Log in to the AbleCare mobile app as a caregiver.</li>
      <li><span class="step-num">2</span>From the Dashboard, tap <strong>Patient Profile</strong> in the bottom navigation bar.</li>
      <li><span class="step-num">3</span>Tap <strong>Add Patient</strong> and fill in the patient's information: name, date of birth, gender, disability category, and specific condition.</li>
      <li><span class="step-num">4</span>Tap <strong>Save</strong>. The patient profile is now linked to your account.</li>
      <li><span class="step-num">5</span>To update patient information later, go to Patient Profile and tap <strong>Edit</strong>.</li>
    </ol>
  </div>

  <!-- 4. AI Health Guidance -->
  <div class="help-section">
    <div class="help-section-header">
      <div class="help-section-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2z"/><path d="M12 8v4l3 3"/></svg>
      </div>
      <div>
        <div class="help-section-number">Guide 4</div>
        <div class="help-section-title">How to Use the AI Health Guidance Feature</div>
      </div>
    </div>
    <ol class="help-steps">
      <li><span class="step-num">1</span>From the Dashboard, tap <strong>AI Help</strong> in the bottom navigation bar.</li>
      <li><span class="step-num">2</span>On the AI Guidance screen, tap <strong>Start a New Session</strong>.</li>
      <li><span class="step-num">3</span>Describe the symptoms or health concern in the text box (e.g., "Patient has a high fever and difficulty breathing").</li>
      <li><span class="step-num">4</span>Tap <strong>Send</strong>. The AI will respond with step-by-step first aid instructions tailored to the situation.</li>
      <li><span class="step-num">5</span>Follow the guidance carefully. You can ask follow-up questions in the same session.</li>
      <li><span class="step-num">6</span>If the AI advises emergency care, use the Emergency Alert feature immediately (see Guide 5).</li>
    </ol>
    <div class="help-note">ℹ AI Health Guidance is designed as a first-response tool and does not replace professional medical advice. Always consult a healthcare provider for serious conditions.</div>
  </div>

  <!-- 5. Emergency Alert -->
  <div class="help-section">
    <div class="help-section-header">
      <div class="help-section-icon">
        <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div>
        <div class="help-section-number">Guide 5</div>
        <div class="help-section-title">How to Trigger an Emergency Alert</div>
      </div>
    </div>
    <ol class="help-steps">
      <li><span class="step-num">1</span>From the Dashboard, tap the <strong>Emergency</strong> tab in the bottom navigation bar, or tap the red <strong>Emergency Alert</strong> button on the dashboard.</li>
      <li><span class="step-num">2</span>On the Emergency Alert screen, review the pre-filled patient information to confirm it is correct.</li>
      <li><span class="step-num">3</span>Tap <strong>Send Emergency Alert</strong>. A confirmation screen will appear.</li>
      <li><span class="step-num">4</span>Confirm the alert. An emergency notification will be sent to the LGU health office and linked healthcare providers.</li>
      <li><span class="step-num">5</span>Stay on the line — a responder may call you to coordinate assistance.</li>
    </ol>
    <div class="help-note">⚠ Only use this feature for genuine emergencies. False alerts may delay responses for other patients in need.</div>
  </div>

  <!-- 6. Recommended Clinics -->
  <div class="help-section">
    <div class="help-section-header">
      <div class="help-section-icon">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
      </div>
      <div>
        <div class="help-section-number">Guide 6</div>
        <div class="help-section-title">How to View Recommended Clinics</div>
      </div>
    </div>
    <ol class="help-steps">
      <li><span class="step-num">1</span>From the Dashboard, tap the <strong>Clinics</strong> section or navigate to Recommended Clinics from the drawer menu.</li>
      <li><span class="step-num">2</span>Allow location access when prompted so AbleCare can find clinics near you. If location is unavailable, results will default to the Nasugbu, Batangas area.</li>
      <li><span class="step-num">3</span>Select the patient's <strong>disability category</strong> and <strong>specific condition</strong> to filter clinics by specialization.</li>
      <li><span class="step-num">4</span>Tap <strong>Find Clinics</strong>. AbleCare ranks clinics by specialization match, distance, accessibility features, and availability.</li>
      <li><span class="step-num">5</span>Review the clinic cards — each shows distance, address, operating hours, and accessibility badges (wheelchair access, walk-ins, ground floor).</li>
      <li><span class="step-num">6</span>Tap <strong>Select Clinic</strong> on a result to confirm your choice and proceed to contact or schedule.</li>
    </ol>
  </div>

  <!-- 7. Therapy Sessions -->
  <div class="help-section">
    <div class="help-section-header">
      <div class="help-section-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div>
        <div class="help-section-number">Guide 7</div>
        <div class="help-section-title">How to Schedule Therapy Sessions</div>
      </div>
    </div>
    <ol class="help-steps">
      <li><span class="step-num">1</span>From the Dashboard, open the drawer menu and tap <strong>Therapy Schedule</strong>.</li>
      <li><span class="step-num">2</span>Tap <strong>+ Add Session</strong> to schedule a new therapy appointment.</li>
      <li><span class="step-num">3</span>Select the healthcare provider, session date, and time.</li>
      <li><span class="step-num">4</span>Add any optional notes (e.g., type of therapy, special instructions).</li>
      <li><span class="step-num">5</span>Tap <strong>Save Session</strong>. The session will appear in your therapy schedule.</li>
      <li><span class="step-num">6</span>You can pull down on the Therapy Schedule screen to refresh and check the status (Scheduled, Completed, Missed, or Cancelled).</li>
    </ol>
  </div>

</div><!-- /help-content -->

<!-- ── FOOTER (same as index.php) ── -->
<footer id="contact">
  <div class="footer-top">
    <div class="footer-col">
      <div class="footer-brand">
        <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:28px;height:28px;object-fit:contain;">
        <span>AbleCare</span>
      </div>
      <p>Supporting caregivers with AI-powered healthcare guidance, clinic recommendations, and emergency assistance for elderly individuals and persons with disabilities.</p>
    </div>
    <div class="footer-col">
      <h4>Contact</h4>
      <ul>
        <li><a href="mailto:Support@ablecare.com">Support@ablecare.com</a></li>
        <li><a href="#">63+ 9654571094</a></li>
        <li><a href="#">Municipality of Nasugbu</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>&copy; 2026 AbleCare. All rights reserved. Dedicated to supporting caregivers and improving healthcare accessibility.</p>
  </div>
</footer>

</body>
</html>
