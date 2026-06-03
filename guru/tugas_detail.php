<?php
$pageTitle = 'Detail Tugas & Penilaian';
require_once __DIR__ . '/../includes/auth.php';

if (strpos($_SESSION['role'], 'guru_kelas_') !== 0) {
    redirect('/index.php');
}

$id = $_GET['id'] ?? 0;
if (!$id) redirect('/guru/tugas.php');

$stmtTugas = $pdo->prepare("SELECT t.*, k.nama_kelas, p.nama_mapel 
                            FROM tugas t 
                            JOIN kelas k ON t.kelas_id = k.id 
                            JOIN mata_pelajaran p ON t.mapel_id = p.id 
                            WHERE t.id = ?");
$stmtTugas->execute([$id]);
$tugas = $stmtTugas->fetch();

if (!$tugas) {
    setFlash('danger', 'Tugas tidak ditemukan.');
    redirect('/guru/tugas.php');
}

// Proses Penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['siswa_id'])) {
    $siswa_id = (int)$_POST['siswa_id'];
    $nilai = (int)$_POST['nilai'];
    $catatan = clean($_POST['catatan']);
    
    // Cek apakah sudah ada nilai
    $cek = $pdo->prepare("SELECT id FROM nilai WHERE tugas_id = ? AND siswa_id = ?");
    $cek->execute([$id, $siswa_id]);
    $existing = $cek->fetch();
    
    if ($existing) {
        $pdo->prepare("UPDATE nilai SET nilai = ?, catatan = ? WHERE id = ?")
            ->execute([$nilai, $catatan, $existing['id']]);
    } else {
        $pdo->prepare("INSERT INTO nilai (siswa_id, tugas_id, nilai, catatan) VALUES (?, ?, ?, ?)")
            ->execute([$siswa_id, $id, $nilai, $catatan]);
    }
    
    setFlash('success', 'Nilai berhasil disimpan.');
    redirect('/guru/tugas_detail.php?id=' . $id);
}

// Ambil daftar siswa di kelas tersebut beserta status pengumpulan dan nilai
$querySiswa = "SELECT s.id, s.nis, s.nama, 
               pt.id as pengumpulan_id, pt.file_path, pt.waktu_kumpul, pt.status as status_kumpul,
               n.nilai, n.catatan
               FROM siswa s 
               LEFT JOIN pengumpulan_tugas pt ON s.id = pt.siswa_id AND pt.tugas_id = ?
               LEFT JOIN nilai n ON s.id = n.siswa_id AND n.tugas_id = ?
               WHERE s.kelas_id = ? AND s.status = 'aktif'
               ORDER BY s.nama";
$stmtSiswa = $pdo->prepare($querySiswa);
$stmtSiswa->execute([$id, $id, $tugas['kelas_id']]);
$siswaList = $stmtSiswa->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="/guru/tugas.php" class="btn btn-outline-secondary rounded-circle px-2 py-1"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-700 mb-0">Detail Tugas & Penilaian</h4>
    </div>

    <!-- Info Tugas -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5 class="fw-bold text-dark mb-1"><?= clean($tugas['judul']) ?></h5>
                    <div class="mb-3">
                        <span class="badge bg-soft-green text-green me-1"><?= clean($tugas['nama_mapel']) ?></span>
                        <span class="badge bg-light text-dark border me-1"><?= clean($tugas['nama_kelas']) ?></span>
                        <?php if ($tugas['tenggat_waktu']): ?>
                            <span class="badge <?= strtotime($tugas['tenggat_waktu']) < time() ? 'bg-danger' : 'bg-warning text-dark' ?>">
                                Tenggat: <?= date('d M Y H:i', strtotime($tugas['tenggat_waktu'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="text-muted fs-14 bg-light p-3 rounded mb-3" style="white-space: pre-wrap;"><?= clean($tugas['deskripsi']) ?></div>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if ($tugas['file_path']): ?>
                    <a href="/<?= $tugas['file_path'] ?>" target="_blank" class="btn btn-outline-primary mb-2 w-100">
                        <i class="bi bi-download me-2"></i>Unduh Soal/Lampiran Tugas
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa dan Penilaian -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-600">Daftar Pengumpulan & Nilai Siswa</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Siswa</th>
                            <th>Status Kumpul</th>
                            <th>File Jawaban</th>
                            <th>Nilai</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siswaList as $s): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-500 text-dark"><?= clean($s['nama']) ?></div>
                                <div class="text-muted fs-12"><?= clean($s['nis']) ?></div>
                            </td>
                            <td>
                                <?php if ($s['pengumpulan_id']): ?>
                                    <span class="badge bg-success">Sudah Kumpul</span>
                                    <div class="text-muted fs-11 mt-1"><?= date('d/m/Y H:i', strtotime($s['waktu_kumpul'])) ?></div>
                                    <?php
                                    // Indikator terlambat
                                    if ($tugas['tenggat_waktu'] && strtotime($s['waktu_kumpul']) > strtotime($tugas['tenggat_waktu'])) {
                                        echo '<span class="badge bg-danger fs-11 mt-1">Terlambat</span>';
                                    }
                                    ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum Kumpul</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['file_path']): ?>
                                    <a href="/<?= $s['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-file-earmark-arrow-down"></i> Unduh
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted fs-13">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['nilai'] !== null): ?>
                                    <span class="badge bg-primary fs-6"><?= clean($s['nilai']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic fs-13">Belum dinilai</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-primary" onclick="bukaModalNilai(<?= htmlspecialchars(json_encode($s)) ?>)">
                                    <i class="bi bi-pencil-square me-1"></i> Beri Nilai
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penilaian -->
<div class="modal fade" id="modalNilai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/guru/tugas_detail.php?id=<?= $id ?>">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-600">Beri Penilaian</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="siswa_id" id="inputSiswaId">
                    
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded">
                        <div class="me-3 fs-1 text-muted"><i class="bi bi-person-circle"></i></div>
                        <div>
                            <div class="fw-bold fs-5 text-dark" id="txtNamaSiswa"></div>
                            <div class="text-muted fs-14" id="txtNisSiswa"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nilai <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-lg text-center" name="nilai" id="inputNilai" min="0" max="100" required placeholder="0 - 100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Catatan / Feedback <small class="text-muted">(Opsional)</small></label>
                        <textarea class="form-control" name="catatan" id="inputCatatan" rows="3" placeholder="Berikan evaluasi untuk siswa..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-500">Simpan Nilai</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModalNilai(data) {
    document.getElementById('inputSiswaId').value = data.id;
    document.getElementById('txtNamaSiswa').textContent = data.nama;
    document.getElementById('txtNisSiswa').textContent = 'NIS: ' + data.nis;
    document.getElementById('inputNilai').value = data.nilai !== null ? data.nilai : '';
    document.getElementById('inputCatatan').value = data.catatan || '';
    
    new bootstrap.Modal(document.getElementById('modalNilai')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
