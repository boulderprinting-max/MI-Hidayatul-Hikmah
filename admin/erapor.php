<?php
$pageTitle = 'Laporan E-Rapor';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$kelas_id = $_GET['kelas'] ?? '';
$tahun_ajaran = getTahunAjaranAktif($pdo);

$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();

$siswaList = [];
if ($kelas_id) {
    $stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.kelas_id = ? ORDER BY s.nama");
    $stmt->execute([$kelas_id]);
    $siswaList = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="fw-700 mb-0"><i class="bi bi-file-earmark-medical me-2 text-green"></i>Rekap E-Rapor</h4>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Pilih Kelas</label>
                    <select class="form-select" name="kelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $kelas_id == $k['id'] ? 'selected' : '' ?>><?= clean($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($kelas_id): ?>
    <div class="card">
        <div class="card-header bg-white">
            <h6 class="mb-0 fw-600">Daftar E-Rapor Kelas</h6>
        </div>
        <div class="card-body">
            <?php if (empty($siswaList)): ?>
                <div class="text-center py-4 text-muted">Belum ada siswa di kelas ini.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status E-Rapor</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($siswaList as $s): ?>
                            <tr>
                                <td><?= clean($s['nis']) ?></td>
                                <td class="fw-500"><?= clean($s['nama']) ?></td>
                                <td><?= clean($s['nama_kelas']) ?></td>
                                <td><span class="badge bg-secondary">Fitur Cetak Tersedia</span></td>
                                <td>
                                    <a href="/admin/cetak_rapor.php?siswa=<?= $s['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-printer"></i> Cetak Rapor
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
