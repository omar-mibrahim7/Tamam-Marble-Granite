<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
$adminRole = require_admin_role(['admin', 'staff'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/product.php");

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$perPage = 10;
$totalProducts = Product::countAll($conn);
$totalPages = max(1, (int)ceil($totalProducts / $perPage));
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages));
$offset = ($currentPage - 1) * $perPage;
$products = Product::findAll($conn, $perPage, $offset);
$deleted = isset($_GET['deleted']) && $_GET['deleted'] == '1';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Items</title>
 <link rel="stylesheet" href="../css/navbaradmin.css?v=<?php echo filemtime('../css/navbaradmin.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/edit item.css?v=<?php echo filemtime('../css/edit item.css'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="header">
    <div class="overlay"></div>

    <div class="navbar">

        <!-- MENU ICON -->
        <i class="fas fa-bars menu-icon" onclick="toggleMenu()"></i>

        <!-- LOGO -->
        <img src="../pic/logo.png" class="logo">

        <!-- RIGHT ICON -->
        <div class="right-icons">
            <a href="dashboard.php">
                <i class="fas fa-user-shield admin-icon"></i>
            </a>
        </div>

    </div>

    <!-- TITLE -->
    <div class="title">
        <h1><?php echo $isAdmin ? 'Manage Items' : 'Staff Panel'; ?></h1>
    </div>
</header>


<?php render_admin_sidebar('manage-items'); ?>

<div class="container">

  <h2 class="section-title">All Items</h2>

  <?php if ($deleted): ?>
    <p style="color:green; margin-bottom:15px;">Product deleted successfully.</p>
  <?php elseif ($error !== ''): ?>
    <p style="color:#b00020; margin-bottom:15px;">Product action could not be completed.</p>
  <?php endif; ?>

  <div class="table-header">
    <span>Product</span>
    <span>Id Product</span>
    <span>Type</span>
    <span>Product Name</span>
    <span>Short Description</span>
    <span>Actions</span>
  </div>

  <?php if (empty($products)): ?>
    <div class="table-row">
      <span style="color:#888;">No products found.</span>
    </div>
  <?php endif; ?>

  <?php foreach ($products as $product): ?>
    <div class="table-row">

      <img src="<?php echo h(Product::imageUrl($product['image'] ?? '', '../pic/product-2.jpg')); ?>"
           alt="<?php echo h($product['product_name']); ?>">

      <span>#<?php echo (int)$product['product_id']; ?></span>
      <span><?php echo h($product['product_type']); ?></span>
      <span><?php echo h($product['product_name']); ?></span>
      <p><?php echo h($product['description'] ?: 'No description.'); ?></p>

      <div class="actions">
        <a href="edit-item.php?id=<?php echo (int)$product['product_id']; ?>" title="Edit product">
          <i class="fa-regular fa-pen-to-square edit"></i>
        </a>
        <form action="../../Controller/delete_item.php" method="POST" style="display:inline;"
              onsubmit="return confirm('Are you sure you want to delete this product?');">
          <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">
          <button type="submit" title="Delete product" style="border:0;background:transparent;padding:0;cursor:pointer;">
            <i class="fa-solid fa-trash delete"></i>
          </button>
        </form>
      </div>

    </div>
  <?php endforeach; ?>
<!-- Pagination -->
<div class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="manage-items.php?page=<?php echo $currentPage - 1; ?>"><button class="circle-btn">&lt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&lt;</button>
  <?php endif; ?>

  <span class="page-number"><?php echo $currentPage; ?> / <?php echo $totalPages; ?></span>

  <?php if ($currentPage < $totalPages): ?>
    <a href="manage-items.php?page=<?php echo $currentPage + 1; ?>"><button class="circle-btn">&gt;</button></a>
  <?php else: ?>
    <button class="circle-btn" disabled>&gt;</button>
  <?php endif; ?>
</div>

</div>

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
      <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="manage-items.php"><i class="fas fa-cog"></i> Manage Products</a>
      <a href="new-item.php"><i class="fas fa-plus"></i> New Item</a>
      <a href="report.php"><i class="fas fa-file-export"></i> Reports</a>
      <a href="escalated-messages.php"><i class="fas fa-triangle-exclamation"></i> Escalated Messages</a>
    </div>
    <div class="footer-contact">
      <h3>Contact Us</h3>
      <p><i class="fas fa-map-marker-alt"></i> 123 Street Name, City</p>
      <p><i class="fas fa-phone"></i> +20 01111111111</p>
      <p><i class="fas fa-envelope"></i> info@gmail.com</p>
    </div>
  </div>
</footer>

<script src="../js/navbaradmin.js?v=<?php echo filemtime('../js/navbaradmin.js'); ?>"></script>
</body>
</html>
