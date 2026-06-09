<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AbleCare</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --teal: #3aafa9;
      --teal-dark: #2b9e98;
      --teal-light: #d4f0ee;
      --dark: #1e2a2a;
      --text: #333;
      --muted: #666;
      --white: #fff;
      --bg-light: #f0f7f6;
      --border: #e0e0e0;
    }

    body {
      font-family: 'Open Sans', sans-serif;
      color: var(--text);
      background: var(--white);
    }

    /* ── NAVBAR ── */
    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 48px;
      background: var(--white);
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .nav-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.2rem;
      color: var(--dark);
      text-decoration: none;
    }

    .nav-brand svg {
      width: 32px;
      height: 32px;
    }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 32px;
      list-style: none;
    }

    .nav-links a {
      text-decoration: none;
      color: var(--text);
      font-size: 0.95rem;
      font-family: 'Open Sans', sans-serif;
    }

    .nav-links a:hover { color: var(--teal); }

    .nav-actions {
      display: flex;
      gap: 10px;
    }

    .btn-outline {
      padding: 8px 22px;
      border: 1.5px solid var(--teal);
      border-radius: 6px;
      background: transparent;
      color: var(--teal);
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
      font-family: 'Open Sans', sans-serif;
    }

    .btn-solid {
      padding: 8px 22px;
      border: none;
      border-radius: 6px;
      background: var(--teal);
      color: var(--white);
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
      font-family: 'Open Sans', sans-serif;
    }

    .btn-solid:hover { background: var(--teal-dark); }
    .btn-outline:hover { background: var(--teal-light); }

    /* ── MAIN BANNER (was hero) ── */
    .main-banner {
      position: relative;
      min-height: 420px;
      display: flex;
      align-items: center;
      overflow: hidden;
      background: #2c3535;
    }

    .main-banner-bg {
      position: absolute;
      inset: 0;
      background: url('bg.png') center/cover no-repeat;
      opacity: 0.45;
    }

    .main-banner-content {
      position: relative;
      z-index: 2;
      max-width: 500px;
      padding: 60px 48px;
      color: var(--white);
    }

    .main-banner-content h1 {
      font-family: 'Poppins', sans-serif;
      font-size: 2.8rem;
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 14px;
    }

    .main-banner-content .subtitle {
      color: var(--teal);
      font-family: 'Poppins', sans-serif;
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 16px;
    }

    .main-banner-content p {
      font-size: 0.9rem;
      line-height: 1.7;
      color: #e0e0e0;
      margin-bottom: 28px;
      max-width: 380px;
    }

    .main-banner-buttons {
      display: flex;
      gap: 14px;
    }

    .btn-banner-solid {
      padding: 12px 26px;
      background: var(--teal);
      color: var(--white);
      border: none;
      border-radius: 6px;
      font-size: 0.95rem;
      cursor: pointer;
      text-decoration: none;
      font-family: 'Open Sans', sans-serif;
    }

    .btn-banner-outline {
      padding: 12px 26px;
      background: transparent;
      color: var(--white);
      border: 1.5px solid var(--white);
      border-radius: 6px;
      font-size: 0.95rem;
      cursor: pointer;
      text-decoration: none;
      font-family: 'Open Sans', sans-serif;
    }

    .banner-logo {
      position: absolute;
      right: 300px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 2;
      width: 200px;
    }

    /* ── SECTIONS ── */
    section { padding: 64px 48px; }

    .section-title {
      text-align: center;
      font-family: 'Poppins', sans-serif;
      font-size: 1.7rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 10px;
    }

    .section-underline {
      width: 48px;
      height: 3px;
      background: var(--teal);
      margin: 0 auto 40px;
      border-radius: 2px;
    }

    /* ── KEY FEATURES ── */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      max-width: 1100px;
      margin: 0 auto;
    }

    .feature-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 28px 20px;
      text-align: left;
    }

    .feature-icon {
      width: 44px;
      height: 44px;
      border: 1.5px solid var(--teal);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
    }

    .feature-icon svg {
      width: 22px;
      height: 22px;
      stroke: var(--teal);
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .feature-card h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 8px;
    }

    .feature-card p {
      font-size: 0.82rem;
      color: var(--muted);
      line-height: 1.6;
    }

    /* ── HOW IT WORKS ── */
    .how-section {
      background: var(--bg-light);
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      max-width: 1100px;
      margin: 0 auto;
    }

    .step-card {
      background: var(--white);
      border-radius: 12px;
      padding: 28px 20px;
      position: relative;
    }

    .step-number {
      width: 36px;
      height: 36px;
      background: var(--teal);
      color: var(--white);
      border-radius: 50%;
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
    }

    .step-icon {
      margin-bottom: 14px;
    }

    .step-icon svg {
      width: 28px;
      height: 28px;
      stroke: var(--teal);
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .step-card h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 0.92rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 8px;
    }

    .step-card p {
      font-size: 0.82rem;
      color: var(--muted);
      line-height: 1.6;
    }

    /* ── ABOUT ── */
    .about-box {
      max-width: 860px;
      margin: 0 auto;
      border: 1.5px solid var(--teal);
      border-radius: 14px;
      padding: 40px 48px;
    }

    .about-box p {
      font-size: 0.9rem;
      line-height: 1.8;
      color: var(--text);
      text-align: center;
      margin-bottom: 36px;
    }

    .stats-row {
      display: flex;
      justify-content: space-around;
      gap: 20px;
      text-align: center;
    }

    .stat h2 {
      font-family: 'Poppins', sans-serif;
      font-size: 2rem;
      font-weight: 700;
      color: var(--teal);
    }

    .stat p {
      font-size: 0.82rem;
      color: var(--muted);
      margin-top: 4px;
    }

    /* ── FOOTER ── */
    footer {
      background: #1c2626;
      color: #ccc;
      padding: 48px 80px 24px;
    }

    .footer-top {
      display: grid;
      grid-template-columns: 1.5fr 1fr 1fr;
      gap: 48px;
      padding-bottom: 28px;
      border-bottom: 1px solid #2e3d3d;
      margin-bottom: 20px;
    }

    .footer-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
    }

    .footer-brand span {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      color: var(--white);
      font-size: 1.1rem;
    }

    .footer-col p {
      font-size: 0.83rem;
      color: #aaa;
      line-height: 1.7;
    }

    .footer-col h4 {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      font-size: 0.95rem;
      color: var(--teal);
      margin-bottom: 14px;
    }

    .footer-col ul {
      list-style: none;
    }

    .footer-col ul li {
      margin-bottom: 8px;
    }

    .footer-col ul li a {
      text-decoration: none;
      color: #aaa;
      font-size: 0.85rem;
    }

    .footer-col ul li a:hover { color: var(--teal); }

    .footer-bottom {
      text-align: center;
      font-size: 0.78rem;
      color: #666;
    }

    @media (max-width: 900px) {
      .features-grid, .steps-grid { grid-template-columns: repeat(2, 1fr); }
      .banner-logo { display: none; }
      .footer-top { grid-template-columns: 1fr; }
      nav { padding: 14px 20px; }
      section { padding: 48px 20px; }
    }

    html {
      scroll-behavior: smooth;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="#" class="nav-brand">
    <!-- ablecare logo -->
    <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:32px;height:32px;object-fit:contain;">
    AbleCare
  </a>

 <ul class="nav-links">
    <li><a href="#home">Home</a></li>
    <li><a href="#about">About</a></li>
    <li><a href="#features">Features</a></li>
    <li><a href="#contact">Contact</a></li>
</ul>

  <div class="nav-actions">
    <a href="login.php" class="btn-outline">Login</a>
    <a href="create_account.php" class="btn-solid">Register</a>
  </div>
</nav>

<!-- MAIN BANNER SECTION (formerly hero) -->
<section id="home" class="main-banner">
  <div class="main-banner-bg"></div>
  <div class="main-banner-content">
    <h1>AbleCare</h1>
    <div class="subtitle">Smart care support for elderly and PWD health needs</div>
    <p>AbleCare provides caregivers with AI-powered first aid guidance, personalized clinic recommendations, and rapid emergency assistance all in one comprehensive platform designed to support the health and well being of elderly individuals and persons with disabilities.</p>
    <div class="main-banner-buttons">
      <a href="create_account.php" class="btn-banner-solid">Get Started</a>
      <a href="#" class="btn-banner-outline">Learn More</a>
    </div>
  </div>

  <!-- Large logo area -->
  <div class="banner-logo">
    <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:400px;height:auto;">
  </div>
</section>

<!-- KEY FEATURES -->
<section id="features">
  <h2 class="section-title">Key Features</h2>
  <div class="section-underline"></div>

  <div class="features-grid">
    <!-- AI First Aid Guidance -->
    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24"><path d="M12 21C12 21 4 15 4 9a8 8 0 0116 0c0 6-8 12-8 12z"/><path d="M12 9v4m0 4h.01"/></svg>
      </div>
      <h3>AI First Aid Guidance</h3>
      <p>Provides step by step assistance during health emergencies.</p>
    </div>

    <!-- Clinic Recommendation -->
    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      </div>
      <h3>Clinic Recommendation</h3>
      <p>Suggests suitable healthcare providers based on the patient condition.</p>
    </div>

    <!-- Emergency Alert System -->
    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
      </div>
      <h3>Emergency Alert System</h3>
      <p>Allows caregivers to quickly notify emergency responders.</p>
    </div>

    <!-- Caregiver Support Platform -->
    <div class="feature-card">
      <div class="feature-icon">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h4m12 0h4M12 2v4m0 12v4"/></svg>
      </div>
      <h3>Caregiver Support Platform</h3>
      <p>Helps caregivers manage patient needs and communicate with healthcare providers.</p>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-section">
  <h2 class="section-title">How AbleCare Works</h2>
  <div class="section-underline"></div>

  <div class="steps-grid">
    <div class="step-card">
      <div class="step-number">1</div>
      <div class="step-icon">
        <svg viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 10h8M8 14h5"/></svg>
      </div>
      <h3>Enter patient symptoms</h3>
      <p>Describe the health concern or symptoms.</p>
    </div>

    <div class="step-card">
      <div class="step-number">2</div>
      <div class="step-icon">
        <svg viewBox="0 0 24 24"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>
      </div>
      <h3>Receive AI guidance</h3>
      <p>Get step-by-step first aid instructions.</p>
    </div>

    <div class="step-card">
      <div class="step-number">3</div>
      <div class="step-icon">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>
      </div>
      <h3>Find Recommended Clinics</h3>
      <p>Get the top clinic suggestions for immediate care.</p>
    </div>

    <div class="step-card">
      <div class="step-number">4</div>
      <div class="step-icon">
        <svg viewBox="0 0 24 24"><path d="M22 16.92V19a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.08 4.13 2 2 0 012 2h2.09a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L5.09 9.1a16 16 0 006.81 6.81l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/></svg>
      </div>
      <h3>Contact healthcare providers or emergency responders</h3>
      <p>Reach out to professionals instantly.</p>
    </div>
  </div>
</section>

<!-- ABOUT -->
<section id="about">
  <h2 class="section-title">About AbleCare</h2>
  <div class="section-underline"></div>

  <div class="about-box">
    <p>AbleCare is a digital healthcare assistance platform designed to support caregivers, elderly individuals, and persons with disabilities. Our mission is to empower caregivers with the tools and resources they need to provide exceptional care while ensuring the health and safety of their loved ones. Through cutting-edge AI technology, comprehensive clinic recommendations, and instant emergency support, AbleCare bridges the gap between caregivers and healthcare providers, making quality healthcare accessible, responsive, and reliable for everyone who needs it.</p>

    <div class="stats-row">
      <div class="stat">
        <h2>24/7</h2>
        <p>AI Support Available</p>
      </div>
      <div class="stat">
        <h2>500+</h2>
        <p>Healthcare Providers</p>
      </div>
      <div class="stat">
        <h2>&lt;2min</h2>
        <p>Emergency Response</p>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer id="contact">
  <div class="footer-top">
    <div class="footer-col">
      <div class="footer-brand">
        <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:28px;height:28px;object-fit:contain;">
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