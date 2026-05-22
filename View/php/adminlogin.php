<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminRole = $_SESSION['user_type'] ?? ($_SESSION['role'] ?? '');
if (isset($_SESSION['user_id']) && in_array($adminRole, ['admin', 'staff'], true)) {
$target = 'dashboard.php';
    header("Location: {$target}");
    exit();
}

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/LoginSecurity.php");

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = $_GET['error'] ?? '';
$loginEmail = trim(strtolower($_GET['email'] ?? ''));
$clientIp = LoginSecurity::getClientIp();
$showCaptcha = filter_var($loginEmail, FILTER_VALIDATE_EMAIL)
    && LoginSecurity::shouldShowCaptcha($conn, $loginEmail, $clientIp);

if (isset($_GET['captcha']) && $_GET['captcha'] == '1' && filter_var($loginEmail, FILTER_VALIDATE_EMAIL)) {
    $showCaptcha = true;
}

$recaptchaConfigured = LoginSecurity::isRecaptchaConfigured();
$errorMessages = [
    'invalid' => 'Invalid email or password.',
    'captcha' => 'Please complete CAPTCHA verification.',
    'locked' => 'Too many failed attempts. Try again after 5 minutes.',
    '1' => 'Invalid email or password.'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Admin Login</title>

<link rel="stylesheet" href="../css/adminlogin.css?v=<?php echo filemtime('../css/adminlogin.css'); ?>">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <?php if ($showCaptcha && $recaptchaConfigured): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>

</head>

<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">

        <img src="../pic/logo.png" class="logo">

        <h1>Welcome Back!</h1>

        <p>
            Sign in to access the admin dashboard
            and manage the system.
        </p>

    </div>


    <!-- RIGHT -->
    <div class="right">

        <h2>Admin Login</h2>
        <div class="box">

        <?php if($error): ?>

            <div class="error-msg">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?php echo h($errorMessages[$error] ?? 'Invalid email or password.'); ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->
<form method="POST" action="../../Controller/login_admin.php">
                <!-- EMAIL -->
            <div class="input">

                <i class="fa-regular fa-envelope"></i>

                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    value="<?php echo h($loginEmail); ?>"
                    required
                >

            </div>


            <!-- PASSWORD -->
            <div class="input">

                <i class="fa-solid fa-lock"></i>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Password"
                    required
                >

                <i
                    class="fa-regular fa-eye toggle"
                    onclick="togglePassword()"
                ></i>

            </div>

            <?php if ($showCaptcha): ?>
                <?php if ($recaptchaConfigured): ?>
                    <div class="captcha-box">
                        <div class="g-recaptcha" data-sitekey="<?php echo h(RECAPTCHA_SITE_KEY); ?>"></div>
                    </div>
                <?php else: ?>
                    <div class="captcha-note">
                        CAPTCHA would appear here after repeated failed attempts. Add real keys in config/security.php before production.
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- BUTTON -->
            <button type="submit" name="login">

                Sign In

            </button>

        </form>

    </div>

</div>

<script src="../js/adminlogin.js?v=<?php echo filemtime('../js/adminlogin.js'); ?>"></script>
</body>
</html>
