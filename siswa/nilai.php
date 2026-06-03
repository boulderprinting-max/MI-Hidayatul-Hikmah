<?php
$pageTitle = 'Transkrip Nilai';
require_once __DIR__ . '/../includes/auth.php';
requireRole('siswa');

$tahun_ajaran = getTahunAjaranAktif($pdo);

$stmt = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$siswa_id = $stmt->fetchColumn();

// Ambil semua mapel yang ada tugasnya di kelas dia
$queryMapel = "SELECT DISTINCT p.id, p.nama_mapel 
               FROM tugas t 
               JOIN mata_pelajaran p ON t.mapel_id = p.id 
               WHERE t.tahun_ajaran_id = ? 
               ORDER BY p.nama_mapel";
$stmtMapel = $pdo->prepare($queryMapel);
$stmtMapel->execute([$tahun_ajaran['id']]);
$mapelList = $stmtMapel->fetchAll();

// Ambil semua nilai dia di tahun ajaran aktif
$queryNilai = "SELECT n.*, t.judul, p.id as mapel_id 
               FROM nilai n 
               JOIN tugas t ON n.tugas_id = t.id 
               JOIN mata_pelajaran p ON t.mapel_id = p.id 
               WHERE n.siswa_id = ? AND t.tahun_ajaran_id = ? 
               ORDER BY t.created_at ASC";
$stmtNilai = $pdo->prepare($queryNilai);
$stmtNilai->execute([$siswa_id, $tahun_ajaran['id']]);
$semuaNilai = $stmtNilai->fetchAll();

// Kelompokkan nilai berdasarkan mapel
$nilaiPerMapel = [];
foreach ($semuaNilai as $n) {
    $nilaiPerMapel[$n['mapel_id']][] = $n;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-award me-2 text-green"></i>Transkrip Nilai Siswa</h4>
    </div>

    <div class="alert alert-info border-0 mb-4 rounded-4 shadow-sm">
        <i class="bi bi-info-circle-fill me-2"></i> Menampilkan nilai tugas harian pada Tahun Ajaran: <strong><?= clean($tahun_ajaran['nama_tahun']) ?></strong>
    </div>

    <?php if (empty($mapelList)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5">
                <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">Belum ada data nilai.</h5>
                <p class="text-muted">Kamu belum memiliki nilai dari tugas manapun.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="accordion shadow-sm rounded-4 border-0" id="accordionNilai">
            <?php foreach ($mapelList as $index => $mp): 
                $listNilai = $nilaiPerMapel[$mp['id']] ?? [];
                $rata = 0;
                if (count($listNilai) > 0) {
                    $total = array_sum(array_column($listNilai, 'nilai'));
                    $rata = round($total / count($listNilai));
                }
            ?>
            <div class="accordion-item border-0 mb-3 rounded-4 bg-white overflow-hidden">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?> bg-white fw-bold fs-5 text-dark p-4 border-bottom" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $mp['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                            <span><i class="bi bi-book text-primary me-2"></i><?= clean($mp['nama_mapel']) ?></span>
                            <?php if ($rata > 0): ?>
                                <span class="badge <?= $rata >= 75 ? 'bg-success' : 'bg-danger' ?> rounded-pill fs-14">Rata-rata: <?= $rata ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill fs-14">Belum ada nilai</span>
                            <?php endif; ?>
                        </div>
                    </button>
                </h2>
                <div id="collapse<?= $mp['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#accordionNilai">
                    <div class="accordion-body p-0">
                        <?php if (empty($listNilai)): ?>
                            <div class="text-center p-4 text-muted fst-italic">Belum ada nilai tugas untuk mata pelajaran ini.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Judul Tugas</th>
                                            <th class="text-center">Nilai</th>
                                            <th>Catatan Guru</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($listNilai as $n): ?>
                                        <tr>
                                            <td class="ps-4 fw-500 text-dark"><?= clean($n['judul']) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $n['nilai'] >= 75 ? 'bg-primary' : 'bg-danger' ?> fs-6 px-3 rounded-pill"><?= clean($n['nilai']) ?></span>
                                            </td>
                                            <td class="text-muted fs-14"><?= clean($n['catatan'] ?: '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
