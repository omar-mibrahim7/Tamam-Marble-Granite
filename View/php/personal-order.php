<?php
require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
$adminRole = require_admin_role(['admin', 'staff'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/AdminOrder.php");

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$perPage = 16;
$totalOrders = AdminOrder::countOrdersByAccountType($conn, 'customer', false);
$totalPages = max(1, (int)ceil($totalOrders / $perPage));
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $perPage;
$orders = AdminOrder::getOrdersByAccountType($conn, 'customer', false, $perPage, $offset);
$startItem = $totalOrders > 0 ? $offset + 1 : 0;
$endItem = min($offset + count($orders), $totalOrders);
$statusLabels = AdminOrder::statusLabels();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Personal Orders</title>
<link rel="stylesheet" href="../css/personal order.css?v=<?php echo filemtime('../css/personal order.css'); ?>">

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
        <h1><?php echo $isAdmin ? 'Orders' : 'Staff Panel'; ?></h1>
    </div>
</header>


<?php render_admin_sidebar('orders'); ?>

<div class="container">

    <div class="tabs">
        <a href="personal-order.php" class="active">Personal Orders</a>
        <a href="company-order.php">Company Orders</a>
        <a href="completed-personal-orders.php">Completed Personal Orders</a>
        <a href="completed-company-orders.php">Completed Company Orders</a>
    </div>

    <h2 class="section-title">
        <span class="gold-bar"></span>
        Personal Orders
    </h2>

    <div class="table">

        <div class="table-header">
            <span>Client Name</span>
            <span>Order ID</span>
            <span>Items</span>
            <span>WhatsApp Number</span>
            <span>Site Visit</span>
            <span>Status</span>
        </div>

        <?php if (empty($orders)): ?>
        <div class="row">
            <span style="grid-column:1 / -1; justify-content:center; color:#888;">No personal orders found.</span>
        </div>
        <?php endif; ?>

        <?php foreach($orders as $order): ?>
        <div class="row">
            <span><?php echo h($order['full_name'] ?: $order['user_full_name']); ?></span>
            <span>#<?php echo (int)$order['booking_id']; ?></span>
            <span><?php echo (int)$order['items_count']; ?></span>
            <span><?php echo h($order['whatsapp'] ?: ($order['user_whatsapp'] ?: $order['phone'])); ?></span>
            <span><?php echo !empty($order['engineer_requested']) ? 'Yes' : 'No'; ?></span>

            <div class="actions">
                <a class="view-btn" href="order-detail.php?id=<?php echo (int)$order['booking_id']; ?>">
                    View
                </a>

                <div class="status-dropdown">
                    <div class="selected <?php echo h($order['status']); ?>">
                        <?php echo h($statusLabels[$order['status']] ?? ucfirst($order['status'])); ?> &#9662;
                    </div>
                    <div class="options">
                        <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                            <div class="option <?php echo h($statusValue); ?>"
                                 onclick="updateOrderStatus(<?php echo (int)$order['booking_id']; ?>, '<?php echo h($statusValue); ?>')">
                                <?php echo h($statusLabel); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
        <?php endforeach; ?>

    </div>

<!-- Pagination -->
<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="personal-order.php?page=<?php echo $currentPage - 1; ?>"><button class="circle-btn">&lt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&lt;</button>
  <?php endif; ?>

  <span class="page-number"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

  <?php if ($currentPage < $totalPages): ?>
    <a href="personal-order.php?page=<?php echo $currentPage + 1; ?>"><button class="circle-btn">&gt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&gt;</button>
  <?php endif; ?>
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
            <a href="personal-order.php"><i class="fas fa-box"></i> Orders</a>
            <a href="escalated-messages.php"><i class="fas fa-envelope"></i> Messages</a>
            <a href="completed-personal-orders.php"><i class="fas fa-check"></i> Completed Personal Orders</a>
            <a href="completed-company-orders.php"><i class="fas fa-check-double"></i> Completed Company Orders</a>
        </div>
        <div class="footer-contact">
            <h3>Contact Us</h3>
            <p><i class="fas fa-map-marker-alt"></i> 123 Street Name, City</p>
            <p><i class="fas fa-phone"></i> +20 01111111111</p>
            <p><i class="fas fa-envelope"></i> info@gmail.com</p>
        </div>
    </div>
</footer>
<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script>
<script src="../js/personal-order.js?v=<?php echo filemtime('../js/personal-order.js'); ?>"></script>
</body>
</html>
