<?php
session_start();

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/cart.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/php/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/cart.php");
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if ($productId && $productId > 0) {

    $userId = (int)$_SESSION['user_id'];

    /* هات cart_id الخاص بالمستخدم */
    $getCart = mysqli_query($conn, "
        SELECT cart_id
        FROM carts
        WHERE customer_id = $userId
    ");

    $cartData = mysqli_fetch_assoc($getCart);

    if ($cartData) {

        $cartId = (int)$cartData['cart_id'];

        $cart = new Cart(null, $conn);

        $items = $cart->getItemsByUser($userId);

        foreach($items as $item){

            if((int)$item['product_id'] === $productId){

                if((int)$item['quantity'] > 1){

                    $newQty = (int)$item['quantity'] - 1;

                    mysqli_query($conn,
                        "UPDATE cart_items
                         SET quantity = $newQty
                         WHERE cart_id = $cartId
                         AND product_id = $productId");

                } else {

                    mysqli_query($conn,
                        "DELETE FROM cart_items
                         WHERE cart_id = $cartId
                         AND product_id = $productId");
                }

                break;
            }
        }
    }
}

header("Location: ../View/php/cart.php?removed=1");
exit;
?>