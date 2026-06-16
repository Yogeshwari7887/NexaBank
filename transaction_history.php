<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$uid = $_SESSION['user_id'];

// Filters
$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$where = "WHERE (t.sender_id=$uid OR t.receiver_id=$uid)";
if ($filter === 'sent')     $where .= " AND t.sender_id=$uid";
if ($filter === 'received') $where .= " AND t.receiver_id=$uid";
if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where .= " AND (s.name LIKE '%$s%' OR r.name LIKE '%$s%' OR t.note LIKE '%$s%')";
}

$query = "
    SELECT t.*, s.name AS sender_name, r.name AS receiver_name
    FROM transactions t
    JOIN user_accounts s ON t.sender_id   = s.id
    JOIN user_accounts r ON t.receiver_id = r.id
    $where
    ORDER BY t.date_time DESC
";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Transaction History – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-wrapper">
  <div class="container">
    <div class="page-header mt-md" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">
      <div>
        <div class="breadcrumb"><a href="dashboard.php">← Dashboard</a><span>/</span>Transaction History</div>
        <h2>Transaction History</h2>
        <p>Complete record of all your transactions</p>
      </div>
      <a href="transfer.php" class="btn btn-primary">+ New Transfer</a>
    </div>

    <!-- Filters -->
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:24px;">
      <div class="search-bar" style="flex:1;min-width:200px;margin-bottom:0;">
        <span class="search-icon">🔍</span>
        <form method="GET" style="display:contents;">
          <input type="text" name="search" placeholder="Search by name or note..." value="<?= e($search) ?>" onchange="this.form.submit()">
          <input type="hidden" name="filter" value="<?= e($filter) ?>">
        </form>
      </div>
      <div style="display:flex;gap:8px;">
        <a href="?filter=all"      class="btn btn-sm <?= $filter==='all'      ? 'btn-primary' : 'btn-ghost' ?>">All</a>
        <a href="?filter=sent"     class="btn btn-sm <?= $filter==='sent'     ? 'btn-primary' : 'btn-ghost' ?>">Sent</a>
        <a href="?filter=received" class="btn btn-sm <?= $filter==='received' ? 'btn-primary' : 'btn-ghost' ?>">Received</a>
      </div>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Type</th>
              <th>Counterparty</th>
              <th>Note</th>
              <th>Amount</th>
              <th>Date & Time</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $count = 0;
            if (mysqli_num_rows($result) === 0):
            ?>
            <tr>
              <td colspan="6" style="padding:48px;text-align:center;color:var(--slate-400);">
                <div style="font-size:2rem;margin-bottom:10px;">💳</div>
                No transactions found.
              </td>
            </tr>
            <?php else: while ($row = mysqli_fetch_assoc($result)): $count++; ?>
            <?php
              $isSent = $row['sender_id'] == $uid;
              $other  = $isSent ? $row['receiver_name'] : $row['sender_name'];
            ?>
            <tr>
              <td style="color:var(--slate-600);font-family:var(--font-mono);font-size:0.8rem;">#<?= $row['id'] ?></td>
              <td><span class="badge <?= $isSent ? 'badge-sent' : 'badge-received' ?>"><?= $isSent ? '↑ Sent' : '↓ Received' ?></span></td>
              <td><strong><?= e($other) ?></strong></td>
              <td style="color:var(--slate-400);font-size:0.85rem;"><?= $row['note'] ? e($row['note']) : '—' ?></td>
              <td style="font-family:var(--font-mono);font-weight:600;color:<?= $isSent ? 'var(--red-400)' : 'var(--green-400)' ?>;">
                <?= $isSent ? '-' : '+' ?><?= currency((float)$row['amount']) ?>
              </td>
              <td style="color:var(--slate-400);font-size:0.85rem;white-space:nowrap;"><?= date('d M Y, H:i', strtotime($row['date_time'])) ?></td>
            </tr>
            <?php endwhile; endif; ?>
          </tbody>
        </table>
      </div>
      <?php if ($count > 0): ?>
      <div style="padding:14px 20px;text-align:right;border-top:1px solid rgba(255,255,255,0.05);color:var(--slate-400);font-size:0.82rem;">
        Showing <?= $count ?> transaction<?= $count !== 1 ? 's' : '' ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
