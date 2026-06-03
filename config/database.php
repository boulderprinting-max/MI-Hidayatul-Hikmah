<?php
// =====================================================
// KONFIGURASI DATABASE
// MI Hidayatul Hikmah - E-Learning
// =====================================================

$db_host = 'localhost';
$db_name = 'mi_hidayatul_hikmah';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="text-align:center;padding:50px;font-family:Poppins,sans-serif;">
        <h2>❌ Koneksi Database Gagal</h2>
        <p>Pastikan MySQL sudah berjalan dan database <strong>mi_hidayatul_hikmah</strong> sudah dibuat.</p>
        <p style="color:#dc3545;font-size:14px;">' . $e->getMessage() . '</p>
    </div>');
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL (sesuaikan dengan hosting)
$base_url = '/';

// Upload paths
define('UPLOAD_MATERI', __DIR__ . '/../uploads/materi/');
define('UPLOAD_TUGAS', __DIR__ . '/../uploads/tugas/');
define('UPLOAD_JAWABAN', __DIR__ . '/../uploads/jawaban/');
define('UPLOAD_FOTO', __DIR__ . '/../uploads/foto/');
