<?php
$pageTitle = 'Tahun Ajaran';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// SET AKTIF
if ($action === 'set_aktif' && $id) {
    // Nonaktifkan semua dulu
    $pdo->query("UPDATE tahun_ajaran SET status = 'nonaktif'");
    // Aktifkan yang dipilih
    $stmt = $pdo->prepare("UPDATE tahun_ajaran SET status = 'aktif' WHERE id = ?");
    $stmt->execute([$id]);
    setFlash('success', 'Tahun ajaran berhasil diaktifkan');
    redirect('/admin/tahun_ajaran.php');
}

// HAPUS
if ($action === 'delete' && $id) {
    // Cek apakah ini tahun ajaran aktif
    $cek = $pdo->prepare("SELECT status FROM tahun_ajaran WHERE id = ?");
    $cek->execute([$id]);
    if ($cek->fetchColumn() === 'aktif') {
        setFlash('danger', 'Tahun ajaran yang sedang aktif tidak bisa dihapus!');
    } else {
        $pdo->prepare("DELETE FROM tahun_ajaran WHERE id = ?")->execute([$id]);
        setFlash('success', 'Tahun ajaran berhasil dihapus');
    }
    redirect('/admin/tahun_ajaran.php');
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($_POST['nama_tahun']);
    $semester = clean($_POST['semester']);
    $editId = $_POST['id'] ?? 0;
    
    if ($editId) {
        $stmt = $pdo->prepare("UPDATE tahun_ajaran SET nama_tahun=?, semester=? WHERE id=?");
        $stmt->execute([$nama, $semester, $editId]);
        setFlash('success', 'Data berhasil diupdate');
    } else {
        $stmt = $pdo->prepare("INSERT INTO tahun_ajaran (nama_tahun, semester, status) VALUES (?, ?, 'nonaktif')");
        $stmt->execute([$nama, $semester]);
        setFlash('success', "Tahun ajaran berhasil ditambahkan.");
    }
    redirect('/admin/tahun_ajaran.php');
}

$ta = $pdo->query("SELECT * FROM tahun_ajaran ORDER BY id DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-calendar-range me-2 text-green"></i>Tahun Ajaran</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTa" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Baru
        </button>
    </div>

    <div class="alert alert-info border-0 d-flex align-items-center">
        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
        <div>Hanya boleh ada <strong>1 (satu)</strong> tahun ajaran yang berstatus Aktif. Tahun ajaran yang aktif akan digunakan sebagai acuan untuk presensi, materi, tugas, dan nilai.</div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tahun Ajaran</th>
                            <th>Semester</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ta as $i => $t): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-600"><?= clean($t['nama_tahun']) ?></td>
                            <td>Semester <?= ucfirst($t['semester']) ?></td>
                            <td>
                                <?php if ($t['status'] === 'aktif'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                <?php else: ?>
                                    <a href="/admin/tahun_ajaran.php?action=set_aktif&id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-success">Set Aktif</a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                   onclick="editTa(<?= htmlspecialchars(json_encode($t)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($t['status'] !== 'aktif'): ?>
                                <a href="/admin/tahun_ajaran.php?action=delete&id=<?= $t['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($t['nama_tahun']) ?>">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/tahun_ajaran.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="modalTitle">Tambah Tahun Ajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_tahun" id="inputNama" placeholder="Contoh: 2024/2025" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" name="semester" id="inputSemester" required>
                            <option value="ganjil">Ganjil</option>
                            <option value="genap">Genap</option>
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
    document.getElementById('inputSemester').value = 'ganjil';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-calendar-range me-2"></i>Tambah Tahun Ajaran';
}

function editTa(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('inputNama').value = data.nama_tahun;
    document.getElementById('inputSemester').value = data.semester;
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Tahun Ajaran';
    new bootstrap.Modal(document.getElementById('modalTa')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
