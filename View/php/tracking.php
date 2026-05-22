<?php
// ============================================================
// TRACKING.PHP
// ============================================================
session_start();
require_once(__DIR__ . "/../../config/db.php");

// --- لو مش logged in ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";


$userId = (int)$_SESSION['user_id'];
$stmt = mysqli_prepare(
    $conn,
    "SELECT booking_id, booking_date, status
     FROM booking_requests
     WHERE customer_id = ?
     ORDER BY booking_id DESC"
);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
mysqli_stmt_close($stmt);

// --- Status → Step mapping ---
$statusSteps = [
    "pending"    => 1,
    "confirmed"  => 2,
    "in_progress"=> 3,
    "ready"      => 4,
    "completed"  => 5,
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Track Order</title>
 <link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/myaccounttrackorder.css?v=<?php echo filemtime('../css/myaccounttrackorder.css'); ?>">
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
          <a class="user-link" href="orders.php">
            <span><i class="fa-regular fa-rectangle-list"></i> Orders</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
          <a class="user-link" href="favorites.php">
            <span><i class="fa-regular fa-heart"></i> Favorites</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
          <a class="user-link active" href="tracking.php">
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

    <!-- Order Tracking -->
    <section class="order-status">

      <h2 class="order-title">
        <i class="fa-regular fa-clipboard"></i> Your Orders
      </h2>

    <?php if (empty($orders)): ?>
    <p class="order-id">No orders found.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <?php $currentStep = $statusSteps[$order['status']] ?? 1; ?>
        <p class="order-id">Order #<?php echo (int)$order['booking_id']; ?></p>
        <div class="steps">
            <div class="step <?php echo $currentStep >= 1 ? 'active' : ''; ?>">
                <div class="circle"><i class="fa-solid fa-bag-shopping"></i></div>
                <p>Order Submitted</p>
            </div>
            <div class="step <?php echo $currentStep >= 2 ? 'active' : ''; ?>">
                <div class="circle"><i class="fa-solid fa-check"></i></div>
                <p>Order Confirmed</p>
            </div>
            <div class="step <?php echo $currentStep >= 3 ? 'active' : ''; ?>">
                <div class="circle"><i class="fa-solid fa-gear"></i></div>
                <p>In Progress</p>
            </div>
            <div class="step <?php echo $currentStep >= 4 ? 'active' : ''; ?>">
                <div class="circle"><i class="fa-solid fa-truck"></i></div>
                <p>Ready for Installation</p>
            </div>
            <div class="step <?php echo $currentStep >= 5 ? 'active' : ''; ?>">
                <div class="circle"><i class="fa-solid fa-file-circle-check"></i></div>
                <p>Completed</p>
            </div>
        </div>
        <p class="order-note">
            You will receive updates via WhatsApp.<br>
            Orders completed within 7–14 days.
        </p>
        <hr style="margin: 30px 0; border: 1px dashed #ddd;">
    <?php endforeach; ?>
<?php endif; ?>
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
