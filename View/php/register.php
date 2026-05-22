<?php
session_start();

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$deleted = isset($_GET['deleted']);

$errorMessages = [
    'missing' => 'Please fill in all required fields.',
    'email' => 'Please enter a valid email address.',
    'password' => 'Passwords do not match. Please re-enter them carefully.',
    'short' => 'Password must be at least 8 characters.',
    'exists' => 'This email is already registered. Try signing in instead.',
    'type' => 'Please choose an account type.',
    'save' => 'We could not create your account right now. Please try again.'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
<link rel="stylesheet" href="../css/register.css?v=<?php echo filemtime('../css/register.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">

    <div class="left">
        <img src="../pic/logo.png" class="logo">
        <h1>Welcome!</h1>
        <p>Create your account to start exploring premium marble and granite products.</p>
    </div>

    <div class="right">

        <h2>Create Your Account</h2>

        <?php if($error): ?>
            <div class="form-message error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <strong>Account was not created</strong>
                    <span><?php echo $errorMessages[$error] ?? 'Something went wrong. Please try again.'; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="form-message success">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <strong>Account created</strong>
                    <span>Your account has been created successfully. You can sign in now.</span>
                </div>
            </div>
        <?php endif; ?>

        <?php if($deleted): ?>
            <div class="form-message success">
                <i class="fa-solid fa-circle-check"></i>
                <div>
                    <strong>Account deleted</strong>
                    <span>Your account has been deleted successfully.</span>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../Controller/register.php" class="box">
            <div class="input">
                <i class="fa-regular fa-user"></i>
                <input type="text" name="name" placeholder="Full Name Or Name's Company" required>
            </div>

            <div class="input">
                <i class="fa-solid fa-phone"></i>
                <input type="text" name="phone" placeholder="Phone Number" required>
            </div>

            <div class="input">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="pass1" name="password" placeholder="Password" required>
                <i class="fa-regular fa-eye toggle" onclick="toggle1()"></i>
            </div>

            <div class="input">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="pass2" name="confirm" placeholder="Confirm Password" required>
                <i class="fa-regular fa-eye toggle" onclick="toggle2()"></i>
            </div>

            <div class="input">
                <i class="fa-regular fa-user"></i>
                <select name="type" required>
                    <option value="">Account Type</option>
                    <option value="customer">Customer</option>
                    <option value="company">Company</option>
                </select>
            </div>

            <button type="submit" name="register">Sign Up</button>
        </form>

        <a href="login.php" class="login-btn">
            <span class="icon">
                <i class="fa-solid fa-arrow-right"></i>
            </span>
            Login
        </a>

    </div>

</div>

<script src="../js/register.js"></script>
</body>
</html>
