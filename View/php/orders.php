<?php
// ============================================================
// ORDERS.PHP
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";


function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$userId = (int)$_SESSION['user_id'];
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        br.booking_id,
        br.booking_date,
        br.status,
        br.product_id AS fallback_product_id,
        fallback_product.product_name AS fallback_product_name,
        fallback_product.product_type AS fallback_product_type,
        bri.product_id,
        bri.product_name,
        bri.quantity,
        p.product_type
     FROM booking_requests br
     LEFT JOIN booking_request_items bri ON bri.booking_id = br.booking_id
     LEFT JOIN products p ON p.product_id = bri.product_id
     LEFT JOIN products fallback_product ON fallback_product.product_id = br.product_id
     WHERE br.customer_id = ?
     ORDER BY br.booking_id DESC, bri.created_at ASC, bri.product_id ASC"
);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $bookingId = (int)$row['booking_id'];

    if (!isset($orders[$bookingId])) {
        $orders[$bookingId] = [
            'booking_id' => $bookingId,
            'booking_date' => $row['booking_date'],
            'status' => $row['status'],
            'items' => []
        ];
    }

    if (!empty($row['product_id'])) {
        $orders[$bookingId]['items'][] = [
            'product_id' => (int)$row['product_id'],
            'product_name' => $row['product_name'],
            'product_type' => $row['product_type'] ?? '',
            'quantity' => (int)$row['quantity']
        ];
    } elseif (!empty($row['fallback_product_id'])) {
        $orders[$bookingId]['items'][] = [
            'product_id' => (int)$row['fallback_product_id'],
            'product_name' => $row['fallback_product_name'] ?? 'Product',
            'product_type' => $row['fallback_product_type'] ?? '',
            'quantity' => 1
        ];
    }
}

mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Orders</title>
<link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/myaccountorder.css?v=<?php echo filemtime('../css/myaccountorder.css'); ?>">

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

  
  <!-- Login / Profile Icon (dynamic) -->
  <a href="<?php echo $userHref; ?>" id="userLink" data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
    <i class="fas <?php echo $userIcon; ?> user-icon" id="userIcon"></i>
  </a>
  
  <!-- Cart Icon -->
 <a href="<?php echo isset($_SESSION['user_id']) ? 'cart.php' : 'login.php'; ?>">
    <i class="fas fa-shopping-cart cart-icon"></i>
</a>
</div>
  </div>

  <div class="title">
    <h1>My Account</h1>
  </div>
</header>

<!-- Side Menu -->
<div class="side-menu" id="menu">
  <i class="fas fa-times close-btn" onclick="toggleMenu()"></i>
  <a href="home.php"><i class="fas fa-home"></i> Home</a>
  <a href="marble.php"><i class="fas fa-gem"></i> Marble</a>
  <a href="granite.php"><i class="fas fa-mountain"></i> Granite</a>
  <a href="<?php echo isset($_SESSION['user_id']) ? 'cart.php' : 'login.php'; ?>"><i class="fas fa-shopping-cart"></i> Cart</a>
  <a href="contact.php"><i class="fas fa-phone"></i> Contact Us</a>
  <a href="about.php"><i class="fas fa-users"></i> About Us</a>
  <?php if(isset($_SESSION['user_id'])): ?>
    <a href="profile.php"><i class="fas fa-circle-user"></i> Profile</a>
<a href="../../Controller/logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
  <?php else: ?>
    <a href="login.php"><i class="fas fa-right-to-bracket"></i> Login</a>
    <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
  <?php endif; ?>
</div>

<main class="page-shell">
  <section class="account-layout">

    <!-- Sidebar -->
    <aside>
      <h2 class="user-panel-title">User Panel</h2>
      <div class="user-panel">
        <nav class="user-links">
          <a class="user-link" href="profile.php">
            <span><i class="fa-regular fa-user"></i> Profile</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
          <a class="user-link active" href="orders.php">
            <span><i class="fa-regular fa-rectangle-list"></i> Orders</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
          <a class="user-link" href="favorites.php">
            <span><i class="fa-regular fa-heart"></i> Favorites</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
          <a class="user-link" href="tracking.php">
            <span><i class="fa-solid fa-location-dot"></i> Order Tracking</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
          <a class="user-link" href="account-management.php">
            <span><i class="fa-solid fa-shield-halved"></i> Account Management</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
        </nav>
        <div class="panel-separator"></div>
        <a class="logout-btn" href="../../Controller/logout.php">
          Logout <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
      </div>
    </aside>

    <!-- Orders Table -->
    <section class="orders-section">
      <div class="orders-wrapper">
        <table class="orders-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Product ID</th>
              <th>Type</th>
              <th>Product Name</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>

            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="6" style="text-align:center; color:#888;">No orders found.</td>
              </tr>
            <?php endif; ?>

            <?php foreach ($orders as $order): ?>
              <tr>
                <td>#<?php echo $order['booking_id']; ?></td>

                <td>
                  <?php foreach ($order['items'] as $item): ?>
                    #<?php echo $item['product_id']; ?>
                    <?php if ($item['quantity'] > 1): ?>
                      x<?php echo $item['quantity']; ?>
                    <?php endif; ?>
                    <br>
                  <?php endforeach; ?>
                </td>

                <td>
                  <?php foreach ($order['items'] as $item): ?>
                    <?php echo h($item['product_type']); ?><br>
                  <?php endforeach; ?>
                </td>

                <td>
                  <?php foreach ($order['items'] as $item): ?>
                    <?php echo h($item['product_name']); ?><br>
                  <?php endforeach; ?>
                </td>

                <td><?php echo h($order['booking_date']); ?></td>

                <td>
                  <span class="status <?php echo h(strtolower($order['status'])); ?>">
                    <?php echo h(ucfirst($order['status'])); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>

          </tbody>
        </table>
      </div>
    </section>

  </section>
</main>

<!-- Footer -->
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
      <a href="home.php"><i class="fas fa-home"></i> Home</a>
      <a href="marble.php"><i class="fas fa-gem"></i> Marble</a>
      <a href="granite.php"><i class="fas fa-mountain"></i> Granite</a>
      <a href="cart.php"><i class="fas fa-calendar-check"></i> Book Now</a>
      <a href="about.php"><i class="fas fa-users"></i> About Us</a>
      <a href="contact.php"><i class="fas fa-phone"></i> Contact Us</a>
    </div>
    <div class="footer-contact">
      <h3>Contact Us</h3>
      <p><i class="fas fa-map-marker-alt"></i> 123 Street Name, City</p>
      <p><i class="fas fa-phone"></i> +20 01111111111</p>
      <p><i class="fas fa-envelope"></i> info@gmail.com</p>
    </div>
  </div>
</footer>

<script src="../js/navbar.js?v=<?php echo filemtime('../js/navbar.js'); ?>"></script>

</body>
</html>
