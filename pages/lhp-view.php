<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Check if document ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['flash_message'] = "ID dokumen tidak valid.";
    $_SESSION['flash_type'] = "danger";
    header("Location: /pages/lhp-list.php");
    exit();
}

try {
    // Get document details
    $stmt = $pdo->prepare("
        SELECT * FROM lhp_documents 
        WHERE id = ? AND created_by = ?
    ");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $document = $stmt->fetch();
    
    if (!$document) {
        $_SESSION['flash_message'] = "Dokumen tidak ditemukan.";
        $_SESSION['flash_type'] = "danger";
        header("Location: /pages/lhp-list.php");
        exit();
    }
    
    // Mark related notifications as read
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = true 
        WHERE document_id = ? AND user_id = ?
    ");
    $stmt->execute([$document['id'], $_SESSION['user_id']]);
    
} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Terjadi kesalahan saat mengambil data.";
    $_SESSION['flash_type'] = "danger";
    header("Location: /pages/lhp-list.php");
    exit();
}

// Calculate remaining days
$days_until_expiry = (strtotime($document['expiry_date']) - time()) / (60 * 60 * 24);
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Detail Dokumen LHP</h4>
                <div>
                    <a href="/pages/lhp-edit.php?id=<?php echo $document['id']; ?>" class="btn btn-light btn-sm me-2">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="/pages/lhp-list.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-primary">Status Dokumen</h5>
                        <?php if ($document['status'] === 'active'): ?>
                            <span class="badge bg-success fs-5">Aktif</span>
                            <?php if ($days_until_expiry > 0 && $days_until_expiry <= 30): ?>
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Dokumen akan kadaluarsa dalam <?php echo floor($days_until_expiry); ?> hari
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge bg-danger fs-5">Kadaluarsa</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="<?php echo htmlspecialchars($document['file_path']); ?>" 
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-download"></i> Download Dokumen
                        </a>
                    </div>
                </div>

                <div class="document-details">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Judul Dokumen</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($document['title']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nomor Dokumen</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($document['document_number']); ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Deskripsi</div>
                        <div class="col-md-8">
                            <?php echo nl2br(htmlspecialchars($document['description'])); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Mulai</div>
                        <div class="col-md-8">
                            <?php echo date('d/m/Y', strtotime($document['start_date'])); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Kadaluarsa</div>
                        <div class="col-md-8">
                            <?php echo date('d/m/Y', strtotime($document['expiry_date'])); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Masa Berlaku</div>
                        <div class="col-md-8">
                            <?php echo $document['validity_period']; ?> hari
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Tanggal Dibuat</div>
                        <div class="col-md-8">
                            <?php echo date('d/m/Y H:i', strtotime($document['created_at'])); ?>
                        </div>
                    </div>

                    <?php if ($document['updated_at']): ?>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Terakhir Diperbarui</div>
                        <div class="col-md-8">
                            <?php echo date('d/m/Y H:i', strtotime($document['updated_at'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="document-preview mt-4">
                    <h5 class="text-primary mb-3">Preview Dokumen</h5>
                    <?php
                    $file_extension = strtolower(pathinfo($document['file_path'], PATHINFO_EXTENSION));
                    if ($file_extension === 'pdf'): ?>
                        <div class="ratio ratio-16x9">
                            <iframe src="<?php echo htmlspecialchars($document['file_path']); ?>" 
                                    title="Document Preview"></iframe>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Preview tidak tersedia untuk dokumen format <?php echo strtoupper($file_extension); ?>.
                            Silakan download dokumen untuk melihat isinya.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
