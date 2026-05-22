<?php
// ============================================================
// ACCOUNT-MANAGEMENT.PHP
// ============================================================
session_start();

// --- لو مش logged in ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userHref   = $isLoggedIn ? "profile.php" : "login.php";
$userIcon   = $isLoggedIn ? "fa-circle-user" : "fa-right-to-bracket";

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Account Management</title>
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

    <div class="content-wrap">
      <div class="security-card">
        <div class="card-head">
          <span class="icon-badge"><i class="fa-solid fa-shield"></i></span>
          <div>
            <h3 class="card-title">Security Management</h3>
            <p class="card-subtitle">Manage the security options for your account with a clean, clear layout that matches the Tamam design style.</p>
          </div>
        </div>

        <div class="security-list">
          <!-- Change Password → controllers/change_password.php بعدين -->
          <a class="security-action" href="change-password.php">
            <span>Change Password</span>
            <i class="fa-solid fa-chevron-right"></i>
          </a>
          <!-- Delete Account → controllers/delete_account.php بعدين -->
          <button class="security-action danger danger-action-button" type="button" id="open-delete-modal">
            <span>Delete Account</span>
            <i class="fa-solid fa-chevron-right"></i>
          </button>
        </div>

        <?php if ($error === 'delete_password'): ?>
          <div class="inline-alert inline-alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            Current password is incorrect.
          </div>
        <?php elseif ($error === 'delete_failed'): ?>
          <div class="inline-alert inline-alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            Account could not be deleted. Please try again.
          </div>
        <?php endif; ?>
      </div>
    </div>

  </section>
</main>

<div class="account-modal-overlay" id="delete-account-modal" aria-hidden="true" hidden>
  <div class="account-modal" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
    <button class="modal-close" type="button" data-close-delete-modal aria-label="Close">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <form action="../../Controller/delete_account.php" method="POST" id="delete-account-form">
      <div class="delete-step" id="delete-password-step">
        <div class="modal-icon danger-icon"><i class="fa-solid fa-lock"></i></div>
        <h3 id="delete-modal-title">Confirm Your Password</h3>
        <p>Enter your current password before deleting your account.</p>

        <div class="form-group has-left-icon">
          <div class="input-wrap">
            <span class="input-icon"><i class="fa-solid fa-key"></i></span>
            <input id="deletePassword"
                   class="form-input"
                   type="password"
                   name="current_password"
                   placeholder="Current Password"
                   required>
            <button class="eye-btn" type="button" data-toggle-password="deletePassword">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="modal-actions">
          <button class="secondary-btn" type="button" data-close-delete-modal>Cancel</button>
          <button class="danger-btn" type="button" id="continue-delete">Continue</button>
        </div>
      </div>

      <div class="delete-step is-hidden" id="delete-confirm-step" hidden>
        <div class="modal-icon danger-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <h3>Delete Account?</h3>
        <p>This will permanently remove your account and related account data.</p>

        <div class="modal-actions">
          <button class="secondary-btn" type="button" id="back-delete-password">Back</button>
          <button class="danger-btn" type="submit">Delete Account</button>
        </div>
      </div>
    </form>
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
<script src="../js/navbar.js?v=<?php echo filemtime('../js/navbar.js'); ?>"></script>

<script src="../js/account-management.js?v=<?php echo filemtime('../js/account-management.js'); ?>"></script>
</body>
</html>
