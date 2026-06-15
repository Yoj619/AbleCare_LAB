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
 <link rel="stylesheet" href="css/forgot_pass.css">
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
