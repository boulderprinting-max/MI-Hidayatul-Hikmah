<?php
// =====================================================
// AUTH - Cek Session & Hak Akses
// MI Hidayatul Hikmah - E-Learning
// =====================================================

session_start();

// Include database & functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Cek apakah user sudah login, jika belum redirect ke login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('danger', 'Silakan login terlebih dahulu');
        redirect('/index.php');
    }
}

/**
 * Cek hak akses berdasarkan role
 * @param array|string $allowedRoles - Role yang diizinkan
 */
function requireRole($allowedRoles) {
    requireLogin();
    
    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    
    // Super admin selalu punya akses
    if ($_SESSION['role'] === 'super_admin') {
        return true;
    }
    
    // Cek apakah 'guru' ada di allowed roles (untuk semua guru kelas)
    if (in_array('guru', $allowedRoles) && isGuru()) {
        return true;
    }
    
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        setFlash('danger', 'Anda tidak memiliki akses ke halaman ini');
        redirect('/index.php');
    }
    
    return true;
}

/**
 * Ambil data lengkap guru berdasarkan user_id
 */
function getGuruByUserId($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT * FROM guru WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Ambil data lengkap siswa berdasarkan user_id
 */
function getSiswaByUserId($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Ambil kelas_id berdasarkan role guru
 */
function getKelasIdGuru($pdo) {
    $kelasNum = getKelasGuru();
    if ($kelasNum > 0) {
        $stmt = $pdo->prepare("SELECT id FROM kelas WHERE tingkat = ?");
        $stmt->execute([$kelasNum]);
        $kelas = $stmt->fetch();
        return $kelas ? $kelas['id'] : 0;
    }
    return 0;
}
