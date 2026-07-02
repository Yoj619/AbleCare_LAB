<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AbleCare</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/index.css">

  <!--
    ISSUE: Improve Dashboard UI
    The block below adds consistency + responsiveness fixes on top of the
    existing css/index.css. It does not replace that file — it patches
    spacing, type scale, color tokens, and small-screen layout so nothing
    breaks if index.css already sets these properties differently.
    Once verified, these rules should be merged into css/index.css directly.
  -->
  <style>
    /* ---------- 1. Consistent design tokens ---------- */
    :root {
      --font-heading: 'Poppins', sans-serif;
      --font-body: 'Open Sans', sans-serif;

      --color-primary: #2f7d6b;      /* AbleCare green accent */
      --color-primary-dark: #235c4f;
      --color-text: #1f2937;
      --color-text-muted: #5b6472;
      --color-bg: #ffffff;
      --color-bg-alt: #f6f8f7;
      --color-border: #e3e7e5;

      --space-xs: 8px;
      --space-sm: 16px;
      --space-md: 24px;
      --space-lg: 40px;
      --space-xl: 64px;

      --radius-md: 12px;
      --shadow-card: 0 2px 10px rgba(0,0,0,0.06);
    }

    * { box-sizing: border-box; }

    body {
      font-family: var(--font-body);
      color: var(--color-text);
      margin: 0;
      line-height: 1.6;
    }

    h1, h2, h3, h4 {
      font-family: var(--font-heading);
      color: var(--color-text);
      margin: 0 0 var(--space-sm);
    }

    /* ---------- 2. Consistent section spacing ---------- */
    section, footer {
      padding: var(--space-xl) var(--space-md);
    }

    .section-title {
      text-align: center;
      font-size: clamp(1.5rem, 2.5vw, 2rem);
      font-weight: 700;
      margin-bottom: var(--space-xs);
    }

    .section-underline {
      width: 60px;
      height: 4px;
      background: var(--color-primary);
      margin: 0 auto var(--space-lg);
      border-radius: 2px;
    }

    /* ---------- 3. Navbar consistency ---------- */
    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: var(--space-sm) var(--space-md);
      border-bottom: 1px solid var(--color-border);
      background: var(--color-bg);
      position: sticky;
      top: 0;
      z-index: 10;
      flex-wrap: wrap;
      gap: var(--space-sm);
    }

    .nav-brand {
      display: flex;
      align-items: center;
      gap: var(--space-xs);
      font-family: var(--font-heading);
      font-weight: 700;
      font-size: 1.25rem;
      color: var(--color-text);
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      list-style: none;
      gap: var(--space-md);
      margin: 0;
      padding: 0;
      font-weight: 600;
    }

    .nav-links a {
      color: var(--color-text);
      text-decoration: none;
    }

    .nav-links a:hover,
    .nav-links a:focus-visible {
      color: var(--color-primary);
    }

    .nav-actions {
      display: flex;
      gap: var(--space-sm);
    }

    .btn-outline, .btn-solid,
    .btn-banner-outline, .btn-banner-solid {
      font-family: var(--font-heading);
      font-weight: 600;
      font-size: 0.95rem;
      padding: 10px 20px;
      border-radius: var(--radius-md);
      text-decoration: none;
      display: inline-block;
      transition: transform 0.15s ease, opacity 0.15s ease;
    }

    .btn-outline {
      color: var(--color-primary);
      border: 1.5px solid var(--color-primary);
      background: transparent;
    }

    .btn-solid, .btn-banner-solid {
      color: #fff;
      background: var(--color-primary);
      border: 1.5px solid var(--color-primary);
    }

    .btn-banner-outline {
      color: var(--color-primary);
      border: 1.5px solid var(--color-primary);
      background: transparent;
    }

    .btn-outline:hover, .btn-solid:hover,
    .btn-banner-outline:hover, .btn-banner-solid:hover {
      transform: translateY(-2px);
      opacity: 0.92;
    }

    /* ---------- 4. Main banner ---------- */
    .main-banner {
      text-align: center;
      background: var(--color-bg-alt);
    }

    .main-banner-content {
      max-width: 720px;
      margin: 0 auto;
    }

    .banner-logo {
      width: 72px;
      height: 72px;
      object-fit: contain;
      margin-bottom: var(--space-sm);
    }

    .main-banner h1 {
      font-size: clamp(2rem, 4vw, 2.75rem);
    }

    .subtitle {
      font-weight: 600;
      color: var(--color-primary-dark);
      margin-bottom: var(--space-sm);
      font-size: 1.05rem;
    }

    .main-banner p {
      color: var(--color-text-muted);
      margin-bottom: var(--space-lg);
    }

    .main-banner-buttons {
      display: flex;
      justify-content: center;
      gap: var(--space-sm);
      flex-wrap: wrap;
    }

    /* ---------- 5. Card grids (features / steps) — consistent look ---------- */
    .features-grid, .steps-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: var(--space-md);
      max-width: 1100px;
      margin: 0 auto;
    }

    .feature-card, .step-card {
      background: var(--color-bg);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-card);
      padding: var(--space-md);
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: var(--space-xs);
    }

    .feature-icon svg, .step-icon svg {
      width: 32px;
      height: 32px;
      stroke: var(--color-primary);
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .feature-card h3, .step-card h3 {
      font-size: 1.05rem;
      margin: var(--space-xs) 0 4px;
    }

    .feature-card p, .step-card p {
      font-size: 0.92rem;
      color: var(--color-text-muted);
      margin: 0;
    }

    .step-card {
      position: relative;
    }

    .step-number {
      position: absolute;
      top: -14px;
      left: -14px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--color-primary);
      color: #fff;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.9rem;
    }

    .how-section {
      background: var(--color-bg-alt);
    }

    /* ---------- 6. About section ---------- */
    .about-box {
      max-width: 800px;
      margin: 0 auto;
      text-align: center;
    }

    .about-box > p {
      color: var(--color-text-muted);
      margin-bottom: var(--space-lg);
    }

    .stats-row {
      display: flex;
      justify-content: center;
      gap: var(--space-xl);
      flex-wrap: wrap;
    }

    .stat h2 {
      color: var(--color-primary);
      font-size: 2rem;
      margin-bottom: 4px;
    }

    .stat p {
      color: var(--color-text-muted);
      margin: 0;
      font-size: 0.9rem;
    }

    /* ---------- 7. Footer consistency ---------- */
    footer {
      background: var(--color-text);
      color: #dfe4e2;
    }

    .footer-top {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-lg);
      max-width: 1100px;
      margin: 0 auto var(--space-lg);
    }

    .footer-col { flex: 1; min-width: 220px; }

    .footer-brand {
      display: flex;
      align-items: center;
      gap: var(--space-xs);
      font-family: var(--font-heading);
      font-weight: 700;
      font-size: 1.1rem;
      color: #fff;
      margin-bottom: var(--space-xs);
    }

    .footer-col h4 {
      color: #fff;
      font-size: 1rem;
      margin-bottom: var(--space-xs);
    }

    .footer-col ul {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .footer-col a {
      color: #c7cfcc;
      text-decoration: none;
      font-size: 0.92rem;
    }

    .footer-col a:hover, .footer-col a:focus-visible {
      color: #fff;
    }

    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.15);
      padding-top: var(--space-sm);
      text-align: center;
      font-size: 0.85rem;
      color: #a9b2af;
      max-width: 1100px;
      margin: 0 auto;
    }

    /* ---------- 8. Responsiveness (smaller screens) ---------- */
    @media (max-width: 960px) {
      .features-grid, .steps-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      nav {
        flex-direction: column;
        align-items: flex-start;
      }

      .nav-links {
        flex-wrap: wrap;
        gap: var(--space-sm);
      }

      section, footer {
        padding: var(--space-lg) var(--space-sm);
      }

      .stats-row {
        gap: var(--space-lg);
      }
    }

    @media (max-width: 520px) {
      .features-grid, .steps-grid {
        grid-template-columns: 1fr;
      }

      .main-banner-buttons {
        flex-direction: column;
        align-items: stretch;
      }

      .btn-banner-outline, .btn-banner-solid {
        text-align: center;
      }

      .footer-top {
        flex-direction: column;
        gap: var(--space-md);
      }
    }

    /* ---------- 9. Accessibility ---------- */
    a:focus-visible, button:focus-visible {
      outline: 2px solid var(--color-primary);
      outline-offset: 2px;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="#" class="nav-brand">
    <!-- ablecare logo -->
    <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:48px;height:48px;object-fit:contain;">
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
    <a href="register_provider.php" class="btn-solid">Register</a>
  </div>
</nav>

<!-- MAIN BANNER SECTION (formerly hero) -->
<section id="home" class="main-banner">
  <div class="main-banner-content">
    <img src="image/ablecarelogo.png" alt="AbleCare Logo" class="banner-logo">
    <h1>AbleCare</h1>
    <div class="subtitle">Smart care support for elderly and PWD health needs</div>
    <p>AbleCare provides caregivers with AI-powered first aid guidance, personalized clinic recommendations, and rapid emergency assistance all in one comprehensive platform designed to support the health and well being of elderly individuals and persons with disabilities.</p>
    <div class="main-banner-buttons">
      <a href="register_provider.php" class="btn-banner-solid">Get Started</a>
      <a href="help.php" class="btn-banner-outline">Learn More</a>
    </div>
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
        <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:28px;height:28px;object-fit:contain;">
        <span>AbleCare</span>
      </div>
      <p>Supporting caregivers with AI-powered healthcare guidance, clinic recommendations, and emergency assistance for elderly individuals and persons with disabilities.</p>
    </div>

    <div class="footer-col">
      <h4>Contact</h4>
      <ul>
        <li><a href="mailto:Support@ablecare.com">Support@ablecare.com</a></li>
        <li><a href="tel:+639654571094">+63 965 457 1094</a></li>
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