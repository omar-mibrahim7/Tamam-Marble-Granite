<?php
// ============================================================
// PROFILE.PHP (my account)
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../../config/db.php");

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

$userId = (int)$_SESSION['user_id'];
$stmt = mysqli_prepare(
    $conn,
    "SELECT user_id, full_name, email, phone, whatsapp, address, city, area, role
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$currentUser = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$currentUser || $currentUser['role'] !== 'customer') {
    session_unset();
    session_destroy();
    header("Location: login.php?error=session");
    exit;
}

$_SESSION['full_name'] = $currentUser['full_name'];
$_SESSION['email'] = $currentUser['email'];
$_SESSION['phone'] = $currentUser['phone'];
$_SESSION['whatsapp'] = $currentUser['whatsapp'];
$_SESSION['address'] = $currentUser['address'];
$_SESSION['city'] = $currentUser['city'];
$_SESSION['area'] = $currentUser['area'];

$saved = isset($_GET['saved']) && $_GET['saved'] == '1';
$registered = isset($_GET['registered']) && $_GET['registered'] == '1';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - Profile</title>
  <link rel="stylesheet" href="../css/navbar.css?v=<?php echo filemtime('../css/navbar.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/my account.css?v=<?php echo filemtime('../css/my account.css'); ?>">
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
          <a class="user-link active" href="profile.php">
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

    <!-- Profile Form -->
    <section class="account-card">
      <h2>Personal Information</h2>

      <?php if ($saved || $registered): ?>
        <p style="color:green; margin-bottom:10px;">
          <?php echo $registered ? 'Account created successfully. You are signed in now.' : 'Saved successfully!'; ?>
        </p>
      <?php endif; ?>

      <?php if ($error): ?>
        <p style="color:#b00020; margin-bottom:10px;">
          <?php
            if ($error === 'exists') {
                echo 'This email is already used by another account.';
            } elseif ($error === 'email') {
                echo 'Please enter a valid email address.';
            } elseif ($error === 'missing') {
                echo 'Full name, email, and phone are required.';
            } else {
                echo 'Profile could not be saved. Please try again.';
            }
          ?>
        </p>
      <?php endif; ?>

      <form class="account-form" action="../../Controller/update_profile.php" method="POST">

        <div class="form-row">
          <label>Full Name</label>
          <input type="text" name="full_name"
                 value="<?php echo h($currentUser['full_name']); ?>"
                 placeholder="e.g. Ahmed Mohamed" required>
        </div>

        <div class="form-row">
          <label>Email Address</label>
          <input type="email" name="email"
                 value="<?php echo h($currentUser['email']); ?>"
                 placeholder="e.g. ahmed@email.com" required>
        </div>

        <div class="form-row">
          <label>Phone Number</label>
          <input type="text" name="phone"
                 value="<?php echo h($currentUser['phone']); ?>"
                 placeholder="01012345678" required>
        </div>

        <div class="form-row">
          <label>WhatsApp Number</label>
          <input type="text" name="whatsapp"
                 value="<?php echo h($currentUser['whatsapp']); ?>"
                 placeholder="01098765432">
        </div>

        <div class="form-row">
          <label>City</label>
          <input type="text" name="city"
                 value="<?php echo h($currentUser['city']); ?>"
                 placeholder="e.g. Cairo">
        </div>

        <div class="form-row">
          <label>Area</label>
          <input type="text" name="area"
                 value="<?php echo h($currentUser['area']); ?>"
                 placeholder="e.g. Maadi">
        </div>

        <button type="submit" class="save-btn">Save</button>

      </form>
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

<script src="../js/account.js?v=<?php echo filemtime('../js/account.js'); ?>"></script>

</body>
</html>
