<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, password FROM user_accounts WHERE email = ? AND is_active = 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $email;
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
  <!-- Left Panel -->
  <div class="auth-left">
    <div class="logo">🏦 NexaBank</div>
    <p class="tagline">Secure, modern banking at your fingertips. Log in to manage your finances with confidence.</p>
    <div style="margin-top:48px;display:flex;flex-direction:column;gap:18px;width:100%;">
      <div style="display:flex;align-items:center;gap:14px;background:rgba(212,170,82,0.07);border:1px solid rgba(212,170,82,0.15);border-radius:12px;padding:14px 18px;">
        <span style="font-size:1.3rem;">🔒</span>
        <div><div style="font-size:0.85rem;font-weight:600;color:var(--slate-100);">Encrypted Transactions</div><div style="font-size:0.78rem;color:var(--slate-400);">All transfers are end-to-end secured</div></div>
      </div>
      <div style="display:flex;align-items:center;gap:14px;background:rgba(212,170,82,0.07);border:1px solid rgba(212,170,82,0.15);border-radius:12px;padding:14px 18px;">
        <span style="font-size:1.3rem;">⚡</span>
        <div><div style="font-size:0.85rem;font-weight:600;color:var(--slate-100);">Instant Transfers</div><div style="font-size:0.78rem;color:var(--slate-400);">Money moves in real time</div></div>
      </div>
    </div>
  </div>

  <!-- Right Panel (Form) -->
  <div class="auth-right">
    <div class="auth-form-container">
      <h2>Welcome Back</h2>
      <p class="subtitle">Sign in to your NexaBank account</p>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-error">⚠️ <?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">Sign In →</button>
      </form>

      <hr class="divider">
      <p class="text-center text-muted">Don't have an account? <a href="createuser.php">Open one free</a></p>
      <p class="text-center text-muted mt-sm">Are you an admin? <a href="admin_login.php">Admin Login</a></p>
    </div>
  </div>
</div>
</body>
</html>
