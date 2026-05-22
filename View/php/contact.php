<?php
// ============================================================
// CONTACT.PHP
// ============================================================
session_start();
require_once(__DIR__ . "/../../Model/contactMessage.php");

// --- Login State ---
$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";

// --- لو جه من Controller بعد ما بعت الرسالة ---
$success = isset($_GET['success']) && $_GET['success'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us</title>
  <link rel="stylesheet" href="../css/contact us.css?v=<?php echo filemtime('../css/contact us.css'); ?>">
<link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">
<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">
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
    <h1>Contact Us </h1>
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

<div class="contact-container">

  <div class="contact-header">
    <div class="header-line">
      <span class="line"></span>
      <div class="mail-icon"><i class="fas fa-envelope"></i></div>
      <span class="line"></span>
    </div>
    <h2>Send Us a Message</h2>
    <p class="contact-sub">We'd love to hear from you. Send us a message and we'll get back to you soon.</p>
  </div>

  <form class="contact-form" action="../../Controller/send_message.php" method="POST">
    <div class="form-left">
      <label>Full Name</label>
      <div class="input-group">
        <i class="fas fa-user"></i>
        <input type="text" name="full_name" placeholder="e.g. Ahmed Mohamed" required>
      </div>

      <label>Phone / WhatsApp Number</label>
      <div class="input-group">
        <i class="fas fa-phone"></i>
        <input type="text" name="phone" placeholder="01012345678" required>
      </div>

      <label>Subject</label>
      <div class="input-group">
        <i class="fas fa-file-alt"></i>
        <input type="text" name="subject" placeholder="e.g. Request for site visit" required>
      </div>
    </div>

    <div class="form-right">
      <label>Message</label>
      <div class="input-group textarea-group">
        <i class="fas fa-pen"></i>
        <textarea name="message" placeholder="e.g. I would like more details about marble options..." required></textarea>
      </div>
    </div>
  </form>

  <button type="button" class="send-btn" onclick="submitForm()">
    <i class="fas fa-paper-plane"></i> Send Message
  </button>

</div>

<!-- Popup (بيظهر لو success=1 في الـ URL) -->
<div class="success-popup" id="popup" style="<?php echo $success ? 'display:flex' : 'display:none'; ?>">
  <div class="popup-box">
    <div class="check">✔</div>
    <h3>Message Sent Successfully</h3>
    <p>
      Thank you for contacting us <br>
      Our team will get back to you via WhatsApp within 48 hours
    </p>
    <button onclick="window.location.href='home.php'">Back to Home</button>
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
<script src="../js/contact.js?v=<?php echo filemtime('../js/contact.js'); ?>"></script>

</body>
</html>
