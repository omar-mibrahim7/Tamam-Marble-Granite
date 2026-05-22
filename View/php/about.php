<?php
// ============================================================
// ABOUT.PHP
// ============================================================
session_start();

// --- Login State ---
$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us</title>
  <link rel="stylesheet" href="../css/about us.css?v=<?php echo filemtime('../css/about us.css'); ?>">
  <link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">
  <link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
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
    <h1>About Us</h1>
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

<div class="main-bg">

  <!-- WHO -->
  <section class="who">
    <img src="../pic/inside.png">
    <div class="text">
      <h2>Who We Are ?</h2>
      <p>
        Tamam Marble & Granite is a leading manufacturer and supplier of high-quality marble and granite.
        We specialize in delivering premium stone solutions by combining modern manufacturing techniques
        with skilled craftsmanship to meet the needs of residential and commercial projects.
      </p>
      <ul>
        <li>Premium marble & granite materials</li>
        <li>Advanced manufacturing techniques</li>
        <li>Skilled engineers and craftsmen</li>
        <li>Accurate measurement and inspection</li>
        <li>Residential & commercial projects</li>
      </ul>
    </div>
  </section>

  <!-- STORY -->
  <section class="story">
    <img src="../pic/Factorytmam.png">
    <div class="text">
      <h2>Our Story</h2>
      <p>Founded with a passion for stone craftsmanship, Tamam Marble & Granite has grown into a trusted name in marble and granite manufacturing.</p>
      <p>Over the years, we have built our reputation on quality, precision, and attention to detail, delivering exceptional results across residential and commercial projects.</p>
      <p>Our commitment to excellence and customer satisfaction has allowed us to establish long-term partnerships and a strong presence in the industry.</p>
    </div>
  </section>

  <h2 class="section-title">Our Values</h2>

  <section class="values">
    <div class="card">
      <div class="icon">🛡️</div>
      <h3>Quality First</h3>
      <p>Using the finest marble and granite materials to deliver exceptional quality and long-lasting results.</p>
    </div>
    <div class="card">
      <div class="icon">🤝</div>
      <h3>Commitment</h3>
      <p>Dedicated to exceeding client expectations through reliability, professionalism, and attention to detail.</p>
    </div>
    <div class="card">
      <div class="icon">
        <svg viewBox="0 0 64 64" width="72" height="72" aria-hidden="true">
          <path d="M22 2h8l-2 14h-4L22 2z" fill="#d32f2f"/>
          <path d="M34 2h8l-2 14h-4L34 2z" fill="#b71c1c"/>
          <circle cx="32" cy="40" r="18" fill="#f4b400"/>
          <circle cx="32" cy="40" r="14" fill="#ffca28"/>
          <polygon points="32,30 34.9,36.5 42,37.2 36.8,41.8 38.3,48.8 32,45.2 25.7,48.8 27.2,41.8 22,37.2 29.1,36.5" fill="#f57f17"/>
          <circle cx="27" cy="34" r="4" fill="#fff59d" opacity="0.6"/>
        </svg>
      </div>
      <h3>Experience & Expertise</h3>
      <p>Years of experience combined with technical knowledge to deliver high-quality marble and granite solutions.</p>
    </div>
  </section>

  <h2 class="section-title">Our Facility</h2>

  <section class="facility">
    <img src="../pic/about1.png" alt="facility 1">
    <img src="../pic/about2.png" alt="facility 2">
    <img src="../pic/about3.png" alt="facility 3">
  </section>

  <p class="desc">
    Our factory is equipped with advanced machinery and skilled professionals,
    enabling us to handle projects of all sizes with consistent quality and precision.
  </p>

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

</body>
</html>
