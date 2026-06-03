<?php
$pageTitle = 'Import Data Siswa';
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');
require_once __DIR__ . '/../includes/SimpleXLSX.php';

use Shuchkin\SimpleXLSX;

$kelasList = $pdo->query("SELECT id, nama_kelas, tingkat FROM kelas ORDER BY tingkat")->fetchAll();
$kelasMap = [];
foreach ($kelasList as $k) {
    $kelasMap[strtolower(trim($k['nama_kelas']))] = $k['id'];
    $kelasMap[(string)$k['tingkat']] = $k['id'];
    $kelasMap['kelas ' . $k['tingkat']] = $k['id'];
}

$previewData = [];
$errors = [];
$importSuccess = false;
$importCount = 0;

// ==========================================
// PROSES PREVIEW (Upload file Excel)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'preview') {
    if (!isset($_FILES['file_excel']) || $_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
        setFlash('danger', 'Gagal mengunggah file. Pastikan file Excel (.xlsx) valid.');
        redirect('/admin/import_siswa.php');
    }

    $ext = strtolower(pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'xlsx') {
        setFlash('danger', 'Format file harus .xlsx (Excel 2007+)');
        redirect('/admin/import_siswa.php');
    }

    $tmpFile = $_FILES['file_excel']['tmp_name'];

    if ($xlsx = SimpleXLSX::parse($tmpFile)) {
        $rows = $xlsx->rows();
        $header = array_shift($rows); // Baris pertama = header

        foreach ($rows as $i => $row) {
            $nis   = trim($row[0] ?? '');
            $nama  = trim($row[1] ?? '');
            $jk    = strtoupper(trim($row[2] ?? 'L'));
            $kelas = strtolower(trim($row[3] ?? ''));
            $wali  = trim($row[4] ?? '');

            if (empty($nis) && empty($nama)) continue; // skip baris kosong

            $kelasId = $kelasMap[$kelas] ?? null;
            $error = '';

            if (empty($nis)) $error .= 'NIS kosong. ';
            if (empty($nama)) $error .= 'Nama kosong. ';
            if (!in_array($jk, ['L', 'P'])) { $jk = 'L'; $error .= 'JK tidak valid (diset L). '; }
            if (!$kelasId) $error .= 'Kelas "' . htmlspecialchars($row[3] ?? '') . '" tidak ditemukan. ';

            // Cek NIS duplikat di database
            $cek = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE nis = ?");
            $cek->execute([$nis]);
            if ($cek->fetchColumn() > 0) $error .= 'NIS sudah terdaftar di database. ';

            $previewData[] = [
                'row'      => $i + 2,
                'nis'      => $nis,
                'nama'     => $nama,
                'jk'       => $jk,
                'kelas'    => $row[3] ?? '',
                'kelas_id' => $kelasId,
                'wali'     => $wali,
                'error'    => $error,
                'valid'    => empty($error),
            ];
        }

        // Simpan data preview ke session untuk diproses di step import
        $_SESSION['import_preview'] = $previewData;
    } else {
        setFlash('danger', 'Gagal membaca file Excel: ' . SimpleXLSX::parseError());
        redirect('/admin/import_siswa.php');
    }
}

