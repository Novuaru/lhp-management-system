<?php
require_once 'includes/header.php';

// If user is logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: /pages/dashboard.php");
    exit();
}
?>

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-75">
        <div class="col-md-8 text-center">
            <h1 class="display-4 mb-4">Sistem Manajemen LHP</h1>
            <p class="lead mb-4">
                Selamat datang di Sistem Manajemen Laporan Hasil Pengawasan (LHP).
                Kelola dokumen LHP Anda dengan mudah dan dapatkan notifikasi untuk dokumen yang akan kadaluarsa.
            </p>
            
            <div class="row justify-content-center mb-5">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Manajemen Dokumen</h5>
                            <p class="card-text">Kelola semua dokumen LHP dalam satu sistem</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <i class="fas fa-bell fa-3x text-warning mb-3"></i>
                            <h5 class="card-title">Notifikasi</h5>
                            <p class="card-text">Dapatkan pengingat untuk dokumen yang akan kadaluarsa</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <i class="fas fa-search fa-3x text-success mb-3"></i>
                            <h5 class="card-title">Pencarian Mudah</h5>
                            <p class="card-text">Temukan dokumen dengan cepat dan mudah</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-block">
                <a href="/auth/login.php" class="btn btn-primary btn-lg me-md-2">Login</a>
                <a href="/auth/register.php" class="btn btn-outline-primary btn-lg">Register</a>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <h2 class="mb-4">Fitur Utama</h2>
            <ul class="list-unstyled">
                <li class="mb-3">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Upload dan kelola dokumen LHP
                </li>
                <li class="mb-3">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Notifikasi otomatis untuk dokumen yang akan kadaluarsa
                </li>
                <li class="mb-3">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Pencarian dan filter dokumen
                </li>
                <li class="mb-3">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Riwayat lengkap dokumen
                </li>
                <li class="mb-3">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Dashboard informatif
                </li>
            </ul>
        </div>
        <div class="col-md-6">
            <h2 class="mb-4">Cara Penggunaan</h2>
            <div class="accordion" id="howToUse">
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                            1. Daftar atau Login
                        </button>
                    </h3>
                    <div id="step1" class="accordion-collapse collapse show" data-bs-parent="#howToUse">
                        <div class="accordion-body">
                            Buat akun baru atau login jika sudah memiliki akun.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                            2. Upload Dokumen
                        </button>
                    </h3>
                    <div id="step2" class="accordion-collapse collapse" data-bs-parent="#howToUse">
                        <div class="accordion-body">
                            Upload dokumen LHP dengan mengisi informasi yang diperlukan.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                            3. Kelola Dokumen
                        </button>
                    </h3>
                    <div id="step3" class="accordion-collapse collapse" data-bs-parent="#howToUse">
                        <div class="accordion-body">
                            Lihat, edit, atau hapus dokumen yang telah diupload.
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                            4. Terima Notifikasi
                        </button>
                    </h3>
                    <div id="step4" class="accordion-collapse collapse" data-bs-parent="#howToUse">
                        <div class="accordion-body">
                            Dapatkan notifikasi otomatis untuk dokumen yang akan kadaluarsa.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
