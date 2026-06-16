<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['admin_id'])) { header('Location: admin_dashboard.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare('SELECT id, username, password FROM admin_accounts WHERE email = ?');
        $stmt->bind_param('s', $email); $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']       = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid credentials. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-left">
    <div class="logo">🛡️ Admin Portal</div>
    <p class="tagline">Secure administrative access to NexaBank management systems.</p>
    <div style="margin-top:40px;padding:20px;background:rgba(240,90,110,0.08);border:1px solid rgba(240,90,110,0.2);border-radius:12px;max-width:320px;width:100%;">
      <p style="font-size:0.85rem;color:var(--red-400);font-weight:600;">🔒 Restricted Access</p>
      <p style="font-size:0.8rem;color:var(--slate-400);margin-top:6px;">Unauthorized access is prohibited. All activities are logged and monitored.</p>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-form-container">
      <h2>Admin Login</h2>
      <p class="subtitle">NexaBank Administration Panel</p>

      <?php foreach ($errors as $err): ?>
        <div class="alert alert-error">⚠️ <?= e($err) ?></div>
      <?php endforeach; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Admin Email</label>
          <input type="email" name="email" class="form-control" placeholder="admin@nexabank.com" value="<?= e($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">Access Admin Panel →</button>
      </form>

      <hr class="divider">
      <p class="text-center text-muted"><a href="index.php">← Back to main site</a></p>
      <p class="text-center text-muted mt-sm" style="font-size:0.78rem;">Default: admin@nexabank.com / password</p>
    </div>
  </div>
</div>
</body>
</html>
