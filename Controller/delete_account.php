<?php
// ============================================================
// Controller/delete_account.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../View/php/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/account-management.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$currentPassword = $_POST['current_password'] ?? '';

if ($currentPassword === '') {
    header("Location: ../View/php/account-management.php?error=delete_password");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE user_id = ? AND role = 'customer' LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user || !password_verify($currentPassword, $user['password'])) {
    header("Location: ../View/php/account-management.php?error=delete_password");
    exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ? AND role = 'customer'");
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$deleted = mysqli_stmt_affected_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$deleted) {
    header("Location: ../View/php/account-management.php?error=delete_failed");
    exit;
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();
header("Location: ../View/php/register.php?deleted=1");
exit;
?>
