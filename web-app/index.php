<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AbleCare</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="css/index.css">
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