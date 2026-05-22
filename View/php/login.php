<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

require_once(__DIR__ . "/../../config/db.php");
require_once(__DIR__ . "/../../Model/LoginSecurity.php");

function h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$loginEmail = trim(strtolower($_GET['email'] ?? ''));
$clientIp = LoginSecurity::getClientIp();
$showCaptcha = filter_var($loginEmail, FILTER_VALIDATE_EMAIL)
    && LoginSecurity::shouldShowCaptcha($conn, $loginEmail, $clientIp);

if (isset($_GET['captcha']) && $_GET['captcha'] == '1' && filter_var($loginEmail, FILTER_VALIDATE_EMAIL)) {
    $showCaptcha = true;
}

$recaptchaConfigured = LoginSecurity::isRecaptchaConfigured();

$errorMessages = [
    'missing' => 'Please enter your email and password.',
    'invalid' => 'Invalid email or password. Please check your details and try again.',
    'captcha' => 'Please complete CAPTCHA verification.',
    'locked' => 'Too many failed attempts. Try again after 5 minutes.',
    'session' => 'Your session expired. Please sign in again.',
    '1' => 'Invalid email or password. Please check your details and try again.'
];

$successMessages = [
    '1' => 'Password updated successfully. You can sign in now.',
    'reset' => 'Password updated successfully. You can sign in now.',
    'login' => 'Login successful.'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
<link rel="stylesheet" href="../css/login.css?v=<?php echo filemtime('../css/login.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php if ($showCaptcha && $recaptchaConfigured): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
</head>
<body>

<div class="login-container">

    <!-- LEFT -->
    <div class="left-side">
        <img src="../pic/logo.png" class="logo">
        <h1>Welcome Back!</h1>
        <p>Sign in to continue browsing and managing your orders.</p>
    </div>

    <!-- RIGHT -->
    <div class="right-side">

        <h2>Sign In to Your Account</h2>

        <?php if($error): ?>
            <div class="form-message error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <strong>Sign in failed</strong>
                    <span><?php echo $errorMessages[$error] ?? 'Something went wrong. Please try again.'; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="form-message success">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <strong>Ready to sign in</strong>
                    <span><?php echo $successMessages[$success] ?? 'Operation completed successfully.'; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../Controller/customer_login.php" class="login-box">

            <!-- EMAIL -->
            <div class="input-box">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" value="<?php echo h($loginEmail); ?>" required>
            </div>

            <!-- PASSWORD -->
            <div class="input-box">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password" placeholder="Password" required>
                <i class="fa-regular fa-eye toggle" onclick="togglePassword()"></i>
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

            <button type="submit" name="login">Sign In</button>

        </form>

        <!-- FORGET -->
        <a href="forget.php" class="forgot">Forget Password?</a>

        <!-- REGISTER -->
        <a href="register.php" class="register-btn">
            <i class="fa-regular fa-user"></i>
            Register
        </a>

    </div>

</div>

<script src="../js/login.js"></script>
</body>
</html>
