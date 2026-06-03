<?php
$pageTitle = 'Rekap Nilai';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');

$kelas_id = $_GET['kelas'] ?? '';
$mapel_id = $_GET['mapel'] ?? '';
$tahun_ajaran = getTahunAjaranAktif($pdo);

$query = "SELECT n.*, s.nama as nama_siswa, s.nis, t.judul as judul_tugas 
          FROM nilai n 
          JOIN siswa s ON n.siswa_id = s.id 
          JOIN tugas t ON n.tugas_id = t.id 
          WHERE t.tahun_ajaran_id = ?";
          
$params = [$tahun_ajaran['id']];

if ($kelas_id) {
    $query .= " AND t.kelas_id = ?";
    $params[] = $kelas_id;
}
if ($mapel_id) {
    $query .= " AND t.mapel_id = ?";
    $params[] = $mapel_id;
}

$query .= " ORDER BY s.nama, t.judul";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$nilaiData = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();
$mapelList = $pdo->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <h4 class="fw-700 mb-0"><i class="bi bi-award me-2 text-green"></i>Rekap Nilai Siswa</h4>
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
                    <a href="/admin/nilai.php" class="btn btn-secondary">Reset</a>
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
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Judul Tugas</th>
                            <th>Nilai</th>
                            <th>Catatan Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nilaiData as $n): ?>
                        <tr>
                            <td><?= clean($n['nis']) ?></td>
                            <td class="fw-500"><?= clean($n['nama_siswa']) ?></td>
                            <td><?= clean($n['judul_tugas']) ?></td>
                            <td>
                                <?php
                                $badge = 'success';
                                if ($n['nilai'] < 75) $badge = 'danger'; // KKM standar 75
                                ?>
                                <span class="badge bg-<?= $badge ?> fs-6"><?= clean($n['nilai']) ?></span>
                            </td>
                            <td><?= clean($n['catatan'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
