<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $document_number = trim($_POST['document_number']);
    $description = trim($_POST['description']);
    $validity_period = (int)$_POST['validity_period'];
    $start_date = $_POST['start_date'];
    
    $errors = [];
    
    // Validation
    if (empty($title)) {
        $errors[] = "Judul dokumen wajib diisi";
    }
    
    if (empty($document_number)) {
        $errors[] = "Nomor dokumen wajib diisi";
    }
    
    if ($validity_period <= 0) {
        $errors[] = "Masa berlaku harus lebih dari 0 hari";
    }
    
    if (empty($start_date)) {
        $errors[] = "Tanggal mulai wajib diisi";
    }
    
    // File upload handling
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['document_file'];
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Tipe file tidak didukung. Hanya PDF dan DOC/DOCX yang diperbolehkan.";
        }
        
        if ($file['size'] > $max_size) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 5MB.";
        }
    } else {
        $errors[] = "File dokumen wajib diunggah";
    }
    
    if (empty($errors)) {
        try {
            // Calculate expiry date
            $expiry_date = date('Y-m-d', strtotime($start_date . ' + ' . $validity_period . ' days'));
            
            // Upload file
            $upload_dir = '../uploads/documents/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                // Insert document record
                $stmt = $pdo->prepare("
                    INSERT INTO lhp_documents (
                        title, document_number, description, file_path,
                        validity_period, start_date, expiry_date,
                        created_by, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
                ");
                
                $stmt->execute([
                    $title,
                    $document_number,
                    $description,
                    $file_path,
                    $validity_period,
                    $start_date,
                    $expiry_date,
                    $_SESSION['user_id']
                ]);
                
                $document_id = $pdo->lastInsertId();
                
                // Create notification for document creation
                $notification_message = "Dokumen baru telah dibuat: " . $title;
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (document_id, user_id, message)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$document_id, $_SESSION['user_id'], $notification_message]);
                
                $_SESSION['flash_message'] = "Dokumen LHP berhasil ditambahkan!";
                $_SESSION['flash_type'] = "success";
                
                header("Location: /pages/lhp-list.php");
                exit();
            } else {
                $errors[] = "Gagal mengunggah file. Silakan coba lagi.";
            }
        } catch (PDOException $e) {
            $errors[] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Tambah Dokumen LHP Baru</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Judul Dokumen *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="document_number" class="form-label">Nomor Dokumen *</label>
                        <input type="text" class="form-control" id="document_number" name="document_number" 
                               value="<?php echo isset($_POST['document_number']) ? htmlspecialchars($_POST['document_number']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Tanggal Mulai *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d'); ?>" 
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="validity_period" class="form-label">Masa Berlaku (hari) *</label>
                            <input type="number" class="form-control" id="validity_period" name="validity_period" 
                                   value="<?php echo isset($_POST['validity_period']) ? $_POST['validity_period'] : '365'; ?>" 
                                   min="1" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="document_file" class="form-label">File Dokumen (PDF/DOC/DOCX, max 5MB) *</label>
                        <input type="file" class="form-control" id="document_file" name="document_file" 
                               accept=".pdf,.doc,.docx" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Dokumen</button>
                        <a href="/pages/lhp-list.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
