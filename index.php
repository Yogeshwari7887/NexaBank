<?php
session_start();
require_once 'config.php';

// Quick stats for hero section
$totalUsers = $conn->query("SELECT COUNT(*) AS c FROM user_accounts")->fetch_assoc()['c'] ?? 0;
$totalTx    = $conn->query("SELECT COUNT(*) AS c FROM transactions")->fetch_assoc()['c'] ?? 0;
$totalVol   = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM transactions")->fetch_assoc()['s'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NexaBank – Modern Banking</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-tag">✦ Next-Generation Banking</div>
      <h1>Banking That <span class="accent">Moves</span><br>With You</h1>
      <p>Experience seamless, secure, and smart financial management. Open your account today and take total control of your money.</p>
      <div class="hero-btns">
        <a href="createuser.php" class="btn btn-primary btn-lg">Open Account →</a>
        <a href="login.php"      class="btn btn-outline btn-lg">Login to Bank</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <div class="num"><?= number_format($totalUsers) ?>+</div>
          <div class="lbl">Active Accounts</div>
        </div>
        <div class="hero-stat">
          <div class="num"><?= number_format($totalTx) ?>+</div>
          <div class="lbl">Transactions</div>
        </div>
        <div class="hero-stat">
          <div class="num">₹<?= $totalVol >= 100000 ? number_format($totalVol/100000,1).'L' : number_format($totalVol) ?></div>
          <div class="lbl">Volume Processed</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Quick Actions -->
<section style="padding: 80px 0; background: var(--navy-900); border-top: 1px solid rgba(255,255,255,0.05);">
  <div class="container">
    <div class="text-center mb-md">
      <h2>Everything You Need</h2>
      <p style="margin-top:8px;">Comprehensive banking services in one place</p>
    </div>
    <div class="grid-3 mt-lg">
      <a href="createuser.php" class="action-card">
        <div class="icon">👤</div>
        <div class="label-text">Open Account</div>
        <p style="font-size:0.8rem;color:var(--slate-400);margin-top:6px;">Create your bank account in minutes</p>
      </a>
      <a href="login.php" class="action-card">
        <div class="icon">💸</div>
        <div class="label-text">Transfer Funds</div>
        <p style="font-size:0.8rem;color:var(--slate-400);margin-top:6px;">Instant secure money transfer</p>
      </a>
      <a href="login.php" class="action-card">
        <div class="icon">📊</div>
        <div class="label-text">View Statements</div>
        <p style="font-size:0.8rem;color:var(--slate-400);margin-top:6px;">Complete transaction history</p>
      </a>
    </div>
  </div>
</section>

<!-- Why NexaBank -->
<section style="padding: 80px 0;">
  <div class="container">
    <div class="text-center mb-md">
      <h2>Why Choose <span class="text-gold">NexaBank</span></h2>
      <p style="margin-top:8px;">Built with security and simplicity at the core</p>
    </div>
    <div class="grid-4 mt-lg">
      <div class="card text-center">
        <div style="font-size:2rem;margin-bottom:12px;">🔐</div>
        <h4>Bank-Grade Security</h4>
        <p style="font-size:0.85rem;margin-top:8px;">256-bit encryption, bcrypt password hashing, and session-based authentication.</p>
      </div>
      <div class="card text-center">
        <div style="font-size:2rem;margin-bottom:12px;">⚡</div>
        <h4>Instant Transfers</h4>
        <p style="font-size:0.85rem;margin-top:8px;">Transfer money instantly using account number and IFSC code.</p>
      </div>
      <div class="card text-center">
        <div style="font-size:2rem;margin-bottom:12px;">📈</div>
        <h4>Real-time Balance</h4>
        <p style="font-size:0.85rem;margin-top:8px;">Watch your balance update live after every transaction.</p>
      </div>
      <div class="card text-center">
        <div style="font-size:2rem;margin-bottom:12px;">🌐</div>
        <h4>24/7 Access</h4>
        <p style="font-size:0.85rem;margin-top:8px;">Bank from anywhere, any time, on any device.</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA Banner -->
<section style="padding: 60px 0; background: var(--navy-700); border-top:1px solid rgba(212,170,82,0.12); border-bottom:1px solid rgba(212,170,82,0.12);">
  <div class="container text-center">
    <h2>Ready to <span class="text-gold">Get Started?</span></h2>
    <p style="margin-top:10px;margin-bottom:28px;max-width:500px;margin-left:auto;margin-right:auto;">Join thousands of customers who trust NexaBank for their daily banking needs.</p>
    <a href="createuser.php" class="btn btn-primary btn-lg">Open a Free Account</a>
  </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
