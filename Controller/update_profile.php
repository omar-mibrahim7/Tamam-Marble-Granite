<?php
// ============================================================
// Controller/update_profile.php
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
    header("Location: ../View/php/profile.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$fullName = trim($_POST['full_name'] ?? '');
$email = trim(strtolower($_POST['email'] ?? ''));
$phone = trim($_POST['phone'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$city = trim($_POST['city'] ?? '');
$area = trim($_POST['area'] ?? '');
$address = trim($city . (($city !== '' && $area !== '') ? ', ' : '') . $area);

if ($fullName === '' || $email === '' || $phone === '') {
    header("Location: ../View/php/profile.php?error=missing");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../View/php/profile.php?error=email");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "si", $email, $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$emailExists = mysqli_fetch_assoc($result) ? true : false;
mysqli_stmt_close($stmt);

if ($emailExists) {
    header("Location: ../View/php/profile.php?error=exists");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET full_name = ?, email = ?, phone = ?, whatsapp = ?, address = ?, city = ?, area = ?
     WHERE user_id = ? AND role = 'customer'"
);
mysqli_stmt_bind_param($stmt, "sssssssi", $fullName, $email, $phone, $whatsapp, $address, $city, $area, $userId);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$success) {
    header("Location: ../View/php/profile.php?error=save");
    exit;
}

$_SESSION['full_name'] = $fullName;
$_SESSION['email'] = $email;
$_SESSION['phone'] = $phone;
$_SESSION['whatsapp'] = $whatsapp;
$_SESSION['address'] = $address;
$_SESSION['city'] = $city;
$_SESSION['area'] = $area;

header("Location: ../View/php/profile.php?saved=1");
exit;
?>
