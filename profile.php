<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$uid = $_SESSION['user_id'];
$errors = []; $success = '';

$stmt = $conn->prepare('SELECT * FROM user_accounts WHERE id=?');
$stmt->bind_param('i', $uid); $stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $newPass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '') $errors[] = 'Name cannot be empty.';
    if ($newPass !== '') {
        if (strlen($newPass) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($newPass !== $confirm) $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        if ($newPass !== '') {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $upd  = $conn->prepare('UPDATE user_accounts SET name=?,phone=?,address=?,password=? WHERE id=?');
            $upd->bind_param('ssssi', $name,$phone,$address,$hash,$uid);
        } else {
            $upd = $conn->prepare('UPDATE user_accounts SET name=?,phone=?,address=? WHERE id=?');
            $upd->bind_param('sssi', $name,$phone,$address,$uid);
        }
        if ($upd->execute()) {
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully.';
            $user['name'] = $name; $user['phone'] = $phone; $user['address'] = $address;
        } else {
            $errors[] = 'Update failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Profile – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-wrapper">
  <div class="container-sm">
    <div class="page-header mt-md">
      <div class="breadcrumb"><a href="dashboard.php">← Dashboard</a><span>/</span>My Profile</div>
      <h2>My Profile</h2>
      <p>Manage your account details and security</p>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= e($success) ?></div>
    <?php endif; ?>
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-error">⚠️ <?= e($err) ?></div>
    <?php endforeach; ?>

    <!-- Account card -->
    <div class="balance-card mb-md" style="display:flex;align-items:center;gap:20px;">
      <div style="width:60px;height:60px;background:linear-gradient(135deg,var(--gold-400),var(--gold-300));border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:700;color:var(--navy-900);">
        <?= strtoupper(substr($user['name'],0,1)) ?>
      </div>
      <div>
        <div class="label">Account Holder</div>
        <div style="font-size:1.2rem;font-weight:600;color:var(--gold-300);"><?= e($user['name']) ?></div>
        <div class="acno"><?= e($user['account_number']) ?> · IFSC: <?= e($user['ifsc']) ?></div>
      </div>
    </div>

    <div class="card">
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="2"><?= e($user['address'] ?? '') ?></textarea>
        </div>
        <hr class="divider">
        <h4 style="margin-bottom:16px;color:var(--slate-400);">Change Password <span style="font-size:0.78rem;font-weight:400;">(leave blank to keep current)</span></h4>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" placeholder="Min. 8 characters">
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password">
          </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:8px;">
          <a href="dashboard.php" class="btn btn-ghost" style="flex:1;">Cancel</a>
          <button type="submit" class="btn btn-primary" style="flex:2;">Save Changes</button>
        </div>
      </form>
    </div>

    <!-- Read-only info -->
    <div class="card mt-md">
      <div class="card-header"><span class="card-title">Account Information</span></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div><div class="text-muted" style="font-size:0.78rem;margin-bottom:3px;">EMAIL</div><div style="font-size:0.9rem;"><?= e($user['email']) ?></div></div>
        <div><div class="text-muted" style="font-size:0.78rem;margin-bottom:3px;">GENDER</div><div style="font-size:0.9rem;"><?= e($user['gender'] ?? '—') ?></div></div>
        <div><div class="text-muted" style="font-size:0.78rem;margin-bottom:3px;">DATE OF BIRTH</div><div style="font-size:0.9rem;"><?= $user['dob'] ? date('d M Y', strtotime($user['dob'])) : '—' ?></div></div>
        <div><div class="text-muted" style="font-size:0.78rem;margin-bottom:3px;">MEMBER SINCE</div><div style="font-size:0.9rem;"><?= date('d M Y', strtotime($user['created_at'])) ?></div></div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
