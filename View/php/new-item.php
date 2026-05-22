<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/../../Controller/admin_auth.php");
require_once(__DIR__ . "/../../Controller/admin_nav.php");
$adminRole = require_admin_role(['admin'], "adminlogin.php");
$isAdmin = $adminRole === 'admin';


function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$success = isset($_GET['success']) && $_GET['success'] == '1';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Item</title>

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
        <h1><?php echo $isAdmin ? 'Add Item' : 'Staff Panel'; ?></h1>
    </div>
</header>


<?php render_admin_sidebar('new-item'); ?>

<div class="container">
<h2 class="section-title">New Product</h2>

 
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
Product added successfully.
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

  <form class="form-box" action="../../Controller/add_item.php" method="POST" enctype="multipart/form-data">

    <div class="left">

      <label>Type Product</label>
      <select name="product_type" required>
        <option value="">Choose Type</option>
        <option value="Marble">Marble</option>
        <option value="Granite">Granite</option>
      </select>

      <label>Product Name</label>
      <input type="text" name="product_name" maxlength="150" placeholder="e.g. Black & White Marble" required>

      <label>Price</label>
      <input type="number" name="price" min="0" step="0.01" placeholder="e.g. 250" required>

      <label>Add Image</label>
      <div class="image-upload">
        <span>+</span>
      </div>
      <input type="file" name="product_image" accept="image/*">

      <label>Short Description</label>
      <textarea name="description" placeholder="e.g. Beige marble with soft natural veining, elegant look suitable for modern and classic designs."></textarea>

      <label>Application Type</label>
      <input type="text" name="application_type" placeholder="e.g. Kitchen Countertops, Flooring, Wall Cladding">

    </div>

    <div class="right">

      <label>Material</label>
      <input type="text" name="material" placeholder="e.g. Natural Marble">

      <label>Color</label>
      <input type="text" name="color" placeholder="e.g. Light Beige with subtle veins">

      <label>Finish Options</label>
      <input type="text" name="finish" placeholder="e.g. Polished / Honed / Leathered">

      <label>Available Thickness</label>
      <input type="text" name="thickness" placeholder="e.g. 2 cm / 3 cm">

      <label>Available Sizes</label>
      <input type="text" name="sizes" placeholder="e.g. Custom sizes available upon request">

      <label>Standard Slab Size</label>
      <input type="text" name="slab_size" placeholder="e.g. 240 x 120 cm">

      <label>Edge Options</label>
      <input type="text" name="edge_options" placeholder="e.g. Straight, Beveled, Bullnose">

      <label>Water Resistance</label>
      <input type="text" name="water_resistance" placeholder="e.g. Medium / High">

      <label>Heat Resistance</label>
      <input type="text" name="heat_resistance" placeholder="e.g. High">

      <label>Scratch Resistance</label>
      <input type="text" name="scratch_resistance" placeholder="e.g. Medium / High">

      <label>Stock</label>
      <select name="stock" required>
        <option value="100">In Stock</option>
        <option value="10">Low Stock</option>
        <option value="0">Out of Stock</option>
      </select>

      <label>Best Selling</label>
      <select name="is_best_selling">
        <option value="0">No</option>
        <option value="1">Yes</option>
      </select>

    </div>

    <button type="submit" class="submit-btn">Add New Item</button>

  </form>

</div>

<footer class="footer">
  <div class="footer-content">
    <div class="footer-left">
      <div class="logo-box">
        <img src="../pic/logo.png" alt="Logo">
        <h2>Tamam Marble & Granite</h2>
      </div>
      <p>
        Tamam Marble & Granite delivers high-quality marble and granite solutions
        using advanced manufacturing and precision craftsmanship.
      </p>
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
