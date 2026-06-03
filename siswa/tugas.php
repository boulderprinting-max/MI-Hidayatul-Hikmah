<?php
$pageTitle = 'Tugas Saya';
require_once __DIR__ . '/../includes/auth.php';
requireRole('siswa');

$tahun_ajaran = getTahunAjaranAktif($pdo);

$stmt = $pdo->prepare("SELECT id, kelas_id FROM siswa WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$siswa = $stmt->fetch();
$siswa_id = $siswa['id'];
$kelas_id = $siswa['kelas_id'];

$filter = $_GET['filter'] ?? 'semua'; // semua, tertunda, selesai

$query = "SELECT t.*, p.nama_mapel, g.nama as nama_guru,
          pt.id as pengumpulan_id, pt.waktu_kumpul,
          n.nilai
          FROM tugas t 
          JOIN mata_pelajaran p ON t.mapel_id = p.id 
          JOIN guru g ON t.guru_id = g.id 
          LEFT JOIN pengumpulan_tugas pt ON t.id = pt.tugas_id AND pt.siswa_id = ?
          LEFT JOIN nilai n ON t.id = n.tugas_id AND n.siswa_id = ?
          WHERE t.kelas_id = ? AND t.tahun_ajaran_id = ? AND t.status = 'aktif'";

$params = [$siswa_id, $siswa_id, $kelas_id, $tahun_ajaran['id']];

if ($filter === 'tertunda') {
    $query .= " AND pt.id IS NULL";
} elseif ($filter === 'selesai') {
    $query .= " AND pt.id IS NOT NULL";
}

$query .= " ORDER BY t.tenggat_waktu ASC, t.created_at DESC";
$stmtData = $pdo->prepare($query);
$stmtData->execute($params);
$tugasData = $stmtData->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-journal-check me-2 text-green"></i>Tugas Pembelajaran</h4>
    </div>

    <!-- Tab Filter -->
    <ul class="nav nav-pills mb-4 gap-2 bg-white p-2 rounded-4 shadow-sm d-inline-flex">
        <li class="nav-item">
            <a class="nav-link rounded-pill <?= $filter === 'semua' ? 'active' : 'text-dark' ?>" href="/siswa/tugas.php?filter=semua">Semua Tugas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill <?= $filter === 'tertunda' ? 'active' : 'text-dark' ?>" href="/siswa/tugas.php?filter=tertunda">Belum Dikerjakan</a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill <?= $filter === 'selesai' ? 'active' : 'text-dark' ?>" href="/siswa/tugas.php?filter=selesai">Sudah Dikerjakan</a>
        </li>
    </ul>

    <!-- Grid Tugas -->
    <div class="row g-4">
        <?php if (empty($tugasData)): ?>
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/2836/2836526.png" alt="Empty" width="120" class="mb-3 opacity-50">
                <h5 class="text-muted fw-bold">Tidak ada tugas.</h5>
                <p class="text-muted">Yeay! Kamu tidak memiliki daftar tugas untuk kategori ini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($tugasData as $t): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 hover-lift rounded-4 overflow-hidden position-relative">
                    <?php if ($t['nilai'] !== null): ?>
                        <div class="position-absolute top-0 end-0 bg-success text-white fw-bold px-3 py-1 rounded-bottom-start shadow-sm" style="font-size: 1.1rem; z-index:10;">
                            Nilai: <?= clean($t['nilai']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="card-body p-4 pt-4 mt-2">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-12 fw-bold"><?= clean($t['nama_mapel']) ?></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-2"><?= clean($t['judul']) ?></h5>
                        <p class="text-muted fs-14 mb-3 line-clamp-2"><?= strip_tags($t['deskripsi']) ?></p>
                        
                        <div class="bg-light p-2 rounded-3 mb-4 d-flex align-items-center">
                            <div class="me-3 fs-3 <?= $t['tenggat_waktu'] && strtotime($t['tenggat_waktu']) < time() ? 'text-danger' : 'text-primary' ?>">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <div class="fs-12 text-muted fw-bold text-uppercase">Tenggat Waktu</div>
                                <div class="fw-bold fs-14 <?= $t['tenggat_waktu'] && strtotime($t['tenggat_waktu']) < time() ? 'text-danger' : 'text-dark' ?>">
                                    <?= $t['tenggat_waktu'] ? date('d M Y, H:i', strtotime($t['tenggat_waktu'])) : 'Tidak ada' ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($t['pengumpulan_id']): ?>
                            <a href="/siswa/tugas_detail.php?id=<?= $t['id'] ?>" class="btn btn-outline-success w-100 rounded-pill fw-500">
                                <i class="bi bi-check-circle me-1"></i> Sudah Mengumpulkan
                            </a>
                        <?php else: ?>
                            <a href="/siswa/tugas_detail.php?id=<?= $t['id'] ?>" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">
                                <i class="bi bi-pencil-square me-1"></i> Kerjakan Sekarang
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
