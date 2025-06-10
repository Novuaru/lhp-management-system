<?php
require_once '../includes/header.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'expiring_documents' => []
    ]);
    exit();
}

try {
    // Get documents that will expire in the next 30 days
    $stmt = $pdo->prepare("
        SELECT id, title, expiry_date,
               DATEDIFF(expiry_date, CURDATE()) as days_remaining
        FROM lhp_documents 
        WHERE created_by = ? 
        AND status = 'active'
        AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY expiry_date ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $expiring_documents = $stmt->fetchAll();
    
    // Create notifications for documents that are about to expire
    foreach ($expiring_documents as $doc) {
        // Check if we already sent a notification for this expiry
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM notifications 
            WHERE document_id = ? 
            AND created_at >= CURDATE()
            AND message LIKE ?
        ");
        $message_pattern = "%akan kadaluarsa dalam {$doc['days_remaining']} hari%";
        $stmt->execute([$doc['id'], $message_pattern]);
        $notification_exists = $stmt->fetchColumn() > 0;
        
        // If no notification was sent today for this document
        if (!$notification_exists) {
            // Create notification
            $message = "Dokumen '{$doc['title']}' akan kadaluarsa dalam {$doc['days_remaining']} hari";
            $stmt = $pdo->prepare("
                INSERT INTO notifications (document_id, user_id, message)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$doc['id'], $_SESSION['user_id'], $message]);
        }
    }
    
    // Format response
    $formatted_documents = array_map(function($doc) {
        return [
            'id' => $doc['id'],
            'title' => $doc['title'],
            'expiry_date' => $doc['expiry_date'],
            'days_remaining' => $doc['days_remaining']
        ];
    }, $expiring_documents);
    
    echo json_encode([
        'expiring_documents' => $formatted_documents
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Terjadi kesalahan saat memeriksa dokumen.',
        'expiring_documents' => []
    ]);
}

// Update expired documents
try {
    $stmt = $pdo->prepare("
        UPDATE lhp_documents 
        SET status = 'expired'
        WHERE expiry_date < CURDATE() 
        AND status = 'active'
        AND created_by = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Create notifications for newly expired documents
    $stmt = $pdo->prepare("
        SELECT id, title 
        FROM lhp_documents 
        WHERE created_by = ? 
        AND status = 'expired'
        AND expiry_date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $expired_documents = $stmt->fetchAll();
    
    foreach ($expired_documents as $doc) {
        $message = "Dokumen '{$doc['title']}' telah kadaluarsa";
        $stmt = $pdo->prepare("
            INSERT INTO notifications (document_id, user_id, message)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$doc['id'], $_SESSION['user_id'], $message]);
    }
    
} catch (PDOException $e) {
    // Log error but don't affect the response
    error_log("Error updating expired documents: " . $e->getMessage());
}
?>
