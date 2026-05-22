<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$context = $_SESSION['otp_context'] ?? 'account';
$isForgotPassword = $context === 'forgot_password' && isset($_SESSION['reset_user_id']);
$verifyPage = $isForgotPassword ? "../View/php/verify.php" : "../View/php/verify-code.php";
$successPage = $isForgotPassword ? "../View/php/reset.php" : "../View/php/change-password.php";

if (!$isForgotPassword && !isset($_SESSION['user_id'])) {
    header("Location: ../View/php/login.php");
    exit;
}

if (!isset($_SESSION['otp_sent']) || !$_SESSION['otp_sent']) {
    header("Location: " . ($isForgotPassword ? "../View/php/forget.php" : "../View/php/account-management.php"));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: {$verifyPage}");
    exit;
}

$entered = ($_POST['d1'] ?? '') .
           ($_POST['d2'] ?? '') .
           ($_POST['d3'] ?? '') .
           ($_POST['d4'] ?? '') .
           ($_POST['d5'] ?? '');

$storedOtp = $_SESSION['otp_code'] ?? '';
$storedExpiry = $_SESSION['otp_expiry'] ?? 0;

if (time() > $storedExpiry) {
    unset($_SESSION['otp_code'], $_SESSION['otp_expiry'], $_SESSION['otp_sent']);
    header("Location: {$verifyPage}?error=expired");
    exit;
}

if ($entered !== $storedOtp) {
    header("Location: {$verifyPage}?error=invalid");
    exit;
}

unset($_SESSION['otp_code'], $_SESSION['otp_expiry'], $_SESSION['otp_sent']);
$_SESSION['otp_verified'] = true;

header("Location: {$successPage}");
exit;
?>
