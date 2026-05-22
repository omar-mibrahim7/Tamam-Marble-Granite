<?php
session_start();

if (!isset($_SESSION['otp_sent']) || !$_SESSION['otp_sent']) {
    header("Location: forget.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Code</title>
    <link rel="stylesheet" href="../css/verify.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">

    <!-- LEFT -->
    <div class="left">
        <img src="../pic/logo.png" class="logo">
        <h1>Need Help Accessing Your Account?</h1>
        <p>Enter your email address to receive a one-time verification code and reset your password.</p>
    </div>

    <!-- RIGHT -->
    <div class="right">

        <h2>Enter Verification Code</h2>
        <p class="sub">Please enter the 5-digit verification code sent to your email.</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php
                    if($_GET['error'] == 'expired') echo "Code expired. Please request a new one.";
                    elseif($_GET['error'] == 'invalid') echo "Invalid code. Please try again.";
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['resent'])): ?>
            <div class="success-msg">
                <i class="fa-solid fa-circle-check"></i>
                A new code has been sent to your email.
            </div>
        <?php endif; ?>

        <form method="POST" action="../../Controller/verify_otp.php" class="box">

            <div class="otp-inputs">
                <input type="text" name="d1" maxlength="1" oninput="move(this, 0)">
                <input type="text" name="d2" maxlength="1" oninput="move(this, 1)">
                <input type="text" name="d3" maxlength="1" oninput="move(this, 2)">
                <input type="text" name="d4" maxlength="1" oninput="move(this, 3)">
                <input type="text" name="d5" maxlength="1" oninput="move(this, 4)">
            </div>

            <button type="submit" name="verify">Continue</button>

        </form>

        <!-- Resend OTP -->
        <a href="../../Controller/resend_otp.php" class="resend">
            <i class="fa-solid fa-rotate-right"></i>
            Resend Code
        </a>

    </div>

</div>

<script src="../js/verify.js"></script>
</body>
</html>
