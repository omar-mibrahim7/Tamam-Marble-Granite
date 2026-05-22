<?php
require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
require_once(__DIR__ . "/../../config/db.php");

$adminRole = require_admin_role(['admin', 'staff'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$statusLabels = [
    'unread' => 'New',
    'pending' => 'Read',
    'confirmed' => 'Replied'
];

$statusClasses = [
    'unread' => 'new',
    'pending' => 'pending',
    'confirmed' => 'confirmed'
];

$perPage = 10;
$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM contact_messages WHERE COALESCE(is_escalated, 0) = 0");
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
        u.full_name AS user_full_name,
        u.phone AS user_phone,
        u.whatsapp AS user_whatsapp
     FROM contact_messages cm
     LEFT JOIN users u ON u.user_id = cm.customer_id
     WHERE COALESCE(cm.is_escalated, 0) = 0
     ORDER BY cm.sent_date DESC, cm.message_id DESC
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Message</title>
 <link rel="stylesheet" href="../css/messages.css?v=<?php echo filemtime('../css/messages.css'); ?>">

<link rel="stylesheet" href="../css/navbaradmin.css?v=<?php echo filemtime('../css/navbaradmin.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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


<?php render_admin_sidebar('messages'); ?>

<div class="container">

  <h2 class="section-title">
    <span class="gold-bar"></span>
    Inbox
  </h2>

  <?php if (isset($_GET['escalated'])): ?>
    <div class="message-alert success">Message escalated to admin successfully.</div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="message-alert error">Message action could not be completed.</div>
  <?php endif; ?>

  <div class="table messages-table">

    <div class="table-header">
      <span>Subject</span>
      <span>Message</span>
      <span>Client Name</span>
      <span>ID Message</span>
      <span>WhatsApp</span>
      <span>Date</span>
      <span>Status</span>
      <span>Escalate</span>
    </div>

    <?php if (empty($messages)): ?>
      <div class="row">
        <span style="grid-column:1 / -1; justify-content:center; color:#888;">No messages found.</span>
      </div>
    <?php endif; ?>

    <?php foreach ($messages as $msg): ?>
      <?php
        $status = $msg['status'] ?: 'unread';
        $statusClass = $statusClasses[$status] ?? 'new';
        $clientName = $msg['full_name'] ?: ($msg['user_full_name'] ?: 'Visitor');
        $whatsapp = $msg['phone'] ?: ($msg['user_whatsapp'] ?: ($msg['user_phone'] ?: '-'));
        $whatsappDigits = preg_replace('/\D+/', '', $whatsapp);
      ?>
      <div class="row">
        <span><?php echo h($msg['subject']); ?></span>
        <span class="msg-text"><?php echo h($msg['message_body']); ?></span>
        <span><?php echo h($clientName); ?></span>
        <span>#<?php echo (int)$msg['message_id']; ?></span>

        <span>
          <?php if ($whatsappDigits !== ''): ?>
            <a href="https://wa.me/2<?php echo h($whatsappDigits); ?>" target="_blank">
              <?php echo h($whatsapp); ?>
            </a>
          <?php else: ?>
            -
          <?php endif; ?>
        </span>

        <span><?php echo h(date('Y-m-d', strtotime($msg['sent_date']))); ?></span>

        <span>
          <div class="status-dropdown">
            <div class="selected <?php echo h($statusClass); ?>">
              <?php echo h($statusLabels[$status] ?? ucfirst($status)); ?> <span class="arrow">&#9662;</span>
            </div>
            <div class="options">
              <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                <div class="option <?php echo h($statusClasses[$statusValue]); ?>"
                     onclick="updateMessageStatus(<?php echo (int)$msg['message_id']; ?>, '<?php echo h($statusValue); ?>')">
                  <?php echo h($statusLabel); ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </span>

        <span>
          <form class="escalate-form" method="POST" action="../../Controller/escalate_message.php">
            <input type="hidden" name="message_id" value="<?php echo (int)$msg['message_id']; ?>">
            <input type="text" name="escalation_reason" placeholder="Reason">
            <button type="submit">Escalate</button>
          </form>
        </span>

      </div>
    <?php endforeach; ?>

  </div>
<!-- Pagination -->
<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="messages.php?page=<?php echo $currentPage - 1; ?>"><button class="circle-btn">&lt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&lt;</button>
  <?php endif; ?>

  <span class="page-number"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

  <?php if ($currentPage < $totalPages): ?>
    <a href="messages.php?page=<?php echo $currentPage + 1; ?>"><button class="circle-btn">&gt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&gt;</button>
  <?php endif; ?>
</div>

</div>
<footer class="footer">
    <div class="footer-content">

        <div class="footer-left">
            <div class="logo-box">
                <img src="../pic/logo.png" alt="Logo">
                <h2>Tamam Marble & Granite</h2>
            </div>
            <p>Tamam Marble & Granite delivers high-quality marble and granite solutions using advanced manufacturing and precision craftsmanship.</p>
            <div class="socials">
                <img src="../pic/social.png" class="social-img">
                <i class="fab fa-facebook-f"></i>
                <i class="fab fa-whatsapp"></i>
                <i class="fab fa-telegram"></i>
                <i class="fab fa-youtube"></i>
            </div>
        </div>

        <div class="footer-links">
            <h3>Quick Links</h3>
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="personal-order.php"><i class="fas fa-box"></i> Orders</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
            <?php if($isAdmin): ?>
            <a href="new-item.php"><i class="fas fa-plus"></i> New Item</a>
            <a href="manage-items.php"><i class="fas fa-cog"></i> Manage Items</a>
            <a href="sales.php"><i class="fas fa-chart-bar"></i> Sales Dashboard</a>
            <a href="report.php"><i class="fas fa-file-export"></i> Export Report</a>
            <?php endif; ?>
        </div>

        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p><i class="fas fa-map-marker-alt"></i> 123 Street Name, City</p>
            <p><i class="fas fa-phone"></i> +20 01111111111</p>
            <p><i class="fas fa-envelope"></i> info@gmail.com</p>
        </div>

    </div>
</footer>
<script src="../js/messages.js?v=<?php echo filemtime('../js/messages.js'); ?>"></script>

<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script>
</body>
</html>
