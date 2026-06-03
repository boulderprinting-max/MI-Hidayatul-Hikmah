<?php
$pageTitle = 'Kelola Pengguna';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS
if ($action === 'delete' && $id) {
    // Jangan hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        setFlash('danger', 'Anda tidak dapat menghapus akun Anda sendiri!');
    } else {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        setFlash('success', 'Akun berhasil dihapus');
    }
    redirect('/admin/users.php');
}

// SIMPAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editId = $_POST['id'] ?? 0;
    
    if ($editId) {
        $status = $_POST['status'];
        $password = $_POST['password'];
        
        if (!empty($password)) {
            // Update password & status
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET status=?, password=? WHERE id=?");
            $stmt->execute([$status, $hash, $editId]);
            setFlash('success', 'Status dan Password berhasil diupdate');
        } else {
            // Update status saja
            $stmt = $pdo->prepare("UPDATE users SET status=? WHERE id=?");
            $stmt->execute([$status, $editId]);
            setFlash('success', 'Status akun berhasil diupdate');
        }
    }
    redirect('/admin/users.php');
}

$roleFilter = $_GET['role'] ?? '';
$query = "SELECT * FROM users";
if ($roleFilter) {
    $query .= " WHERE role = " . $pdo->quote($roleFilter);
}
$query .= " ORDER BY role, nama";
$users = $pdo->query($query)->fetchAll();

// Daftar roles untuk filter
$roles = $pdo->query("SELECT DISTINCT role FROM users ORDER BY role")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="fw-700 mb-0"><i class="bi bi-shield-lock me-2 text-green"></i>Kelola Pengguna</h4>
        
        <form class="d-flex gap-2" method="GET">
            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r ?>" <?= $roleFilter == $r ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $r)) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-500"><?= clean($u['nama']) ?></td>
                            <td><code><?= clean($u['username']) ?></code></td>
                            <td><span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $u['role'])) ?></span></td>
                            <td>
                                <?php if ($u['status'] === 'aktif'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="btn btn-sm btn-outline-primary me-1" 
                                   onclick="editUser(<?= htmlspecialchars(json_encode([
                                       'id' => $u['id'], 
                                       'nama' => $u['nama'], 
                                       'username' => $u['username'],
                                       'status' => $u['status']
                                   ])) ?>)">
                                    <i class="bi bi-gear"></i>
                                </a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="/admin/users.php?action=delete&id=<?= $u['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger btn-delete" data-nama="<?= clean($u['nama']) ?>">
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

<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/admin/users.php">
                <div class="modal-header">
                    <h5 class="modal-title fw-600" id="modalTitle"><i class="bi bi-gear me-2"></i>Pengaturan Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label">Nama Pengguna</label>
                        <input type="text" class="form-control" id="infoNama" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" id="infoUsername" readonly disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Akun</label>
                        <select class="form-select" name="status" id="inputStatus">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Reset Password <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" class="form-control" name="password" placeholder="Masukkan password baru">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('infoNama').value = data.nama;
    document.getElementById('infoUsername').value = data.username;
    document.getElementById('inputStatus').value = data.status;
    new bootstrap.Modal(document.getElementById('modalUser')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
