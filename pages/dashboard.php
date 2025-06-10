<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Get active documents count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM lhp_documents WHERE status = 'active' AND created_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$active_docs = $stmt->fetchColumn();

// Get expired documents count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM lhp_documents WHERE status = 'expired' AND created_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$expired_docs = $stmt->fetchColumn();

// Get documents expiring soon (within 30 days)
$stmt = $pdo->prepare("
    SELECT * FROM lhp_documents 
    WHERE created_by = ? 
    AND status = 'active' 
    AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY expiry_date ASC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$expiring_soon = $stmt->fetchAll();

// Get recent notifications
$stmt = $pdo->prepare("
    SELECT n.*, d.title as document_title 
    FROM notifications n 
    JOIN lhp_documents d ON n.document_id = d.id 
    WHERE n.user_id = ? 
    ORDER BY n.created_at DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_notifications = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card dashboard-card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Dokumen Aktif</h5>
                <h2 class="mb-0"><?php echo $active_docs; ?></h2>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card dashboard-card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Dokumen Kadaluarsa</h5>
                <h2 class="mb-0"><?php echo $expired_docs; ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">Dokumen Akan Kadaluarsa</h5>
            </div>
            <div class="card-body">
                <?php if ($expiring_soon): ?>
                    <div class="list-group">
                        <?php foreach ($expiring_soon as $doc): ?>
                            <a href="/pages/lhp-view.php?id=<?php echo $doc['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($doc['title']); ?></h6>
                                    <small class="text-danger">
                                        Kadaluarsa: <?php echo date('d/m/Y', strtotime($doc['expiry_date'])); ?>
                                    </small>
                                </div>
                                <small class="text-muted">No. Dokumen: <?php echo htmlspecialchars($doc['document_number']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center mb-0">Tidak ada dokumen yang akan kadaluarsa dalam waktu dekat.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0">Notifikasi Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if ($recent_notifications): ?>
                    <div class="list-group">
                        <?php foreach ($recent_notifications as $notif): ?>
                            <div class="list-group-item <?php echo !$notif['is_read'] ? 'list-group-item-light' : ''; ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($notif['document_title']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-1"><?php echo htmlspecialchars($notif['message']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center mb-0">Tidak ada notifikasi baru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 text-center">
        <a href="/pages/lhp-create.php" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i> Tambah Dokumen LHP Baru
        </a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
