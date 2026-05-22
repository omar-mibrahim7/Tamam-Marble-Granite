<?php
session_start();
header("Content-Type: application/json");

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/cart.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["count" => 0, "loggedIn" => false]);
    exit;
}

$cart = new Cart(null, $conn);
$count = $cart->countItems($_SESSION['user_id']);

echo json_encode(["count" => $count, "loggedIn" => true]);