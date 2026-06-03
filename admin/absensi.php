<?php
$pageTitle = 'Rekap Absensi';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$kelas_id = $_GET['kelas'] ?? '';

$query = "SELECT a.*, s.nama as nama_siswa, s.nis, k.nama_kelas 
          FROM absensi a 
          JOIN siswa s ON a.siswa_id = s.id 
          JOIN kelas k ON s.kelas_id = k.id 
          WHERE a.tanggal = ?";

$params = [$tanggal];

if ($kelas_id) {
    $query .= " AND s.kelas_id = ?";
    $params[] = $kelas_id;
}

$query .= " ORDER BY k.tingkat, s.nama";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$absensi = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="fw-700 mb-0"><i class="bi bi-clipboard-check me-2 text-green"></i>Rekap Absensi Siswa</h4>
    </div>

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" value="<?= $tanggal ?>">
                </div>
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
                    <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i>Filter</button>
                    <a href="/admin/absensi.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Absensi -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($absensi)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-muted">Belum ada data absensi untuk filter ini.</h5>
                    <p class="text-muted">Guru wali kelas biasanya melakukan presensi pada jam pelajaran.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($absensi as $i => $a): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= clean($a['nis']) ?></td>
                                <td class="fw-500"><?= clean($a['nama_siswa']) ?></td>
                                <td><?= clean($a['nama_kelas']) ?></td>
                                <td>
                                    <?php
                                    $badge = 'secondary';
                                    if ($a['status'] == 'hadir') $badge = 'success';
                                    elseif ($a['status'] == 'sakit') $badge = 'warning';
                                    elseif ($a['status'] == 'izin') $badge = 'info';
                                    elseif ($a['status'] == 'alfa') $badge = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                                </td>
                                <td><?= clean($a['keterangan'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
