<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Check if document ID is provided
if (!isset($_POST['id'])) {
    $_SESSION['flash_message'] = "ID dokumen tidak valid.";
    $_SESSION['flash_type'] = "danger";
    header("Location: /pages/lhp-list.php");
    exit();
}

try {
    // Get document details first (to get file path)
    $stmt = $pdo->prepare("
        SELECT * FROM lhp_documents 
        WHERE id = ? AND created_by = ?
    ");
    $stmt->execute([$_POST['id'], $_SESSION['user_id']]);
    $document = $stmt->fetch();
    
    if (!$document) {
        $_SESSION['flash_message'] = "Dokumen tidak ditemukan.";
        $_SESSION['flash_type'] = "danger";
        header("Location: /pages/lhp-list.php");
        exit();
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Delete related notifications first
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE document_id = ?");
    $stmt->execute([$document['id']]);
    
    // Delete document record
    $stmt = $pdo->prepare("DELETE FROM lhp_documents WHERE id = ? AND created_by = ?");
    $stmt->execute([$document['id'], $_SESSION['user_id']]);
    
    // Delete physical file if exists
    if (file_exists($document['file_path'])) {
        unlink($document['file_path']);
    }
    
    // Commit transaction
    $pdo->commit();
    
    $_SESSION['flash_message'] = "Dokumen berhasil dihapus.";
    $_SESSION['flash_type'] = "success";
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    
    $_SESSION['flash_message'] = "Terjadi kesalahan saat menghapus dokumen.";
    $_SESSION['flash_type'] = "danger";
}

header("Location: /pages/lhp-list.php");
exit();
?>
