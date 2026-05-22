<?php
header('Content-Type: application/json; charset=utf-8');

require_once(__DIR__ . "/admin_auth.php");
admin_normalize_session();
require_once(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_id']) || !in_array(admin_current_role(), ['admin', 'staff'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$messageId = (int)($payload['id'] ?? $payload['message_id'] ?? 0);
$status = trim((string)($payload['status'] ?? ''));
$allowedStatuses = ['unread', 'pending', 'confirmed'];

if ($messageId <= 0 || !in_array($status, $allowedStatuses, true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid message or status']);
    exit;
}

$stmt = mysqli_prepare($conn, "UPDATE contact_messages SET status = ? WHERE message_id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $messageId);
$success = mysqli_stmt_execute($stmt);
$affectedRows = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if (!$success) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Message status could not be updated']);
    exit;
}

if ($affectedRows < 1) {
    $checkStmt = mysqli_prepare($conn, "SELECT message_id FROM contact_messages WHERE message_id = ? LIMIT 1");
    mysqli_stmt_bind_param($checkStmt, "i", $messageId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $messageExists = mysqli_fetch_assoc($checkResult) !== null;
    mysqli_stmt_close($checkStmt);

    if (!$messageExists) {
        echo json_encode(['success' => false, 'message' => 'Message was not found']);
        exit;
    }
}

echo json_encode(['success' => true]);
exit;
?>
