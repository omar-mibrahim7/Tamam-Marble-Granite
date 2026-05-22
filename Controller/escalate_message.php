<?php
require_once(__DIR__ . "/admin_auth.php");
require_once(__DIR__ . "/../config/db.php");

require_admin_role(['staff'], "../View/php/adminlogin.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/messages.php");
    exit;
}

$messageId = (int)($_POST['message_id'] ?? 0);
$reason = trim((string)($_POST['escalation_reason'] ?? ''));

if ($messageId <= 0) {
    header("Location: ../View/php/messages.php?error=invalid_message");
    exit;
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE contact_messages
     SET is_escalated = 1,
         escalation_reason = ?,
         escalated_at = NOW()
     WHERE message_id = ?"
);
mysqli_stmt_bind_param($stmt, "si", $reason, $messageId);
$success = mysqli_stmt_execute($stmt);
$affectedRows = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if (!$success || $affectedRows < 1) {
    header("Location: ../View/php/messages.php?error=escalation_failed");
    exit;
}

header("Location: ../View/php/messages.php?escalated=1");
exit;
?>
