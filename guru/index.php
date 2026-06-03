<?php
$pageTitle = 'Dashboard Guru';
require_once __DIR__ . '/../includes/auth.php';

// Pastikan yang akses adalah role guru
if (strpos($_SESSION['role'], 'guru_kelas_') !== 0) {
    redirect('/index.php');
}

// Ambil info guru
$stmt = $pdo->prepare("SELECT g.*, k.nama_kelas, k.tingkat 
                       FROM guru g 
                       LEFT JOIN kelas k ON g.kelas = k.id OR g.kelas = k.tingkat /* fallback jika struktur kelas beda */
                       WHERE g.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$guru = $stmt->fetch();

// Cek Wali Kelas dari tabel kelas secara eksplisit
$stmtWali = $pdo->prepare("SELECT * FROM kelas WHERE wali_kelas = ?");
$stmtWali->execute([$guru['id'] ?? 0]);
$waliKelas = $stmtWali->fetch();

$kelas_id = $waliKelas ? $waliKelas['id'] : null;
$nama_kelas = $waliKelas ? $waliKelas['nama_kelas'] : 'Belum diatur';

// Statistik Guru
$totalSiswa = 0;
if ($kelas_id) {
    $stmtSiswa = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE kelas_id = ? AND status='aktif'");
    $stmtSiswa->execute([$kelas_id]);
    $totalSiswa = $stmtSiswa->fetchColumn();
}

$stmtMateri = $pdo->prepare("SELECT COUNT(*) FROM materi WHERE guru_id = ?");
$stmtMateri->execute([$guru['id'] ?? 0]);
$totalMateri = $stmtMateri->fetchColumn();

$stmtTugas = $pdo->prepare("SELECT COUNT(*) FROM tugas WHERE guru_id = ? AND status='aktif'");
$stmtTugas->execute([$guru['id'] ?? 0]);
$totalTugas = $stmtTugas->fetchColumn();

// Absensi Hari Ini (Jika dia wali kelas)
$today = date('Y-m-d');
$absensiHariIni = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0];
$sudahAbsen = false;

if ($kelas_id) {
    // Cek apakah sudah ada input absensi hari ini
    $cekAbsen = $pdo->prepare("SELECT COUNT(*) FROM absensi a JOIN siswa s ON a.siswa_id = s.id WHERE a.tanggal = ? AND s.kelas_id = ?");
    $cekAbsen->execute([$today, $kelas_id]);
    if ($cekAbsen->fetchColumn() > 0) {
        $sudahAbsen = true;
        
        // Ambil rekap
        $rekapAbsen = $pdo->prepare("SELECT a.status, COUNT(*) as total FROM absensi a JOIN siswa s ON a.siswa_id = s.id WHERE a.tanggal = ? AND s.kelas_id = ? GROUP BY a.status");
        $rekapAbsen->execute([$today, $kelas_id]);
        while ($row = $rekapAbsen->fetch()) {
            $absensiHariIni[$row['status']] = $row['total'];
        }
    }
}

// Pengumuman terbaru
$pengumuman = $pdo->query("SELECT * FROM pengumuman WHERE is_published = 1 AND (target = 'semua' OR target = 'guru') ORDER BY tanggal DESC LIMIT 5")->fetchAll();

