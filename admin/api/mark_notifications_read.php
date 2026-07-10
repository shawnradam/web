<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
// admin/api/mark_notifications_read.php
// API endpoint to mark notifications as read
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

try {
    if ($action === 'mark_all_read') {
        $stmt = $pdo->prepare("UPDATE feedback_submissions SET is_read = 1 WHERE is_read = 0");
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
    } elseif ($action === 'clear_all') {
        $stmt = $pdo->prepare("DELETE FROM feedback_submissions WHERE is_read = 1");
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Read notifications cleared']);
    } elseif ($action === 'mark_single_read' && isset($data['id'])) {
        $stmt = $pdo->prepare("UPDATE feedback_submissions SET is_read = 1 WHERE id = ?");
        $stmt->execute([$data['id']]);

        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>