// ==========================================
// PROSES IMPORT (Simpan ke Database)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step']) && $_POST['step'] === 'import') {
    $data = $_SESSION['import_preview'] ?? [];
    unset($_SESSION['import_preview']);

    if (empty($data)) {
        setFlash('danger', 'Tidak ada data untuk diimport. Silakan upload ulang.');
        redirect('/admin/import_siswa.php');
    }

    $imported = 0;
    $skipped = 0;
    $akunList = [];

    $pdo->beginTransaction();
    try {
        foreach ($data as $row) {
            if (!$row['valid']) { $skipped++; continue; }

            $nis = $row['nis'];
            $nama = $row['nama'];
            $jk = $row['jk'];
            $kelasId = $row['kelas_id'];
            $wali = $row['wali'];

            // Cek lagi NIS unik (antisipasi race condition)
            $cek = $pdo->prepare("SELECT COUNT(*) FROM siswa WHERE nis = ?");
            $cek->execute([$nis]);
            if ($cek->fetchColumn() > 0) { $skipped++; continue; }

            // Buat user siswa (password = NIS)
            $password = password_hash($nis, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role, status) VALUES (?, ?, ?, 'siswa', 'aktif')");
            $stmt->execute([$nama, $nis, $password]);
            $userId = $pdo->lastInsertId();

            // Buat data siswa
            $stmt = $pdo->prepare("INSERT INTO siswa (user_id, nis, nama, jenis_kelamin, kelas_id, nama_wali) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $nis, $nama, $jk, $kelasId, $wali]);

            // Buat user wali murid (password = NIS)
            $usernameWali = 'wali_' . $nis;
            $stmtWali = $pdo->prepare("INSERT INTO users (nama, username, password, role, status) VALUES (?, ?, ?, 'wali_murid', 'aktif')");
            $stmtWali->execute(['Wali dari ' . $nama, $usernameWali, $password]);

            $akunList[] = ['nis' => $nis, 'nama' => $nama, 'wali_user' => $usernameWali];
            $imported++;
        }
        $pdo->commit();
        $importSuccess = true;
        $importCount = $imported;

        // Simpan daftar akun ke session untuk ditampilkan
        $_SESSION['import_result'] = [
            'imported' => $imported,
            'skipped' => $skipped,
            'akun' => $akunList
        ];

        setFlash('success', "✅ Berhasil mengimport <b>$imported</b> siswa! (Dilewati: $skipped)");
    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Gagal mengimport data: ' . $e->getMessage());
    }
    redirect('/admin/import_siswa.php?result=1');
}

