<?php
function admin_start_session(){
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function admin_normalize_session(){
    admin_start_session();

    if (!isset($_SESSION['user_type']) && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff'], true)) {
        $_SESSION['user_type'] = $_SESSION['role'];
    }

    if (!isset($_SESSION['role']) && isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['admin', 'staff'], true)) {
        $_SESSION['role'] = $_SESSION['user_type'];
    }
}

function admin_current_role(){
    admin_normalize_session();
    return $_SESSION['user_type'] ?? ($_SESSION['role'] ?? '');
}

function require_admin_role($allowedRoles = ['admin', 'staff'], $redirect = '../View/php/adminlogin.php'){
    admin_normalize_session();
    $role = admin_current_role();

    if (!isset($_SESSION['user_id']) || !in_array($role, $allowedRoles, true)) {
        header("Location: {$redirect}");
        exit;
    }

    return $role;
}

function admin_is_admin(){
    return admin_current_role() === 'admin';
}
?>
