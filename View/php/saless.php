<?php
require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/AdminDashboard.php");

require_admin_role(['admin'], "adminlogin.php");
$adminRole = require_admin_role(['admin'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($value){
    return number_format((float)$value, 2) . ' EGP';
}

$summary = AdminDashboard::getSummaryStats($conn);
$monthlyRevenue = AdminDashboard::getMonthlyRevenue($conn, 6);
$monthlyOrders = AdminDashboard::getMonthlyOrders($conn, 6);
$statusStats = AdminDashboard::getOrderStatusStats($conn);
$typeStats = AdminDashboard::getOrderTypeStats($conn);
$topProducts = AdminDashboard::getTopProducts($conn, 5);
$lowStockProducts = AdminDashboard::getLowStockProducts($conn, 5);
$latestOrders = AdminDashboard::getLatestOrders($conn, 5);
$latestEscalations = AdminDashboard::getLatestEscalations($conn, 5);

$topProductLabels = array_map(function($row) {
    return $row['product_name'];
}, $topProducts);
$topProductValues = array_map(function($row) {
    return (int)$row['quantity'];
}, $topProducts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/saless.css?v=<?php echo filemtime('../css/saless.css'); ?>">

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
        <h1><?php echo $isAdmin ? 'Control Center' : 'Staff Panel'; ?></h1>
    </div>
</header>


<?php render_admin_sidebar('dashboard'); ?>

<main class="dashboard-shell">
    <section class="dashboard-hero">
        <div>
            <p class="eyebrow">Tamam Marble & Granite</p>
            <h2>Business Overview</h2>
            <p>Live analytics from booking requests, products, customers, and escalated messages.</p>
        </div>
        <a class="hero-action" href="report.php"><i class="fas fa-file-export"></i> Open Reports</a>
    </section>

    <section class="metric-grid">
        <div class="metric-card highlight"><span>Total Revenue</span><strong><?php echo h(money($summary['totalRevenue'])); ?></strong></div>
        <div class="metric-card"><span>Estimated Profit</span><strong><?php echo h(money($summary['estimatedProfit'])); ?></strong></div>
        <div class="metric-card"><span>Pending Requests</span><strong><?php echo (int)$summary['pendingRequests']; ?></strong></div>
        <div class="metric-card"><span>Completed Requests</span><strong><?php echo (int)$summary['completedRequests']; ?></strong></div>
        <div class="metric-card danger"><span>Cancelled Requests</span><strong><?php echo (int)$summary['cancelledRequests']; ?></strong></div>
        <div class="metric-card"><span>Total Products</span><strong><?php echo (int)$summary['totalProducts']; ?></strong></div>
        <div class="metric-card"><span>Total Customers</span><strong><?php echo (int)$summary['totalCustomers']; ?></strong></div>
        <div class="metric-card warning"><span>Escalated Messages</span><strong><?php echo (int)$summary['escalatedMessages']; ?></strong></div>
        <div class="metric-card"><span>Low Stock Products</span><strong><?php echo (int)$summary['lowStockProducts']; ?></strong></div>
        <div class="metric-card"><span>Active Orders</span><strong><?php echo (int)$summary['activeOrders']; ?></strong></div>
        <div class="metric-card"><span>Total Booking Requests</span><strong><?php echo (int)$summary['totalBookingRequests']; ?></strong></div>
        <div class="metric-card"><span>Staff / Admin Users</span><strong><?php echo (int)$summary['totalStaffAdmins']; ?></strong></div>
    </section>

    <section class="charts-grid">
        <article class="chart-card wide">
            <h3>Monthly Revenue</h3>
            <canvas id="monthlyRevenueChart"></canvas>
        </article>
        <article class="chart-card wide">
            <h3>Monthly Orders</h3>
            <canvas id="monthlyOrdersChart"></canvas>
        </article>
        <article class="chart-card">
            <h3>Order Status Distribution</h3>
            <canvas id="statusChart"></canvas>
        </article>
        <article class="chart-card">
            <h3>Order Type Distribution</h3>
            <canvas id="typeChart"></canvas>
        </article>
        <article class="chart-card wide">
            <h3>Top Products</h3>
            <canvas id="topProductsChart"></canvas>
        </article>
        <article class="chart-card wide">
            <h3>Revenue vs Estimated Profit</h3>
            <canvas id="profitChart"></canvas>
        </article>
    </section>

    <section class="insight-grid">
        <article class="insight-card">
            <h3>Low Stock Products</h3>
            <?php if (empty($lowStockProducts)): ?>
                <p class="empty-state">No products found.</p>
            <?php endif; ?>
            <?php foreach ($lowStockProducts as $product): ?>
                <div class="mini-row">
                    <div>
                        <strong><?php echo h($product['product_name']); ?></strong>
                        <span><?php echo h($product['product_type']); ?></span>
                    </div>
                    <b><?php echo (int)$product['stock']; ?></b>
                </div>
            <?php endforeach; ?>
        </article>

        <article class="insight-card">
            <h3>Latest Orders</h3>
            <?php if (empty($latestOrders)): ?>
                <p class="empty-state">No orders found.</p>
            <?php endif; ?>
            <?php foreach ($latestOrders as $order): ?>
                <div class="mini-row">
                    <div>
                        <strong>#<?php echo (int)$order['booking_id']; ?> - <?php echo h($order['full_name'] ?: 'Customer'); ?></strong>
                        <span><?php echo h(ucfirst(str_replace('_', ' ', $order['status']))); ?> / <?php echo h($order['account_type'] === 'company' ? 'Company' : 'Personal'); ?></span>
                    </div>
                    <b><?php echo h(number_format((float)$order['total_amount'], 0)); ?></b>
                </div>
            <?php endforeach; ?>
        </article>

        <article class="insight-card">
            <h3>Latest Escalations</h3>
            <?php if (empty($latestEscalations)): ?>
                <p class="empty-state">No escalated messages found.</p>
            <?php endif; ?>
            <?php foreach ($latestEscalations as $message): ?>
                <div class="mini-row">
                    <div>
                        <strong>#<?php echo (int)$message['message_id']; ?> - <?php echo h($message['subject'] ?: 'Message'); ?></strong>
                        <span><?php echo h($message['full_name'] ?: ($message['phone'] ?: 'Visitor')); ?></span>
                    </div>
                    <b><?php echo $message['escalated_at'] ? h(date('M d', strtotime($message['escalated_at']))) : '-'; ?></b>
                </div>
            <?php endforeach; ?>
        </article>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthlyRevenue = <?php echo json_encode($monthlyRevenue); ?>;
const monthlyOrders = <?php echo json_encode($monthlyOrders); ?>;
const statusStats = <?php echo json_encode($statusStats); ?>;
const typeStats = <?php echo json_encode($typeStats); ?>;
const topProductLabels = <?php echo json_encode($topProductLabels); ?>;
const topProductValues = <?php echo json_encode($topProductValues); ?>;
const totalRevenue = <?php echo json_encode((float)$summary['totalRevenue']); ?>;
const estimatedProfit = <?php echo json_encode((float)$summary['estimatedProfit']); ?>;

const palette = {
    gold: '#f0ce3f',
    black: '#111111',
    green: '#22a06b',
    red: '#d64545',
    blue: '#3366cc',
    purple: '#7c3aed',
    mint: '#8bdc9f',
    gray: '#d6d6d6'
};

Chart.defaults.font.family = 'Arial, sans-serif';
Chart.defaults.color = '#333';

new Chart(document.getElementById('monthlyRevenueChart'), {
    type: 'bar',
    data: {
        labels: monthlyRevenue.labels,
        datasets: [{ label: 'Revenue', data: monthlyRevenue.values, backgroundColor: palette.gold, borderColor: palette.black, borderWidth: 1 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

new Chart(document.getElementById('monthlyOrdersChart'), {
    type: 'line',
    data: {
        labels: monthlyOrders.labels,
        datasets: [{ label: 'Orders', data: monthlyOrders.values, borderColor: palette.black, backgroundColor: 'rgba(240,206,63,.18)', fill: true, tension: .35 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusStats.labels.map(label => label.replace('_', ' ')),
        datasets: [{ data: statusStats.values, backgroundColor: [palette.gold, palette.blue, palette.purple, palette.mint, palette.green, palette.red] }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('typeChart'), {
    type: 'pie',
    data: {
        labels: typeStats.labels,
        datasets: [{ data: typeStats.values, backgroundColor: [palette.gold, palette.black] }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: topProductLabels.length ? topProductLabels : ['No data'],
        datasets: [{ label: 'Quantity', data: topProductValues.length ? topProductValues : [0], backgroundColor: palette.black }]
    },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
});

new Chart(document.getElementById('profitChart'), {
    type: 'bar',
    data: {
        labels: ['Revenue', 'Estimated Profit'],
        datasets: [{ data: [totalRevenue, estimatedProfit], backgroundColor: [palette.gold, palette.green] }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
<script src="../js/navbaradmin.js"></script>
</body>
</html>
