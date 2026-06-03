<?php
$pageTitle = 'Materi Pembelajaran';
require_once __DIR__ . '/../includes/auth.php';

if (strpos($_SESSION['role'], 'guru_kelas_') !== 0) {
    redirect('/index.php');
}

// Ambil info guru
$stmt = $pdo->prepare("SELECT id FROM guru WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$guru = $stmt->fetch();
$guru_id = $guru['id'] ?? 0;

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

// HAPUS MATERI
if ($action === 'delete' && $id) {
    $stmt = $pdo->prepare("SELECT file_path FROM materi WHERE id = ? AND guru_id = ?");
    $stmt->execute([$id, $guru_id]);
    $materi = $stmt->fetch();
    
    if ($materi) {
        if ($materi['file_path'] && file_exists(__DIR__ . '/../' . $materi['file_path'])) {
            unlink(__DIR__ . '/../' . $materi['file_path']);
        }
        $pdo->prepare("DELETE FROM materi WHERE id = ?")->execute([$id]);
        setFlash('success', 'Materi berhasil dihapus');
    } else {
        setFlash('danger', 'Materi tidak ditemukan atau Anda tidak berhak menghapusnya.');
    }
    redirect('/guru/materi.php');
}

// SIMPAN MATERI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = clean($_POST['judul']);
    $deskripsi = $_POST['deskripsi'];
    $kelas_id = (int)$_POST['kelas_id'];
    $mapel_id = (int)$_POST['mapel_id'];
    $link_youtube = clean($_POST['link_youtube']);
    
    // File upload
    $filePath = null;
    if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] === UPLOAD_ERR_OK) {
        $uploadRes = uploadFile($_FILES['file_materi'], 'uploads/materi/', ['pdf','doc','docx','ppt','pptx']);
        if ($uploadRes['status']) {
            $filePath = $uploadRes['path'];
        } else {
            setFlash('danger', 'Upload gagal: ' . $uploadRes['message']);
            redirect('/guru/materi.php');
        }
    }

    $stmt = $pdo->prepare("INSERT INTO materi (judul, deskripsi, file_path, link_youtube, guru_id, mapel_id, kelas_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$judul, $deskripsi, $filePath, $link_youtube, $guru_id, $mapel_id, $kelas_id]);
    
    setFlash('success', "Materi berhasil diunggah.");
    redirect('/guru/materi.php');
}

// Data List
$query = "SELECT m.*, k.nama_kelas, p.nama_mapel 
          FROM materi m 
          JOIN kelas k ON m.kelas_id = k.id 
          JOIN mata_pelajaran p ON m.mapel_id = p.id 
          WHERE m.guru_id = ? 
          ORDER BY m.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$guru_id]);
$materiData = $stmt->fetchAll();

$kelasList = $pdo->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat")->fetchAll();
$mapelList = $pdo->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-700 mb-0"><i class="bi bi-journal-text me-2 text-green"></i>Kelola Materi Saya</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMateri">
            <i class="bi bi-cloud-arrow-up me-1"></i> Unggah Materi Baru
        </button>
    </div>

    <div class="row g-4">
        <?php if (empty($materiData)): ?>
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Empty" width="120" class="mb-3 opacity-50">
                <h5 class="text-muted">Belum ada materi yang diunggah.</h5>
                <button class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalMateri">Mulai Unggah Materi Pertama Anda</button>
            </div>
        <?php else: ?>
            <?php foreach ($materiData as $m): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0 hover-lift">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-soft-green text-green px-2 py-1 fs-12"><i class="bi bi-book me-1"></i><?= clean($m['nama_mapel']) ?></span>
                            <span class="badge bg-light text-dark border px-2 py-1 fs-12"><i class="bi bi-building me-1"></i><?= clean($m['nama_kelas']) ?></span>
                        </div>
                        <h5 class="fw-bold mb-2"><?= clean($m['judul']) ?></h5>
                        <p class="text-muted fs-14 line-clamp-3 mb-3"><?= strip_tags($m['deskripsi']) ?></p>
                        
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <?php if ($m['file_path']): ?>
                            <a href="/<?= $m['file_path'] ?>" target="_blank" class="badge bg-danger text-decoration-none p-2 fs-13">
                                <i class="bi bi-file-earmark-pdf me-1"></i> File PDF/Doc
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($m['link_youtube']): ?>
                            <a href="<?= clean($m['link_youtube']) ?>" target="_blank" class="badge bg-danger text-decoration-none p-2 fs-13">
                                <i class="bi bi-youtube me-1"></i> Video YouTube
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= formatTanggal(substr($m['created_at'], 0, 10), 'short') ?></small>
                        <a href="#" class="btn btn-sm btn-outline-danger btn-delete" 
                           data-nama="<?= clean($m['judul']) ?>" 
                           data-href="/guru/materi.php?action=delete&id=<?= $m['id'] ?>">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="modalMateri" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/guru/materi.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title fw-600"><i class="bi bi-cloud-arrow-up me-2"></i>Unggah Materi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kelas Tujuan <span class="text-danger">*</span></label>
                            <select class="form-select" name="kelas_id" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= clean($k['nama_kelas']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="mapel_id" required>
                                <option value="">-- Pilih Mapel --</option>
                                <?php foreach ($mapelList as $mp): ?>
                                <option value="<?= $mp['id'] ?>"><?= clean($mp['nama_mapel']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judul Materi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" required placeholder="Contoh: Bab 1 Rukun Islam">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penjelasan Singkat</label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Tuliskan rangkuman materi di sini..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">File Lampiran <small class="text-muted">(PDF/Word/PPT, Max 2MB)</small></label>
                            <input type="file" class="form-control" name="file_materi" accept=".pdf,.doc,.docx,.ppt,.pptx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link YouTube <small class="text-muted">(Opsional)</small></label>
                            <input type="url" class="form-control" name="link_youtube" placeholder="https://youtube.com/...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Unggah Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle delete button on cards
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtns = document.querySelectorAll('.btn-delete');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const nama = this.getAttribute('data-nama');
            const href = this.getAttribute('data-href');
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                html: `Menghapus materi <strong>${nama}</strong> secara permanen?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
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
