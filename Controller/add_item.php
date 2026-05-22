<?php
require_once(__DIR__ . "/admin_auth.php");
require_admin_role(['admin'], "../View/php/adminlogin.php");

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/product.php");
require_once(__DIR__ . "/product_form_helpers.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/new-item.php");
    exit;
}

list($data, $error) = product_admin_collect_data();
if ($error !== '') {
    header("Location: ../View/php/new-item.php?error={$error}");
    exit;
}

$productId = Product::create($conn, $data);
if (!$productId) {
    header("Location: ../View/php/new-item.php?error=save");
    exit;
}

header("Location: ../View/php/edit-item.php?id={$productId}&success=1");
exit;
?>
