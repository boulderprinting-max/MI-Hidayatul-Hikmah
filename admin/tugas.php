<?php
$pageTitle = 'Manajemen Tugas';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS
if ($action === 'delete' && $id) {
    // Ambil info file untuk dihapus
    $stmt = $pdo->prepare("SELECT file_path FROM tugas WHERE id = ?");
    $stmt->execute([$id]);
    $tugas = $stmt->fetch();
    
    if ($tugas) {
        if ($tugas['file_path'] && file_exists(__DIR__ . '/../' . $tugas['file_path'])) {
            unlink(__DIR__ . '/../' . $tugas['file_path']);
        }
        
        // Hapus pengumpulan tugas terkait file-filenya juga
        $pengumpulan = $pdo->prepare("SELECT file_path FROM pengumpulan_tugas WHERE tugas_id = ?");
        $pengumpulan->execute([$id]);
        while($p = $pengumpulan->fetch()) {
            if ($p['file_path'] && file_exists(__DIR__ . '/../' . $p['file_path'])) {
                unlink(__DIR__ . '/../' . $p['file_path']);
            }
        }
        $pdo->prepare("DELETE FROM pengumpulan_tugas WHERE tugas_id = ?")->execute([$id]);
        
        // Hapus tugas
        $pdo->prepare("DELETE FROM tugas WHERE id = ?")->execute([$id]);
        setFlash('success', 'Tugas beserta file lampirannya berhasil dihapus');
    }
    redirect('/admin/tugas.php');
}

$kelas_id = $_GET['kelas'] ?? '';
$mapel_id = $_GET['mapel'] ?? '';

$query = "SELECT t.*, k.nama_kelas, p.nama_mapel, g.nama as nama_guru,
          (SELECT COUNT(*) FROM pengumpulan_tugas WHERE tugas_id = t.id) as jml_terkumpul
          FROM tugas t 
          JOIN kelas k ON t.kelas_id = k.id 
          JOIN mata_pelajaran p ON t.mapel_id = p.id 
          JOIN guru g ON t.guru_id = g.id 
          WHERE 1=1";

$params = [];
if ($kelas_id) {
    $query .= " AND t.kelas_id = ?";
    $params[] = $kelas_id;
}
if ($mapel_id) {
    $query .= " AND t.mapel_id = ?";
    $params[] = $mapel_id;
}

$query .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tugasData = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();
$mapelList = $pdo->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="fw-700 mb-0"><i class="bi bi-journal-check me-2 text-green"></i>Manajemen Tugas Pembelajaran</h4>
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
                    <a href="/admin/tugas.php" class="btn btn-secondary">Reset</a>
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
                            <th>Judul Tugas</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Guru</th>
                            <th>Batas Waktu</th>
                            <th>Status</th>
                            <th>Terkumpul</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tugasData as $t): ?>
                        <tr>
                            <td class="fw-500"><?= clean($t['judul']) ?></td>
                            <td><?= clean($t['nama_kelas']) ?></td>
                            <td><?= clean($t['nama_mapel']) ?></td>
                            <td><?= clean($t['nama_guru']) ?></td>
                            <td>
                                <?php if ($t['tenggat_waktu']): ?>
                                    <?= date('d/m/Y H:i', strtotime($t['tenggat_waktu'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($t['status'] == 'aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-info"><?= $t['jml_terkumpul'] ?> Siswa</span></td>
                            <td>
                                <a href="/admin/tugas.php?action=delete&id=<?= $t['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($t['judul']) ?>">
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
