<?php
// ============================================================
//  AbleCare – Create Account
// ============================================================
session_start();

// Already logged in → go straight to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'lgu_admin' ? 'dashboard_admin.php' : 'dashboard_provider.php'));
    exit;
}

require_once 'db.php';

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name        = trim($_POST['full_name']        ?? '');
    $email            = trim($_POST['email']            ?? '');
    $password         = $_POST['password']              ?? '';
    $confirm_password = $_POST['confirm_password']      ?? '';
    $role             = $_POST['role']                  ?? '';

    // ── Validation ──
    if ($full_name === '')
        $errors[] = 'Full name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'A valid email address is required.';
    if (strlen($password) < 8)
        $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm_password)
        $errors[] = 'Passwords do not match.';
    if (!in_array($role, ['lgu_admin', 'healthcare_provider'], true))
        $errors[] = 'Please select a valid role.';

    // ── Check for duplicate email ──
    if (empty($errors)) {
        $db   = get_db();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0)
            $errors[] = 'An account with that email already exists.';
        $stmt->close();
    }

    // ── Insert new user ──
    if (empty($errors)) {
        $db   = get_db();
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare(
            'INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('ssss', $full_name, $email, $hash, $role);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            // ── Auto-login after registration ──
            $_SESSION['user_id']   = $user_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email']     = $email;
            $_SESSION['role']      = $role;

            $redirect = ($role === 'lgu_admin') ? 'dashboard_admin.php' : 'dashboard_provider.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors[] = 'Registration failed. Please try again.';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AbleCare – Create Account</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/create_account.css">
</head>
<body>

<div class="card-wrapper">

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:240px;height:auto;">
    <h2>Join AbleCare Today</h2>
    <p>Empowering caregivers with smart healthcare support</p>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">

    <div class="brand-row">
      <img src="ablecarelogo.png" alt="AbleCare Logo" style="width:50px;height:auto;">
      <span>AbleCare</span>
    </div>

    <div class="form-card">
      <h3>Create an AbleCare Account</h3>
      <div class="subtitle">Register to access healthcare support and assistance</div>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <ul>
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="create_account.php">

        <!-- Full Name -->
        <div class="field">
          <label for="full_name">Full Name</label>
          <div class="input-wrap">
            <input type="text" id="full_name" name="full_name"
              placeholder="Enter your full name"
              value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
              required/>
          </div>
        </div>

        <!-- Email -->
        <div class="field">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <input type="email" id="email" name="email"
              placeholder="Enter your email"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              required/>
          </div>
        </div>

        <!-- Password -->
        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <input type="password" id="password" name="password"
              placeholder="Enter your password" required/>
            <button type="button" class="eye-btn" onclick="togglePass('password','eye1')" aria-label="Toggle password">
              <svg id="eye1" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Confirm Password -->
        <div class="field">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-wrap">
            <input type="password" id="confirm_password" name="confirm_password"
              placeholder="Confirm your password" required/>
            <button type="button" class="eye-btn" onclick="togglePass('confirm_password','eye2')" aria-label="Toggle confirm password">
              <svg id="eye2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Role -->
        <div class="field">
          <label for="role">Role Selection</label>
          <div class="select-wrap">
            <select id="role" name="role" required>
              <option value="" disabled <?= empty($_POST['role']) ? 'selected' : '' ?>>Select Role</option>
              <option value="lgu_admin"           <?= ($_POST['role'] ?? '') === 'lgu_admin'           ? 'selected' : '' ?>>LGU Admin</option>
              <option value="healthcare_provider" <?= ($_POST['role'] ?? '') === 'healthcare_provider' ? 'selected' : '' ?>>Healthcare Provider</option>
            </select>
          </div>
        </div>

        <button type="submit" class="btn-create">Create Account</button>

        <div class="login-row">
          Already have an account? <a href="login.php">Login</a>
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
  function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
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
