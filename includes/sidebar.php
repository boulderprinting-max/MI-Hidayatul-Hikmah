<?php
$role = $_SESSION['role'] ?? '';
$currentFile = basename($_SERVER['PHP_SELF']);

// Tentukan base path
$basePath = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) $basePath = '.';
elseif (strpos($_SERVER['PHP_SELF'], '/guru/') !== false) $basePath = '.';
elseif (strpos($_SERVER['PHP_SELF'], '/siswa/') !== false) $basePath = '.';
elseif (strpos($_SERVER['PHP_SELF'], '/wali/') !== false) $basePath = '.';
?>

<aside class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">🕌</div>
        <h5>MI Hidayatul Hikmah</h5>
        <small>E-Learning System</small>
    </div>
    
    <div class="sidebar-menu">
        
        <?php if ($role === 'super_admin'): ?>
        <!-- ===== MENU ADMIN ===== -->
        <div class="sidebar-menu-label">Menu Utama</div>
        <a href="/admin/index.php" class="<?= $currentFile == 'index.php' && strpos($_SERVER['PHP_SELF'], '/admin/') !== false ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        
        <div class="sidebar-menu-label">Master Data</div>
        <a href="/admin/guru.php" class="<?= $currentFile == 'guru.php' ? 'active' : '' ?>">
            <i class="bi bi-person-badge"></i> Data Guru
        </a>
        <a href="/admin/siswa.php" class="<?= $currentFile == 'siswa.php' ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Data Siswa
        </a>
        <a href="/admin/import_siswa.php" class="<?= $currentFile == 'import_siswa.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-spreadsheet"></i> Import Siswa
        </a>
        <a href="/admin/kelas.php" class="<?= $currentFile == 'kelas.php' ? 'active' : '' ?>">
            <i class="bi bi-building"></i> Data Kelas
        </a>
        <a href="/admin/mapel.php" class="<?= $currentFile == 'mapel.php' ? 'active' : '' ?>">
            <i class="bi bi-book"></i> Mata Pelajaran
        </a>
        <a href="/admin/tahun_ajaran.php" class="<?= $currentFile == 'tahun_ajaran.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-range"></i> Tahun Ajaran
        </a>
        
        <div class="sidebar-menu-label">Akademik</div>
        <a href="/admin/absensi.php" class="<?= $currentFile == 'absensi.php' ? 'active' : '' ?>">
            <i class="bi bi-clipboard-check"></i> Absensi
        </a>
        <a href="/admin/materi.php" class="<?= $currentFile == 'materi.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i> Materi
        </a>
        <a href="/admin/tugas.php" class="<?= $currentFile == 'tugas.php' ? 'active' : '' ?>">
            <i class="bi bi-pencil-square"></i> Tugas
        </a>
        <a href="/admin/nilai.php" class="<?= $currentFile == 'nilai.php' ? 'active' : '' ?>">
            <i class="bi bi-trophy"></i> Nilai
        </a>
        <a href="/admin/pengumuman.php" class="<?= $currentFile == 'pengumuman.php' ? 'active' : '' ?>">
            <i class="bi bi-megaphone"></i> Pengumuman
        </a>
        
        <div class="sidebar-menu-label">Laporan</div>
        <a href="/admin/rapor.php" class="<?= $currentFile == 'rapor.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i> E-Rapor
        </a>
        <a href="/admin/laporan.php" class="<?= $currentFile == 'laporan.php' ? 'active' : '' ?>">
            <i class="bi bi-printer"></i> Laporan
        </a>
        
        <div class="sidebar-menu-label">Sistem</div>
        <a href="/admin/users.php" class="<?= $currentFile == 'users.php' ? 'active' : '' ?>">
            <i class="bi bi-shield-lock"></i> Kelola Akun
        </a>
        <a href="/admin/backup.php" class="<?= $currentFile == 'backup.php' ? 'active' : '' ?>">
            <i class="bi bi-database-down"></i> Backup DB
        </a>

        <?php elseif (isGuru()): ?>
        <!-- ===== MENU GURU ===== -->
        <div class="sidebar-menu-label">Menu Guru</div>
        <a href="/guru/index.php" class="<?= $currentFile == 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="/guru/absensi.php" class="<?= $currentFile == 'absensi.php' ? 'active' : '' ?>">
            <i class="bi bi-clipboard-check"></i> Input Absensi
        </a>
        <a href="/guru/materi.php" class="<?= $currentFile == 'materi.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i> Upload Materi
        </a>
        <a href="/guru/tugas.php" class="<?= $currentFile == 'tugas.php' ? 'active' : '' ?>">
            <i class="bi bi-pencil-square"></i> Upload Tugas
        </a>
        <a href="/guru/nilai.php" class="<?= $currentFile == 'nilai.php' ? 'active' : '' ?>">
            <i class="bi bi-trophy"></i> Input Nilai
        </a>
        <a href="/guru/rekap.php" class="<?= $currentFile == 'rekap.php' ? 'active' : '' ?>">
            <i class="bi bi-printer"></i> Cetak Rekap
        </a>

        <?php elseif ($role === 'siswa'): ?>
        <!-- ===== MENU SISWA ===== -->
        <div class="sidebar-menu-label">Menu Siswa</div>
        <a href="/siswa/index.php" class="<?= $currentFile == 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="/siswa/materi.php" class="<?= $currentFile == 'materi.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i> Materi
        </a>
        <a href="/siswa/tugas.php" class="<?= $currentFile == 'tugas.php' ? 'active' : '' ?>">
            <i class="bi bi-pencil-square"></i> Tugas
        </a>
        <a href="/siswa/nilai.php" class="<?= $currentFile == 'nilai.php' ? 'active' : '' ?>">
            <i class="bi bi-trophy"></i> Nilai Saya
        </a>
        <a href="/siswa/absensi.php" class="<?= $currentFile == 'absensi.php' ? 'active' : '' ?>">
            <i class="bi bi-clipboard-check"></i> Absensi Saya
        </a>

        <?php elseif ($role === 'wali_murid'): ?>
        <!-- ===== MENU WALI MURID ===== -->
        <div class="sidebar-menu-label">Menu Wali Murid</div>
        <a href="/wali/index.php" class="<?= $currentFile == 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="/wali/nilai.php" class="<?= $currentFile == 'nilai.php' ? 'active' : '' ?>">
            <i class="bi bi-trophy"></i> Nilai Anak
        </a>
        <a href="/wali/absensi.php" class="<?= $currentFile == 'absensi.php' ? 'active' : '' ?>">
            <i class="bi bi-clipboard-check"></i> Absensi Anak
        </a>
        <a href="/wali/pengumuman.php" class="<?= $currentFile == 'pengumuman.php' ? 'active' : '' ?>">
            <i class="bi bi-megaphone"></i> Pengumuman
        </a>
        
        <?php endif; ?>
        
        <!-- Logout -->
        <div class="sidebar-menu-label">Akun</div>
        <a href="/logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</aside>
