<?php
// ============================================================
// Controller/send_message.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../config/db.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/contact.php");
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$messageBody = trim($_POST['message'] ?? '');
$customerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

if ($fullName === '' || $phone === '' || $subject === '' || $messageBody === '') {
    header("Location: ../View/php/contact.php?error=empty");
    exit;
}

if ($customerId) {
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $customerId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $customerId = mysqli_fetch_assoc($result) ? $customerId : null;
    mysqli_stmt_close($stmt);
}

if ($customerId) {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO contact_messages (customer_id, subject, message_body, full_name, phone)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "issss", $customerId, $subject, $messageBody, $fullName, $phone);
} else {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO contact_messages (customer_id, subject, message_body, full_name, phone)
         VALUES (NULL, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "ssss", $subject, $messageBody, $fullName, $phone);
}

$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$success) {
    header("Location: ../View/php/contact.php?error=save");
    exit;
}

header("Location: ../View/php/contact.php?success=1");
exit;
?>
