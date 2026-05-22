<?php
// ============================================================
// MARDETAILS.PHP
// ============================================================
session_start();
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/product.php");
require_once(__DIR__ . "/../../Model/category.php");
require_once(__DIR__ . "/../../Model/Wishlist.php");

$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function detailImageUrl($image, $fallback){
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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$productImg = "../pic/black&gray marble.jpeg";
$productInDb = false;

$dbProduct = Product::findById($conn, $id);

if ($dbProduct && $dbProduct['product_type'] === 'Marble') {
    $productInDb = true;
    $product = [
        'id' => (int)$dbProduct['product_id'],
        'name' => $dbProduct['product_name'],
        'type' => $dbProduct['product_type'],
        'price' => $dbProduct['price'],
        'dimensions' => $dbProduct['dimensions'],
        'stock' => $dbProduct['stock'],
        'image' => $dbProduct['image'],
        'description' => $dbProduct['description'],
        'material' => $dbProduct['material'],
        'color' => $dbProduct['color'],
        'finish' => $dbProduct['finish'],
        'thickness' => $dbProduct['thickness'],
        'sizes' => $dbProduct['sizes'],
        'water_resistance' => $dbProduct['water_resistance'],
        'heat_resistance' => $dbProduct['heat_resistance'],
        'scratch_resistance' => $dbProduct['scratch_resistance'],
        'application_type' => $dbProduct['application_type']
    ];
    $productImg = detailImageUrl($dbProduct['image'], "../pic/black&gray marble.jpeg");
} else {
    header("Location: marble.php");
    exit;
}

$isInWishlist = false;
if ($isLoggedIn && $productInDb) {
    $wishlistModel = new Wishlist($conn);
    $isInWishlist = $wishlistModel->isInWishlist((int)$_SESSION['user_id'], (int)$product['id']);
}

$description = trim((string)($product['description'] ?? ''));
if ($description === '') {
    $description = $product['name'] . ' features a soft natural tone with subtle veining. It offers a warm, elegant look suitable for both classic and modern designs.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo h($product['name']); ?> - Marble Details</title>
  <link rel="stylesheet" href="../css/mardetails.css?v=<?php echo filemtime('../css/mardetails.css'); ?>">
<link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">
<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="details-page">

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
    <h1>Marble</h1>
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

<!-- CONTENT -->
<div class="details-container">

  <!-- IMAGE -->
  <div class="left">
    <img src="<?php echo h($productImg); ?>" alt="<?php echo h($product['name']); ?>">
  </div>

  <!-- RIGHT SIDE -->
  <div class="right">

    <div class="info-box">
      <p class="category">MARBLE | <?php echo h($product['name']); ?></p>
      <p class="desc"><?php echo h($description); ?></p>
      <div class="code-row">
        <span class="code">#<?php echo str_pad((int)$product['id'], 6, '0', STR_PAD_LEFT); ?></span>
        <form action="../../Controller/toggle_wishlist.php" method="POST" class="heart-form">
          <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
          <button type="submit" class="heart-submit" aria-label="Toggle wishlist">
            <i class="<?php echo $isInWishlist ? 'fa-solid active' : 'fa-regular'; ?> fa-heart heart"></i>
          </button>
        </form>
      </div>
      <p class="stock">
       <p class="stock">
  <?php
    $stock = (int)$product['stock'];
    if ($stock >= 100) {
        echo '<span class="in-stock">In Stock</span>';
    } elseif ($stock > 0) {
        echo '<span class="low-stock">Low Stock</span>';
    } else {
        echo '<span class="out-stock">Out of Stock</span>';
    }
  ?>
</p>
      </p>
    </div>
<!-- ACTIONS -->
<div class="actions">
  <form action="../../Controller/add_to_cart.php" method="POST" class="add-cart-form">
    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
    <input type="hidden" name="quantity" id="quantityInput" value="1">
    <div class="qty">
      <span class="minus">-</span>
      <span class="count">1</span>
      <span class="plus">+</span>
    </div>
    <button type="submit" class="add">Add to Cart</button>
  </form>
</div>

    <!-- BOOK -->
    <!-- BOOK -->
<form method="POST" action="../../Controller/add_to_cart.php">
    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
    <input type="hidden" name="quantity" id="bookQuantityInput" value="1">
    <input type="hidden" name="redirect" value="cart">
    <button type="submit" name="book_now" class="book">
        Book Now
    </button>
</form>

    <hr>
<p class="note">
  <i class="fas fa-location-dot"></i>
  Pickup available from <strong>Tamam Marble & Granite</strong>, Shaqet El T3ban.
</p>
  </div>
</div>

<!-- DESCRIPTION TABLE -->
<div class="table">
  <h2>Description</h2>
  <table>
    <tr><th>Feature</th><th>Details</th></tr>
    <tr><td>Material</td><td><?php echo h($product['material'] ?: $product['type']); ?></td></tr>
    <tr><td>Color</td><td><?php echo h($product['color'] ?: $product['name']); ?></td></tr>
    <tr><td>Finish</td><td><?php echo h($product['finish'] ?: 'Polished / Honed'); ?></td></tr>
    <tr><td>Available Thickness</td><td><?php echo h($product['thickness'] ?: '2 cm / 3 cm'); ?></td></tr>
    <tr><td>Available Sizes</td><td><?php echo h($product['sizes'] ?: 'Custom sizes available upon request'); ?></td></tr>
    <tr><td>Dimensions</td><td><?php echo h($product['dimensions']); ?> cm</td></tr>
    <tr><td>Water Resistance</td><td><?php echo h($product['water_resistance'] ?: 'Medium'); ?></td></tr>
    <tr><td>Heat Resistance</td><td><?php echo h($product['heat_resistance'] ?: 'Medium'); ?></td></tr>
    <tr><td>Scratch Resistance</td><td><?php echo h($product['scratch_resistance'] ?: 'Medium'); ?></td></tr>
    <tr><td>Application Type</td><td><?php echo h($product['application_type'] ?: 'Kitchen Countertops, Flooring, Walls'); ?></td></tr>
  </table>
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

<script src="../js/navbar.js?v=<?php echo filemtime('../js/navbar.js'); ?>"></script>
<script src="../js/mardetails.js?v=<?php echo filemtime('../js/mardetails.js'); ?>"></script>
</body>
</html>
