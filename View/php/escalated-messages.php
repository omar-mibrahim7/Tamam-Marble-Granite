<?php
require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
require_once(__DIR__ . "/../../config/db.php");

$adminRole = require_admin_role(['admin'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';



function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$statusLabels = [
    'unread' => 'New',
    'pending' => 'Read',
    'confirmed' => 'Replied'
];

$perPage = 16;
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_messages WHERE is_escalated = 1");
$countRow = mysqli_fetch_assoc($countResult);
$totalMessages = (int)($countRow['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalMessages / $perPage));
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $perPage;

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        cm.message_id,
        cm.customer_id,
        cm.subject,
        cm.message_body,
        cm.sent_date,
        cm.status,
        cm.full_name,
        cm.phone,
        cm.escalation_reason,
        cm.escalated_at,
        u.full_name AS user_full_name,
        u.phone AS user_phone,
        u.whatsapp AS user_whatsapp
     FROM contact_messages cm
     LEFT JOIN users u ON u.user_id = cm.customer_id
     WHERE cm.is_escalated = 1
     ORDER BY cm.escalated_at DESC, cm.message_id DESC
     LIMIT ? OFFSET ?"
);
mysqli_stmt_bind_param($stmt, "ii", $perPage, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$messages = [];

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

mysqli_stmt_close($stmt);
$startItem = $totalMessages > 0 ? $offset + 1 : 0;
$endItem = min($offset + count($messages), $totalMessages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Escalated Messages</title>
 <link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime('../css/admin.css'); ?>">

<link rel="stylesheet" href="../css/navbaradmin.css?v=<?php echo filemtime('../css/navbaradmin.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* Pagination */
.circle-btn {
  width: 30px;
  height: 30px;
  font-size: 12px;
}
.page-number {
  font-size: 12px;
  padding: 4px 12px;
}
.pagination {
  margin: 15px 0;
}

/* Section Heading */
.section-heading {
  font-size: 28px;
  font-weight: 700;
  text-align: center;
  margin: 30px 0 20px;
  display: inline-block;
  border-bottom: 3px solid #f4c542;
  padding-bottom: 6px;
}

.admin-shell {
  text-align: center;
}

/* Card */
.export-preview-card {
  background: #f9f9f9;
  border-radius: 16px;
  padding: 30px;
  margin: 0 auto 40px;
  max-width: 1300px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  text-align: left;
}

.export-panel-head {
  display: flex !important;
  flex-direction: row !important;
  justify-content: space-between !important;
  align-items: center !important;
  gap: 22px;
  margin-bottom: 22px;
}
.export-mini-stats {
  display: flex;
  justify-content: flex-end;
}
.export-panel-head h2 {
  font-size: 20px;
  font-weight: 700;
  margin: 0 0 6px;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* الخط الأصفر جنب العنوان */
.export-panel-head h2::before {
  content: "";
  display: inline-block;
  width: 5px;
  height: 24px;
  background: #f4c542;
  border-radius: 4px;
}

.export-panel-head p {
  color: #888;
  font-size: 14px;
  margin: 0;
}

/* Stats */
.export-mini-stats div {
  background: white;
  border-radius: 12px;
  padding: 10px 20px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.06);
  display: flex;
  align-items: center;
  gap: 10px;
}
.export-mini-stats strong {
  font-size: 22px;
  font-weight: 700;
  color: #f4c542;
}
.export-mini-stats span {
  font-size: 13px;
  color: #888;
}

/* Table */
.table-wrap {
  overflow-x: auto;
}
.orders-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
}
.orders-table thead tr {
  background: #eee;
  border-radius: 10px;
}
.orders-table th {
  padding: 16px 20px;
  font-weight: 600;
  color: #444;
  text-align: center;
}
.orders-table td {
  padding: 16px 20px;
  text-align: center;
  border-bottom: 1px solid #f0f0f0;
  color: #333;
}
.orders-table tbody tr:hover {
  background: #fafafa;
}

/* Status Badge */
.status-badge {
  padding: 5px 16px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
}
.status-unread   { background: #fff3cd; color: #856404; }
.status-pending  { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d1f0e0; color: #1a7a45; }
</style>
</head>
<body>

<header class="header">
    <div class="overlay"></div>

    <div class="navbar">

        <!-- MENU ICON -->
        <i class="fas fa-bars menu-icon" onclick="toggleMenu()"></i>

        <!-- LOGO -->
        <img src="../pic/logo.png" class="logo">

        <!-- RIGHT ICON -->
        <div class="right-icons">
            <a href="dashboard.php">
                <i class="fas fa-user-shield admin-icon"></i>
            </a>
        </div>

    </div>

    <!-- TITLE -->
    <div class="title">
        <h1><?php echo $isAdmin ? 'Messages' : 'Staff Panel'; ?></h1>
    </div>
</header>


<?php render_admin_sidebar('escalated-messages'); ?>

<main class="admin-shell">
    <h1 class="section-heading">Escalated Messages</h1>

    <section class="export-preview-card">
        <div class="export-panel-head">
            <div>
                <h2>Messages Requiring Admin Review</h2>
                <p>Only messages escalated by staff appear here.</p>
            </div>
            <div class="export-mini-stats">
                <div><strong><?php echo $totalMessages; ?></strong><span>Escalated</span></div>
            </div>
        </div>

        <div class="table-wrap">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Phone / WhatsApp</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Reason</th>
                        <th>Escalated At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="8">No escalated messages found.</td>
                    </tr>
                    <?php endif; ?>

                    <?php foreach ($messages as $msg): ?>
                    <?php
                        $clientName = $msg['full_name'] ?: ($msg['user_full_name'] ?: 'Visitor');
                        $contact = $msg['phone'] ?: ($msg['user_whatsapp'] ?: ($msg['user_phone'] ?: '-'));
                        $status = $msg['status'] ?: 'unread';
                    ?>
                    <tr>
                        <td>#<?php echo (int)$msg['message_id']; ?></td>
                        <td><?php echo h($clientName); ?></td>
                        <td><?php echo h($contact); ?></td>
                        <td><?php echo h($msg['subject'] ?: '-'); ?></td>
                        <td><?php echo h($msg['message_body'] ?: '-'); ?></td>
                        <td><?php echo h($msg['escalation_reason'] ?: '-'); ?></td>
                        <td><?php echo $msg['escalated_at'] ? h(date('Y-m-d H:i', strtotime($msg['escalated_at']))) : '-'; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo h(str_replace('_', '-', $status)); ?>">
                                <?php echo h($statusLabels[$status] ?? ucfirst($status)); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

   <!-- Pagination -->
<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="escalated-messages.php?page=<?php echo $currentPage - 1; ?>"><button class="circle-btn">&lt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&lt;</button>
  <?php endif; ?>

  <span class="page-number"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

  <?php if ($currentPage < $totalPages): ?>
    <a href="escalated-messages.php?page=<?php echo $currentPage + 1; ?>"><button class="circle-btn">&gt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&gt;</button>
  <?php endif; ?>
</div>

    </section>
</main>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <div class="logo-box">
                <img src="../pic/logo.png" alt="Logo">
                <h2>Tamam Marble & Granite</h2>
            </div>
            <p>Tamam Marble & Granite delivers high-quality marble and granite solutions using advanced manufacturing and precision craftsmanship.</p>
        </div>
        <div class="footer-links">
            <h3>Quick Links</h3>
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="manage-items.php"><i class="fas fa-cog"></i> Manage Products</a>
            <a href="new-item.php"><i class="fas fa-plus"></i> New Item</a>
            <a href="report.php"><i class="fas fa-file-export"></i> Reports</a>
            <a href="escalated-messages.php"><i class="fas fa-triangle-exclamation"></i> Escalated Messages</a>
        </div>
        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p><i class="fas fa-map-marker-alt"></i> 123 Street Name, City</p>
            <p><i class="fas fa-phone"></i> +20 01111111111</p>
            <p><i class="fas fa-envelope"></i> info@gmail.com</p>
        </div>
    </div>
</footer>

<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script></body>
</html>
