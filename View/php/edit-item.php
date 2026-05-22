<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
require_admin_role(['admin'], "adminlogin.php");

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/product.php");
$adminRole = require_admin_role(['admin'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';
function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: manage-items.php?error=missing");
    exit;
}

$product = Product::findById($conn, $id);
if (!$product) {
    header("Location: manage-items.php?error=not_found");
    exit;
}

$success = isset($_GET['success']) && $_GET['success'] == '1';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Item</title>
 <link rel="stylesheet" href="../css/navbaradmin.css?v=<?php echo filemtime('../css/navbaradmin.css'); ?>">

<link rel="stylesheet" href="../css/footer.css?v=<?php echo filemtime('../css/footer.css'); ?>">

<link rel="stylesheet" href="../css/adminadditem.css?v=<?php echo filemtime('../css/adminadditem.css'); ?>">
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
        <h1><?php echo $isAdmin ? 'Edit Item' : 'Staff Panel'; ?></h1>
    </div>
</header>


<?php render_admin_sidebar('manage-items'); ?>

<div class="container">
  <h2 class="section-title">Edit Product</h2>

 <?php if ($success): ?>

  <p style="
    color:#1f7a1f;
    background:#eafbea;
    border:1px solid #b8e6b8;
    padding:14px 24px;
    border-radius:16px;
    width:fit-content;
    margin:0 auto 20px;
    text-align:center;
    font-weight:600;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
  ">
    Product updated successfully.
  </p>

<?php elseif ($error !== ''): ?>

  <p style="
    color:#b00020;
    background:#ffe8ec;
    border:1px solid #ffb8c4;
    padding:14px 24px;
    border-radius:16px;
    width:fit-content;
    margin:0 auto 20px;
    text-align:center;
    font-weight:600;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
  ">
    Please check product data and try again.
  </p>

<?php endif; ?>

  <form class="form-box" action="../../Controller/update_item.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="product_id" value="<?php echo (int)$product['product_id']; ?>">

    <div class="left">

      <label>Type Product</label>
      <select name="product_type" required>
        <option value="Marble" <?php echo $product['product_type'] === 'Marble' ? 'selected' : ''; ?>>Marble</option>
        <option value="Granite" <?php echo $product['product_type'] === 'Granite' ? 'selected' : ''; ?>>Granite</option>
      </select>

      <label>Product Name</label>
      <input type="text" name="product_name" maxlength="150"
             value="<?php echo h($product['product_name']); ?>" required>

      <label>Price</label>
      <input type="number" name="price" min="0" step="0.01"
             value="<?php echo h($product['price']); ?>" required>

      <label>Id Product</label>
      <input type="text" name="product_id_display" value="#<?php echo (int)$product['product_id']; ?>" readonly>

      <label>Current Image</label>
      <div class="image-upload">
        <img src="<?php echo h(Product::imageUrl($product['image'] ?? '', '../pic/product-2.jpg')); ?>"
             alt="<?php echo h($product['product_name']); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
      </div>
      <input type="file" name="product_image" accept="image/*">

      <label>Short Description</label>
      <textarea name="description" placeholder="Short description..."><?php echo h($product['description']); ?></textarea>

      <label>Application Type</label>
      <input type="text" name="application_type"
             value="<?php echo h($product['application_type']); ?>"
             placeholder="e.g. Kitchen Countertops, Flooring">

    </div>

    <div class="right">

      <label>Material</label>
      <input type="text" name="material" value="<?php echo h($product['material']); ?>">

      <label>Color</label>
      <input type="text" name="color" value="<?php echo h($product['color']); ?>" placeholder="e.g. Light Beige">

      <label>Finish Options</label>
      <input type="text" name="finish" value="<?php echo h($product['finish']); ?>" placeholder="e.g. Polished / Honed">

      <label>Available Thickness</label>
      <input type="text" name="thickness" value="<?php echo h($product['thickness']); ?>" placeholder="e.g. 2 cm / 3 cm">

      <label>Available Sizes</label>
      <input type="text" name="sizes" value="<?php echo h($product['sizes']); ?>" placeholder="e.g. Custom sizes">

      <label>Standard Slab Size</label>
      <input type="text" name="slab_size" value="<?php echo h($product['dimensions']); ?>">

      <label>Edge Options</label>
      <input type="text" name="edge_options" value="<?php echo h($product['edge_options']); ?>" placeholder="e.g. Straight, Beveled">

      <label>Water Resistance</label>
      <input type="text" name="water_resistance" value="<?php echo h($product['water_resistance']); ?>" placeholder="e.g. Medium / High">

      <label>Heat Resistance</label>
      <input type="text" name="heat_resistance" value="<?php echo h($product['heat_resistance']); ?>" placeholder="e.g. High">

      <label>Scratch Resistance</label>
      <input type="text" name="scratch_resistance" value="<?php echo h($product['scratch_resistance']); ?>" placeholder="e.g. Medium">

      <label>Stock</label>
      <select name="stock">
        <option value="100" <?php echo (int)$product['stock'] >= 50 ? 'selected' : ''; ?>>In Stock</option>
        <option value="10" <?php echo (int)$product['stock'] > 0 && (int)$product['stock'] < 50 ? 'selected' : ''; ?>>Low Stock</option>
        <option value="0" <?php echo (int)$product['stock'] === 0 ? 'selected' : ''; ?>>Out of Stock</option>
      </select>

      <label>Best Selling</label>
      <select name="is_best_selling">
        <option value="1" <?php echo (int)$product['is_best_selling'] === 1 ? 'selected' : ''; ?>>Yes</option>
        <option value="0" <?php echo (int)$product['is_best_selling'] !== 1 ? 'selected' : ''; ?>>No</option>
      </select>

    </div>

    <button type="submit" class="submit-btn">Save Changes</button>

  </form>
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
