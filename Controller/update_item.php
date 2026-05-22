<?php
require_once(__DIR__ . "/admin_auth.php");
require_admin_role(['admin'], "../View/php/adminlogin.php");

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/product.php");
require_once(__DIR__ . "/product_form_helpers.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/manage-items.php");
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
if ($productId <= 0) {
    header("Location: ../View/php/manage-items.php?error=missing");
    exit;
}

$product = Product::findById($conn, $productId);
if (!$product) {
    header("Location: ../View/php/manage-items.php?error=not_found");
    exit;
}

list($data, $error) = product_admin_collect_data($product['image'] ?? '');
if ($error !== '') {
    header("Location: ../View/php/edit-item.php?id={$productId}&error={$error}");
    exit;
}

if (!Product::updateById($conn, $productId, $data)) {
    header("Location: ../View/php/edit-item.php?id={$productId}&error=save");
    exit;
}

header("Location: ../View/php/edit-item.php?id={$productId}&success=1");
exit;
?>
