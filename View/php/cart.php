<?php
// ============================================================
// CART / BOOKING REQUEST
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/cart.php");

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

function productImageUrl($image, $fallback = "../pic/product-1.jpg"){
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

$cartModel = new Cart(null, $conn);
$cartItems = $cartModel->getItemsByUser((int)$_SESSION['user_id']);
$isEmpty = empty($cartItems);

$success = isset($_GET['success']) && $_GET['success'] == '1';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Now</title>
<link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/account-management.css?v=<?php echo filemtime('../css/account-management.css'); ?>">

<link rel="stylesheet" href="../css/book-now.css?v=<?php echo filemtime('../css/book-now.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
main{
 
    min-height:45vh;

    
}
</style>
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
    <h1>Book Now</h1>
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


<main class="book-ui-shell">

  <?php if ($error): ?>
    <p style="color:#b00020; margin-bottom:15px;">
      <?php
        if ($error === 'empty_cart') {
            echo 'Your cart is empty.';
        } elseif ($error === 'missing_fields') {
            echo 'Please fill in all required booking fields.';
        } elseif ($error === 'booking_failed') {
            echo 'Booking request could not be saved. Please try again.';
        } else {
            echo 'Something went wrong. Please try again.';
        }
      ?>
    </p>
  <?php endif; ?>

  <?php if ($isEmpty): ?>
    <section class="book-intro">
      <div>
        <h2>Your cart is empty.</h2>
        <p>Add marble or granite products to start your booking request.</p>
        <a href="marble.php" class="btn primary" style="margin-top:15px; display:inline-block;">
          Browse Products
        </a>
      </div>
      <div class="intro-chip">
        <i class="fa-solid fa-cart-shopping"></i>
        0 Items
      </div>
    </section>

  <?php else: ?>

    <section class="book-intro">
      <div>
        <h2>Review your selected products</h2>
        <p>Enter the dimensions for each product and fill in your booking information.</p>
      </div>
      <div class="intro-chip">
        <i class="fa-solid fa-cart-shopping"></i>
        <?php echo count($cartItems); ?> Items
      </div>
    </section>

    <form action="../../Controller/submit_booking.php" method="POST" class="book-main-grid" autocomplete="off">

      <!-- Cart Items -->
      <div class="book-card cart-card">
        <h2>Your Cart</h2>
        <div class="cart-list">

          <?php foreach ($cartItems as $item): ?>

  <?php for($i = 1; $i <= (int)$item['quantity']; $i++): ?>

    <?php
      $productId = (int)$item['product_id'];
      $productName = $item['product_name'];

      $description = trim((string)$item['description']);

      if ($description === '') {
          $description = $productName . ' - ' . $item['product_type'] . ' stone with natural texture.';
      }
    ?>

    <article class="cart-row">

      <div class="img-box">
        <button type="submit"
                form="remove-cart-<?php echo $productId; ?>"
                class="remove-x">
          &times;
        </button>

        <img src="<?php echo h(productImageUrl($item['image'], '../pic/product-1.jpg')); ?>">
      </div>

      <div class="desc-box">
        <h3><?php echo h($productName); ?></h3>

        <p><?php echo h($description); ?></p>

        <strong>
          #<?php echo str_pad($productId, 6, '0', STR_PAD_LEFT); ?>
          - Piece <?php echo $i; ?>
        </strong>
      </div>

      <div class="size-box">
        <input type="text"
               name="length[<?php echo $productId; ?>][]"
               placeholder="Length">

        <input type="text"
               name="width[<?php echo $productId; ?>][]"
               placeholder="Width">
      </div>

    </article>

  <?php endfor; ?>

<?php endforeach; ?>

        </div>
      </div>

      <!-- Booking Form -->
      <div class="book-card request-card">
        <h2>Booking Information</h2>

        <div class="form-grid">
          <input type="text" name="full_name" placeholder="Full Name" value="<?php echo h($_SESSION['full_name'] ?? ''); ?>" required>
          <input type="text" name="phone" placeholder="Phone Number" value="<?php echo h($_SESSION['phone'] ?? ''); ?>" required>
          <input type="text" name="whatsapp_number" placeholder="Whatsapp Number" required>
          <input type="text" name="city" placeholder="City" required>
          <input type="text" name="area" placeholder="Area" required>
        </div>

        <textarea name="notes" placeholder="Notes"></textarea>

        <label class="visit-row">
          <input type="checkbox" name="needs_engineering_visit" value="1" checked>
          <span></span>
          <small>Request engineer visit for measurements</small>
        </label>

        <button type="submit">Book Now</button>
      </div>

    </form>

    <?php foreach ($cartItems as $item): ?>
      <form id="remove-cart-<?php echo (int)$item['product_id']; ?>"
            action="../../Controller/remove_from_cart.php"
            method="POST">
        <input type="hidden" name="product_id" value="<?php echo (int)$item['product_id']; ?>">
      </form>
    <?php endforeach; ?>

  <?php endif; ?>

</main>

<!-- Success Modal -->
<div class="modal-overlay" id="booking-success-modal"
     style="<?php echo $success ? 'display:flex' : 'display:none'; ?>">
  <div class="success-modal">
    <div class="success-icon"><i class="fa-solid fa-check"></i></div>
    <h3>Booking Successful</h3>
    <p>Thank you for your request<br>You will receive a WhatsApp message within 48 hours</p>
    <a href="home.php">Back to Home</a>
  </div>
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

<script src="../js/book-now.js?v=<?php echo filemtime('../js/book-now.js'); ?>"></script>
</body>
</html>
