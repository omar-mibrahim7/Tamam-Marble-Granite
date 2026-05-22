<?php

require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");

$adminRole = require_admin_role(['admin', 'staff'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
<?php if($isAdmin): ?>
<link rel="stylesheet" href="../css/dashboard.css?v=<?php echo filemtime('../css/dashboard.css'); ?>">
<?php else: ?>
<link rel="stylesheet" href="../css/staff-dashboard.css?v=<?php echo filemtime('../css/staff-dashboard.css'); ?>">
<?php endif; ?>
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
        <h1><?php echo $isAdmin ? 'Dashboard' : 'Staff Dashboard'; ?></h1>
    </div>
</header>
<?php render_admin_sidebar('dashboard'); ?>

<section class="<?php echo $isAdmin ? 'dashboard' : 'staff-dashboard'; ?>">
<h2><?php echo $isAdmin ? 'Admin Panel' : 'Staff Panel'; ?></h2>

<div class="<?php echo $isAdmin ? 'cards' : 'staff-cards'; ?>">
       <!-- Orders -->
<div class="<?php echo $isAdmin ? 'card' : 'staff-card'; ?>">
    <div class="<?php echo $isAdmin ? 'icon' : 'staff-icon'; ?>"><i class="fa-solid fa-box-open"></i></div>
    <h3>Orders</h3>
    <button onclick="goTo('personal-order.php')">View Orders</button>
</div>

        <!-- Messages -->
<div class="<?php echo $isAdmin ? 'card' : 'staff-card'; ?>">
<div class="<?php echo $isAdmin ? 'icon' : 'staff-icon'; ?>"><i class="fa-regular fa-comment-dots"></i></div>
            <h3>Messages</h3>
            <button onclick="goTo('<?php echo $isAdmin ? 'escalated-messages.php' : 'messages.php'; ?>')">View Messages</button>
        </div>

        <?php if($isAdmin): ?>
        <!-- New Item -->
        <div class="card">
            <div class="icon"><i class="fa-solid fa-plus"></i></div>
            <h3>New Item</h3>
            <button onclick="goTo('new-item.php')">Add Item</button>
        </div>

        <!-- Manage -->
<div class="card">

<div class="<?php echo $isAdmin ? 'icon' : 'staff-icon'; ?>">
            <i class="fa-solid fa-user-gear"></i>
    </div>

    <h3>Manage Items</h3>

    <button onclick="goTo('manage-items.php')">
        Manage Items
    </button>

</div>
        <?php endif; ?>
        <!-- Sales Dashboard - Admin Only -->
        <?php if($isAdmin): ?>
        <div class="card">
            <div class="icon"><i class="fa-solid fa-chart-bar"></i></div>
            <h3>Sales Dashboard</h3>
            <button onclick="goTo('saless.php')">View Sales</button>
        </div>

        <!-- Export Report - Admin Only -->
        <div class="card">
            <div class="icon"><i class="fa-solid fa-file-export"></i></div>
            <h3>Export Report</h3>
            <button onclick="goTo('report.php')">Export</button>
        </div>
        <?php endif; ?>

    </div>

</section>

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
<script src="../js/dashboard.js?v=<?php echo filemtime('../js/dashboard.js'); ?>"></script>

<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script>

</body>
</html>
