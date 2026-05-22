<?php
// ============================================================
// Controller/register.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../config/db.php");

if (!isset($_POST['register']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/register.php");
    exit();
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim(strtolower($_POST['email'] ?? ''));
$password = trim($_POST['password'] ?? '');
$confirm = trim($_POST['confirm'] ?? '');
$type = trim($_POST['type'] ?? '');

if ($name === '' || $phone === '' || $email === '' || $password === '' || $confirm === '') {
    header("Location: ../View/php/register.php?error=missing");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../View/php/register.php?error=email");
    exit();
}

if ($password !== $confirm) {
    header("Location: ../View/php/register.php?error=password");
    exit();
}

if (strlen($password) < 8) {
    header("Location: ../View/php/register.php?error=short");
    exit();
}

if (!in_array($type, ['customer', 'company'], true)) {
    header("Location: ../View/php/register.php?error=type");
    exit();
}

$checkEmail = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
mysqli_stmt_bind_param($checkEmail, "s", $email);
mysqli_stmt_execute($checkEmail);
mysqli_stmt_store_result($checkEmail);

if (mysqli_stmt_num_rows($checkEmail) > 0) {
    mysqli_stmt_close($checkEmail);
    header("Location: ../View/php/register.php?error=exists");
    exit();
}

mysqli_stmt_close($checkEmail);

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (full_name, email, password, phone, role, account_type) VALUES (?, ?, ?, ?, 'customer', ?)"
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashedPassword, $phone, $type);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    $userId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $stmtCust = mysqli_prepare($conn, "INSERT INTO customers (customer_id) VALUES (?)");
    if (!$stmtCust) {
        throw new Exception(mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmtCust, "i", $userId);
    if (!mysqli_stmt_execute($stmtCust)) {
        throw new Exception(mysqli_stmt_error($stmtCust));
    }
    mysqli_stmt_close($stmtCust);

    $stmtCart = mysqli_prepare($conn, "INSERT INTO carts (customer_id) VALUES (?)");
    if (!$stmtCart) {
        throw new Exception(mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmtCart, "i", $userId);
    if (!mysqli_stmt_execute($stmtCart)) {
        throw new Exception(mysqli_stmt_error($stmtCart));
    }
    mysqli_stmt_close($stmtCart);

    mysqli_commit($conn);


session_unset();
session_destroy();

    header("Location: ../View/php/login.php?registered=1");
    exit();
} catch (Throwable $e) {
    mysqli_rollback($conn);
    header("Location: ../View/php/register.php?error=save");
    exit();
}
?>
