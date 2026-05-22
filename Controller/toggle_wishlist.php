<?php
session_start();
require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/Wishlist.php");

function redirectBackToWishlistSource($fallback){
    $target = $_SERVER['HTTP_REFERER'] ?? $fallback;
    $host = $_SERVER['HTTP_HOST'] ?? '';

    if (preg_match('/^https?:\/\//i', $target)) {
        $targetHost = parse_url($target, PHP_URL_HOST);
        if ($targetHost !== $host) {
            $target = $fallback;
        }
    }

    header("Location: " . $target);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/php/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/favorites.php");
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$productId || $productId <= 0) {
    redirectBackToWishlistSource("../View/php/favorites.php?error=invalid_product");
}

$wishlist = new Wishlist($conn);
$result = $wishlist->toggleItem((int)$_SESSION['user_id'], $productId);

if ($result === 'removed') {
    redirectBackToWishlistSource("../View/php/favorites.php?removed=1");
}

if ($result === 'added') {
    redirectBackToWishlistSource("../View/php/favorites.php?added=1");
}

redirectBackToWishlistSource("../View/php/favorites.php?error=product_not_found");
?>
