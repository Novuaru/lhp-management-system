# Sistem Manajemen Laporan Hasil Pengawasan (LHP)

Aplikasi berbasis web untuk mengelola Laporan Hasil Pengawasan (LHP) dengan fitur notifikasi untuk dokumen yang akan kadaluarsa.

## Fitur Utama

- Manajemen dokumen LHP (Create, Read, Update, Delete)
- Notifikasi otomatis untuk dokumen yang akan kadaluarsa
- Sistem autentikasi pengguna
- Dashboard informatif
- Pencarian dan filter dokumen
- Riwayat lengkap dokumen
- Responsive design

## Teknologi yang Digunakan

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Service Workers untuk Push Notifications
- XAMPP sebagai development server

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- XAMPP (atau server web yang setara)
- Browser modern yang mendukung Service Workers

## Instalasi

1. Clone repository ini ke direktori web server Anda:
```bash
git clone [repository-url]
cd lhp-management
```

2. Import database menggunakan file `config/init.sql`:
```bash
mysql -u root -p < config/init.sql
```

3. Konfigurasi koneksi database di `config/database.php`:
```php
$host = 'localhost';
$dbname = 'lhp_management';
$username = 'root';
$password = '';
```

4. Buat direktori untuk upload dokumen dan atur permissions:
```bash
mkdir uploads/documents
chmod 777 uploads/documents
```

5. Pastikan web server dan MySQL berjalan di XAMPP
6. Akses aplikasi melalui browser: `http://localhost/lhp-management`

## Struktur Direktori

```
lhp-management/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/
│   ├── database.php
│   └── init.sql
├── includes/
│   ├── header.php
│   └── footer.php
├── notifications/
│   ├── check.php
│   ├── check-expiry.php
│   └── service-worker.js
├── pages/
│   ├── dashboard.php
│   ├── lhp-list.php
│   ├── lhp-create.php
│   ├── lhp-edit.php
│   ├── lhp-view.php
│   └── lhp-delete.php
├── uploads/
│   └── documents/
└── index.php
```

## Penggunaan

1. Register akun baru atau login jika sudah memiliki akun
2. Upload dokumen LHP dengan mengisi informasi yang diperlukan
3. Kelola dokumen melalui dashboard
4. Terima notifikasi otomatis untuk dokumen yang akan kadaluarsa

## Fitur Notifikasi

- Notifikasi otomatis untuk dokumen yang akan kadaluarsa
- Notifikasi real-time menggunakan Service Workers
- Pengaturan masa berlaku dokumen
- Riwayat notifikasi

## Keamanan

- Password hashing
- Validasi input
- Proteksi terhadap SQL injection
- Session management
- File upload validation

## Pemeliharaan

- Backup database secara berkala
- Monitor log sistem
- Update dependencies
- Periksa keamanan sistem

## Kontribusi

1. Fork repository
2. Buat branch fitur baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

[MIT License](LICENSE)

## Kontak

Untuk pertanyaan dan dukungan, silakan hubungi:
[Your Contact Information]
