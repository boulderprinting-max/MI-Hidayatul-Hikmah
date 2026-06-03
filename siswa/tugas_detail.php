<?php
$pageTitle = 'Kerjakan Tugas';
require_once __DIR__ . '/../includes/auth.php';
requireRole('siswa');

$id = $_GET['id'] ?? 0;
if (!$id) redirect('/siswa/tugas.php');

$stmtSiswa = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ?");
$stmtSiswa->execute([$_SESSION['user_id']]);
$siswa_id = $stmtSiswa->fetchColumn();

// Ambil data tugas
$stmtTugas = $pdo->prepare("
    SELECT t.*, p.nama_mapel, g.nama as nama_guru, 
           pt.id as pengumpulan_id, pt.file_path as file_jawaban, pt.waktu_kumpul, pt.status as status_kumpul,
           n.nilai, n.catatan
    FROM tugas t
    JOIN mata_pelajaran p ON t.mapel_id = p.id
    JOIN guru g ON t.guru_id = g.id
    LEFT JOIN pengumpulan_tugas pt ON t.id = pt.tugas_id AND pt.siswa_id = ?
    LEFT JOIN nilai n ON t.id = n.tugas_id AND n.siswa_id = ?
    WHERE t.id = ?
");
$stmtTugas->execute([$siswa_id, $siswa_id, $id]);
$tugas = $stmtTugas->fetch();

if (!$tugas) {
    setFlash('danger', 'Tugas tidak ditemukan.');
    redirect('/siswa/tugas.php');
}

// Proses Upload Jawaban
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$tugas['pengumpulan_id']) {
    // Cek tenggat waktu
    $terlambat = false;
    if ($tugas['tenggat_waktu'] && strtotime($tugas['tenggat_waktu']) < time()) {
        $terlambat = true;
        // Boleh tetap mengumpulkan, tapi statusnya terlambat
    }
    
    $filePath = null;
    if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['file_jawaban'], 'uploads/jawaban/', ['pdf','doc','docx','zip','rar','jpg','jpeg','png']);
        if ($uploadRes['status']) {
            $filePath = $uploadRes['path'];
            
            $stmtInsert = $pdo->prepare("INSERT INTO pengumpulan_tugas (tugas_id, siswa_id, file_path, status) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$id, $siswa_id, $filePath, $terlambat ? 'terlambat' : 'tepat_waktu']);
            
            setFlash('success', 'Jawaban tugas berhasil diunggah.');
            redirect('/siswa/tugas_detail.php?id=' . $id);
        } else {
            setFlash('danger', 'Gagal mengunggah file: ' . $uploadRes['message']);
        }
    } else {
        setFlash('danger', 'Silakan pilih file jawaban untuk diunggah.');
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex align-items-center mb-4 gap-3">
        <a href="/siswa/tugas.php" class="btn btn-outline-secondary rounded-circle px-2 py-1"><i class="bi bi-arrow-left"></i></a>
        <h4 class="fw-700 mb-0">Detail Tugas</h4>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Soal Tugas -->
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body p-4 p-md-5">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-12 fw-bold mb-3"><?= clean($tugas['nama_mapel']) ?></span>
                    <h3 class="fw-bold text-dark mb-4"><?= clean($tugas['judul']) ?></h3>
                    
                    <div class="d-flex align-items-center mb-4 pb-4 border-bottom border-light">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-size: 20px;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark"><?= clean($tugas['nama_guru']) ?></div>
                            <div class="text-muted fs-13">Dipublikasikan pada: <?= date('d M Y', strtotime($tugas['created_at'])) ?></div>
                        </div>
                    </div>

                    <div class="tugas-deskripsi mb-5" style="line-height: 1.8; font-size: 15px; white-space: pre-wrap;"><?= clean($tugas['deskripsi']) ?></div>

                    <?php if ($tugas['file_path']): ?>
                    <div class="bg-light p-4 rounded-4 border">
                        <h6 class="fw-bold mb-3"><i class="bi bi-paperclip me-2"></i>File Lampiran Soal</h6>
                        <a href="/<?= $tugas['file_path'] ?>" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center rounded-pill">
                            <i class="bi bi-download me-2"></i> Unduh File
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Pengumpulan -->
            <div class="card shadow-sm border-0 rounded-4 mb-4 bg-gradient-blue text-white">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i>Status Tugas</h5>
                    
                    <div class="d-flex justify-content-between mb-3 border-bottom border-light border-opacity-25 pb-2">
                        <span>Tenggat Waktu</span>
                        <span class="fw-bold text-end">
                            <?php if ($tugas['tenggat_waktu']): ?>
                                <?= date('d M Y, H:i', strtotime($tugas['tenggat_waktu'])) ?>
                            <?php else: ?>
                                Tidak Ada
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3 border-bottom border-light border-opacity-25 pb-2">
                        <span>Status</span>
                        <span class="fw-bold text-end">
                            <?php if ($tugas['pengumpulan_id']): ?>
                                <?php if ($tugas['status_kumpul'] == 'terlambat'): ?>
                                    <span class="badge bg-warning text-dark">Selesai (Terlambat)</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Selesai Dikumpulkan</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-danger">Belum Dikerjakan</span>
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if ($tugas['pengumpulan_id']): ?>
                    <div class="d-flex justify-content-between">
                        <span>Waktu Kumpul</span>
                        <span class="fw-bold text-end"><?= date('d M Y, H:i', strtotime($tugas['waktu_kumpul'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Upload Jawaban -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
                    <h5 class="fw-bold mb-0">Tugas Anda</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if ($tugas['pengumpulan_id']): ?>
                        
                        <div class="alert alert-success border-0 mb-4 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                            <div>Tugas berhasil diunggah.</div>
                        </div>

                        <?php if ($tugas['file_jawaban']): ?>
                            <a href="/<?= $tugas['file_jawaban'] ?>" target="_blank" class="btn btn-outline-secondary w-100 mb-4 rounded-pill">
                                <i class="bi bi-file-earmark-check me-2"></i>Lihat File Jawaban Anda
                            </a>
                        <?php endif; ?>

                        <!-- Hasil Nilai -->
                        <?php if ($tugas['nilai'] !== null): ?>
                            <div class="border rounded-4 p-3 text-center bg-light">
                                <div class="text-muted fw-bold mb-2 text-uppercase fs-12">Nilai Akhir</div>
                                <h1 class="display-4 fw-bold text-primary mb-0"><?= clean($tugas['nilai']) ?></h1>
                                <?php if ($tugas['catatan']): ?>
                                    <hr>
                                    <div class="text-start">
                                        <small class="fw-bold">Catatan Guru:</small>
                                        <p class="fs-13 text-muted mb-0 mt-1">"<?= clean($tugas['catatan']) ?>"</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-3 border rounded-4 bg-light">
                                <i class="bi bi-hourglass-split fs-2 text-warning mb-2"></i>
                                <p class="mb-0 text-muted fs-14 fw-500">Tugas Anda sedang menunggu penilaian dari guru.</p>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Form Upload -->
                        <?php
                        $terlambat = ($tugas['tenggat_waktu'] && strtotime($tugas['tenggat_waktu']) < time());
                        if ($terlambat):
                        ?>
                        <div class="alert alert-warning border-0 fs-13 mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Tenggat waktu sudah terlewat, Anda masih bisa mengumpulkan tapi akan ditandai terlambat.
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="/siswa/tugas_detail.php?id=<?= $id ?>" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Pilih File Jawaban</label>
                                <input type="file" class="form-control form-control-lg" name="file_jawaban" required accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png">
                                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Format diizinkan: PDF, Word, ZIP, Gambar (Max 2MB).</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                                <i class="bi bi-upload me-2"></i> Kumpulkan Tugas
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
