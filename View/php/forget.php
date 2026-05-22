<?php
session_start();

$error = $_GET['error'] ?? '';

$errorMessages = [
    'empty' => 'Please enter your email address.',
    'email' => 'Please enter a valid email address.',
    'notfound' => 'We could not find a customer account with this email.',
    'send' => 'We could not send the verification code. Please try again.'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forget Password</title>
<link rel="stylesheet" href="../css/forget.css?v=<?php echo filemtime('../css/forget.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <img src="../pic/logo.png" class="logo">
        <h1>Need Help Accessing Your Account?</h1>
        <p>Enter your email to receive a one-time code and reset your password.</p>
    </div>

    <!-- RIGHT -->
    <div class="right">

        <h2>Forget Password</h2>
        <p class="desc">
            Enter your email address and we'll send you a OTP to reset your password.
        </p>

        <?php if($error): ?>
            <div class="form-message error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <strong>Reset request failed</strong>
                    <span><?php echo $errorMessages[$error] ?? 'Something went wrong. Please try again.'; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../Controller/forget_password.php" class="box">

            <div class="input">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <button type="submit" name="reset" id="btn">
                Continue
            </button>

        </form>

    </div>

</div>

<script src="../js/forget.js"></script>
</body>
</html>
