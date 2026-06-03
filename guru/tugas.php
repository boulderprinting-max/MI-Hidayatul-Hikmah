<?php
$pageTitle = 'Kelola Tugas';
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

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS TUGAS
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT file_path FROM tugas WHERE id = ? AND guru_id = ?");
    $stmt->execute([$id, $guru_id]);
    $tugas = $stmt->fetch();
    
    if ($tugas) {
        if ($tugas['file_path'] && file_exists(__DIR__ . '/../' . $tugas['file_path'])) {
            unlink(__DIR__ . '/../' . $tugas['file_path']);
        }
        
        // Hapus file pengumpulan siswa
        $pengumpulan = $pdo->prepare("SELECT file_path FROM pengumpulan_tugas WHERE tugas_id = ?");
        $pengumpulan->execute([$id]);
        while($p = $pengumpulan->fetch()) {
            if ($p['file_path'] && file_exists(__DIR__ . '/../' . $p['file_path'])) {
                unlink(__DIR__ . '/../' . $p['file_path']);
            }
        }
        $pdo->prepare("DELETE FROM pengumpulan_tugas WHERE tugas_id = ?")->execute([$id]);
        
        $pdo->prepare("DELETE FROM tugas WHERE id = ?")->execute([$id]);
        setFlash('success', 'Tugas berhasil dihapus');
    }
    redirect('/guru/tugas.php');
}

// SIMPAN TUGAS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = clean($_POST['judul']);
    $deskripsi = $_POST['deskripsi'];
    $kelas_id = (int)$_POST['kelas_id'];
    $mapel_id = (int)$_POST['mapel_id'];
    $tenggat_waktu = $_POST['tenggat_waktu'];
    
    // File upload
    $filePath = null;
    if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['file_tugas'], 'uploads/tugas/', ['pdf','doc','docx','zip','rar']);
        if ($uploadRes['status']) {
            $filePath = $uploadRes['path'];
        } else {
            setFlash('danger', 'Upload gagal: ' . $uploadRes['message']);
            redirect('/guru/tugas.php');
        }
    }

    $stmt = $pdo->prepare("INSERT INTO tugas (judul, deskripsi, file_path, guru_id, mapel_id, kelas_id, tahun_ajaran_id, tenggat_waktu, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktif')");
    $stmt->execute([$judul, $deskripsi, $filePath, $guru_id, $mapel_id, $kelas_id, $tahun_ajaran['id'], $tenggat_waktu]);
    
    setFlash('success', "Tugas berhasil dibuat.");
    redirect('/guru/tugas.php');
}

// Data List
$query = "SELECT t.*, k.nama_kelas, p.nama_mapel, 
          (SELECT COUNT(*) FROM pengumpulan_tugas WHERE tugas_id = t.id) as jml_terkumpul,
          (SELECT COUNT(*) FROM nilai WHERE tugas_id = t.id) as jml_dinilai
          FROM tugas t 
          JOIN kelas k ON t.kelas_id = k.id 
          JOIN mata_pelajaran p ON t.mapel_id = p.id 
          WHERE t.guru_id = ? AND t.tahun_ajaran_id = ?
          ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$guru_id, $tahun_ajaran['id']]);
$tugasData = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();
$mapelList = $pdo->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-journal-check me-2 text-green"></i>Kelola Tugas</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTugas">
            <i class="bi bi-plus-lg me-1"></i> Buat Tugas Baru
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (empty($tugasData)): ?>
                <div class="text-center py-5">
                    <img src="https://cdn-icons-png.flaticon.com/512/2836/2836526.png" alt="Empty" width="100" class="mb-3 opacity-50">
                    <h5 class="text-muted">Belum ada tugas yang Anda berikan pada semester ini.</h5>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table align-middle">
                    <thead>
                        <tr>
                            <th>Judul Tugas</th>
                            <th>Kelas</th>
                            <th>Mapel</th>
                            <th>Tenggat Waktu</th>
                            <th>Terkumpul</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tugasData as $t): ?>
                        <tr>
                            <td>
                                <div class="fw-500"><?= clean($t['judul']) ?></div>
                                <?php if ($t['status'] == 'aktif'): ?>
                                    <span class="badge bg-success fs-12">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary fs-12">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td><?= clean($t['nama_kelas']) ?></td>
                            <td><?= clean($t['nama_mapel']) ?></td>
                            <td>
                                <?php if ($t['tenggat_waktu']): ?>
                                    <span class="<?= strtotime($t['tenggat_waktu']) < time() ? 'text-danger' : 'text-primary' ?> fw-500">
                                        <?= date('d/m/Y H:i', strtotime($t['tenggat_waktu'])) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <span class="badge bg-info"><?= $t['jml_terkumpul'] ?> Siswa</span> Mengumpulkan
                                </div>
                                <div>
                                    <span class="badge bg-success"><?= $t['jml_dinilai'] ?> Siswa</span> Dinilai
                                </div>
                            </td>
                            <td>
                                <a href="/guru/tugas_detail.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-card-checklist me-1"></i> Periksa / Nilai
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-danger btn-delete ms-1" 
                                   data-nama="<?= clean($t['judul']) ?>" 
                                   data-href="/guru/tugas.php?action=delete&id=<?= $t['id'] ?>">
                                    <i class="bi bi-trash"></i>
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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTugas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/guru/tugas.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title fw-600"><i class="bi bi-journal-check me-2"></i>Buat Tugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" required placeholder="Contoh: Latihan Soal Bab 1">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" name="kelas_id" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= clean($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mapel_id" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= clean($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batas Waktu (Tenggat)</label>
                            <input type="datetime-local" class="form-control" name="tenggat_waktu">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi / Instruksi Tugas</label>
                        <textarea class="form-control" name="deskripsi" rows="4" placeholder="Jelaskan apa yang harus dikerjakan siswa..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Lampiran <small class="text-muted">(PDF/Word/ZIP, Max 2MB)</small></label>
                        <input type="file" class="form-control" name="file_tugas" accept=".pdf,.doc,.docx,.zip,.rar">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Terbitkan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtns = document.querySelectorAll('.btn-delete');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const nama = this.getAttribute('data-nama');
            const href = this.getAttribute('data-href');
            
            Swal.fire({
                title: 'Hapus Tugas?',
                html: `Menghapus tugas <strong>${nama}</strong> akan menghapus semua file pengumpulan dan nilai siswa untuk tugas ini.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = href;
                }
            });
        });
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
