<?php
session_start();

$wasAdmin = isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['admin', 'staff'], true);
if (!$wasAdmin && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff'], true)) {
    $wasAdmin = true;
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
header("Location: " . ($wasAdmin ? "../View/php/adminlogin.php" : "../View/php/login.php"));
exit;
?>
