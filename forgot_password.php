<?php
// AbleCare Forgot Password Page

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // TODO: Add your password-reset email logic here
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AbleCare – Forgot Password</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --teal:      #3aafa9;
      --teal-dark: #2b9e98;
      --mint-bg:   #d6eeea;
      --white:     #ffffff;
      --dark:      #1e2a2a;
      --text:      #333;
      --muted:     #888;
      --border:    #dde8e7;
      --input-bg:  #f7fafa;
      --error:     #e05555;
      --success:   #3aafa9;
    }

    html, body {
      height: 100%;
      font-family: 'Open Sans', sans-serif;
      background: #eaf3f2;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ── outer card ── */
    .card-wrapper {
      background: var(--white);
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.10);
      display: flex;
      width: 860px;
      min-height: 500px;
      overflow: hidden;
    }

    /* ── LEFT PANEL ── */
    .left-panel {
      background: var(--mint-bg);
      flex: 0 0 48%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 52px 40px;
    }

    .shield-wrap {
      margin-bottom: 32px;
    }

    .left-panel h2 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.45rem;
      color: var(--dark);
      text-align: center;
      margin-bottom: 8px;
    }

    .left-panel p {
      font-size: 0.88rem;
      color: var(--teal);
      font-weight: 600;
      text-align: center;
    }

    /* ── RIGHT PANEL ── */
    .right-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 52px;
    }

    /* brand row */
    .brand-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 22px;
    }

    .brand-row span {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.25rem;
      color: var(--dark);
    }

    /* form card */
    .form-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 36px 38px;
      width: 100%;
      max-width: 390px;
      box-shadow: 0 2px 14px rgba(58,175,169,0.07);
    }

    .form-card h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--dark);
      text-align: center;
      margin-bottom: 8px;
    }

    .form-card .subtitle {
      text-align: center;
      font-size: 0.83rem;
      color: var(--teal);
      line-height: 1.55;
      margin-bottom: 24px;
    }

    /* alerts */
    .alert {
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 0.82rem;
      margin-bottom: 16px;
      text-align: center;
    }

    .alert-error   { background: #fde8e8; color: var(--error); border: 1px solid #f5c0c0; }
    .alert-success { background: #e0f5f3; color: var(--success); border: 1px solid #b0e0dc; }

    /* field */
    .field {
      margin-bottom: 20px;
    }

    .field label {
      display: block;
      font-size: 0.83rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 6px;
    }

    .field input {
      width: 100%;
      padding: 11px 16px;
      border: 1.5px solid var(--border);
      border-radius: 8px;
      background: var(--input-bg);
      font-size: 0.87rem;
      color: var(--text);
      font-family: 'Open Sans', sans-serif;
      outline: none;
      transition: border-color 0.2s;
    }

    .field input:focus {
      border-color: var(--teal);
      background: #fff;
    }

    .field input::placeholder { color: #bbb; }

    /* send button */
    .btn-send {
      width: 100%;
      padding: 13px;
      background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      cursor: pointer;
      letter-spacing: 0.3px;
      transition: opacity 0.2s;
      margin-bottom: 18px;
    }

    .btn-send:hover { opacity: 0.9; }

    /* remember row */
    .login-row {
      text-align: center;
      font-size: 0.83rem;
      color: var(--muted);
    }

    .login-row a {
      color: var(--teal);
      font-weight: 600;
      text-decoration: none;
    }

    .login-row a:hover { text-decoration: underline; }

    /* back to home */
    .back-home {
      margin-top: 22px;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.85rem;
      color: var(--dark);
      text-decoration: none;
    }

    .back-home svg {
      width: 16px;
      height: 16px;
      stroke: var(--dark);
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .back-home:hover { color: var(--teal); }
    .back-home:hover svg { stroke: var(--teal); }

    @media (max-width: 700px) {
      .card-wrapper { flex-direction: column; width: 95vw; }
      .left-panel { padding: 36px 24px; }
      .right-panel { padding: 32px 20px; }
      .form-card { padding: 28px 20px; }
    }
  </style>
</head>
<body>

<div class="card-wrapper">

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <div class="shield-wrap">
     <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:350px;height:auto;">
    </div>

    <h2>Reset Your Password</h2>
    <p>We'll help you recover your account</p>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">

    <!-- Brand -->
    <div class="brand-row">
      <svg width="28" height="28" viewBox="0 0 40 40" fill="none">
        <path d="M20 4 L34 10 L34 22 C34 30 20 38 20 38 C20 38 6 30 6 22 L6 10 Z"
              fill="none" stroke="#3aafa9" stroke-width="2.5" stroke-linejoin="round"/>
        <circle cx="20" cy="21" r="6" fill="#7ec8e3" opacity="0.35"/>
        <path d="M20 17 L20 25 M16 21 L24 21" stroke="#3aafa9" stroke-width="2.2" stroke-linecap="round"/>
      </svg>
      <span>AbleCare</span>
    </div>

    <!-- Form card -->
    <div class="form-card">
      <h3>Forgot Password?</h3>
      <div class="subtitle">
        Enter your email address and we'll send you a link<br>to reset your password.
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success">
          ✓ Reset link sent! Please check your email.
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="forgot_password.php">
        <div class="field">
          <label for="email">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            required
          />
        </div>

        <button type="submit" class="btn-send">Send Reset Link</button>

        <div class="login-row">
          Remember your password? <a href="login.php">Log In</a>
        </div>
      </form>
    </div>

    <!-- Back to Home -->
    <a href="index.php" class="back-home">
      <svg viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to Home
    </a>

  </div>
</div>

</body>
</html>
