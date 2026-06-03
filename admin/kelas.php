<?php
$pageTitle = 'Data Kelas';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

// Proses CRUD
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS
if ($action === 'delete' && $id) {
    // Cek apakah ada siswa di kelas ini
    $cek = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE kelas_id = ?");
    $cek->execute([$id]);
    if ($cek->fetchColumn() > 0) {
        setFlash('danger', 'Kelas tidak bisa dihapus karena masih memiliki siswa!');
    } else {
        $stmt = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Data kelas berhasil dihapus');
    }
    redirect('/admin/kelas.php');
}

// SIMPAN (Tambah / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($_POST['nama_kelas']);
    $tingkat = (int) $_POST['tingkat'];
    $wali = $_POST['wali_kelas'] ?: null;
    $editId = $_POST['id'] ?? 0;
    
    if ($editId) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE kelas SET nama_kelas=?, tingkat=?, wali_kelas=? WHERE id=?");
        $stmt->execute([$nama, $tingkat, $wali, $editId]);
        setFlash('success', 'Data kelas berhasil diupdate');
    } else {
        // INSERT
        $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas, tingkat, wali_kelas) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $tingkat, $wali]);
        setFlash('success', "Data kelas berhasil ditambahkan.");
    }
    redirect('/admin/kelas.php');
}

// Ambil semua data kelas beserta nama wali kelas dan jumlah siswa
$kelas = $pdo->query("
    SELECT k.*, g.nama as nama_wali, 
    (SELECT COUNT(*) FROM siswa WHERE kelas_id = k.id AND status='aktif') as jumlah_siswa 
    FROM kelas k 
    LEFT JOIN guru g ON k.wali_kelas = g.id 
    ORDER BY k.tingkat, k.nama_kelas
")->fetchAll();

// Ambil daftar guru untuk dropdown wali kelas
$guru = $pdo->query("SELECT id, nama FROM guru ORDER BY nama")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-building me-2 text-green"></i>Data Kelas</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKelas" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kelas
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Wali Kelas</th>
                            <th>Jumlah Siswa</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kelas as $i => $k): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-500"><?= clean($k['nama_kelas']) ?></td>
                            <td>Kelas <?= clean($k['tingkat']) ?></td>
                            <td><?= clean($k['nama_wali'] ?? '- Belum diset -') ?></td>
                            <td><span class="badge bg-info"><?= $k['jumlah_siswa'] ?> Siswa</span></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                   onclick="editKelas(<?= htmlspecialchars(json_encode($k)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/kelas.php?action=delete&id=<?= $k['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($k['nama_kelas']) ?>">
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

<!-- Modal Tambah/Edit Kelas -->
<div class="modal fade" id="modalKelas" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/kelas.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="modalTitle"><i class="bi bi-building me-2"></i>Tambah Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kelas" id="inputNama" placeholder="Contoh: Kelas 1A" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                        <select class="form-select" name="tingkat" id="inputTingkat" required>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>">Kelas <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wali Kelas</label>
                        <select class="form-select" name="wali_kelas" id="inputWali">
                            <option value="">-- Pilih Wali Kelas --</option>
                            <?php foreach ($guru as $g): ?>
                            <option value="<?= $g['id'] ?>"><?= clean($g['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
    document.getElementById('inputNama').value = '';
    document.getElementById('inputTingkat').value = '1';
    document.getElementById('inputWali').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-building me-2"></i>Tambah Kelas';
}

function editKelas(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('inputNama').value = data.nama_kelas;
    document.getElementById('inputTingkat').value = data.tingkat;
    document.getElementById('inputWali').value = data.wali_kelas || '';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Kelas';
    new bootstrap.Modal(document.getElementById('modalKelas')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
