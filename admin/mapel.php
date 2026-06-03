<?php
$pageTitle = 'Mata Pelajaran';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM mata_pelajaran WHERE id = ?")->execute([$id]);
    setFlash('success', 'Mata pelajaran berhasil dihapus');
    redirect('/admin/mapel.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = clean($_POST['kode']);
    $nama = clean($_POST['nama_mapel']);
    $editId = $_POST['id'] ?? 0;
    
    if ($editId) {
        $stmt = $pdo->prepare("UPDATE mata_pelajaran SET kode=?, nama_mapel=? WHERE id=?");
        $stmt->execute([$kode, $nama, $editId]);
        setFlash('success', 'Mata pelajaran berhasil diupdate');
    } else {
        $stmt = $pdo->prepare("INSERT INTO mata_pelajaran (kode, nama_mapel) VALUES (?, ?)");
        $stmt->execute([$kode, $nama]);
        setFlash('success', "Mata pelajaran berhasil ditambahkan.");
    }
    redirect('/admin/mapel.php');
}

$mapel = $pdo->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-book me-2 text-green"></i>Mata Pelajaran</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMapel" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Mapel
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Nama Mata Pelajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mapel as $i => $m): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><span class="badge bg-secondary"><?= clean($m['kode'] ?? '-') ?></span></td>
                            <td class="fw-500"><?= clean($m['nama_mapel']) ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                   onclick="editMapel(<?= htmlspecialchars(json_encode($m)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/mapel.php?action=delete&id=<?= $m['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($m['nama_mapel']) ?>">
                                    <i class="bi bi-trash"></i>
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

<div class="modal fade" id="modalMapel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/mapel.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="modalTitle"><i class="bi bi-book me-2"></i>Tambah Mapel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Kode Mapel</label>
                        <input type="text" class="form-control" name="kode" id="inputKode" placeholder="Contoh: PAI">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_mapel" id="inputNama" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('editId').value = '';
    document.getElementById('inputKode').value = '';
    document.getElementById('inputNama').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-book me-2"></i>Tambah Mapel';
}

function editMapel(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('inputKode').value = data.kode || '';
    document.getElementById('inputNama').value = data.nama_mapel;
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Mapel';
    new bootstrap.Modal(document.getElementById('modalMapel')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
