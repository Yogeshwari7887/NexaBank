<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }

// Stats
$totalUsers   = $conn->query("SELECT COUNT(*) AS c FROM user_accounts")->fetch_assoc()['c'];
$activeUsers  = $conn->query("SELECT COUNT(*) AS c FROM user_accounts WHERE is_active=1")->fetch_assoc()['c'];
$totalTx      = $conn->query("SELECT COUNT(*) AS c FROM transactions")->fetch_assoc()['c'];
$totalVol     = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM transactions")->fetch_assoc()['s'];
$todayTx      = $conn->query("SELECT COUNT(*) AS c FROM transactions WHERE DATE(date_time)=CURDATE()")->fetch_assoc()['c'];
$todayVol     = $conn->query("SELECT COALESCE(SUM(amount),0) AS s FROM transactions WHERE DATE(date_time)=CURDATE()")->fetch_assoc()['s'];

// Recent transactions
$recentTx = $conn->query("
    SELECT t.id, t.amount, t.date_time, t.note,
           s.name AS sender_name, r.name AS receiver_name
    FROM transactions t
    JOIN user_accounts s ON t.sender_id=s.id
    JOIN user_accounts r ON t.receiver_id=r.id
    ORDER BY t.date_time DESC LIMIT 8
");

// Recent users
$recentUsers = $conn->query("SELECT id,name,email,balance,is_active,created_at FROM user_accounts ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      🏦 NexaBank
      <small>Admin Panel</small>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-title">Overview</div>
      <a href="admin_dashboard.php" class="active">📊 Dashboard</a>
      <div class="nav-section-title">Management</div>
      <a href="createuser.php">➕ Create User</a>
      <a href="view_users.php">👥 All Users</a>
      <a href="view_transactions.php">💳 Transactions</a>
      <div class="nav-section-title">System</div>
      <a href="index.php" style="color:var(--slate-600);">🌐 View Site</a>
      <a href="admin_logout.php" style="color:var(--red-400);">🚪 Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;">
      <div>
        <h2 style="margin-bottom:4px;">Dashboard</h2>
        <p class="text-muted">Welcome back, <?= e($_SESSION['admin_username']) ?> 👋</p>
      </div>
      <div style="display:flex;gap:10px;">
        <a href="createuser.php" class="btn btn-primary btn-sm">+ New User</a>
        <a href="admin_logout.php" class="btn btn-danger btn-sm">Logout</a>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid-4" style="margin-bottom:28px;">
      <div class="stat-card">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?= number_format($totalUsers) ?></div>
        <div class="stat-sub"><?= $activeUsers ?> active</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Total Volume</div>
        <div class="stat-value" style="font-size:1.5rem;">₹<?= number_format($totalVol/1000,1) ?>K</div>
        <div class="stat-sub"><?= number_format($totalTx) ?> transactions</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Today's Transactions</div>
        <div class="stat-value"><?= $todayTx ?></div>
        <div class="stat-sub">₹<?= number_format($todayVol) ?> today</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Avg Transaction</div>
        <div class="stat-value" style="font-size:1.5rem;">₹<?= $totalTx > 0 ? number_format($totalVol/$totalTx) : 0 ?></div>
        <div class="stat-sub">per transfer</div>
      </div>
    </div>

    <!-- Bottom Grid -->
    <div class="grid-2">
      <!-- Recent Transactions -->
      <div class="card" style="padding:0;overflow:hidden;">
        <div class="card-header" style="padding:18px 22px;">
          <span class="card-title">Recent Transactions</span>
          <a href="view_transactions.php" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Amount</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($tx = $recentTx->fetch_assoc()): ?>
              <tr>
                <td><?= e($tx['sender_name']) ?></td>
                <td><?= e($tx['receiver_name']) ?></td>
                <td style="font-family:var(--font-mono);color:var(--gold-400);">₹<?= number_format($tx['amount']) ?></td>
                <td style="color:var(--slate-400);font-size:0.8rem;"><?= timeAgo($tx['date_time']) ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Users -->
      <div class="card" style="padding:0;overflow:hidden;">
        <div class="card-header" style="padding:18px 22px;">
          <span class="card-title">Recent Users</span>
          <a href="view_users.php" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($u = $recentUsers->fetch_assoc()): ?>
              <tr>
                <td>
                  <div style="font-weight:600;"><?= e($u['name']) ?></div>
                  <div style="font-size:0.78rem;color:var(--slate-400);"><?= e($u['email']) ?></div>
                </td>
                <td style="font-family:var(--font-mono);color:var(--green-400);">₹<?= number_format($u['balance']) ?></td>
                <td><span class="badge <?= $u['is_active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                <td style="color:var(--slate-400);font-size:0.8rem;"><?= timeAgo($u['created_at']) ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>
</body>
</html>
