<?php
session_start();

// لازم يكون عمل verify OTP الأول
if(!isset($_SESSION['otp_verified'], $_SESSION['reset_user_id'])) {
    header("Location: forget.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/reset.css">
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

        <h2>Reset Your Password</h2>
        <p class="sub">Enter your new password below to reset your account password.</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php
                    if($_GET['error'] == 'mismatch') echo "Passwords do not match.";
                    elseif($_GET['error'] == 'short') echo "Password must be at least 8 characters.";
                    elseif($_GET['error'] == 'save') echo "Could not update your password. Please try again.";
                    else echo "Something went wrong. Please try again.";
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../Controller/reset_forget.php" class="box">

            <input type="hidden" name="source" value="forget">

            <div class="input">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="pass1" name="new_password" placeholder="New Password" required>
                <i class="fa-regular fa-eye toggle" onclick="toggle1()"></i>
            </div>

            <div class="input">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="pass2" name="confirm_password" placeholder="Confirm Password" required>
                <i class="fa-regular fa-eye toggle" onclick="toggle2()"></i>
            </div>

            <button type="submit" name="reset">
                <i class="fa-solid fa-rotate-right"></i> Reset Password
            </button>

        </form>

    </div>

</div>

<script src="../js/reset.js"></script>
</body>
</html>
