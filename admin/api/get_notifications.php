<?php
if (!headers_sent()) {
    header_remove('X-Powered-By');
}
// admin/api/get_notifications.php
// API endpoint to fetch admin notifications
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'notifications' => [], 'unread_count' => 0]);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT 
            id,
            name,
            email,
            LEFT(message, 100) as message_preview,
            created_at,
            is_read
        FROM feedback_submissions 
        ORDER BY created_at DESC 
        LIMIT 20
    ");

    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $unreadCount = $pdo->query("SELECT COUNT(*) FROM feedback_submissions WHERE is_read = 0")->fetchColumn();

    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => (int) $unreadCount
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>