<?php
$pageTitle = 'Pantau Siswa';
require_once __DIR__ . '/../includes/auth.php';
requireRole('wali_murid');

$username = $_SESSION['username'];
$nis = str_replace('wali_', '', $username);

// Cari data anak (siswa)
$stmt = $pdo->prepare("SELECT id FROM siswa WHERE nis = ?");
$stmt->execute([$nis]);
$siswa_id = $stmt->fetchColumn();

if (!$siswa_id) {
    die("Data anak (siswa) tidak ditemukan.");
}

$tahun_ajaran = getTahunAjaranAktif($pdo);

// Rekap Absensi
$stmtAbsen = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) as alfa
    FROM absensi 
    WHERE siswa_id = ? AND tahun_ajaran_id = ?
");
$stmtAbsen->execute([$siswa_id, $tahun_ajaran['id']]);
$rekapAbsen = $stmtAbsen->fetch();

// History Absensi (30 Hari Terakhir)
$stmtHistAbsen = $pdo->prepare("SELECT tanggal, status, keterangan FROM absensi WHERE siswa_id = ? AND tahun_ajaran_id = ? ORDER BY tanggal DESC LIMIT 30");
$stmtHistAbsen->execute([$siswa_id, $tahun_ajaran['id']]);
$historiAbsen = $stmtHistAbsen->fetchAll();

// Semua Nilai
$stmtNilai = $pdo->prepare("
    SELECT n.*, t.judul, p.nama_mapel 
    FROM nilai n 
    JOIN tugas t ON n.tugas_id = t.id 
    JOIN mata_pelajaran p ON t.mapel_id = p.id 
    WHERE n.siswa_id = ? AND t.tahun_ajaran_id = ? 
    ORDER BY p.nama_mapel, t.created_at DESC
");
$stmtNilai->execute([$siswa_id, $tahun_ajaran['id']]);
$semuaNilai = $stmtNilai->fetchAll();

// Kelompokkan Nilai per Mapel
$nilaiPerMapel = [];
foreach ($semuaNilai as $n) {
    $nilaiPerMapel[$n['nama_mapel']][] = $n;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-search me-2 text-green"></i>Pantau Siswa</h4>
    </div>

    <!-- Navigasi Tabs -->
    <ul class="nav nav-tabs mb-4" id="pantauTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="absensi-tab" data-bs-toggle="tab" data-bs-target="#absensi" type="button" role="tab">
                <i class="bi bi-clipboard-check me-2"></i>Rekap Absensi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="nilai-tab" data-bs-toggle="tab" data-bs-target="#nilai" type="button" role="tab">
                <i class="bi bi-award me-2"></i>Rekap Nilai Tugas
            </button>
        </li>
    </ul>

    <!-- Konten Tabs -->
    <div class="tab-content" id="pantauTabsContent">
        
        <!-- Tab Absensi -->
        <div class="tab-pane fade show active" id="absensi" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                            <h6 class="fw-bold mb-0">Total Kehadiran Semester Ini</h6>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="row g-3 text-center">
                                <div class="col-6">
                                    <div class="border rounded-3 p-3 bg-soft-success border-success border-opacity-25">
                                        <div class="fs-2 fw-bold text-success mb-1"><?= $rekapAbsen['hadir'] ?? 0 ?></div>
                                        <div class="fs-13 text-success fw-500">Hadir</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-3 bg-soft-warning border-warning border-opacity-25">
                                        <div class="fs-2 fw-bold text-warning mb-1"><?= $rekapAbsen['sakit'] ?? 0 ?></div>
                                        <div class="fs-13 text-warning fw-500">Sakit</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-3 bg-soft-info border-info border-opacity-25">
                                        <div class="fs-2 fw-bold text-info mb-1"><?= $rekapAbsen['izin'] ?? 0 ?></div>
                                        <div class="fs-13 text-info fw-500">Izin</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded-3 p-3 bg-soft-danger border-danger border-opacity-25">
                                        <div class="fs-2 fw-bold text-danger mb-1"><?= $rekapAbsen['alfa'] ?? 0 ?></div>
                                        <div class="fs-13 text-danger fw-500">Alfa</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                            <h6 class="fw-bold mb-0">Riwayat Presensi (30 Hari Terakhir)</h6>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <?php if (empty($historiAbsen)): ?>
                                <p class="text-muted text-center py-4">Belum ada riwayat absensi.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($historiAbsen as $h): ?>
                                            <tr>
                                                <td class="fw-500"><?= formatTanggal($h['tanggal'], 'long') ?></td>
                                                <td>
                                                    <?php
                                                    $b = 'secondary'; $l = 'Unknown';
                                                    if ($h['status'] == 'hadir') { $b = 'success'; $l = 'Hadir'; }
                                                    elseif ($h['status'] == 'sakit') { $b = 'warning'; $l = 'Sakit'; }
                                                    elseif ($h['status'] == 'izin') { $b = 'info'; $l = 'Izin'; }
                                                    elseif ($h['status'] == 'alfa') { $b = 'danger'; $l = 'Alfa'; }
                                                    ?>
                                                    <span class="badge bg-<?= $b ?>"><?= $l ?></span>
                                                </td>
                                                <td class="text-muted fs-14"><?= clean($h['keterangan'] ?? '-') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Nilai -->
        <div class="tab-pane fade" id="nilai" role="tabpanel">
            <?php if (empty($nilaiPerMapel)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                    <i class="bi bi-award text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Belum ada data nilai</h5>
                    <p class="text-muted">Nilai tugas harian putra/putri Anda akan muncul di sini.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($nilaiPerMapel as $mapel => $nilais): 
                        $rata = round(array_sum(array_column($nilais, 'nilai')) / count($nilais));
                    ?>
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 rounded-4 h-100">
                            <div class="card-header bg-white border-bottom border-light pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-primary mb-0"><i class="bi bi-book me-2"></i><?= clean($mapel) ?></h6>
                                <span class="badge <?= $rata >= 75 ? 'bg-success' : 'bg-danger' ?> rounded-pill fs-12">Rata-rata: <?= $rata ?></span>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($nilais as $n): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-start px-4 py-3">
                                        <div>
                                            <div class="fw-500 text-dark"><?= clean($n['judul']) ?></div>
                                            <?php if ($n['catatan']): ?>
                                                <div class="text-muted fs-13 mt-1"><i class="bi bi-chat-left-text me-1"></i> "<?= clean($n['catatan']) ?>"</div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge <?= $n['nilai'] >= 75 ? 'bg-primary' : 'bg-danger' ?> fs-6 rounded-pill ms-3"><?= clean($n['nilai']) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<script>
// Buka tab nilai jika URL hash adalah #nilai
if(window.location.hash === '#nilai') {
    var triggerEl = document.querySelector('#nilai-tab');
    bootstrap.Tab.getInstance(triggerEl) || new bootstrap.Tab(triggerEl).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
