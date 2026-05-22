<?php
// ============================================================
// HOME.PHP
// ============================================================
session_start();
require_once(__DIR__ . "/../../Model/product.php");
require_once(__DIR__ . "/../../Model/category.php");
// --- Login State ---
function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon = $isLoggedIn ? "fa-user" : "fa-right-to-bracket";

require_once(__DIR__ . "/../../config/db.php");

$stmt = mysqli_prepare($conn, 
    "SELECT * FROM products 
     WHERE is_best_selling = 1 AND is_deleted = 0 
     ORDER BY product_id DESC 
     LIMIT 10"
);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bestSelling = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bestSelling[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home</title>
  <link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">
<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">
<link rel="stylesheet" href="../css/home.css?v=<?php echo filemtime('../css/home.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="header home-header">

  <div class="header-bg"></div>

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

  <div class="hero-content home-hero">
    <h1 class="hero-title">Refined Marble & Granite</h1>
    <div class="hero-buttons">
      <a href="marble.php"><button class="btn primary">Shop Now</button></a>
      <a href="granite.php"><button class="btn outline">Explore</button></a>
    </div>
  </div>

  <div class="layers">
    <img src="../pic/layer1.png" class="layer layer1">
    <img src="../pic/layer2.png" class="layer layer2">
    <img src="../pic/layer3.png" class="layer layer3">
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

<!-- Sections + Best Selling -->
<!-- =========================
     SECTIONS
========================= -->

<section class="home-sections">

    <!-- Header -->
    <div class="sections-header">

        <div class="top-icon">
            <i class="fa-regular fa-map"></i>
        </div>

        <div class="top-lines">
            <span></span>
            <span></span>
        </div>

        <h2 class="section-title">Sections</h2>

        <div class="title-line"></div>

        <p class="section-subtitle">
            Explore our premium marble and granite collections
        </p>

    </div>

    <!-- Cards -->
    <div class="categories">

        <!-- Marble -->
        <div class="category-card">

            <div class="card-image">
                <img src="../pic/marble.png" alt="Marble">
            </div>

            <div class="small-icon">
                <i class="fa-solid fa-landmark"></i>
            </div>

            <h3>Marble</h3>

            

            <a href="marble.php" class="explore-btn">
                Explore Marble
            </a>

        </div>

        <!-- Granite -->
        <div class="category-card">

            <div class="card-image">
                <img src="../pic/granite.png" alt="Granite">
            </div>

            <div class="small-icon">
                <i class="fa-regular fa-gem"></i>
            </div>

            <h3>Granite</h3>


            <a href="granite.php" class="explore-btn">
                Explore Granite
            </a>

        </div>

    </div>
    
<!-- Best Selling Section -->
<section class="best-section">

  <!-- TITLE -->
  <div class="title-box">

    <span class="crown">
      <i class="fa-solid fa-crown"></i>
    </span>

    <h2 class="best-title">
      Best Selling !
    </h2>

    <div class="line-box">

      <span></span>

      <div class="dot"></div>

      <span></span>

    </div>

    <p class="sub-title">
      Our most popular marble types
    </p>

  </div>

  <!-- SLIDER -->
  <div class="slider-container">

    <!-- LEFT BUTTON -->
    <button class="arrow left">
      &#10094;
    </button>

    <!-- WRAPPER -->
    <div class="best-selling-wrapper">

      <!-- PRODUCTS -->
      <div class="best-selling">

       <?php foreach($bestSelling as $product){ 
  $imgUrl = Product::imageUrl($product['image']);
  $detailPage = $product['product_type'] === 'Marble' ? 'mardetails.php' : 'gradetails.php';
?>
  <div class="product">
    <img src="<?php echo h($imgUrl); ?>" alt="<?php echo h($product['product_name']); ?>">
    <div class="product-info">
      <h3><?php echo h($product['product_name']); ?></h3>
      <div class="rating">⭐ <span>5.0</span></div>
      <a href="<?php echo $detailPage; ?>?id=<?php echo (int)$product['product_id']; ?>">
        <button class="view-btn">View More →</button>
      </a>
    </div>
  </div>
<?php } ?>
            


      </div>

    </div>

    <!-- RIGHT BUTTON -->
    <button class="arrow right">
      &#10095;
    </button>

  </div>

  <!-- DOTS -->
  <div class="dots">

    <span class="active"></span>

    <span></span>

  </div>

</section>
</section>

<!-- WHY CHOOSE US -->
<section class="why-us">

    <!-- Top Title -->
    <div class="why-header">
        <div class="title-decoration"></div>

        <h2 class="why-title">
            <span class="crown">
                <i class="fas fa-crown"></i>
            </span>
            WHY CHOOSE US !
        </h2>

        <p class="why-subtitle">
            Excellence in every step, from stone to masterpiece
        </p>
    </div>

    <!-- Cards -->
    <div class="why-container">

        <!-- Card 1 -->
        <div class="why-card">
            <div class="icon">
                <i class="fas fa-industry"></i>
            </div>

            <h3>Advanced Machinery</h3>

            <div class="line"></div>

            <p>
                Using CNC & Water Jet machines for accurate marble processing
            </p>
        </div>

        <!-- Card 2 -->
        <div class="why-card">
            <div class="icon">
                <i class="fas fa-scissors"></i>
            </div>

            <h3>Precision Cutting</h3>

            <div class="line"></div>

            <p>
                Precise cutting with minimal waste and perfect dimensions.
            </p>
        </div>

        <!-- Card 3 -->
        <div class="why-card">
            <div class="icon">
                <i class="fas fa-star"></i>
            </div>

            <h3>Premium Polishing</h3>

            <div class="line"></div>

            <p>
                High-quality polishing for smooth, glossy marble finishes.
            </p>
        </div>

        <!-- Card 4 -->
        <div class="why-card">
            <div class="icon">
                <i class="fas fa-tools"></i>
            </div>

            <h3>Custom Fabrication</h3>

            <div class="line"></div>

            <p>
                Custom CNC milling and fabrication tailored to your needs.
            </p>
        </div>

        <!-- Card 5 -->
        <div class="why-card">
            <div class="icon">
                <i class="fas fa-user-cog"></i>
            </div>

            <h3>Professional Installation</h3>

            <div class="line"></div>

            <p>
                On-site professional installation by our skilled team.
            </p>
        </div>

        <!-- Card 6 -->
        <div class="why-card">
            <div class="icon">
                <i class="fas fa-shield-alt"></i>
            </div>

            <h3>Trusted & Reliable</h3>

            <div class="line"></div>

            <p>
                Marble supply, fabrication, and installation you can trust.
            </p>
        </div>

    </div>
</section>

<!-- Stats -->
<section class="stats-section">
  <div class="stats-container">
    <div class="stat"><h2>300+</h2><p>Premium Quality</p></div>
    <div class="stat"><h2>30K+</h2><p>Happy Customers</p></div>
    <div class="stat"><h2>15+</h2><p>Years Experience</p></div>
    <div class="stat"><h2>98%</h2><p>Satisfaction Rate</p></div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">

    <div class="cta-box">

        <!-- Top Icon -->
        <div class="cta-icon">
            <i class="fa-regular fa-building"></i>
        </div>

        <!-- Top Lines -->
        <div class="cta-lines">
            <span></span>
            <span></span>
        </div>

        <!-- Text -->
        <div class="cta-text">

            <h2>
                Ready to
                <span>Upgrade</span>
                Your Space?!
            </h2>

            <p>
                Premium marble and granite solutions with precision cutting
                and professional installation.
            </p>

        </div>

        <!-- Buttons -->
        <div class="cta-buttons">

            <a href="marble.php" class="btn primary">
                View Products
                <i class="fas fa-arrow-right"></i>
            </a>

            <a href="contact.php" class="btn outline">
                Contact Us
                <i class="fas fa-phone"></i>
            </a>

        </div>

    </div>

</section>

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
      <a href="booking.php"><i class="fas fa-calendar-check"></i> Book Now</a>
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
<script src="../js/navbar.js?v=<?php echo filemtime('../js/navbar.js'); ?>" ></script>
<script src="../js/home.js?v=<?php echo filemtime('../js/home.js'); ?>"defer></script>

</body>
</html>