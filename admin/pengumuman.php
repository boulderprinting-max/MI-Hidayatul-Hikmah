<?php
$pageTitle = 'Pengumuman';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS
if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM pengumuman WHERE id = ?")->execute([$id]);
    setFlash('success', 'Pengumuman berhasil dihapus');
    redirect('/admin/pengumuman.php');
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = clean($_POST['judul']);
    $isi = $_POST['isi']; // Tidak dibersihkan total karena butuh formatting line break
    $target = clean($_POST['target']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $editId = $_POST['id'] ?? 0;
    
    if ($editId) {
        $stmt = $pdo->prepare("UPDATE pengumuman SET judul=?, isi=?, target=?, is_published=? WHERE id=?");
        $stmt->execute([$judul, $isi, $target, $is_published, $editId]);
        setFlash('success', 'Pengumuman berhasil diupdate');
    } else {
        $stmt = $pdo->prepare("INSERT INTO pengumuman (judul, isi, target, is_published, author_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$judul, $isi, $target, $is_published, $_SESSION['user_id']]);
        setFlash('success', "Pengumuman berhasil ditambahkan.");
    }
    redirect('/admin/pengumuman.php');
}

$pengumuman = $pdo->query("SELECT p.*, u.nama as author FROM pengumuman p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.tanggal DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-megaphone me-2 text-green"></i>Pengumuman</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPengumuman" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i> Buat Pengumuman
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pengumuman as $p): ?>
                        <tr>
                            <td><?= formatTanggal($p['tanggal'], 'short') ?></td>
                            <td class="fw-500">
                                <?= clean($p['judul']) ?>
                                <div class="fs-13 text-muted">Oleh: <?= clean($p['author'] ?? 'Admin') ?></div>
                            </td>
                            <td>
                                <?php
                                $badge = 'secondary';
                                if ($p['target'] == 'semua') $badge = 'primary';
                                elseif ($p['target'] == 'guru') $badge = 'success';
                                elseif ($p['target'] == 'siswa') $badge = 'info';
                                elseif ($p['target'] == 'wali_murid') $badge = 'warning';
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= ucwords(str_replace('_', ' ', $p['target'])) ?></span>
                            </td>
                            <td>
                                <?= $p['is_published'] ? '<span class="badge bg-success">Published</span>' : '<span class="badge bg-secondary">Draft</span>' ?>
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                   onclick="editPengumuman(<?= htmlspecialchars(json_encode($p)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/pengumuman.php?action=delete&id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($p['judul']) ?>">
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

<div class="modal fade" id="modalPengumuman" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/admin/pengumuman.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="modalTitle"><i class="bi bi-megaphone me-2"></i>Buat Pengumuman</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Judul Pengumuman <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" id="inputJudul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi Pengumuman <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="isi" id="inputIsi" rows="6" required></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Target</label>
                            <select class="form-select" name="target" id="inputTarget">
                                <option value="semua">Semua (Guru, Siswa, Wali)</option>
                                <option value="guru">Hanya Guru</option>
                                <option value="siswa">Hanya Siswa</option>
                                <option value="wali_murid">Hanya Wali Murid</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" name="is_published" id="inputStatus" checked value="1">
                                <label class="form-check-label" for="inputStatus">Tampilkan (Publish)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('editId').value = '';
    document.getElementById('inputJudul').value = '';
    document.getElementById('inputIsi').value = '';
    document.getElementById('inputTarget').value = 'semua';
    document.getElementById('inputStatus').checked = true;
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-megaphone me-2"></i>Buat Pengumuman';
}

function editPengumuman(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('inputJudul').value = data.judul;
    document.getElementById('inputIsi').value = data.isi;
    document.getElementById('inputTarget').value = data.target;
    document.getElementById('inputStatus').checked = data.is_published == 1;
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Pengumuman';
    new bootstrap.Modal(document.getElementById('modalPengumuman')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
