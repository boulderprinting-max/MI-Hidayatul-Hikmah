<?php
$pageTitle = 'Input Absensi';
require_once __DIR__ . '/../includes/auth.php';

if (strpos($_SESSION['role'], 'guru_kelas_') !== 0) {
    redirect('/index.php');
}

$tahun_ajaran = getTahunAjaranAktif($pdo);

// Ambil info wali kelas
$stmtWali = $pdo->prepare("SELECT k.* FROM kelas k JOIN guru g ON k.wali_kelas = g.id WHERE g.user_id = ?");
$stmtWali->execute([$_SESSION['user_id']]);
$kelas = $stmtWali->fetch();

if (!$kelas) {
    // Bukan wali kelas, tidak berhak isi absen kelas utama (opsional: guru mapel absen? untuk MI biasanya wali kelas yg absen harian)
    setFlash('warning', 'Hanya Wali Kelas yang dapat mengisi presensi harian.');
    redirect('/guru/index.php');
}

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Proses simpan absen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl_input = $_POST['tanggal'];
    $absensi = $_POST['absensi'] ?? [];
    $keterangan = $_POST['keterangan'] ?? [];
    
    // Hapus data absen hari itu dulu (kalo misal update)
    $stmtDel = $pdo->prepare("DELETE a FROM absensi a JOIN siswa s ON a.siswa_id = s.id WHERE a.tanggal = ? AND s.kelas_id = ?");
    $stmtDel->execute([$tgl_input, $kelas['id']]);
    
    $stmtIns = $pdo->prepare("INSERT INTO absensi (siswa_id, tahun_ajaran_id, tanggal, status, keterangan) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($absensi as $siswa_id => $status) {
        $ket = $keterangan[$siswa_id] ?? '';
        $stmtIns->execute([$siswa_id, $tahun_ajaran['id'], $tgl_input, $status, $ket]);
    }
    
    setFlash('success', 'Data absensi tanggal ' . formatTanggal($tgl_input, 'short') . ' berhasil disimpan.');
    redirect('/guru/absensi.php?tanggal=' . $tgl_input);
}

// Ambil daftar siswa dan status absensi mereka hari ini (jika ada)
$stmtSiswa = $pdo->prepare("
    SELECT s.id, s.nis, s.nama, a.status, a.keterangan 
    FROM siswa s 
    LEFT JOIN absensi a ON s.id = a.siswa_id AND a.tanggal = ? 
    WHERE s.kelas_id = ? AND s.status = 'aktif'
    ORDER BY s.nama
");
$stmtSiswa->execute([$tanggal, $kelas['id']]);
$siswaList = $stmtSiswa->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>
    
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-700 mb-0"><i class="bi bi-clipboard-check me-2 text-green"></i>Input Presensi Kelas</h4>
            <p class="text-muted mb-0">Kelas: <strong><?= clean($kelas['nama_kelas']) ?></strong> | Tahun Ajaran: <strong><?= clean($tahun_ajaran['nama_tahun']) ?></strong></p>
        </div>
    </div>

    <!-- Pilih Tanggal -->
    <div class="card mb-4">
        <div class="card-body bg-light">
            <form method="GET" class="d-flex align-items-end gap-3">
                <div>
                    <label class="form-label fw-bold">Pilih Tanggal Presensi</label>
                    <input type="date" class="form-control" name="tanggal" value="<?= $tanggal ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Tampilkan</button>
            </form>
        </div>
    </div>

    <!-- Form Input Absen -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h6 class="mb-0 fw-600">Daftar Siswa - <?= formatTanggal($tanggal, 'long') ?></h6>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="setSemuaHadir()">Set Semua Hadir</button>
        </div>
        <div class="card-body">
            <?php if (empty($siswaList)): ?>
                <div class="alert alert-warning">Belum ada data siswa di kelas ini.</div>
            <?php else: ?>
            <form method="POST" action="/guru/absensi.php">
                <input type="hidden" name="tanggal" value="<?= $tanggal ?>">
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">NIS</th>
                                <th width="30%">Nama Lengkap</th>
                                <th width="25%">Status Kehadiran</th>
                                <th width="25%">Keterangan <small class="text-muted">(Opsional)</small></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($siswaList as $i => $s): 
                                $status = $s['status'] ?? ''; // kosong jika belum diabsen
                            ?>
                            <tr>
                                <td class="text-center"><?= $i + 1 ?></td>
                                <td><?= clean($s['nis']) ?></td>
                                <td class="fw-500"><?= clean($s['nama']) ?></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <input type="radio" class="btn-check status-hadir" name="absensi[<?= $s['id'] ?>]" id="hadir_<?= $s['id'] ?>" value="hadir" <?= $status == 'hadir' ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-success btn-sm flex-fill" for="hadir_<?= $s['id'] ?>">Hadir</label>

                                        <input type="radio" class="btn-check" name="absensi[<?= $s['id'] ?>]" id="sakit_<?= $s['id'] ?>" value="sakit" <?= $status == 'sakit' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-warning btn-sm flex-fill" for="sakit_<?= $s['id'] ?>">Sakit</label>

                                        <input type="radio" class="btn-check" name="absensi[<?= $s['id'] ?>]" id="izin_<?= $s['id'] ?>" value="izin" <?= $status == 'izin' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-info btn-sm flex-fill" for="izin_<?= $s['id'] ?>">Izin</label>

                                        <input type="radio" class="btn-check" name="absensi[<?= $s['id'] ?>]" id="alfa_<?= $s['id'] ?>" value="alfa" <?= $status == 'alfa' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-danger btn-sm flex-fill" for="alfa_<?= $s['id'] ?>">Alfa</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="keterangan[<?= $s['id'] ?>]" value="<?= clean($s['keterangan'] ?? '') ?>" placeholder="Tulis alasan...">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                        <i class="bi bi-save me-2"></i>Simpan Presensi
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function setSemuaHadir() {
    const hadirRadios = document.querySelectorAll('.status-hadir');
    hadirRadios.forEach(radio => {
        radio.checked = true;
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
