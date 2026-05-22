<?php
session_start();
require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/Wishlist.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/php/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/favorites.php");
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if ($productId && $productId > 0) {
    $wishlist = new Wishlist($conn);
    $wishlist->removeItem((int)$_SESSION['user_id'], $productId);
}

header("Location: ../View/php/favorites.php?removed=1");
exit;
?>
