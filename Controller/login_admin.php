<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/LoginSecurity.php");

function redirectAdminLogin($error, $email = '', $showCaptcha = false){
    $query = ['error' => $error];

    if ($email !== '') {
        $query['email'] = $email;
    }

    if ($showCaptcha) {
        $query['captcha'] = 1;
    }

    header("Location: ../View/php/adminlogin.php?" . http_build_query($query));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['login'])) {
    header("Location: ../View/php/adminlogin.php");
    exit();
}

$email = trim(strtolower($_POST['email'] ?? ''));
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectAdminLogin('invalid', $email);
}

$ip = LoginSecurity::getClientIp();

if (LoginSecurity::isLocked($conn, $email, $ip)) {
    redirectAdminLogin('locked', $email, true);
}

$captchaRequired = LoginSecurity::shouldShowCaptcha($conn, $email, $ip);

if ($captchaRequired) {
    $captchaToken = $_POST['g-recaptcha-response'] ?? '';

    if (!LoginSecurity::verifyRecaptcha($captchaToken, $ip)) {
        redirectAdminLogin('captcha', $email, true);
    }
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT user_id, full_name, email, password, role
     FROM users
     WHERE LOWER(email) = ? AND role IN ('admin', 'staff')
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($password, $user['password'])) {
    LoginSecurity::recordFailedAttempt($conn, $email, $ip);
    $locked = LoginSecurity::isLocked($conn, $email, $ip);
    $showCaptcha = $locked || LoginSecurity::shouldShowCaptcha($conn, $email, $ip);
    redirectAdminLogin($locked ? 'locked' : 'invalid', $email, $showCaptcha);
}

LoginSecurity::clearAttempts($conn, $email, $ip);
session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['user_type'] = $user['role'];
$_SESSION['login_time'] = time();
$_SESSION['login_ip'] = $ip;
$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

$target = '../View/php/dashboard.php';
header("Location: {$target}");
exit();
?>
