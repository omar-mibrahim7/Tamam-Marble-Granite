<?php
require_once(__DIR__ . "/../../Controller/admin_auth.php");
$adminRole = require_admin_role(['admin', 'staff'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/AdminOrder.php");

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$perPage = 10;
$totalOrders = AdminOrder::countOrdersByAccountType($conn, 'company', true);
$totalPages = max(1, (int)ceil($totalOrders / $perPage));
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $perPage;
$orders = AdminOrder::getOrdersByAccountType($conn, 'company', true, $perPage, $offset);
$startItem = $totalOrders > 0 ? $offset + 1 : 0;
$endItem = min($offset + count($orders), $totalOrders);
$pageRevenue = array_sum(array_map(function($order) {
    return (float)$order['total_amount'];
}, $orders));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Completed Company Orders</title>
  <link rel="stylesheet" href="../css/admin.css?v=<?php echo filemtime('../css/admin.css'); ?>">

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

<main class="admin-shell">

    <div class="tabs">
        <a href="personal-order.php">Personal Orders</a>
        <a href="company-order.php">Company Orders</a>
        <a href="completed-personal-orders.php">Completed Personal Orders</a>
        <a href="completed-company-orders.php"class="active">Completed Company Orders</a>
    </div>

    <h1 class="section-heading">Completed Company Orders</h1>

    <div class="order-summary enhanced-summary">
        <div>Showing: &nbsp; <?php echo count($orders); ?> of <?php echo $totalOrders; ?> Orders</div>
        <div>Page Revenue: &nbsp; <?php echo h(number_format($pageRevenue, 2)); ?> EGP</div>
    </div>

    <div class="table-wrap">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Order ID</th>
                    <th>Items</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7">No completed company orders found.</td>
                </tr>
                <?php endif; ?>

                <?php foreach($orders as $order): ?>
                <tr>
                    <td><?php echo h($order['full_name'] ?: $order['user_full_name']); ?></td>
                    <td><?php echo h($order['user_full_name'] ?: $order['full_name']); ?></td>
                    <td>#<?php echo (int)$order['booking_id']; ?></td>
                    <td><?php echo (int)$order['items_count']; ?></td>
                    <td><?php echo h(number_format((float)$order['total_amount'], 2)); ?> EGP</td>
                    <td><span class="status-badge status-completed">Completed</span></td>
                    <td>
                        <a class="btn btn-view" href="order-detail.php?id=<?php echo (int)$order['booking_id']; ?>">
                            View
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if($isAdmin): ?>
    <div class="table-actions">
        <a class="btn btn-export" href="report.php">Export Report</a>
    </div>
    <?php endif; ?>

<!-- Pagination -->
<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="completed-company-orders.php?page=<?php echo $currentPage - 1; ?>"><button class="circle-btn">&lt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&lt;</button>
  <?php endif; ?>

  <span class="page-number"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

  <?php if ($currentPage < $totalPages): ?>
    <a href="completed-company-orders.php?page=<?php echo $currentPage + 1; ?>"><button class="circle-btn">&gt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&gt;</button>
  <?php endif; ?>
</div>

</main>

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
            <a href="escalated-messages.php"><i class="fas fa-envelope"></i> Messages</a>
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

<script src="../js/admin.js?v=<?php echo filemtime('../js/admin.js'); ?>"></script>

<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script>
</body>
</html>
