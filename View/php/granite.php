<?php
// ============================================================
// GRANITE.PHP
// ============================================================
session_start();
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/product.php");
require_once(__DIR__ . "/../../Model/category.php");

$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function productListImageUrl($image, $fallback){
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

$perPage = 16;
$totalCount = Product::countByType($conn, 'Granite');
$totalPages = max(1, (int)ceil($totalCount / $perPage));
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $perPage;
$products = Product::findByType($conn, 'Granite', $perPage, $offset);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Granite</title>
  <link rel="stylesheet" href="../css/granite.css?v=<?php echo filemtime('../css/granite.css'); ?>">
<link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">
<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="granite-page">

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
    <h1>Granite</h1>
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
<!-- Results Count -->
<p class="results">Showing <?php echo $totalCount; ?> results &gt;&gt;</p>

<!-- Products Grid -->
<div class="grid">

  <?php if (empty($products)): ?>
    <p style="color:#888;">No granite products found.</p>
  <?php endif; ?>

  <?php foreach ($products as $product): ?>
    <?php
      $productId = (int)$product['product_id'];
      $productName = $product['product_name'];
    ?>
    <div class="card">
      <img class="card-img"
           src="<?php echo h(productListImageUrl($product['image'], '../pic/mix marble.jpeg')); ?>"
           alt="<?php echo h($productName); ?>">
      <h3><?php echo h($productName); ?></h3>
      <div class="rating">
        5.0 <i class="fa-solid fa-star"></i>
      </div>
      <a href="gradetails.php?id=<?php echo $productId; ?>" class="btn">View More</a>
    </div>
  <?php endforeach; ?>

</div>

<!-- Pagination -->
<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="granite.php?page=<?php echo $currentPage - 1; ?>"><button class="circle-btn">&lt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&lt;</button>
  <?php endif; ?>

  <span class="page-number"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

  <?php if ($currentPage < $totalPages): ?>
    <a href="granite.php?page=<?php echo $currentPage + 1; ?>"><button class="circle-btn">&gt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&gt;</button>
  <?php endif; ?>
</div>

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
<script src="../js/navbar.js?v=<?php echo filemtime('../js/navbar.js'); ?>" defer></script>
<script src="../js/granite.js?v=<?php echo filemtime('../js/granite.js'); ?>"></script>

</body>
</html>
