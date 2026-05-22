<?php
// ============================================================
// Controller/reset_password.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/php/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/change-password.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($newPassword !== $confirmPassword) {
    header("Location: ../View/php/change-password.php?error=mismatch");
    exit;
}

if (strlen($newPassword) < 8) {
    header("Location: ../View/php/change-password.php?error=short");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE user_id = ? AND role = 'customer' LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($currentPassword, $user['password'])) {
    header("Location: ../View/php/change-password.php?error=wrong");
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ? AND role = 'customer'");
mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $userId);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$success) {
    header("Location: ../View/php/change-password.php?error=save");
    exit;
}

header("Location: ../View/php/change-password.php?success=1");
exit;
?>
