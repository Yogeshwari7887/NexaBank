<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$uid = $_SESSION['user_id'];

// Fetch user
$stmt = $conn->prepare('SELECT name,email,phone,address,gender,dob,balance,account_number,ifsc,created_at FROM user_accounts WHERE id=?');
$stmt->bind_param('i', $uid); $stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { session_destroy(); header('Location: login.php'); exit; }

// Last 5 transactions
$txStmt = $conn->prepare('
    SELECT t.*, s.name AS sender_name, r.name AS receiver_name
    FROM transactions t
    JOIN user_accounts s ON t.sender_id   = s.id
    JOIN user_accounts r ON t.receiver_id = r.id
    WHERE t.sender_id=? OR t.receiver_id=?
    ORDER BY t.date_time DESC LIMIT 5
');
$txStmt->bind_param('ii', $uid, $uid);
$txStmt->execute();
$txResult = $txStmt->get_result();

// Stats: total sent, total received this month
$sent = $conn->prepare('SELECT COALESCE(SUM(amount),0) AS s FROM transactions WHERE sender_id=? AND MONTH(date_time)=MONTH(NOW())');
$sent->bind_param('i', $uid); $sent->execute();
$sentAmt = $sent->get_result()->fetch_assoc()['s'];

$recv = $conn->prepare('SELECT COALESCE(SUM(amount),0) AS s FROM transactions WHERE receiver_id=? AND MONTH(date_time)=MONTH(NOW())');
$recv->bind_param('i', $uid); $recv->execute();
$recvAmt = $recv->get_result()->fetch_assoc()['s'];

$txCount = $conn->prepare('SELECT COUNT(*) AS c FROM transactions WHERE sender_id=? OR receiver_id=?');
$txCount->bind_param('ii', $uid, $uid); $txCount->execute();
$txTotal = $txCount->get_result()->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header mt-md">
      <p class="text-muted">Welcome back,</p>
      <h2><?= e($user['name']) ?> 👋</h2>
    </div>

    <!-- Balance + Stats -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:20px;margin-bottom:32px;" class="dashboard-top-grid">
      <div class="balance-card" style="grid-column:1/3;">
        <div class="label">Current Balance</div>
        <div class="amount"><?= currency((float)$user['balance']) ?></div>
        <div class="acno"><?= e($user['account_number']) ?></div>
        <div class="ifsc-badge">IFSC: <?= e($user['ifsc']) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Sent This Month</div>
        <div class="stat-value" style="font-size:1.4rem;color:var(--red-400);"><?= currency((float)$sentAmt) ?></div>
        <div class="stat-sub negative">↑ Outgoing</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Received This Month</div>
        <div class="stat-value" style="font-size:1.4rem;color:var(--green-400);"><?= currency((float)$recvAmt) ?></div>
        <div class="stat-sub">↓ Incoming</div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="action-grid" style="grid-template-columns:repeat(4,1fr);">
      <a href="transfer.php" class="action-card">
        <div class="icon">💸</div>
        <div class="label-text">Transfer Money</div>
      </a>
      <a href="transaction_history.php" class="action-card">
        <div class="icon">📊</div>
        <div class="label-text">Statements</div>
      </a>
      <a href="profile.php" class="action-card">
        <div class="icon">👤</div>
        <div class="label-text">My Profile</div>
      </a>
      <a href="logout.php" class="action-card" style="border-color:rgba(240,90,110,0.2);">
        <div class="icon">🚪</div>
        <div class="label-text" style="color:var(--red-400);">Logout</div>
      </a>
    </div>

    <!-- Bottom Grid -->
    <div class="grid-2 mt-lg">
      <!-- Recent Transactions -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">Recent Transactions</span>
          <a href="transaction_history.php" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <?php if ($txResult->num_rows === 0): ?>
          <div class="empty-state"><div class="icon">💳</div><h3>No transactions yet</h3></div>
        <?php else: ?>
          <?php while ($tx = $txResult->fetch_assoc()):
            $isSent = $tx['sender_id'] == $uid;
            $other  = $isSent ? $tx['receiver_name'] : $tx['sender_name'];
          ?>
          <div class="tx-row">
            <div class="tx-icon <?= $isSent ? 'sent' : 'received' ?>"><?= $isSent ? '↑' : '↓' ?></div>
            <div class="tx-info">
              <div class="tx-name"><?= $isSent ? 'Sent to ' : 'From ' ?><?= e($other) ?></div>
              <div class="tx-time"><?= timeAgo($tx['date_time']) ?><?= $tx['note'] ? ' · ' . e($tx['note']) : '' ?></div>
            </div>
            <div class="tx-amount <?= $isSent ? 'sent' : 'received' ?>"><?= $isSent ? '-' : '+' ?><?= currency((float)$tx['amount']) ?></div>
          </div>
          <?php endwhile; ?>
        <?php endif; ?>
      </div>

      <!-- Account Info -->
      <div class="card">
        <div class="card-header">
          <span class="card-title">Account Info</span>
          <span class="badge badge-active">Active</span>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px;">
          <div style="display:flex;justify-content:space-between;font-size:0.9rem;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
            <span class="text-muted">Account Number</span>
            <span style="font-family:var(--font-mono);font-size:0.85rem;"><?= e($user['account_number']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.9rem;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
            <span class="text-muted">IFSC Code</span>
            <span style="font-family:var(--font-mono);"><?= e($user['ifsc']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.9rem;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
            <span class="text-muted">Email</span>
            <span><?= e($user['email']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.9rem;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
            <span class="text-muted">Phone</span>
            <span><?= e($user['phone'] ?? '—') ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.9rem;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
            <span class="text-muted">Gender</span>
            <span><?= e($user['gender'] ?? '—') ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.9rem;padding-bottom:12px;border-bottom:1px solid rgba(255,255,255,0.05);">
            <span class="text-muted">Date of Birth</span>
            <span><?= $user['dob'] ? date('d M Y', strtotime($user['dob'])) : '—' ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:0.9rem;">
            <span class="text-muted">Member Since</span>
            <span><?= date('d M Y', strtotime($user['created_at'])) ?></span>
          </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:10px;">
          <a href="transfer.php" class="btn btn-primary" style="flex:1;">Transfer Money</a>
          <a href="transaction_history.php" class="btn btn-outline" style="flex:1;">View History</a>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
@media (max-width: 900px) {
  .dashboard-top-grid { grid-template-columns: 1fr 1fr !important; }
  .dashboard-top-grid .balance-card { grid-column: 1/3; }
}
@media (max-width: 600px) {
  .dashboard-top-grid { grid-template-columns: 1fr !important; }
  .dashboard-top-grid .balance-card { grid-column: auto; }
  .action-grid { grid-template-columns: 1fr 1fr !important; }
}
</style>

<?php include 'footer.php'; ?>
</body>
</html>
