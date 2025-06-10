<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/login.php");
    exit();
}

// Handle search and filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Prepare base query
$query = "SELECT * FROM lhp_documents WHERE created_by = ?";
$params = [$_SESSION['user_id']];

// Add search condition
if (!empty($search)) {
    $query .= " AND (title LIKE ? OR document_number LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

// Add status filter
if ($status !== 'all') {
    $query .= " AND status = ?";
    $params[] = $status;
}

// Add sorting
switch ($sort) {
    case 'oldest':
        $query .= " ORDER BY created_at ASC";
        break;
    case 'title':
        $query .= " ORDER BY title ASC";
        break;
    case 'expiry':
        $query .= " ORDER BY expiry_date ASC";
        break;
    default: // newest
        $query .= " ORDER BY created_at DESC";
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['flash_message'] = "Terjadi kesalahan saat mengambil data.";
    $_SESSION['flash_type'] = "danger";
    $documents = [];
}
?>

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Daftar Dokumen LHP</h4>
        <a href="/pages/lhp-create.php" class="btn btn-light">Tambah Dokumen</a>
    </div>
    <div class="card-body">
        <!-- Search and Filter Form -->
        <form method="GET" action="" class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Cari dokumen..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-secondary" type="submit">Cari</button>
                </div>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="expired" <?php echo $status === 'expired' ? 'selected' : ''; ?>>Kadaluarsa</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <select class="form-select" name="sort" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                    <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                    <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Judul</option>
                    <option value="expiry" <?php echo $sort === 'expiry' ? 'selected' : ''; ?>>Tanggal Kadaluarsa</option>
                </select>
            </div>
        </form>

        <?php if (empty($documents)): ?>
            <div class="text-center py-5">
                <h5>Tidak ada dokumen ditemukan</h5>
                <p class="text-muted">Mulai dengan menambahkan dokumen baru</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Nomor Dokumen</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Kadaluarsa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doc['title']); ?></td>
                                <td><?php echo htmlspecialchars($doc['document_number']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($doc['start_date'])); ?></td>
                                <td>
                                    <?php 
                                    echo date('d/m/Y', strtotime($doc['expiry_date']));
                                    $days_until_expiry = (strtotime($doc['expiry_date']) - time()) / (60 * 60 * 24);
                                    if ($days_until_expiry > 0 && $days_until_expiry <= 30) {
                                        echo ' <span class="badge bg-warning">Akan kadaluarsa dalam ' . floor($days_until_expiry) . ' hari</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($doc['status'] === 'active'): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Kadaluarsa</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/pages/lhp-view.php?id=<?php echo $doc['id']; ?>" 
                                           class="btn btn-sm btn-info text-white" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/pages/lhp-edit.php?id=<?php echo $doc['id']; ?>" 
                                           class="btn btn-sm btn-warning text-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Hapus"
                                                onclick="confirmDelete(<?php echo $doc['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus dokumen ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" action="/pages/lhp-delete.php" style="display: inline;">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    document.getElementById('deleteId').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php require_once '../includes/footer.php'; ?>
