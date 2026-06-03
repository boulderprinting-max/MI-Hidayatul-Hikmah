<?php
$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

// Statistik
$totalGuru = $pdo->query("SELECT COUNT(*) FROM guru")->fetchColumn();
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa WHERE status='aktif'")->fetchColumn();
$totalKelas = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
$totalMapel = $pdo->query("SELECT COUNT(*) FROM mata_pelajaran")->fetchColumn();
$totalMateri = $pdo->query("SELECT COUNT(*) FROM materi")->fetchColumn();
$totalTugas = $pdo->query("SELECT COUNT(*) FROM tugas WHERE status='aktif'")->fetchColumn();

// Absensi hari ini
$today = date('Y-m-d');
$absensiToday = $pdo->prepare("SELECT status, COUNT(*) as total FROM absensi WHERE tanggal = ? GROUP BY status");
$absensiToday->execute([$today]);
$absensiData = [];
while ($row = $absensiToday->fetch()) {
    $absensiData[$row['status']] = $row['total'];
}

// Siswa per kelas untuk chart
$siswaPerKelas = $pdo->query("SELECT k.nama_kelas, COUNT(s.id) as total FROM kelas k LEFT JOIN siswa s ON k.id = s.kelas_id GROUP BY k.id ORDER BY k.tingkat")->fetchAll();

// Pengumuman terbaru
$pengumuman = $pdo->query("SELECT * FROM pengumuman WHERE is_published = 1 ORDER BY tanggal DESC LIMIT 5")->fetchAll();

$tahunAjaran = getTahunAjaranAktif($pdo);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <!-- Tahun Ajaran Info -->
    <div class="alert bg-soft-green border-0 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-calendar-range text-green fs-5"></i>
        <span>Tahun Ajaran Aktif: <strong class="text-green"><?= clean($tahunAjaran['nama_tahun']) ?> — Semester <?= ucfirst($tahunAjaran['semester'] ?? '-') ?></strong></span>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-green">
                <div class="stat-icon"><i class="bi bi-person-badge"></i></div>
                <div class="stat-value"><?= $totalGuru ?></div>
                <div class="stat-label">Total Guru</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-blue">
                <div class="stat-icon"><i class="bi bi-people"></i></div>
                <div class="stat-value"><?= $totalSiswa ?></div>
                <div class="stat-label">Total Siswa</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-cyan">
                <div class="stat-icon"><i class="bi bi-building"></i></div>
                <div class="stat-value"><?= $totalKelas ?></div>
                <div class="stat-label">Total Kelas</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card bg-gradient-orange">
                <div class="stat-icon"><i class="bi bi-book"></i></div>
                <div class="stat-value"><?= $totalMapel ?></div>
                <div class="stat-label">Mata Pelajaran</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Chart Siswa Per Kelas -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-600"><i class="bi bi-bar-chart me-2"></i>Jumlah Siswa Per Kelas</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartSiswa" height="250"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Absensi Hari Ini -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h6 class="mb-0 fw-600"><i class="bi bi-clipboard-check me-2"></i>Absensi Hari Ini</h6>
                </div>
                <div class="card-body">
                    <canvas id="chartAbsensi" height="250"></canvas>
                    <div class="row text-center mt-3 g-2">
                        <div class="col-3">
                            <span class="badge badge-hadir px-2 py-1"><?= $absensiData['hadir'] ?? 0 ?></span>
                            <div class="fs-13 mt-1">Hadir</div>
                        </div>
                        <div class="col-3">
                            <span class="badge badge-sakit px-2 py-1"><?= $absensiData['sakit'] ?? 0 ?></span>
                            <div class="fs-13 mt-1">Sakit</div>
                        </div>
                        <div class="col-3">
                            <span class="badge badge-izin px-2 py-1"><?= $absensiData['izin'] ?? 0 ?></span>
                            <div class="fs-13 mt-1">Izin</div>
                        </div>
                        <div class="col-3">
                            <span class="badge badge-alfa px-2 py-1"><?= $absensiData['alfa'] ?? 0 ?></span>
                            <div class="fs-13 mt-1">Alfa</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengumuman Terbaru -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-600"><i class="bi bi-megaphone me-2"></i>Pengumuman Terbaru</h6>
            <a href="/admin/pengumuman.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pengumuman)): ?>
                <div class="text-center py-4 text-muted">Belum ada pengumuman</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($pengumuman as $p): ?>
                    <div class="list-group-item px-4 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-600"><?= clean($p['judul']) ?></h6>
                                <p class="mb-0 fs-14" style="color:var(--text-secondary);"><?= substr(strip_tags($p['isi']), 0, 120) ?>...</p>
                            </div>
                            <small class="text-muted text-nowrap ms-3"><?= formatTanggal($p['tanggal'], 'short') ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Chart: Siswa Per Kelas
const ctxSiswa = document.getElementById('chartSiswa').getContext('2d');
new Chart(ctxSiswa, {
    type: 'bar',
    data: {
        labels: [<?= implode(',', array_map(fn($k) => "'" . $k['nama_kelas'] . "'", $siswaPerKelas)) ?>],
        datasets: [{
            label: 'Jumlah Siswa',
            data: [<?= implode(',', array_map(fn($k) => $k['total'], $siswaPerKelas)) ?>],
            backgroundColor: ['#198754','#0d6efd','#0dcaf0','#ffc107','#fd7e14','#dc3545'],
            borderRadius: 8,
            barThickness: 40,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 5 } }
        }
    }
});

// Chart: Absensi Hari Ini
const ctxAbsensi = document.getElementById('chartAbsensi').getContext('2d');
new Chart(ctxAbsensi, {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Sakit', 'Izin', 'Alfa'],
        datasets: [{
            data: [<?= $absensiData['hadir'] ?? 0 ?>, <?= $absensiData['sakit'] ?? 0 ?>, <?= $absensiData['izin'] ?? 0 ?>, <?= $absensiData['alfa'] ?? 0 ?>],
            backgroundColor: ['#198754','#ffc107','#0d6efd','#dc3545'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '65%'
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
