<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$uid     = $_SESSION['user_id'];
$message = '';
$msgType = 'error';

// Fetch sender
$stmt = $conn->prepare('SELECT id, name, balance FROM user_accounts WHERE id = ?');
$stmt->bind_param('i', $uid); $stmt->execute();
$sender = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiver_acno = trim($_POST['receiver_account'] ?? '');
    $receiver_ifsc = strtoupper(trim($_POST['receiver_ifsc'] ?? ''));
    $amount        = (float)($_POST['amount'] ?? 0);
    $note          = trim($_POST['note'] ?? '');

    if ($receiver_acno === '' || $receiver_ifsc === '' || $amount <= 0) {
        $message = 'Please fill in all fields with valid values.';
    } elseif ($amount > $sender['balance']) {
        $message = 'Insufficient balance. Your current balance is ' . currency($sender['balance']) . '.';
    } else {
        // Find receiver
        $rStmt = $conn->prepare('SELECT id, name FROM user_accounts WHERE account_number=? AND ifsc=? AND id != ?');
        $rStmt->bind_param('ssi', $receiver_acno, $receiver_ifsc, $uid);
        $rStmt->execute();
        $receiver = $rStmt->get_result()->fetch_assoc();

        if (!$receiver) {
            $message = 'Invalid account number or IFSC code. Please check and try again.';
        } else {
            mysqli_begin_transaction($conn);
            try {
                $conn->query("UPDATE user_accounts SET balance = balance - $amount WHERE id = {$sender['id']}");
                $conn->query("UPDATE user_accounts SET balance = balance + $amount WHERE id = {$receiver['id']}");
                $insStmt = $conn->prepare('INSERT INTO transactions (sender_id, receiver_id, amount, note, date_time) VALUES (?,?,?,?,NOW())');
                $insStmt->bind_param('iids', $sender['id'], $receiver['id'], $amount, $note);
                $insStmt->execute();
                mysqli_commit($conn);
                $msgType = 'success';
                $message = currency($amount) . ' transferred successfully to <strong>' . e($receiver['name']) . '</strong>.';
                // Refresh sender balance
                $stmt2 = $conn->prepare('SELECT balance FROM user_accounts WHERE id=?');
                $stmt2->bind_param('i', $uid); $stmt2->execute();
                $sender['balance'] = $stmt2->get_result()->fetch_assoc()['balance'];
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $message = 'Transaction failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Transfer Money – NexaBank</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="page-wrapper">
  <div class="container-sm">
    <div class="page-header mt-md">
      <div class="breadcrumb"><a href="dashboard.php">← Dashboard</a><span>/</span>Transfer Money</div>
      <h2>Transfer Money</h2>
      <p>Send funds instantly using account number and IFSC code</p>
    </div>

    <!-- Balance Info -->
    <div class="balance-card mb-md">
      <div class="label">Available Balance</div>
      <div class="amount"><?= currency((float)$sender['balance']) ?></div>
      <div class="acno"><?= e($sender['name']) ?></div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
        <?= $msgType === 'success' ? '✅' : '⚠️' ?> <?= $message ?>
        <?php if ($msgType === 'success'): ?>&nbsp;<a href="transaction_history.php">View History →</a><?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <span class="card-title">Fund Transfer</span>
        <div class="card-icon gold">💸</div>
      </div>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Receiver Account Number</label>
          <input type="text" name="receiver_account" class="form-control" placeholder="NEXA0000000001" style="font-family:var(--font-mono);" value="<?= e($_POST['receiver_account'] ?? '') ?>" required>
          <span class="form-hint">Enter the 14-digit NexaBank account number</span>
        </div>

        <div class="form-group">
          <label class="form-label">Receiver IFSC Code</label>
          <input type="text" name="receiver_ifsc" class="form-control" placeholder="NEXA0001" style="font-family:var(--font-mono);text-transform:uppercase;" value="<?= e($_POST['receiver_ifsc'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Amount (₹)</label>
          <input type="number" name="amount" class="form-control" placeholder="0.00" min="1" step="0.01" value="<?= e($_POST['amount'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Remarks / Note <span style="color:var(--slate-600)">(Optional)</span></label>
          <input type="text" name="note" class="form-control" placeholder="e.g. Rent, Dinner, Project payment..." maxlength="200" value="<?= e($_POST['note'] ?? '') ?>">
        </div>

        <div style="display:flex;gap:12px;margin-top:8px;">
          <a href="dashboard.php" class="btn btn-ghost" style="flex:1;">Cancel</a>
          <button type="submit" class="btn btn-primary" style="flex:2;">Send Money →</button>
        </div>
      </form>
    </div>

    <div class="card mt-md" style="background:var(--navy-700);border-color:rgba(212,170,82,0.1);">
      <h4 style="margin-bottom:12px;color:var(--gold-400);">ℹ️ Transfer Information</h4>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;">
        <li style="font-size:0.85rem;color:var(--slate-400);">• Transfers are processed instantly and cannot be reversed.</li>
        <li style="font-size:0.85rem;color:var(--slate-400);">• Only NexaBank accounts (IFSC: NEXA0001) are supported.</li>
        <li style="font-size:0.85rem;color:var(--slate-400);">• Ensure the account number and IFSC are correct before sending.</li>
      </ul>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
