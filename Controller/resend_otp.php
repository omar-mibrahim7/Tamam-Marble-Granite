<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../config/db.php");

$context = $_SESSION['otp_context'] ?? 'account';
$toEmail = '';
$fullName = 'User';
$redirectPage = "../View/php/verify-code.php?resent=1";

if ($context === 'forgot_password' && isset($_SESSION['reset_user_id'], $_SESSION['reset_email'])) {
    $toEmail = $_SESSION['reset_email'];
    $fullName = $_SESSION['full_name'] ?? 'User';
    $redirectPage = "../View/php/verify.php?resent=1";
} elseif (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "SELECT full_name, email FROM users WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$user) {
        header("Location: ../View/php/login.php");
        exit;
    }

    $toEmail = $user['email'];
    $fullName = $user['full_name'] ?: 'User';
    $_SESSION['email'] = $toEmail;
    $_SESSION['full_name'] = $fullName;
    $_SESSION['otp_context'] = 'account';
} else {
    header("Location: ../View/php/forget.php");
    exit;
}

$otp = str_pad((string)rand(0, 99999), 5, '0', STR_PAD_LEFT);
$expiry = time() + (10 * 60);

$_SESSION['otp_code'] = $otp;
$_SESSION['otp_expiry'] = $expiry;
$_SESSION['otp_sent'] = true;

$subject = "Your New Verification Code - Tamam Marble & Granite";
$message = "
Hello {$fullName},

Your new verification code is: {$otp}

This code is valid for 10 minutes.

Tamam Marble & Granite Team
";
$headers = "From: no-reply@tamam.com\r\n";
$headers .= "Reply-To: no-reply@tamam.com\r\n";

mail($toEmail, $subject, $message, $headers);

header("Location: {$redirectPage}");
exit;
?>
