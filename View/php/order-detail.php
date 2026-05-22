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

function filledValue($value, $fallback = '-'){
    $value = trim((string)$value);
    return $value !== '' ? $value : $fallback;
}

function productImageUrl($image){
    $image = trim((string)$image);

    if ($image === '') {
        return '../pic/marble.PNG';
    }

    $image = str_replace('\\', '/', $image);

    if (preg_match('/^https?:\/\//i', $image) || strpos($image, '/') === 0) {
        return $image;
    }

    if (strpos($image, '../') === 0) {
        return $image;
    }

    if (strpos($image, 'View/') === 0) {
        return '../../' . $image;
    }

    if (strpos($image, 'pic/') === 0) {
        return '../' . $image;
    }

    return '../pic/' . $image;
}

function formatDimension($value){
    if ($value === null || $value === '') {
        return '-';
    }

    $formatted = rtrim(rtrim(number_format((float)$value, 2, '.', ''), '0'), '.');
    return $formatted !== '' ? $formatted . ' m' : '-';
}

$bookingIdRaw = preg_replace('/\D+/', '', (string)($_GET['id'] ?? ''));
$bookingId = (int)$bookingIdRaw;

if ($bookingId <= 0) {
    header("Location: personal-order.php");
    exit();
}

$order = AdminOrder::getOrderById($conn, $bookingId);

if (!$order) {
    header("Location: personal-order.php?error=not_found");
    exit();
}

$statusLabels = AdminOrder::statusLabels();
$status = $order['status'] ?: 'pending';
$clientType = ($order['account_type'] ?? 'customer') === 'company' ? 'Company' : 'Individual';
$customerName = filledValue($order['full_name'] ?: $order['user_full_name']);
$whatsapp = filledValue($order['whatsapp'] ?: ($order['user_whatsapp'] ?: ($order['phone'] ?: $order['user_phone'])));
$phone = filledValue($order['phone'] ?: $order['user_phone']);
$city = filledValue($order['city'] ?: $order['user_city']);
$area = filledValue($order['area'] ?: $order['user_area']);
$orderDate = $order['booking_date'] ? date('Y-m-d', strtotime($order['booking_date'])) : '-';
$subtotal = (float)$order['subtotal'];
$total = $subtotal;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
  <link rel="stylesheet" href="../css/order-details-ad.css?v=<?php echo filemtime('../css/order-details-ad.css'); ?>">
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
        <h1><?php echo $isAdmin ? 'Order' : 'Order'; ?></h1>
    </div>
</header>

<?php render_admin_sidebar('orders'); ?>

<main class="container">

    <h1 class="page-title">Order Details</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="admin-notice success">Order status updated successfully.</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="admin-notice error">Order status could not be updated.</div>
    <?php endif; ?>

    <section class="card">
        <h2>Customer Details</h2>
        <div class="grid-2">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" value="<?php echo h($customerName); ?>" readonly>
            </div>
            <div class="input-group">
                <label>WhatsApp</label>
                <input type="text" value="<?php echo h($whatsapp); ?>" readonly>
            </div>
            <div class="input-group">
                <label>Phone Number</label>
                <input type="text" value="<?php echo h($phone); ?>" readonly>
            </div>
            <div class="input-group">
                <label>City</label>
                <input type="text" value="<?php echo h($city); ?>" readonly>
            </div>
            <div class="input-group">
                <label>Client Type</label>
                <input type="text" value="<?php echo h($clientType); ?>" readonly>
            </div>
            <div class="input-group">
                <label>Area</label>
                <input type="text" value="<?php echo h($area); ?>" readonly>
            </div>
        </div>
    </section>

    <section class="card">
        <h2>Order Information</h2>

        <div class="order-top">
            <div class="order-info-left">
                <p>Order ID : <strong>#<?php echo (int)$order['booking_id']; ?></strong></p>
                <p>Order Date : <strong><?php echo h($orderDate); ?></strong></p>
            </div>
            <div class="order-info-right">
                <span class="status-label">Status :</span>
                <div class="status-dropdown">
                    <div class="selected <?php echo h($status); ?>">
                        <?php echo h($statusLabels[$status] ?? ucfirst($status)); ?> &#9662;
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

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Name</th>
                    <th>ID</th>
                    <th>Length</th>
                    <th>Width</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($order['items'])): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No products found for this order.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach($order['items'] as $item): ?>
                <tr>
                    <td><img src="<?php echo h(productImageUrl($item['image'] ?? '')); ?>" alt="<?php echo h($item['product_name']); ?>"></td>
                    <td><?php echo h($item['product_name']); ?></td>
                    <td>#<?php echo (int)$item['product_id']; ?></td>
                    <td><?php echo h(formatDimension($item['requested_length'])); ?></td>
                    <td><?php echo h(formatDimension($item['requested_width'])); ?></td>
                    <td><span class="price"><?php echo h(number_format((float)$item['line_total'], 2)); ?> EGP</span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST" action="../../Controller/update_order.php">
            <input type="hidden" name="orderId" value="<?php echo (int)$order['booking_id']; ?>">

            <div class="summary">
                <p>Subtotal : <span><?php echo h(number_format($subtotal, 2)); ?> EGP</span></p>
                <p>
                    Discount :
                    <input type="number" name="discount" value="0" min="0" max="100" placeholder="0"> %
                </p>
                <p>
                    Delivery :
                    <input type="number" name="delivery" value="0" min="0" placeholder="0">
                </p>
            </div>

            <div class="total" data-subtotal="<?php echo h($subtotal); ?>">
                Total : <span id="totalDisplay"><?php echo h(number_format($total, 2)); ?> EGP</span>
            </div>

            <div class="actions">
                <button type="button" class="btn-outline" onclick="window.print()">
                    Generate Invoice
                </button>
                <button type="submit" name="confirm" class="btn-primary">
                    Confirmed
                </button>
            </div>

        </form>

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
            <a href="personal-order.php"><i class="fas fa-box"></i> Orders</a>
            <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
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

<script src="../js/admin.js?v=<?php echo filemtime('../js/admin.js'); ?>"></script>
<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script>
<script src="../js/order-details-ad.js?v=<?php echo filemtime('../js/order-details-ad.js'); ?>"></script>
</body>
</html>
