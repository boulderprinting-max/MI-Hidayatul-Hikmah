<?php
$pageTitle = 'Dashboard Siswa';
require_once __DIR__ . '/../includes/auth.php';
requireRole('siswa');

$tahun_ajaran = getTahunAjaranAktif($pdo);

// Ambil info siswa
$stmt = $pdo->prepare("SELECT s.*, k.nama_kelas, k.tingkat, k.wali_kelas, g.nama as nama_wali 
                       FROM siswa s 
                       JOIN kelas k ON s.kelas_id = k.id 
                       LEFT JOIN guru g ON k.wali_kelas = g.id 
                       WHERE s.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$siswa = $stmt->fetch();

if (!$siswa) {
    die("Data siswa tidak ditemukan.");
}

$kelas_id = $siswa['kelas_id'];

// Tugas Tertunda (Belum Dikerjakan atau belum dinilai)
$stmtTugas = $pdo->prepare("
    SELECT t.*, p.nama_mapel, g.nama as nama_guru,
           (SELECT COUNT(*) FROM pengumpulan_tugas WHERE tugas_id = t.id AND siswa_id = ?) as sudah_kumpul
    FROM tugas t
    JOIN mata_pelajaran p ON t.mapel_id = p.id
    JOIN guru g ON t.guru_id = g.id
    WHERE t.kelas_id = ? AND t.tahun_ajaran_id = ? AND t.status = 'aktif'
    HAVING sudah_kumpul = 0
    ORDER BY t.tenggat_waktu ASC
    LIMIT 5
");
$stmtTugas->execute([$siswa['id'], $kelas_id, $tahun_ajaran['id']]);
$tugasTertunda = $stmtTugas->fetchAll();

// Materi Terbaru
$stmtMateri = $pdo->prepare("
    SELECT m.*, p.nama_mapel, g.nama as nama_guru
    FROM materi m
    JOIN mata_pelajaran p ON m.mapel_id = p.id
    JOIN guru g ON m.guru_id = g.id
    WHERE m.kelas_id = ?
    ORDER BY m.created_at DESC
    LIMIT 3
");
$stmtMateri->execute([$kelas_id]);
$materiTerbaru = $stmtMateri->fetchAll();

// Pengumuman
$pengumuman = $pdo->query("SELECT * FROM pengumuman WHERE is_published = 1 AND (target = 'semua' OR target = 'siswa') ORDER BY tanggal DESC LIMIT 3")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="alert bg-soft-green border-0 mb-4 d-flex align-items-center gap-3 shadow-sm rounded-4">
        <div class="bg-white rounded-circle p-3 text-green fs-1 shadow-sm">
            <i class="bi bi-person-fill"></i>
        </div>
        <div>
            <h5 class="mb-1 fw-bold text-dark">Assalamu'alaikum, <?= clean($siswa['nama']) ?>!</h5>
            <p class="mb-0 text-muted">
                <i class="bi bi-building me-1"></i> <?= clean($siswa['nama_kelas']) ?> | 
                <i class="bi bi-calendar3 me-1"></i> <?= clean($tahun_ajaran['nama_tahun']) ?> 
            </p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Tugas & Materi -->
        <div class="col-lg-8">
            
            <!-- Tugas Tertunda -->
            <div class="card shadow-sm border-0 mb-4 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-journal-check text-orange me-2"></i>Tugas Yang Harus Dikerjakan</h5>
                    <a href="/siswa/tugas.php" class="btn btn-sm btn-light">Lihat Semua</a>
                </div>
                <div class="card-body px-4">
                    <?php if (empty($tugasTertunda)): ?>
                        <div class="text-center py-4 bg-light rounded-3">
                            <i class="bi bi-emoji-smile fs-1 text-success mb-2"></i>
                            <p class="mb-0 text-muted">Hore! Tidak ada tugas yang tertunda.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush gap-2">
                            <?php foreach ($tugasTertunda as $t): ?>
                            <a href="/siswa/tugas_detail.php?id=<?= $t['id'] ?>" class="list-group-item list-group-item-action bg-light rounded-3 border-0 p-3 hover-lift">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-2"><?= clean($t['nama_mapel']) ?></span>
                                        <h6 class="fw-bold mb-1 text-dark"><?= clean($t['judul']) ?></h6>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= clean($t['nama_guru']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($t['tenggat_waktu']): ?>
                                            <div class="badge <?= strtotime($t['tenggat_waktu']) < time() ? 'bg-danger' : 'bg-primary' ?> mb-1">
                                                Tenggat: <?= date('d M H:i', strtotime($t['tenggat_waktu'])) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-2"><span class="btn btn-sm btn-outline-primary rounded-pill py-0 px-3">Kerjakan</span></div>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Materi Terbaru -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="bi bi-book text-blue me-2"></i>Materi Terbaru</h5>
                    <a href="/siswa/materi.php" class="btn btn-sm btn-light">Lihat Semua</a>
                </div>
                <div class="card-body px-4">
                    <div class="row g-3">
                        <?php if (empty($materiTerbaru)): ?>
                            <div class="col-12 text-center py-4 bg-light rounded-3">
                                <p class="mb-0 text-muted">Belum ada materi baru.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($materiTerbaru as $m): ?>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100 hover-lift">
                                    <div class="card-body p-3">
                                        <div class="badge bg-soft-green text-green mb-2"><?= clean($m['nama_mapel']) ?></div>
                                        <h6 class="fw-bold text-dark mb-2 line-clamp-2"><?= clean($m['judul']) ?></h6>
                                        <a href="/siswa/materi.php" class="text-decoration-none text-primary fs-14 fw-500">Baca Materi <i class="bi bi-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Info & Pengumuman -->
        <div class="col-lg-4">
            
            <!-- Profil Singkat -->
            <div class="card shadow-sm border-0 mb-4 rounded-4 bg-gradient-blue text-white">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Info Akademik</h5>
                    <div class="d-flex justify-content-between border-bottom border-light pb-2 mb-2 border-opacity-25">
                        <span>NIS</span>
                        <span class="fw-bold"><?= clean($siswa['nis']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-light pb-2 mb-2 border-opacity-25">
                        <span>Wali Kelas</span>
                        <span class="fw-bold text-end"><?= clean($siswa['nama_wali'] ?? '-') ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status</span>
                        <span class="badge bg-success text-white">Aktif</span>
                    </div>
                </div>
            </div>

            <!-- Pengumuman -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-megaphone text-danger me-2"></i>Pengumuman</h5>
                </div>
                <div class="card-body px-4">
                    <?php if (empty($pengumuman)): ?>
                        <p class="text-muted text-center my-3">Tidak ada pengumuman.</p>
                    <?php else: ?>
                        <div class="timeline mt-3">
                            <?php foreach ($pengumuman as $p): ?>
                            <div class="mb-4 position-relative ps-4" style="border-left: 2px solid #e9ecef;">
                                <div class="position-absolute bg-primary rounded-circle" style="width: 12px; height: 12px; left: -7px; top: 5px;"></div>
                                <h6 class="fw-bold mb-1"><?= clean($p['judul']) ?></h6>
                                <p class="text-muted fs-13 mb-1 line-clamp-2"><?= strip_tags($p['isi']) ?></p>
                                <small class="text-primary fw-500"><?= formatTanggal($p['tanggal'], 'short') ?></small>
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
