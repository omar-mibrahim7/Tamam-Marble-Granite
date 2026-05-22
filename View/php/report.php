<?php
require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
require_admin_role(['admin'], "adminlogin.php");

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/AdminReport.php");
$adminRole = require_admin_role(['admin'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';


function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function selected($current, $expected){
    return (string)$current === (string)$expected ? 'selected' : '';
}

$filters = AdminReport::normalizeFilters($_GET);
$stats = AdminReport::getStats($conn);
$previewOrders = AdminReport::getOrders($conn, $filters, 50);
$statusLabels = AdminReport::statusLabels();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Export Report</title>
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
        <h1><?php echo $isAdmin ? 'Reports Center' : 'Staff Panel'; ?></h1>
    </div>
</header>


<?php render_admin_sidebar('report'); ?>

<main class="admin-shell">

 
    <h1 class="section-heading">Export Orders Report</h1>

    <section class="export-panel">
        <div class="export-panel-head">
            <div>
                <h2>Choose what you want to export</h2>
                <p>Select order type, status, and date period before downloading the Excel-compatible report.</p>
            </div>
            <div class="export-mini-stats">
                <div><strong><?php echo $stats['totalOrders']; ?></strong><span>Orders</span></div>
                <div><strong><?php echo $stats['completedOrders']; ?></strong><span>Completed</span></div>
                <div><strong><?php echo $stats['activeOrders']; ?></strong><span>Active</span></div>
                <div><strong><?php echo number_format($stats['totalRevenue'] / 1000); ?>K</strong><span>EGP</span></div>
            </div>
        </div>

        <form class="export-form" method="POST" action="../../Controller/export_report.php">

            <div class="export-grid">

                <div class="export-field">
                    <label>Order Type</label>
                    <select name="order_type">
                        <option value="all" <?php echo selected($filters['order_type'], 'all'); ?>>All Orders</option>
                        <option value="personal" <?php echo selected($filters['order_type'], 'personal'); ?>>Personal Orders</option>
                        <option value="company" <?php echo selected($filters['order_type'], 'company'); ?>>Company Orders</option>
                    </select>
                </div>

                <div class="export-field">
                    <label>Status</label>
                    <select name="status">
                        <option value="all" <?php echo selected($filters['status'], 'all'); ?>>All Statuses</option>
                        <option value="active" <?php echo selected($filters['status'], 'active'); ?>>Active / Not Completed</option>
                        <?php foreach ($statusLabels as $statusValue => $statusLabel): ?>
                            <option value="<?php echo h($statusValue); ?>" <?php echo selected($filters['status'], $statusValue); ?>>
                                <?php echo h($statusLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="export-field">
                    <label>Date Mode</label>
                    <select name="date_mode" id="dateMode" onchange="toggleDateFields()">
                        <option value="last_months" <?php echo selected($filters['date_mode'], 'last_months'); ?>>Last N Months</option>
                        <option value="month" <?php echo selected($filters['date_mode'], 'month'); ?>>Specific Month / Year</option>
                        <option value="range" <?php echo selected($filters['date_mode'], 'range'); ?>>Date Range</option>
                        <option value="all" <?php echo selected($filters['date_mode'], 'all'); ?>>All Dates</option>
                    </select>
                </div>

                <div class="export-field">
                    <label>Date Field</label>
                    <select name="date_field">
                        <option value="auto" <?php echo selected($filters['date_field'], 'auto'); ?>>Auto</option>
                        <option value="created" <?php echo selected($filters['date_field'], 'created'); ?>>Created Date</option>
                        <option value="completed" <?php echo selected($filters['date_field'], 'completed'); ?>>Completed Date</option>
                    </select>
                </div>

                <!-- Specific Month -->
                <div class="export-field export-date-option export-month">
                    <label>Month</label>
                    <select name="month">
                        <?php for ($month = 1; $month <= 12; $month++): ?>
                            <option value="<?php echo $month; ?>" <?php echo selected($filters['month'], $month); ?>>
                                <?php echo h(date('F', mktime(0, 0, 0, $month, 1))); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="export-field export-date-option export-month">
                    <label>Year</label>
                    <select name="year">
                        <?php for ($year = (int)date('Y'); $year >= (int)date('Y') - 5; $year--): ?>
                            <option value="<?php echo $year; ?>" <?php echo selected($filters['year'], $year); ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Last N Months -->
                <div class="export-field export-date-option export-last-months">
                    <label>Last Months</label>
                    <select name="last_months">
                        <option value="3" <?php echo selected($filters['last_months'], 3); ?>>Last 3 months</option>
                        <option value="4" <?php echo selected($filters['last_months'], 4); ?>>Last 4 months</option>
                        <option value="6" <?php echo selected($filters['last_months'], 6); ?>>Last 6 months</option>
                        <option value="12" <?php echo selected($filters['last_months'], 12); ?>>Last 12 months</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="export-field export-date-option export-range">
                    <label>From</label>
                    <input type="date" name="date_from" value="<?php echo h($filters['date_from']); ?>">
                </div>

                <div class="export-field export-date-option export-range">
                    <label>To</label>
                    <input type="date" name="date_to" value="<?php echo h($filters['date_to']); ?>">
                </div>

            </div>

            <div class="export-actions">
                <button type="submit" name="action" value="preview" class="btn btn-view">
                    Preview Report
                </button>
                <button type="submit" name="action" value="export" class="btn btn-export">
                    Download Excel
                </button>
            </div>

        </form>
    </section>

    <!-- Preview -->
    <section class="export-preview-card">
        <h2>Preview</h2>
        <p>Showing first <?php echo count($previewOrders); ?> orders from the selected filters.</p>
        <div class="table-wrap">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Type</th>
                        <th>Client / Company</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Created</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($previewOrders as $order): ?>
                    <?php
                        $typeLabel = $order['account_type'] === 'company' ? 'Company' : 'Personal';
                        $clientName = $order['full_name'] ?: $order['user_full_name'];
                        $statusClass = str_replace('_', '-', $order['status']);
                        $createdDate = $order['created_date'] ? date('Y-m-d', strtotime($order['created_date'])) : '-';
                        $completedDate = $order['completed_date'] ? date('Y-m-d', strtotime($order['completed_date'])) : '-';
                    ?>
                    <tr>
                        <td>#<?php echo (int)$order['booking_id']; ?></td>
                        <td><?php echo h($typeLabel); ?></td>
                        <td><?php echo h($clientName); ?></td>
                        <td><?php echo (int)$order['items_count']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo h($statusClass); ?>">
                                <?php echo h($statusLabels[$order['status']] ?? ucfirst($order['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo h(number_format((float)$order['total_amount'], 2)); ?> EGP</td>
                        <td><?php echo h($createdDate); ?></td>
                        <td><?php echo h($completedDate); ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if (empty($previewOrders)): ?>
                    <tr>
                        <td colspan="8">No orders found for the selected filters.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script></body>
<script src="../js/admin.js?v=<?php echo filemtime('../js/admin.js'); ?>"></script></body>
</body>
</html>
