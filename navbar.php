<?php
// navbar.php – shared navigation
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
  <a class="navbar-brand" href="index.php">
    <div class="brand-icon">🏦</div>
    NexaBank
  </a>

  <div class="navbar-links">
    <a href="index.php"    class="<?= $current==='index.php'    ? 'active' : '' ?>">Home</a>
    <a href="services.php" class="<?= $current==='services.php' ? 'active' : '' ?>">Services</a>
    <a href="admin_login.php" class="<?= $current==='admin_login.php' ? 'active' : '' ?>">Admin</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="dashboard.php" class="<?= $current==='dashboard.php' ? 'active' : '' ?>">Dashboard</a>
    <?php else: ?>
      <a href="createuser.php" class="<?= $current==='createuser.php' ? 'active' : '' ?>">Register</a>
      <a href="login.php" class="btn btn-primary btn-sm" style="margin-left:6px;">Login</a>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['user_name'])): ?>
  <div class="navbar-user">
    <div class="navbar-avatar"><?= strtoupper(substr($_SESSION['user_name'],0,1)) ?></div>
    <span><?= e($_SESSION['user_name']) ?></span>
  </div>
  <?php endif; ?>
</nav>
