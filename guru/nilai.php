<?php
$pageTitle = 'Rekap Penilaian';
require_once __DIR__ . '/../includes/auth.php';

if (strpos($_SESSION['role'], 'guru_kelas_') !== 0) {
    redirect('/index.php');
}

$tahun_ajaran = getTahunAjaranAktif($pdo);

// Ambil info guru
$stmt = $pdo->prepare("SELECT id FROM guru WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$guru = $stmt->fetch();
$guru_id = $guru['id'] ?? 0;

$kelas_id = $_GET['kelas'] ?? '';
$mapel_id = $_GET['mapel'] ?? '';

$query = "SELECT n.*, s.nama as nama_siswa, s.nis, t.judul as judul_tugas, 
          k.nama_kelas, p.nama_mapel 
          FROM nilai n 
          JOIN siswa s ON n.siswa_id = s.id 
          JOIN tugas t ON n.tugas_id = t.id 
          JOIN kelas k ON t.kelas_id = k.id 
          JOIN mata_pelajaran p ON t.mapel_id = p.id 
          WHERE t.guru_id = ? AND t.tahun_ajaran_id = ?";

$params = [$guru_id, $tahun_ajaran['id']];

if ($kelas_id) {
    $query .= " AND t.kelas_id = ?";
    $params[] = $kelas_id;
}
if ($mapel_id) {
    $query .= " AND t.mapel_id = ?";
    $params[] = $mapel_id;
}

$query .= " ORDER BY t.created_at DESC, s.nama ASC";
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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-award me-2 text-green"></i>Rekap Penilaian</h4>
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
                    <a href="/guru/nilai.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($nilaiData)): ?>
                <div class="text-center py-5">
                    <h5 class="text-muted">Belum ada data nilai yang sesuai.</h5>
                    <p class="text-muted">Pastikan Anda telah memberikan nilai pada tugas siswa.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table data-table align-middle">
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Tugas</th>
                                <th>Kelas / Mapel</th>
                                <th>Nilai</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nilaiData as $n): ?>
                            <tr>
                                <td>
                                    <div class="fw-500 text-dark"><?= clean($n['nama_siswa']) ?></div>
                                    <div class="fs-12 text-muted"><?= clean($n['nis']) ?></div>
                                </td>
                                <td><?= clean($n['judul_tugas']) ?></td>
                                <td>
                                    <div class="fs-14"><?= clean($n['nama_kelas']) ?></div>
                                    <div class="fs-12 text-muted"><?= clean($n['nama_mapel']) ?></div>
                                </td>
                                <td>
                                    <?php
                                    $badge = 'success';
                                    if ($n['nilai'] < 75) $badge = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $badge ?> fs-6"><?= clean($n['nilai']) ?></span>
                                </td>
                                <td><span class="fs-13"><?= clean($n['catatan'] ?? '-') ?></span></td>
                                <td>
                                    <a href="/guru/tugas_detail.php?id=<?= $n['tugas_id'] ?>" class="btn btn-sm btn-outline-primary">
                                        Edit Nilai
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
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
