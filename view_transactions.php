<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }

$search = trim($_GET['search'] ?? '');
$where  = '';
if ($search) {
    $s = $conn->real_escape_string($search);
    $where = "WHERE u1.name LIKE '%$s%' OR u2.name LIKE '%$s%' OR u1.email LIKE '%$s%' OR t.note LIKE '%$s%'";
}

$result = $conn->query("
    SELECT t.id, t.amount, t.note, t.status, t.date_time,
           u1.name AS sender_name, u1.email AS sender_email,
           u2.name AS receiver_name, u2.email AS receiver_email
    FROM transactions t
    JOIN user_accounts u1 ON t.sender_id   = u1.id
    JOIN user_accounts u2 ON t.receiver_id = u2.id
    $where
    ORDER BY t.date_time DESC
");

$total = $conn->query("SELECT COALESCE(SUM(t.amount),0) AS s FROM transactions t JOIN user_accounts u1 ON t.sender_id=u1.id JOIN user_accounts u2 ON t.receiver_id=u2.id $where")->fetch_assoc()['s'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Transactions – NexaBank Admin</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="admin-layout">
  <aside class="sidebar">
    <div class="sidebar-brand">🏦 NexaBank<small>Admin Panel</small></div>
    <nav class="sidebar-nav">
      <div class="nav-section-title">Overview</div>
      <a href="admin_dashboard.php">📊 Dashboard</a>
      <div class="nav-section-title">Management</div>
      <a href="createuser.php">➕ Create User</a>
      <a href="view_users.php">👥 All Users</a>
      <a href="view_transactions.php" class="active">💳 Transactions</a>
      <div class="nav-section-title">System</div>
      <a href="index.php" style="color:var(--slate-600);">🌐 View Site</a>
      <a href="admin_logout.php" style="color:var(--red-400);">🚪 Logout</a>
    </nav>
  </aside>

  <main class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:14px;">
      <div>
        <h2>All Transactions</h2>
        <p class="text-muted">Complete transaction ledger · Total Volume: <span style="color:var(--gold-400);font-weight:600;">₹<?= number_format($total,2) ?></span></p>
      </div>
    </div>

    <form method="GET" style="margin-bottom:20px;">
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Search by user name, email, or note..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-ghost btn-sm">Search</button>
        <?php if ($search): ?><a href="view_transactions.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </div>
    </form>

    <div class="card" style="padding:0;overflow:hidden;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Sender</th>
              <th>Receiver</th>
              <th>Note</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date & Time</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="7" style="padding:40px;text-align:center;color:var(--slate-400);">No transactions found.</td></tr>
            <?php else: while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td style="color:var(--slate-600);font-family:var(--font-mono);font-size:0.8rem;"><?= $row['id'] ?></td>
              <td>
                <div style="font-weight:600;"><?= e($row['sender_name']) ?></div>
                <div style="font-size:0.75rem;color:var(--slate-400);"><?= e($row['sender_email']) ?></div>
              </td>
              <td>
                <div style="font-weight:600;"><?= e($row['receiver_name']) ?></div>
                <div style="font-size:0.75rem;color:var(--slate-400);"><?= e($row['receiver_email']) ?></div>
              </td>
              <td style="color:var(--slate-400);font-size:0.85rem;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= $row['note'] ? e($row['note']) : '—' ?></td>
              <td style="font-family:var(--font-mono);font-weight:600;color:var(--gold-400);">₹<?= number_format($row['amount'],2) ?></td>
              <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
              <td style="color:var(--slate-400);font-size:0.82rem;white-space:nowrap;"><?= date('d M Y, H:i', strtotime($row['date_time'])) ?></td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
      <div style="padding:12px 20px;border-top:1px solid rgba(255,255,255,0.05);color:var(--slate-400);font-size:0.82rem;text-align:right;">
        <?= $result->num_rows ?> transaction<?= $result->num_rows !== 1 ? 's' : '' ?>
      </div>
    </div>
  </main>
</div>
</body>
</html>