// Ambil hasil import jika ada
$importResult = null;
if (isset($_GET['result']) && isset($_SESSION['import_result'])) {
    $importResult = $_SESSION['import_result'];
    unset($_SESSION['import_result']);
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content fade-in">
    <?= getFlash() ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-700 mb-1"><i class="bi bi-file-earmark-spreadsheet me-2 text-green"></i>Import Data Siswa</h4>
            <p class="text-muted fs-14 mb-0">Upload file Excel (.xlsx) untuk menambah banyak siswa sekaligus</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/template_siswa.php" class="btn btn-sm btn-outline-success">
                <i class="bi bi-download me-1"></i> Download Template
            </a>
            <a href="/admin/siswa.php" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <?php if ($importResult): ?>
    <!-- ===== HASIL IMPORT ===== -->
    <div class="card mb-4 border-success">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0 fw-600"><i class="bi bi-check-circle me-2"></i>Hasil Import Siswa</h6>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-green">
                        <div class="stat-icon"><i class="bi bi-check-lg"></i></div>
                        <div class="stat-value"><?= $importResult['imported'] ?></div>
                        <div class="stat-label">Siswa Berhasil Diimport</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card bg-gradient-orange">
                        <div class="stat-icon"><i class="bi bi-x-lg"></i></div>
                        <div class="stat-value"><?= $importResult['skipped'] ?></div>
                        <div class="stat-label">Siswa Dilewati</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info mb-0 h-100 d-flex align-items-center">
                        <div>
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Password Default:</strong> Sama dengan NIS masing-masing siswa.
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($importResult['akun'])): ?>
            <h6 class="fw-bold mb-3"><i class="bi bi-key me-2"></i>Daftar Akun Login yang Dibuat</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Username Siswa</th>
                            <th>Username Wali</th>
                            <th>Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($importResult['akun'] as $i => $a): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-500"><?= clean($a['nama']) ?></td>
                            <td><code><?= clean($a['nis']) ?></code></td>
                            <td><code><?= clean($a['wali_user']) ?></code></td>
                            <td><code><?= clean($a['nis']) ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button class="btn btn-sm btn-outline-primary mt-2" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Daftar Akun
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($previewData)): ?>
    <!-- ===== PREVIEW DATA ===== -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-600"><i class="bi bi-eye me-2"></i>Preview Data Siswa dari Excel</h6>
            <span class="badge bg-white text-primary"><?= count($previewData) ?> baris data</span>
        </div>
        <div class="card-body">
            <?php
            $validCount = count(array_filter($previewData, fn($r) => $r['valid']));
            $errorCount = count($previewData) - $validCount;
            ?>
            <div class="alert alert-info fs-14 mb-3">
                <i class="bi bi-info-circle me-1"></i>
                <strong><?= $validCount ?></strong> data valid siap diimport,
                <strong class="text-danger"><?= $errorCount ?></strong> data bermasalah akan dilewati.
                Periksa tabel di bawah sebelum mengkonfirmasi.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Baris</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>L/P</th>
                            <th>Kelas</th>
                            <th>Nama Wali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewData as $p): ?>
                        <tr class="<?= $p['valid'] ? '' : 'table-danger' ?>">
                            <td><?= $p['row'] ?></td>
                            <td><code><?= clean($p['nis']) ?></code></td>
                            <td class="fw-500"><?= clean($p['nama']) ?></td>
                            <td><?= $p['jk'] ?></td>
                            <td><?= clean($p['kelas']) ?></td>
                            <td><?= clean($p['wali']) ?></td>
                            <td>
                                <?php if ($p['valid']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Valid</span>
                                <?php else: ?>
                                    <span class="badge bg-danger" title="<?= clean($p['error']) ?>">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Error
                                    </span>
                                    <div class="text-danger fs-13 mt-1"><?= clean($p['error']) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($validCount > 0): ?>
            <form method="POST" class="mt-3">
                <input type="hidden" name="step" value="import">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Yakin ingin mengimport <?= $validCount ?> siswa? Data yang bermasalah akan dilewati.')">
                        <i class="bi bi-cloud-upload me-2"></i>Konfirmasi Import (<?= $validCount ?> siswa)
                    </button>
                    <a href="/admin/import_siswa.php" class="btn btn-outline-secondary btn-lg">
                        <i class="bi bi-x-lg me-1"></i>Batal
                    </a>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-danger mt-3">
                <i class="bi bi-x-circle me-1"></i>Tidak ada data valid untuk diimport. Silakan perbaiki file Excel Anda dan coba lagi.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
    <!-- ===== FORM UPLOAD ===== -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-600"><i class="bi bi-upload me-2"></i>Upload File Excel</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="step" value="preview">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih File Excel (.xlsx)</label>
                            <input type="file" class="form-control form-control-lg" name="file_excel" accept=".xlsx" required>
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Hanya menerima format <strong>.xlsx</strong> (Excel 2007+). Maksimal 5MB.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-eye me-2"></i>Preview Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0 fw-600"><i class="bi bi-lightbulb me-2"></i>Panduan Import</h6>
                </div>
                <div class="card-body">
                    <ol class="fs-14 mb-4" style="line-height:2;">
                        <li>Download <strong>Template Excel</strong> terlebih dahulu.</li>
                        <li>Isi data siswa sesuai kolom yang tersedia.</li>
                        <li>Pastikan <strong>NIS unik</strong> (tidak boleh sama).</li>
                        <li>Kolom <strong>Kelas</strong> isi dengan nama kelas, contoh: <code>Kelas 1</code>, <code>Kelas 2</code>, dst. Atau cukup angka: <code>1</code>, <code>2</code>, dst.</li>
                        <li>Kolom <strong>L/P</strong> isi <code>L</code> untuk laki-laki atau <code>P</code> untuk perempuan.</li>
                        <li>Upload file lalu <strong>Preview</strong> untuk memeriksa.</li>
                        <li>Klik <strong>Konfirmasi Import</strong> jika data sudah benar.</li>
                    </ol>

                    <div class="alert alert-warning fs-13 mb-3">
                        <i class="bi bi-shield-lock me-1"></i>
                        <strong>Akun Otomatis:</strong> Sistem akan otomatis membuat akun login untuk setiap siswa dan wali murid. Password default = NIS.
                    </div>

                    <a href="/admin/template_siswa.php" class="btn btn-success w-100">
                        <i class="bi bi-file-earmark-excel me-2"></i>Download Template Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
