<?php
session_start();
require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/cart.php");
require_once(__DIR__ . "/../Model/bookingRequest.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/php/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/cart.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$cart = new Cart(null, $conn);
$cartItems = $cart->getItemsByUser($userId);

if (empty($cartItems)) {
    header("Location: ../View/php/cart.php?error=empty_cart");
    exit;
}

$data = [
    'full_name' => trim($_POST['full_name'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'whatsapp_number' => trim($_POST['whatsapp_number'] ?? ($_POST['whatsapp'] ?? '')),
    'city' => trim($_POST['city'] ?? ''),
    'area' => trim($_POST['area'] ?? ''),
    'notes' => trim($_POST['notes'] ?? ''),
    'needs_engineering_visit' => isset($_POST['needs_engineering_visit']) ? 1 : 0
];

if (
    $data['full_name'] === '' ||
    $data['phone'] === '' ||
    $data['whatsapp_number'] === '' ||
    $data['city'] === '' ||
    $data['area'] === ''
) {
    header("Location: ../View/php/cart.php?error=missing_fields");
    exit;
}

$measurements = [
    'length' => is_array($_POST['length'] ?? null) ? $_POST['length'] : [],
    'width' => is_array($_POST['width'] ?? null) ? $_POST['width'] : []
];

$booking = new BookingRequest(null, date("Y-m-d"), null, $conn);
$bookingId = $booking->createBooking($userId, $data, $cartItems, $measurements);

if (!$bookingId) {
    $_SESSION['booking_error'] = $booking->getLastError();
    header("Location: ../View/php/cart.php?error=booking_failed");
    exit;
}

header("Location: ../View/php/cart.php?success=1");
exit;
?>
