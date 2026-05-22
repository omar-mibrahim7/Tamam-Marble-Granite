<?php
// ============================================================
// VIEW/php/verify-code.php
// ============================================================
session_start();

// لو مش logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";
// لو مفيش OTP متبعت (حد جه على الصفحة دي مباشرة)
if (!isset($_SESSION['otp_sent']) || !$_SESSION['otp_sent']) {
    header("Location: account-management.php");
    exit;
}

$error   = $_GET['error'] ?? '';
$resent  = isset($_GET['resent']) && $_GET['resent'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Verification Code</title>
 <link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/account-management.css?v=<?php echo filemtime('../css/account-management.css'); ?>">

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
          <a class="user-link" href="tracking.php">
            <span><i class="fa-solid fa-location-dot"></i> Order Tracking</span>
            <span class="go"><i class="fa-solid fa-chevron-right"></i></span>
          </a>
          <a class="user-link active" href="account-management.php">
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

    <!-- Main Content -->
    <div class="content-wrap">
      <div class="verify-card">
        <h3 class="verify-title">Enter Verification Code</h3>
        <p class="verify-text">
          Please enter the 5-digit verification code sent to
          <strong><?php echo htmlspecialchars($_SESSION['email'] ?? 'your email'); ?></strong>
        </p>

        <!-- Error / Resent Messages -->
        <?php if ($error === 'invalid'): ?>
          <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            Invalid code. Please try again.
          </div>
        <?php elseif ($error === 'expired'): ?>
          <div class="alert alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            Code has expired. Please request a new one.
          </div>
        <?php endif; ?>

        <?php if ($resent): ?>
          <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            A new code has been sent to your email.
          </div>
        <?php endif; ?>

        <!-- OTP Form -->
        <form action="../../Controller/verify_otp.php" method="POST">
          <div class="code-row">
            <input class="code-input" name="d1" maxlength="1" inputmode="numeric" required>
            <input class="code-input" name="d2" maxlength="1" inputmode="numeric" required>
            <input class="code-input" name="d3" maxlength="1" inputmode="numeric" required>
            <input class="code-input" name="d4" maxlength="1" inputmode="numeric" required>
            <input class="code-input" name="d5" maxlength="1" inputmode="numeric" required>
          </div>

          <button class="primary-btn" type="submit">Continue</button>
        </form>

        <div class="helper-links">
          <a href="account-management.php">Back</a>
          <a href="../../Controller/resend_otp.php">Resend Code</a>
        </div>
      </div>
    </div>

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
        <img src="../pic/social.png" class="social-img" alt="social art">
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

<script src="../js/navbar.js?v=1"></script>

<script src="../js/account-management.js?v=1"></script>
<script>
// Auto-focus next input
document.querySelectorAll('.code-input').forEach((input, i, inputs) => {
  input.addEventListener('input', () => {
    if (input.value && i < inputs.length - 1) inputs[i + 1].focus();
  });
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !input.value && i > 0) inputs[i - 1].focus();
  });
});
</script>

</body>
</html>
