<?php
require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
require_admin_role(['admin'], "adminlogin.php");

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/AdminReport.php");

$stats = AdminReport::getStats($conn);
$monthlyData = AdminReport::getMonthlyPerformance($conn, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Dashboard</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/navbaradmin.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="header">
    <div class="overlay"></div>
    <div class="navbar">
        <i class="fas fa-bars menu-icon" onclick="toggleMenu()"></i>
        <img src="../pic/logo.png" class="logo">
        <div class="right-icons">
            <div class="search-container">
                <i class="fas fa-search" onclick="toggleSearch()"></i>
                <input type="text" id="searchInput" placeholder="Search...">
            </div>
            <i class="fas fa-user-shield admin-icon"></i>
        </div>
    </div>
    <div class="title">
        <h1>Admin Panel</h1>
    </div>
</header>

<?php render_admin_sidebar('report'); ?>

<main class="admin-shell">

    <nav class="admin-tabs">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage-items.php">Manage Products</a>
        <a href="new-item.php">New Item</a>
        <a class="active" href="sales.php">Sales Dashboard</a>
        <a href="report.php">Export Report</a>
        <a href="escalated-messages.php">Escalated<br>Messages</a>
    </nav>

    <h1 class="section-heading">Sales Dashboard</h1>

    <!-- Orders Stats -->
    <section class="stats-grid">
        <div class="stat-card">
            <span>Personal Orders</span>
            <strong><?php echo $stats['personalOrders']; ?></strong>
        </div>
        <div class="stat-card">
            <span>Company Orders</span>
            <strong><?php echo $stats['companyOrders']; ?></strong>
        </div>
        <div class="stat-card">
            <span>Completed Orders</span>
            <strong><?php echo $stats['completedOrders']; ?></strong>
        </div>
        <div class="stat-card">
            <span>Pending / Active Orders</span>
            <strong><?php echo $stats['pendingOrders']; ?></strong>
        </div>
    </section>

    <!-- Revenue Stats -->
    <section class="stats-grid">
        <div class="stat-card">
            <span>Personal Revenue</span>
            <strong><?php echo number_format($stats['personalRevenue']); ?> EGP</strong>
        </div>
        <div class="stat-card">
            <span>Company Revenue</span>
            <strong><?php echo number_format($stats['companyRevenue']); ?> EGP</strong>
        </div>
        <div class="stat-card">
            <span>Total Revenue</span>
            <strong><?php echo number_format($stats['totalRevenue']); ?> EGP</strong>
        </div>
        <div class="stat-card">
            <span>Estimated Net Profit</span>
            <strong><?php echo number_format($stats['netProfit']); ?> EGP</strong>
        </div>
    </section>

    <!-- Monthly Performance -->
    <section class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Monthly Performance</h3>
            <?php foreach($monthlyData as $month): ?>
            <div class="month-row">
                <span><?php echo $month['month']; ?></span>
                <strong>
                    <?php echo $month['orders']; ?> orders / 
                    <?php echo number_format($month['revenue']); ?> EGP
                </strong>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="dashboard-card">
            <h3>Report Notes</h3>
            <p>Data is fetched dynamically and reflects current orders and revenue.</p>
            <p>
                <a href="report.php" class="btn btn-export">
                    <i class="fas fa-file-export"></i> Export Full Report
                </a>
            </p>
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
            <a href="manage-items.php"><i class="fas fa-cog"></i> Manage Products</a>
            <a href="new-item.php"><i class="fas fa-plus"></i> New Item</a>
            <a href="sales.php"><i class="fas fa-chart-bar"></i> Sales Dashboard</a>
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

<script src="../js/admin.js"></script>
<script src="../js/navbaradmin.js"></script>
</body>
</html>