$tahunAjaran = getTahunAjaranAktif($pdo);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="alert bg-soft-green border-0 mb-4 d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-person-badge fs-2 text-green"></i>
        <div>
            <h5 class="mb-0 fw-bold text-dark">Selamat Datang, <?= clean($_SESSION['nama']) ?></h5>
            <small class="text-muted">Tahun Ajaran: <?= clean($tahunAjaran['nama_tahun']) ?> — Semester <?= ucfirst($tahunAjaran['semester']) ?></small>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-green">
                <div class="stat-icon"><i class="bi bi-building"></i></div>
                <div class="stat-value fs-4"><?= clean($nama_kelas) ?></div>
                <div class="stat-label">Wali Kelas</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-blue">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-value"><?= $totalSiswa ?></div>
                <div class="stat-label">Siswa Kelas Ini</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-cyan">
                <div class="stat-icon"><i class="bi bi-journal-text"></i></div>
                <div class="stat-value"><?= $totalMateri ?></div>
                <div class="stat-label">Materi Saya</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-orange">
                <div class="stat-icon"><i class="bi bi-journal-check"></i></div>
                <div class="stat-value"><?= $totalTugas ?></div>
                <div class="stat-label">Tugas Aktif</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Absensi Cepat -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-600"><i class="bi bi-clipboard-check me-2"></i>Status Absensi Kelas Hari Ini</h6>
                    <?php if ($kelas_id): ?>
                    <a href="/guru/absensi.php" class="btn btn-sm btn-primary">Kelola Absensi</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4"><i class="bi bi-calendar3 me-2"></i><?= formatTanggal($today, 'long') ?></p>
                    
                    <?php if (!$kelas_id): ?>
                        <div class="alert alert-warning">Anda belum diatur sebagai Wali Kelas di kelas manapun. Hubungi Admin.</div>
                    <?php elseif (!$sudahAbsen): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-exclamation-circle text-warning mb-2 d-block" style="font-size: 3rem;"></i>
                            <h5 class="fw-bold">Belum Ada Presensi</h5>
                            <p class="text-muted">Silakan lakukan pengisian absensi siswa untuk hari ini.</p>
                            <a href="/guru/absensi.php" class="btn btn-primary px-4"><i class="bi bi-pencil-square me-2"></i>Isi Sekarang</a>
                        </div>
                    <?php else: ?>
                        <!-- Chart Absensi -->
                        <div style="height: 200px;">
                            <canvas id="chartAbsensi"></canvas>
                        </div>
                        <div class="row text-center mt-3 g-2">
                            <div class="col-3">
                                <span class="badge badge-hadir px-3 py-2 w-100"><?= $absensiHariIni['hadir'] ?> Hadir</span>
                            </div>
                            <div class="col-3">
                                <span class="badge badge-sakit px-3 py-2 w-100"><?= $absensiHariIni['sakit'] ?> Sakit</span>
                            </div>
                            <div class="col-3">
                                <span class="badge badge-izin px-3 py-2 w-100"><?= $absensiHariIni['izin'] ?> Izin</span>
                            </div>
                            <div class="col-3">
                                <span class="badge badge-alfa px-3 py-2 w-100"><?= $absensiHariIni['alfa'] ?> Alfa</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pengumuman Terbaru -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0 fw-600"><i class="bi bi-megaphone me-2"></i>Pengumuman</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($pengumuman)): ?>
                        <div class="text-center py-5 text-muted">Belum ada pengumuman</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($pengumuman as $p): ?>
                            <div class="list-group-item px-4 py-3 border-bottom-0 border-bottom">
                                <h6 class="mb-1 fw-bold text-dark"><?= clean($p['judul']) ?></h6>
                                <p class="mb-1 fs-14 text-muted"><?= nl2br(clean(substr($p['isi'], 0, 150))) ?>...</p>
                                <small class="text-primary fw-500"><i class="bi bi-clock me-1"></i><?= formatTanggal($p['tanggal'], 'short') ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($sudahAbsen): ?>
<script>
// Chart Absensi Hari Ini
const ctxAbsensi = document.getElementById('chartAbsensi').getContext('2d');
new Chart(ctxAbsensi, {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Sakit', 'Izin', 'Alfa'],
        datasets: [{
            data: [<?= $absensiHariIni['hadir'] ?>, <?= $absensiHariIni['sakit'] ?>, <?= $absensiHariIni['izin'] ?>, <?= $absensiHariIni['alfa'] ?>],
            backgroundColor: ['#198754','#ffc107','#0d6efd','#dc3545'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { position: 'right' } 
        },
        cutout: '65%'
    }
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
