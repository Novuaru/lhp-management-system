<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'unread' => 0,
        'notifications' => []
    ]);
    exit();
}

try {
    // Get unread notifications count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM notifications 
        WHERE user_id = ? AND is_read = false
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $unread_count = $stmt->fetchColumn();
    
    // Get recent notifications
    $stmt = $pdo->prepare("
        SELECT n.*, d.title as document_title 
        FROM notifications n 
        JOIN lhp_documents d ON n.document_id = d.id 
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();
    
    // Format notifications for display
    $formatted_notifications = array_map(function($notif) {
        return [
            'id' => $notif['id'],
            'message' => $notif['message'],
            'document_title' => $notif['document_title'],
            'is_read' => (bool)$notif['is_read'],
            'created_at' => date('d/m/Y H:i', strtotime($notif['created_at']))
        ];
    }, $notifications);
    
    echo json_encode([
        'unread' => $unread_count,
        'notifications' => $formatted_notifications
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Terjadi kesalahan saat mengambil notifikasi.',
        'unread' => 0,
        'notifications' => []
    ]);
}
?>
