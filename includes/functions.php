<?php
// =====================================================
// HELPER FUNCTIONS
// MI Hidayatul Hikmah - E-Learning
// =====================================================

/**
 * Redirect ke halaman lain
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Sanitasi input untuk mencegah XSS
 */
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal($tanggal, $format = 'long') {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $date = new DateTime($tanggal);
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    
    if ($format === 'long') {
        return $hari[$date->format('w')] . ', ' . $date->format('d') . ' ' . $bulan[(int)$date->format('m')] . ' ' . $date->format('Y');
    } elseif ($format === 'short') {
        return $date->format('d') . ' ' . $bulan[(int)$date->format('m')] . ' ' . $date->format('Y');
    }
    return $date->format('d/m/Y');
}

/**
 * Upload file dengan validasi
 */
function uploadFile($file, $folder, $allowed = ['pdf','doc','docx','ppt','pptx','xls','xlsx','jpg','jpeg','png','mp4']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error saat upload file'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    // Max 50MB
    if ($file['size'] > 50 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Ukuran file melebihi 50MB'];
    }
    
    // Pastikan folder ada
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $path = $folder . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'Gagal menyimpan file'];
}

/**
 * Hapus file dari server
 */
function deleteFile($filepath) {
    if (file_exists($filepath)) {
        unlink($filepath);
    }
}

/**
 * Flash message menggunakan session
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Tampilkan flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'danger' ? '❌' : 'ℹ️');
        return '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show" role="alert">
            ' . $icon . ' ' . $flash['message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
    return '';
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Ambil data user yang sedang login
 */
function currentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'nama' => $_SESSION['nama'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'username' => $_SESSION['username'] ?? '',
    ];
}

/**
 * Cek role user
 */
function isRole($roles) {
    if (is_string($roles)) {
        $roles = [$roles];
    }
    return in_array($_SESSION['role'] ?? '', $roles);
}

/**
 * Cek apakah role adalah guru (kelas 1-6)
 */
function isGuru() {
    $role = $_SESSION['role'] ?? '';
    return strpos($role, 'guru_kelas_') === 0;
}

/**
 * Ambil nomor kelas dari role guru
 */
function getKelasGuru() {
    $role = $_SESSION['role'] ?? '';
    if (strpos($role, 'guru_kelas_') === 0) {
        return (int) str_replace('guru_kelas_', '', $role);
    }
    return 0;
}

/**
 * Generate pagination HTML
 */
function pagination($totalData, $perPage, $currentPage, $url) {
    $totalPages = ceil($totalData / $perPage);
    if ($totalPages <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous
    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
    $html .= '<li class="page-item ' . $prevDisabled . '"><a class="page-link" href="' . $url . '&page=' . ($currentPage - 1) . '">‹</a></li>';
    
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $url . '&page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Next
    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
    $html .= '<li class="page-item ' . $nextDisabled . '"><a class="page-link" href="' . $url . '&page=' . ($currentPage + 1) . '">›</a></li>';
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Hitung nilai akhir (tugas 30%, UTS 30%, UAS 40%)
 */
function hitungNilaiAkhir($tugas, $uts, $uas) {
    return round(($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4), 2);
}

/**
 * Konversi nilai ke predikat
 */
function predikatNilai($nilai) {
    if ($nilai >= 90) return 'A';
    if ($nilai >= 80) return 'B';
    if ($nilai >= 70) return 'C';
    if ($nilai >= 60) return 'D';
    return 'E';
}

/**
 * Ambil tahun ajaran aktif
 */
function getTahunAjaranAktif($pdo) {
    $stmt = $pdo->query("SELECT * FROM tahun_ajaran WHERE status = 'aktif' LIMIT 1");
    return $stmt->fetch() ?: ['id' => 0, 'nama_tahun' => '-', 'semester' => '-'];
}
