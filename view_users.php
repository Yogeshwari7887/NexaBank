<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }

// Toggle active status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $conn->query("UPDATE user_accounts SET is_active = !is_active WHERE id=$tid");
    header('Location: view_users.php'); exit;
}

$search = trim($_GET['search'] ?? '');
$where  = $search ? "WHERE name LIKE '%{$conn->real_escape_string($search)}%' OR email LIKE '%{$conn->real_escape_string($search)}%'" : '';
$result = $conn->query("SELECT * FROM user_accounts $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>All Users – NexaBank Admin</title>
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
      <a href="view_users.php" class="active">👥 All Users</a>
      <a href="view_transactions.php">💳 Transactions</a>
      <div class="nav-section-title">System</div>
      <a href="index.php" style="color:var(--slate-600);">🌐 View Site</a>
      <a href="admin_logout.php" style="color:var(--red-400);">🚪 Logout</a>
    </nav>
  </aside>

  <main class="admin-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:14px;">
      <div>
        <h2>All Users</h2>
        <p class="text-muted">Manage registered bank accounts</p>
      </div>
      <a href="createuser.php" class="btn btn-primary">+ Create User</a>
    </div>

    <!-- Search -->
    <form method="GET" style="margin-bottom:20px;">
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input type="text" name="search" placeholder="Search by name or email..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-ghost btn-sm">Search</button>
        <?php if ($search): ?><a href="view_users.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
      </div>
    </form>

    <div class="card" style="padding:0;overflow:hidden;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Account Number</th>
              <th>Phone</th>
              <th>Balance</th>
              <th>Status</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="8" style="padding:40px;text-align:center;color:var(--slate-400);">No users found.</td></tr>
            <?php else: while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td style="color:var(--slate-600);font-family:var(--font-mono);">#<?= $row['id'] ?></td>
              <td>
                <div style="font-weight:600;"><?= e($row['name']) ?></div>
                <div style="font-size:0.78rem;color:var(--slate-400);"><?= e($row['email']) ?></div>
              </td>
              <td style="font-family:var(--font-mono);font-size:0.82rem;color:var(--slate-300);"><?= e($row['account_number']) ?></td>
              <td style="color:var(--slate-400);font-size:0.87rem;"><?= e($row['phone'] ?? '—') ?></td>
              <td style="font-family:var(--font-mono);font-weight:600;color:var(--green-400);">₹<?= number_format($row['balance'],2) ?></td>
              <td><span class="badge <?= $row['is_active'] ? 'badge-active' : 'badge-inactive' ?>"><?= $row['is_active'] ? 'Active' : 'Inactive' ?></span></td>
              <td style="color:var(--slate-400);font-size:0.82rem;"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
              <td>
                <a href="?toggle=<?= $row['id'] ?><?= $search ? "&search=".urlencode($search) : '' ?>"
                   class="btn btn-sm <?= $row['is_active'] ? 'btn-danger' : 'btn-outline' ?>"
                   onclick="return confirm('<?= $row['is_active'] ? 'Deactivate' : 'Activate' ?> this user?')">
                  <?= $row['is_active'] ? 'Deactivate' : 'Activate' ?>
                </a>
              </td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
      <div style="padding:12px 20px;border-top:1px solid rgba(255,255,255,0.05);color:var(--slate-400);font-size:0.82rem;text-align:right;">
        <?= $result->num_rows ?> user<?= $result->num_rows !== 1 ? 's' : '' ?> found
      </div>
    </div>
  </main>
</div>
</body>
</html>
