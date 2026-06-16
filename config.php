<?php
// ============================================================
//  NexaBank – config.php
//  Database connection + global helpers
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nexabank');

define('BANK_NAME',  'NexaBank');
define('BANK_IFSC',  'NEXA0001');
define('SITE_URL',   'http://localhost/nexabank');

// --- Connection (OOP style) -----------------------------------
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;color:#c0392b;padding:40px;">
         <h2>Database Connection Failed</h2>
         <p>' . $conn->connect_error . '</p></div>');
}
$conn->set_charset('utf8mb4');

// --- Helper: generate unique account number ------------------
function generateAccountNumber(mysqli $conn): string {
    do {
        $num = 'NEXA' . str_pad(mt_rand(1, 9999999999), 10, '0', STR_PAD_LEFT);
        $r   = $conn->prepare("SELECT id FROM user_accounts WHERE account_number=?");
        $r->bind_param('s', $num);
        $r->execute();
        $r->store_result();
        $exists = $r->num_rows > 0;
        $r->close();
    } while ($exists);
    return $num;
}

// --- Helper: currency format ---------------------------------
function currency(float $amount): string {
    return '₹' . number_format($amount, 2);
}

// --- Helper: sanitise output ---------------------------------
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// --- Helper: relative time -----------------------------------
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff/60)   . 'm ago';
    if ($diff < 86400)  return floor($diff/3600)  . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('d M Y', strtotime($datetime));
}
