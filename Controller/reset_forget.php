<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['otp_verified'], $_SESSION['reset_user_id'], $_SESSION['reset_email'])) {
    header("Location: ../View/php/forget.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/reset.php");
    exit;
}

$newPassword = trim($_POST['new_password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

if (strlen($newPassword) < 8) {
    header("Location: ../View/php/reset.php?error=short");
    exit;
}

if ($newPassword !== $confirmPassword) {
    header("Location: ../View/php/reset.php?error=mismatch");
    exit;
}

$userId = (int)$_SESSION['reset_user_id'];
$email = $_SESSION['reset_email'];
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = mysqli_prepare(
    $conn,
    "UPDATE users SET password = ? WHERE user_id = ? AND email = ? AND role = 'customer'"
);
mysqli_stmt_bind_param($stmt, "sis", $hashedPassword, $userId, $email);
$success = mysqli_stmt_execute($stmt);
$affectedRows = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if (!$success || $affectedRows < 1) {
    header("Location: ../View/php/reset.php?error=save");
    exit;
}

unset(
    $_SESSION['otp_verified'],
    $_SESSION['otp_code'],
    $_SESSION['otp_expiry'],
    $_SESSION['otp_sent'],
    $_SESSION['otp_context'],
    $_SESSION['reset_user_id'],
    $_SESSION['reset_email'],
    $_SESSION['email'],
    $_SESSION['full_name']
);

header("Location: ../View/php/login.php?success=1");
exit;
?>
