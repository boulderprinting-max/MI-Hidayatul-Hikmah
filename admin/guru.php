<?php
$pageTitle = 'Data Guru';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

// Proses CRUD
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM guru WHERE id = ?");
    $stmt->execute([$id]);
    // Hapus juga user terkait
    $pdo->prepare("DELETE FROM users WHERE id = (SELECT user_id FROM guru WHERE id = ?)")->execute([$id]);
    setFlash('success', 'Data guru berhasil dihapus');
    redirect('/admin/guru.php');
}

// SIMPAN (Tambah / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean($_POST['nama']);
    $nip = clean($_POST['nip']);
    $jk = clean($_POST['jenis_kelamin']);
    $alamat = clean($_POST['alamat']);
    $no_hp = clean($_POST['no_hp']);
    $kelas = clean($_POST['kelas']);
    $editId = $_POST['id'] ?? 0;
    
    if ($editId) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE guru SET nama=?, nip=?, jenis_kelamin=?, alamat=?, no_hp=?, kelas=? WHERE id=?");
        $stmt->execute([$nama, $nip, $jk, $alamat, $no_hp, $kelas, $editId]);
        // Update nama di users juga
        $guru = $pdo->prepare("SELECT user_id FROM guru WHERE id = ?")->execute([$editId]);
        $g = $pdo->prepare("SELECT user_id FROM guru WHERE id = ?");
        $g->execute([$editId]);
        $gData = $g->fetch();
        if ($gData && $gData['user_id']) {
            $pdo->prepare("UPDATE users SET nama=? WHERE id=?")->execute([$nama, $gData['user_id']]);
        }
        setFlash('success', 'Data guru berhasil diupdate');
    } else {
        // INSERT - buat user dulu
        $username = strtolower(str_replace(' ', '', $nama)) . rand(10,99);
        $password = password_hash('guru123', PASSWORD_DEFAULT);
        $roleGuru = 'guru_kelas_' . ($kelas ?: '1');
        
        $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role, status) VALUES (?, ?, ?, ?, 'aktif')");
        $stmt->execute([$nama, $username, $password, $roleGuru]);
        $userId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO guru (user_id, nama, nip, jenis_kelamin, alamat, no_hp, kelas) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $nama, $nip, $jk, $alamat, $no_hp, $kelas]);
        setFlash('success', "Data guru berhasil ditambahkan. Username: <strong>$username</strong>, Password: <strong>guru123</strong>");
    }
    redirect('/admin/guru.php');
}

// Ambil semua data guru
$guru = $pdo->query("SELECT g.*, u.username, u.status as user_status FROM guru g LEFT JOIN users u ON g.user_id = u.id ORDER BY g.nama")->fetchAll();

// Jika mode edit, ambil data
$editData = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM guru WHERE id = ?");
    $stmt->execute([$id]);
    $editData = $stmt->fetch();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-person-badge me-2 text-green"></i>Data Guru</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalGuru" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i> Tambah Guru
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>L/P</th>
                            <th>No. HP</th>
                            <th>Kelas</th>
                            <th>Username</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guru as $i => $g): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-500"><?= clean($g['nama']) ?></td>
                            <td><?= clean($g['nip'] ?: '-') ?></td>
                            <td><span class="badge bg-<?= $g['jenis_kelamin'] === 'L' ? 'primary' : 'danger' ?>"><?= $g['jenis_kelamin'] ?></span></td>
                            <td><?= clean($g['no_hp'] ?: '-') ?></td>
                            <td><?= $g['kelas'] ? 'Kelas ' . $g['kelas'] : '-' ?></td>
                            <td><code><?= clean($g['username'] ?? '-') ?></code></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                   onclick="editGuru(<?= htmlspecialchars(json_encode($g)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/guru.php?action=delete&id=<?= $g['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($g['nama']) ?>">
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

<!-- Modal Tambah/Edit Guru -->
<div class="modal fade" id="modalGuru" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/guru.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="modalTitle"><i class="bi bi-person-badge me-2"></i>Tambah Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" id="inputNama" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NIP</label>
                            <input type="text" class="form-control" name="nip" id="inputNip">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin" id="inputJk">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP</label>
                        <input type="text" class="form-control" name="no_hp" id="inputHp">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wali Kelas</label>
                        <select class="form-select" name="kelas" id="inputKelas">
                            <option value="">-- Tidak ada --</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>">Kelas <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="alamat" id="inputAlamat" rows="2"></textarea>
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
    document.getElementById('inputNip').value = '';
    document.getElementById('inputJk').value = 'L';
    document.getElementById('inputHp').value = '';
    document.getElementById('inputKelas').value = '';
    document.getElementById('inputAlamat').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-person-badge me-2"></i>Tambah Guru';
}

function editGuru(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('inputNama').value = data.nama;
    document.getElementById('inputNip').value = data.nip || '';
    document.getElementById('inputJk').value = data.jenis_kelamin;
    document.getElementById('inputHp').value = data.no_hp || '';
    document.getElementById('inputKelas').value = data.kelas || '';
    document.getElementById('inputAlamat').value = data.alamat || '';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Guru';
    new bootstrap.Modal(document.getElementById('modalGuru')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
