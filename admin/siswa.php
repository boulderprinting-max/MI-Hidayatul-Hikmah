<?php
$pageTitle = 'Data Siswa';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

// Proses CRUD
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
    $stmt->execute([$id]);
    // Hapus juga user terkait
    $pdo->prepare("DELETE FROM users WHERE id = (SELECT user_id FROM siswa WHERE id = ?)")->execute([$id]);
    setFlash('success', 'Data siswa berhasil dihapus');
    redirect('/admin/siswa.php');
}

// SIMPAN (Tambah / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = clean($_POST['nis']);
    $nama = clean($_POST['nama']);
    $jk = clean($_POST['jenis_kelamin']);
    $kelas = $_POST['kelas_id'] ?: null;
    $nama_wali = clean($_POST['nama_wali']);
    $editId = $_POST['id'] ?? 0;
    
    if ($editId) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE siswa SET nis=?, nama=?, jenis_kelamin=?, kelas_id=?, nama_wali=? WHERE id=?");
        $stmt->execute([$nis, $nama, $jk, $kelas, $nama_wali, $editId]);
        
        // Update user
        $s = $pdo->prepare("SELECT user_id FROM siswa WHERE id = ?");
        $s->execute([$editId]);
        $sData = $s->fetch();
        if ($sData && $sData['user_id']) {
            $pdo->prepare("UPDATE users SET nama=?, username=? WHERE id=?")->execute([$nama, $nis, $sData['user_id']]);
        }
        setFlash('success', 'Data siswa berhasil diupdate');
    } else {
        // INSERT
        // Cek NIS unik
        $cek = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE nis = ?");
        $cek->execute([$nis]);
        if ($cek->fetchColumn() > 0) {
            setFlash('danger', 'NIS sudah terdaftar!');
            redirect('/admin/siswa.php');
        }

        // Buat user untuk siswa (password default = nis)
        $password = password_hash($nis, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role, status) VALUES (?, ?, ?, 'siswa', 'aktif')");
        $stmt->execute([$nama, $nis, $password]);
        $userId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, nama_wali) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $nis, $nama, $jk, $kelas, $nama_wali]);

        // Buat user untuk wali murid (password default = nis)
        $usernameWali = 'wali_' . $nis;
        $stmtWali = $pdo->prepare("INSERT INTO users (nama, username, password, role, status) VALUES (?, ?, ?, 'wali_murid', 'aktif')");
        $stmtWali->execute(['Wali dari ' . $nama, $usernameWali, $password]);

        setFlash('success', "Siswa berhasil ditambah.<br>Akun Siswa: <b>$nis</b><br>Akun Wali: <b>$usernameWali</b><br>Password: <b>$nis</b>");
    }
    redirect('/admin/siswa.php');
}

// Ambil data siswa
$filterKelas = $_GET['kelas'] ?? '';
$query = "SELECT s.*, k.nama_kelas, u.username FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id LEFT JOIN users u ON s.user_id = u.id";
if ($filterKelas) {
    $query .= " WHERE s.kelas_id = " . (int)$filterKelas;
}
$query .= " ORDER BY k.tingkat, s.nama";
$siswa = $pdo->query($query)->fetchAll();

// Daftar kelas untuk dropdown
$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="fw-700 mb-0"><i class="bi bi-people me-2 text-green"></i>Data Siswa</h4>
        
        <div class="d-flex gap-2">
            <form class="d-flex gap-2" method="GET">
                <select name="kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $filterKelas == $k['id'] ? 'selected' : '' ?>><?= clean($k['nama_kelas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <a href="/admin/import_siswa.php" class="btn btn-sm btn-outline-success" title="Import dari Excel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Import Excel
            </a>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSiswa" onclick="resetForm()">
                <i class="bi bi-plus-lg"></i> Tambah
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>L/P</th>
                            <th>Kelas</th>
                            <th>Nama Wali</th>
                            <th>Akun Login</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswa as $s): ?>
                        <tr>
                            <td><?= clean($s['nis']) ?></td>
                            <td class="fw-500"><?= clean($s['nama']) ?></td>
                            <td><?= $s['jenis_kelamin'] ?></td>
                            <td><?= clean($s['nama_kelas'] ?? '-') ?></td>
                            <td><?= clean($s['nama_wali'] ?? '-') ?></td>
                            <td><code><?= clean($s['username'] ?? '-') ?></code></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                   onclick="editSiswa(<?= htmlspecialchars(json_encode($s)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/siswa.php?action=delete&id=<?= $s['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($s['nama']) ?>">
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

<!-- Modal Tambah/Edit Siswa -->
<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/siswa.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="modalTitle">Tambah Siswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nis" id="inputNis" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="inputNama" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin" id="inputJk">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kelas</label>
                            <select class="form-select" name="kelas_id" id="inputKelas">
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= clean($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Wali Murid</label>
                        <input type="text" class="form-control" name="nama_wali" id="inputWali">
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
    document.getElementById('inputNis').value = '';
    document.getElementById('inputNis').readOnly = false;
    document.getElementById('inputNama').value = '';
    document.getElementById('inputJk').value = 'L';
    document.getElementById('inputKelas').value = '';
    document.getElementById('inputWali').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Tambah Siswa';
}

function editSiswa(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('inputNis').value = data.nis;
    document.getElementById('inputNis').readOnly = true; // NIS ga boleh diubah sembarangan
    document.getElementById('inputNama').value = data.nama;
    document.getElementById('inputJk').value = data.jenis_kelamin;
    document.getElementById('inputKelas').value = data.kelas_id || '';
    document.getElementById('inputWali').value = data.nama_wali || '';
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Siswa';
    new bootstrap.Modal(document.getElementById('modalSiswa')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
