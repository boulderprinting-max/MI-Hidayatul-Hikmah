<?php
$pageTitle = 'Materi Belajar';
require_once __DIR__ . '/../includes/auth.php';
requireRole('siswa');

$stmt = $pdo->prepare("SELECT kelas_id FROM siswa WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$siswa = $stmt->fetch();
$kelas_id = $siswa['kelas_id'];

$mapel_id = $_GET['mapel'] ?? '';

$query = "SELECT m.*, p.nama_mapel, g.nama as nama_guru 
          FROM materi m 
          JOIN mata_pelajaran p ON m.mapel_id = p.id 
          JOIN guru g ON m.guru_id = g.id 
          WHERE m.kelas_id = ?";

$params = [$kelas_id];

if ($mapel_id) {
    $query .= " AND m.mapel_id = ?";
    $params[] = $mapel_id;
}

$query .= " ORDER BY m.created_at DESC";
$stmtData = $pdo->prepare($query);
$stmtData->execute($params);
$materiData = $stmtData->fetchAll();

// Daftar mapel yang ada materinya untuk kelas ini
$stmtMapel = $pdo->prepare("SELECT DISTINCT p.id, p.nama_mapel FROM materi m JOIN mata_pelajaran p ON m.mapel_id = p.id WHERE m.kelas_id = ? ORDER BY p.nama_mapel");
$stmtMapel->execute([$kelas_id]);
$mapelList = $stmtMapel->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-book me-2 text-green"></i>Materi Belajar</h4>
    </div>

    <!-- Filter Mapel -->
    <div class="mb-4">
        <div class="d-flex gap-2 overflow-auto py-2 px-1" style="white-space: nowrap;">
            <a href="/siswa/materi.php" class="btn <?= $mapel_id == '' ? 'btn-primary shadow-sm' : 'btn-outline-primary bg-white' ?> rounded-pill px-4">Semua Mapel</a>
            <?php foreach ($mapelList as $mp): ?>
                <a href="/siswa/materi.php?mapel=<?= $mp['id'] ?>" class="btn <?= $mapel_id == $mp['id'] ? 'btn-primary shadow-sm' : 'btn-outline-primary bg-white' ?> rounded-pill px-4">
                    <?= clean($mp['nama_mapel']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grid Materi -->
    <div class="row g-4">
        <?php if (empty($materiData)): ?>
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/3233/3233483.png" alt="Empty" width="120" class="mb-3 opacity-50">
                <h5 class="text-muted fw-bold">Tidak ada materi ditemukan.</h5>
                <p class="text-muted">Guru belum mengunggah materi untuk mata pelajaran ini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($materiData as $m): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 hover-lift rounded-4 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-soft-green text-green px-3 py-2 rounded-pill fs-12 fw-bold"><?= clean($m['nama_mapel']) ?></span>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i><?= formatTanggal(substr($m['created_at'], 0, 10), 'short') ?></small>
                        </div>
                        <h5 class="fw-bold text-dark mb-2"><?= clean($m['judul']) ?></h5>
                        <p class="text-muted fs-14 mb-4 line-clamp-3" style="min-height: 63px;"><?= strip_tags($m['deskripsi']) ?></p>
                        
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <?php if ($m['file_path']): ?>
                            <a href="/<?= $m['file_path'] ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill w-100 mb-2">
                                <i class="bi bi-file-earmark-pdf me-1"></i> Buka File Materi
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($m['link_youtube']): ?>
                            <a href="<?= clean($m['link_youtube']) ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill w-100">
                                <i class="bi bi-youtube me-1"></i> Tonton Video Penjelasan
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                <i class="bi bi-person"></i>
                            </div>
                            <small class="fw-500 text-dark"><?= clean($m['nama_guru']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
