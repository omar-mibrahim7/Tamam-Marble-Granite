<?php
session_start();
require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/cart.php");

function redirectBackToProduct($fallback){
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
    header("Location: ../View/php/cart.php");
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$productId || $productId <= 0) {
    redirectBackToProduct("../View/php/cart.php?error=invalid_product");
}

if (!$quantity || $quantity <= 0) {
    $quantity = 1;
}

$cart = new Cart(null, $conn);

if (!$cart->productExists($productId)) {
    redirectBackToProduct("../View/php/cart.php?error=product_not_found");
}

$cart->addItem((int)$_SESSION['user_id'], $productId, $quantity);

if (isset($_POST['redirect']) && $_POST['redirect'] === 'cart') {
    header("Location: ../View/php/cart.php");
    exit;
}

redirectBackToProduct("../View/php/cart.php?added=1");
if (isset($_POST['redirect']) && $_POST['redirect'] === 'cart') {
    header("Location: ../View/php/cart.php");
    exit;
}

?>
