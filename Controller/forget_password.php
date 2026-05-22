<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../config/db.php");

if (!isset($_POST['reset'])) {
    header("Location: ../View/php/forget.php");
    exit();
}

$email = trim(strtolower($_POST['email'] ?? ''));

if ($email === '') {
    header("Location: ../View/php/forget.php?error=empty");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../View/php/forget.php?error=email");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT user_id, full_name, email FROM users WHERE LOWER(email) = ? AND role = 'customer' LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: ../View/php/forget.php?error=notfound");
    exit();
}

unset($_SESSION['otp_verified'], $_SESSION['otp_code'], $_SESSION['otp_expiry'], $_SESSION['otp_sent']);

$_SESSION['reset_user_id'] = (int)$user['user_id'];
$_SESSION['reset_email'] = $user['email'];
$_SESSION['email'] = $user['email'];
$_SESSION['full_name'] = $user['full_name'] ?: 'User';
$_SESSION['otp_context'] = 'forgot_password';

header("Location: send_otp.php");
exit();
?>
