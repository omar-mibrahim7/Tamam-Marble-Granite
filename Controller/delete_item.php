<?php
require_once(__DIR__ . "/admin_auth.php");
require_admin_role(['admin'], "../View/php/adminlogin.php");

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/product.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/manage-items.php");
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
if ($productId <= 0) {
    header("Location: ../View/php/manage-items.php?error=missing");
    exit;
}

if (!Product::softDelete($conn, $productId)) {
    header("Location: ../View/php/manage-items.php?error=delete");
    exit;
}

header("Location: ../View/php/manage-items.php?deleted=1");
exit;
?>
