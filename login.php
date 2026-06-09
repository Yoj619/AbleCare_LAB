<?php
// ============================================================
//  AbleCare – Login
// ============================================================
session_start();

// Already logged in → go straight to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'lgu_admin' ? 'dashboard_admin.php' : 'dashboard_provider.php'));
    exit;
}

require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $db   = get_db();
        $stmt = $db->prepare(
            'SELECT id, full_name, email, password_hash, role FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            // ── Start authenticated session ──
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];

            $redirect = ($user['role'] === 'lgu_admin') ? 'dashboard_admin.php' : 'dashboard_provider.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AbleCare – Login</title>
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
    }

    html, body {
      min-height: 100vh;
      font-family: 'Open Sans', sans-serif;
      background: #eaf3f2;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px 10px;
    }

    .card-wrapper {
      background: var(--white);
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.10);
      display: flex;
      width: 860px;
      overflow: hidden;
    }

    /* LEFT PANEL */
    .left-panel {
      background: var(--mint-bg);
      flex: 0 0 40%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 52px 40px;
    }

    .left-panel h2 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.45rem;
      color: var(--dark);
      text-align: center;
      margin-bottom: 8px;
      margin-top: 24px;
    }

    .left-panel p {
      font-size: 0.88rem;
      color: var(--teal);
      font-weight: 600;
      text-align: center;
    }

    /* RIGHT PANEL */
    .right-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 52px;
    }

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

    .form-card {
      background: var(--white);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 36px 38px;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 2px 14px rgba(58,175,169,0.07);
    }

    .form-card h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--dark);
      text-align: center;
      margin-bottom: 4px;
    }

    .form-card .subtitle {
      text-align: center;
      font-size: 0.83rem;
      color: var(--teal);
      margin-bottom: 24px;
    }

    .alert-error {
      background: #fde8e8;
      color: var(--error);
      border: 1px solid #f5c0c0;
      border-radius: 8px;
      padding: 10px 14px;
      font-size: 0.82rem;
      margin-bottom: 16px;
      text-align: center;
    }

    .field { margin-bottom: 16px; }

    .field label {
      display: block;
      font-size: 0.83rem;
      font-weight: 600;
      color: var(--dark);
      margin-bottom: 6px;
    }

    .input-wrap { position: relative; }

    .input-wrap input {
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

    .input-wrap input:focus {
      border-color: var(--teal);
      background: #fff;
    }

    .input-wrap input::placeholder { color: #bbb; }

    .eye-btn {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      display: flex;
      align-items: center;
    }

    .eye-btn svg {
      width: 20px; height: 20px;
      stroke: var(--teal);
      fill: none;
      stroke-width: 1.8;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    .remember-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .remember-row label {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 0.82rem;
      color: var(--dark);
      cursor: pointer;
    }

    .remember-row input[type="checkbox"] {
      accent-color: var(--teal);
      width: 15px; height: 15px;
    }

    .forgot-link {
      font-size: 0.82rem;
      color: var(--teal);
      text-decoration: none;
      font-weight: 600;
    }

    .forgot-link:hover { text-decoration: underline; }

    .btn-login {
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

    .btn-login:hover { opacity: 0.9; }

    .register-row {
      text-align: center;
      font-size: 0.83rem;
      color: var(--muted);
    }

    .register-row a {
      color: var(--teal);
      font-weight: 600;
      text-decoration: none;
    }

    .register-row a:hover { text-decoration: underline; }

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
      width: 16px; height: 16px;
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
    <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:240px;height:auto;">
    <h2>Welcome to AbleCare</h2>
    <p>Supporting caregivers with AI-powered healthcare</p>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">

    <div class="brand-row">
      <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:50px;height:auto;">
      <span>AbleCare</span>
    </div>

    <div class="form-card">
      <h3>Welcome Back</h3>
      <div class="subtitle">Login to continue using AbleCare</div>

      <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php">

        <div class="field">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <input type="email" id="email" name="email"
              placeholder="Enter your email"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              required/>
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input type="password" id="password" name="password"
              placeholder="Enter your password" required/>
            <button type="button" class="eye-btn" onclick="togglePassword()" aria-label="Toggle password visibility">
              <svg id="eye-icon" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="remember-row">
          <label>
            <input type="checkbox" name="remember"/>
            Remember me
          </label>
          <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
        </div>

        <button type="submit" class="btn-login">Login</button>

        <div class="register-row">
          Don't have an account? <a href="create_account.php">Register</a>
        </div>

      </form>
    </div>

    <a href="index.php" class="back-home">
      <svg viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to Home
    </a>

  </div>
</div>

<script>
  function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.innerHTML = `
        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
        <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
        <line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
      input.type = 'password';
      icon.innerHTML = `
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>`;
    }
  }
</script>

</body>
</html>
