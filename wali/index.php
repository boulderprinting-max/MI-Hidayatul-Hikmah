<?php
$pageTitle = 'Dashboard Wali Murid';
require_once __DIR__ . '/../includes/auth.php';
requireRole('wali_murid');

$username = $_SESSION['username'];
// Format username wali murid adalah "wali_NIS"
$nis = str_replace('wali_', '', $username);

// Cari data anak (siswa) berdasarkan NIS
$stmt = $pdo->prepare("SELECT s.*, k.nama_kelas, g.nama as nama_wali_kelas 
                       FROM siswa s 
                       JOIN kelas k ON s.kelas_id = k.id 
                       LEFT JOIN guru g ON k.wali_kelas = g.id 
                       WHERE s.nis = ?");
$stmt->execute([$nis]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die("Data anak (siswa) tidak ditemukan di database.");
}

$tahun_ajaran = getTahunAjaranAktif($pdo);

// Cek Absensi Hari Ini
$today = date('Y-m-d');
$stmtAbsen = $pdo->prepare("SELECT status FROM absensi WHERE siswa_id = ? AND tanggal = ?");
$stmtAbsen->execute([$siswa['id'], $today]);
$absenHariIni = $stmtAbsen->fetchColumn();

// Nilai Terbaru
$stmtNilai = $pdo->prepare("
    SELECT n.nilai, t.judul, p.nama_mapel 
    FROM nilai n 
    JOIN tugas t ON n.tugas_id = t.id 
    JOIN mata_pelajaran p ON t.mapel_id = p.id 
    WHERE n.siswa_id = ? AND t.tahun_ajaran_id = ? 
    ORDER BY t.created_at DESC LIMIT 3
");
$stmtNilai->execute([$siswa['id'], $tahun_ajaran['id']]);
$nilaiTerbaru = $stmtNilai->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <div class="alert bg-gradient-blue border-0 mb-4 d-flex align-items-center gap-3 shadow-sm rounded-4 text-white">
        <div class="bg-white rounded-circle p-3 text-blue fs-1 shadow-sm">
            <i class="bi bi-people-fill"></i>
        </div>
        <div>
            <h5 class="mb-1 fw-bold">Selamat Datang, Bapak/Ibu <?= clean($_SESSION['nama']) ?></h5>
            <p class="mb-0 text-white-50">
                Memantau perkembangan belajar putra/putri Anda
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <!-- Profil Anak -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body text-center p-4">
                    <img src="https://cdn-icons-png.flaticon.com/512/3135/3135768.png" alt="Student" width="80" class="mb-3">
                    <h5 class="fw-bold text-dark mb-1"><?= clean($siswa['nama']) ?></h5>
                    <p class="text-muted mb-3">NIS: <?= clean($siswa['nis']) ?></p>
                    
                    <ul class="list-group list-group-flush text-start mt-3">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Kelas</span>
                            <span class="fw-bold"><?= clean($siswa['nama_kelas']) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Wali Kelas</span>
                            <span class="fw-bold text-end"><?= clean($siswa['nama_wali_kelas'] ?? '-') ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Status Absensi -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-clipboard-check text-success me-2"></i>Status Kehadiran Hari Ini</h5>
                    <a href="/wali/pantau.php" class="btn btn-sm btn-light">Rekap Bulanan</a>
                </div>
                <div class="card-body px-4 pb-4 text-center">
                    <p class="text-muted mb-3"><?= formatTanggal($today, 'long') ?></p>
                    <?php if (!$absenHariIni): ?>
                        <div class="d-inline-flex align-items-center bg-light text-muted px-4 py-2 rounded-pill fw-500">
                            <i class="bi bi-hourglass-split me-2"></i> Belum ada data presensi dari Wali Kelas
                        </div>
                    <?php else: ?>
                        <?php
                        $badge = 'secondary'; $icon = 'question'; $label = 'Tidak Diketahui';
                        if ($absenHariIni == 'hadir') { $badge = 'success'; $icon = 'check-circle'; $label = 'Hadir di Sekolah'; }
                        elseif ($absenHariIni == 'sakit') { $badge = 'warning'; $icon = 'thermometer-half'; $label = 'Sakit'; }
                        elseif ($absenHariIni == 'izin') { $badge = 'info'; $icon = 'envelope'; $label = 'Izin'; }
                        elseif ($absenHariIni == 'alfa') { $badge = 'danger'; $icon = 'x-circle'; $label = 'Tanpa Keterangan (Alfa)'; }
                        ?>
                        <div class="d-inline-flex align-items-center text-<?= $badge ?> fs-3 fw-bold border border-<?= $badge ?> rounded-pill px-5 py-2 bg-soft-<?= $badge ?>">
                            <i class="bi bi-<?= $icon ?> me-3"></i> <?= $label ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Nilai Terbaru -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Nilai Tugas Terbaru</h5>
                    <a href="/wali/pantau.php#nilai" class="btn btn-sm btn-light">Lihat Semua</a>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (empty($nilaiTerbaru)): ?>
                        <div class="text-center py-3 text-muted">Belum ada nilai tugas terbaru.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-2">
                            <?php foreach ($nilaiTerbaru as $n): ?>
                            <div class="list-group-item bg-light border-0 rounded-3 p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="badge bg-warning text-dark mb-1"><?= clean($n['nama_mapel']) ?></div>
                                    <div class="fw-bold text-dark"><?= clean($n['judul']) ?></div>
                                </div>
                                <div>
                                    <span class="badge <?= $n['nilai'] >= 75 ? 'bg-primary' : 'bg-danger' ?> fs-5 rounded-pill px-3 py-2"><?= clean($n['nilai']) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
