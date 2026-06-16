<?php
session_start();
require_once 'config.php';

$isAdmin = isset($_SESSION['admin_id']);
$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $phone    = trim($_POST['phone']    ?? '');
    $address  = trim($_POST['address']  ?? '');
    $gender   = $_POST['gender']  ?? 'Other';
    $dob      = $_POST['dob']     ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $balance  = (float)($_POST['balance'] ?? 0);

    if ($name===''||$email===''||$password==='') $errors[]='Name, email, and password are required.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Please enter a valid email address.';
    if (strlen($password)<8) $errors[]='Password must be at least 8 characters.';
    if ($password!==$confirm) $errors[]='Passwords do not match.';
    if ($balance<0) $errors[]='Initial deposit cannot be negative.';

    if (empty($errors)) {
        $chk=$conn->prepare('SELECT id FROM user_accounts WHERE email=?');
        $chk->bind_param('s',$email);$chk->execute();$chk->store_result();
        if ($chk->num_rows>0) {
            $errors[]='An account with this email already exists.';
        } else {
            $chk->close();
            $hash=password_hash($password,PASSWORD_DEFAULT);
            $acno=generateAccountNumber($conn);
            $ifsc=BANK_IFSC;
            $ins=$conn->prepare('INSERT INTO user_accounts (name,email,phone,address,gender,dob,password,account_number,ifsc,balance) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $ins->bind_param('sssssssssd',$name,$email,$phone,$address,$gender,$dob,$hash,$acno,$ifsc,$balance);
            if ($ins->execute()) {
                $success="User created! Account No: <strong>$acno</strong> | IFSC: <strong>$ifsc</strong>";
                $_POST=[];
            } else {
                $errors[]='Could not create user. Please try again.';
            }
            $ins->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Create User – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if ($isAdmin): ?>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">&#127974; NexaBank<small>Admin Panel</small></div>
    <nav class="sidebar-nav">
      <div class="nav-section-title">Overview</div>
      <a href="admin_dashboard.php">&#128202; Dashboard</a>
      <div class="nav-section-title">Management</div>
      <a href="createuser.php" class="active">&#10133; Create User</a>
      <a href="view_users.php">&#128101; All Users</a>
      <a href="view_transactions.php">&#128179; Transactions</a>
      <div class="nav-section-title">System</div>
      <a href="index.php" style="color:var(--slate-600);">&#127758; View Site</a>
      <a href="admin_logout.php" style="color:var(--red-400);">&#128682; Logout</a>
    </nav>
  </aside>
  <main class="admin-content">
    <div style="margin-bottom:28px;">
      <h2>Create New User</h2>
      <p class="text-muted">Register a new bank account manually</p>
    </div>
    <?php if ($success): ?><div class="alert alert-success">&#9989; <?= $success ?> &nbsp;<a href="view_users.php">View all users &rarr;</a></div><?php endif; ?>
    <?php foreach ($errors as $err): ?><div class="alert alert-error">&#9888;&#65039; <?= e($err) ?></div><?php endforeach; ?>
    <div class="card" style="max-width:700px;">
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" placeholder="Arjun Sharma" value="<?= e($_POST['name']??'') ?>" required></div>
          <div class="form-group"><label class="form-label">Email Address *</label><input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= e($_POST['email']??'') ?>" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Phone Number</label><input type="tel" name="phone" class="form-control" placeholder="9876543210" value="<?= e($_POST['phone']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?= e($_POST['dob']??'') ?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Gender</label><select name="gender" class="form-control"><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
          <div class="form-group"><label class="form-label">Initial Balance (Rs.)</label><input type="number" name="balance" class="form-control" placeholder="0.00" min="0" step="0.01" value="<?= e($_POST['balance']??'0') ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2" placeholder="12 MG Road, Mumbai..."><?= e($_POST['address']??'') ?></textarea></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required></div>
          <div class="form-group"><label class="form-label">Confirm Password *</label><input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required></div>
        </div>
        <div style="display:flex;gap:12px;margin-top:8px;">
          <a href="view_users.php" class="btn btn-ghost" style="flex:1;">Cancel</a>
          <button type="submit" class="btn btn-primary" style="flex:2;">Create Account &rarr;</button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php else: ?>
<?php include 'navbar.php'; ?>
<div class="page-wrapper">
  <div class="container-sm">
    <div class="page-header mt-md">
      <div class="breadcrumb"><a href="index.php">Home</a><span>/</span>Open Account</div>
      <h2>Open Your Account</h2>
      <p>Fill in the details below to get started with NexaBank</p>
    </div>
    <?php if ($success): ?><div class="alert alert-success">&#9989; <?= $success ?> &nbsp;<a href="login.php">Login now &rarr;</a></div><?php endif; ?>
    <?php foreach ($errors as $err): ?><div class="alert alert-error">&#9888;&#65039; <?= e($err) ?></div><?php endforeach; ?>
    <div class="card">
      <form method="POST">
        <div class="form-row">
          <div class="form-group"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" placeholder="Arjun Sharma" value="<?= e($_POST['name']??'') ?>" required></div>
          <div class="form-group"><label class="form-label">Email Address *</label><input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= e($_POST['email']??'') ?>" required></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" placeholder="9876543210" value="<?= e($_POST['phone']??'') ?>"></div>
          <div class="form-group"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?= e($_POST['dob']??'') ?>"></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Gender</label><select name="gender" class="form-control"><option value="Male">Male</option><option value="Female">Female</option><option value="Other">Other</option></select></div>
          <div class="form-group"><label class="form-label">Initial Deposit (Rs.)</label><input type="number" name="balance" class="form-control" placeholder="0.00" min="0" step="0.01" value="<?= e($_POST['balance']??'0') ?>"></div>
        </div>
        <div class="form-group"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= e($_POST['address']??'') ?></textarea></div>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required><span class="form-hint">At least 8 characters</span></div>
          <div class="form-group"><label class="form-label">Confirm Password *</label><input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required></div>
        </div>
        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:12px;">Create Account &rarr;</button>
      </form>
    </div>
    <p class="text-center text-muted mt-md">Already have an account? <a href="login.php">Sign in here</a></p>
  </div>
</div>
<?php include 'footer.php'; ?>
<?php endif; ?>
</body>
</html>
