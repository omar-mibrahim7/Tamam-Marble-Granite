<?php
// ============================================================
// FAVORITES.PHP
// ============================================================
session_start();
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/Wishlist.php");

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

function wishlistProductImageUrl($image, $fallback = "../pic/gold.PNG"){
    $image = trim((string)$image);

    if ($image === '') {
        return $fallback;
    }

    if (preg_match('/^https?:\/\//i', $image) || strpos($image, '/tamam/') === 0) {
        return $image;
    }

    $normalized = str_replace('\\', '/', $image);

    if (strpos($normalized, '../pic/') === 0) {
        return $normalized;
    }

    if (strpos($normalized, 'View/pic/') === 0) {
        return '../../' . $normalized;
    }

    if (strpos($normalized, 'pic/') === 0) {
        return '../' . $normalized;
    }

    if (strpos($normalized, '/') === false) {
        return '../pic/' . $normalized;
    }

    return $normalized;
}

$wishlistModel = new Wishlist($conn);
$wishlist = $wishlistModel->getItemsByUser((int)$_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Wishlist</title>
<link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/myaccountfavourite.css?v=<?php echo filemtime('../css/myaccountfavourite.css'); ?>">
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
          <a class="user-link active" href="favorites.php">
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

    <!-- Wishlist -->
    <section class="wishlist">
      <h2 class="wishlist-title">My Favorites</h2>

      <?php if (empty($wishlist)): ?>
        <p style="color:#888;">Your wishlist is empty.<br>Click the heart icon on products you like.</p>
      <?php endif; ?>

      <?php foreach ($wishlist as $item): ?>
        <?php
          $productId = (int)$item['product_id'];
          $productName = $item['product_name'];
          $description = trim((string)$item['description']);
          if ($description === '') {
              $description = $productName . ' features a natural stone texture with unique patterns.';
          }
          $detailsPage = $item['product_type'] === 'Granite' ? 'gradetails.php' : 'mardetails.php';
        ?>
        <div class="wishlist-item">

          <form action="../../Controller/remove_wishlist.php" method="POST" class="wishlist-remove-form">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <button type="submit" class="remove-btn" aria-label="Remove <?php echo h($productName); ?>">&times;</button>
          </form>

          <img src="<?php echo h(wishlistProductImageUrl($item['image'])); ?>"
               class="wishlist-img"
               alt="<?php echo h($productName); ?>">

          <div class="wishlist-info">
            <h3><?php echo h($productName); ?></h3>
            <p><?php echo h($description); ?></p>
            <span class="product-id">#<?php echo str_pad($productId, 5, '0', STR_PAD_LEFT); ?></span>
          </div>

          <a href="<?php echo $detailsPage; ?>?id=<?php echo $productId; ?>">
            <button class="book-btn">Book Now</button>
          </a>

        </div>
      <?php endforeach; ?>

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
