<?php
$pageTitle = 'Manajemen Materi';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS
if ($action === 'delete' && $id) {
    // Ambil info file untuk dihapus
    $stmt = $pdo->prepare("SELECT file_path FROM materi WHERE id = ?");
    $stmt->execute([$id]);
    $materi = $stmt->fetch();
    
    if ($materi) {
        if ($materi['file_path'] && file_exists(__DIR__ . '/../' . $materi['file_path'])) {
            unlink(__DIR__ . '/../' . $materi['file_path']);
        }
        $pdo->prepare("DELETE FROM materi WHERE id = ?")->execute([$id]);
        setFlash('success', 'Materi beserta file lampirannya berhasil dihapus');
    }
    redirect('/admin/materi.php');
}

$kelas_id = $_GET['kelas'] ?? '';
$mapel_id = $_GET['mapel'] ?? '';

$query = "SELECT m.*, k.nama_kelas, p.nama_mapel, g.nama as nama_guru 
          FROM materi m 
          JOIN kelas k ON m.kelas_id = k.id 
          JOIN mata_pelajaran p ON m.mapel_id = p.id 
          JOIN guru g ON m.guru_id = g.id 
          WHERE 1=1";

$params = [];
if ($kelas_id) {
    $query .= " AND m.kelas_id = ?";
    $params[] = $kelas_id;
}
if ($mapel_id) {
    $query .= " AND m.mapel_id = ?";
    $params[] = $mapel_id;
}

$query .= " ORDER BY m.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$materi = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();
$mapelList = $pdo->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="fw-700 mb-0"><i class="bi bi-journal-text me-2 text-green"></i>Manajemen Materi Pembelajaran</h4>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Kelas</label>
                    <select class="form-select" name="kelas">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= clean($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mata Pelajaran</label>
                    <select class="form-select" name="mapel">
                        <option value="">Semua Mapel</option>
                        <?php foreach ($mapelList as $mp): ?>
                        <option value="<?= $mp['id'] ?>" <?= $mapel_id == $mp['id'] ? 'selected' : '' ?>><?= clean($mp['nama_mapel']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i>Filter</button>
                    <a href="/admin/materi.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul Materi</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Guru</th>
                            <th>File</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materi as $m): ?>
                        <tr>
                            <td><?= formatTanggal(substr($m['created_at'], 0, 10), 'short') ?></td>
                            <td class="fw-500"><?= clean($m['judul']) ?></td>
                            <td><?= clean($m['nama_kelas']) ?></td>
                            <td><?= clean($m['nama_mapel']) ?></td>
                            <td><?= clean($m['nama_guru']) ?></td>
                            <td>
                                <?php if ($m['file_path']): ?>
                                    <a href="/<?= $m['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="bi bi-download"></i> Unduh</a>
                                <?php else: ?>
                                    <span class="text-muted fs-13">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/admin/materi.php?action=delete&id=<?= $m['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($m['judul']) ?>">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
