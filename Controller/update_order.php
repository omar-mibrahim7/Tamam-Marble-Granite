<?php
require_once(__DIR__ . "/admin_auth.php");
admin_normalize_session(); // ← حط ده هنا

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/AdminOrder.php");

if (!isset($_SESSION['user_id']) || !in_array(admin_current_role(), ['admin', 'staff'])) {
    if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    header("Location: ../View/php/adminlogin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/personal-order.php");
    exit;
}

$isJson = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
$payload = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;

$orderId = preg_replace('/\D+/', '', (string)($payload['orderId'] ?? $payload['booking_id'] ?? ''));
$status = $payload['status'] ?? (isset($payload['confirm']) ? 'confirmed' : '');

if ($orderId === '' || $status === '') {
    if ($isJson) {
        echo json_encode(['success' => false, 'message' => 'Missing order or status']);
        exit;
    }

    header("Location: ../View/php/personal-order.php?error=missing");
    exit;
}

$bookingId = (int)$orderId;
$success = AdminOrder::updateStatus($conn, $bookingId, $status);

if ($isJson) {
    echo json_encode(['success' => $success]);
    exit;
}

if (!$success) {
    header("Location: ../View/php/order-detail.php?id={$bookingId}&error=status");
    exit;
}

header("Location: ../View/php/order-detail.php?id={$bookingId}&success=1");
exit;
?>
