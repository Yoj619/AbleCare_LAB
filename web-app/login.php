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
            'SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'pending') {
                $error = 'Your account is still awaiting admin approval.';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Your registration was not approved. Please contact the administrator for more information.';
            } else {
                // users.role enum stores 'admin' — the rest of the app expects 'lgu_admin'
                $role = $user['role'] === 'admin' ? 'lgu_admin' : $user['role'];

                // ── Start authenticated session ──
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
                $_SESSION['email']     = $user['email'];
                $_SESSION['role']      = $role;

                $redirect = ($role === 'lgu_admin') ? 'dashboard_admin.php' : 'dashboard_provider.php';
                header('Location: ' . $redirect);
                exit;
            }
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
 <link rel="stylesheet" href="css/login.css">
</head>
<body>

<div class="card-wrapper">

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:240px;height:auto;">
    <h2>Welcome to AbleCare</h2>
    <p>Supporting caregivers with AI-powered healthcare</p>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">

    <div class="brand-row">
      <img src="image/ablecarelogo.png" alt="AbleCare Logo" style="width:48px;height:48px;object-fit:contain;">
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
          Don't have an account? <a href="register_provider.php">Register</a>
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
