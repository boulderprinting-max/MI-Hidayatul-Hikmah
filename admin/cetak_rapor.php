<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin'); // Bisa dipake guru juga nanti, kita cek role

$siswa_id = $_GET['siswa'] ?? 0;
$tahun_ajaran = getTahunAjaranAktif($pdo);

if (!$siswa_id) {
    die("ID Siswa tidak valid");
}

// Data Siswa
$stmt = $pdo->prepare("SELECT s.*, k.nama_kelas, k.tingkat, k.wali_kelas, g.nama as nama_wali_kelas, g.nip 
                       FROM siswa s 
                       JOIN kelas k ON s.kelas_id = k.id 
                       LEFT JOIN guru g ON k.wali_kelas = g.id 
                       WHERE s.id = ?");
$stmt->execute([$siswa_id]);
$siswa = $stmt->fetch();

if (!$siswa) die("Data siswa tidak ditemukan");

// Rekap Nilai per Mapel
$stmtNilai = $pdo->prepare("
    SELECT p.nama_mapel, AVG(n.nilai) as rata_rata 
    FROM nilai n 
    JOIN tugas t ON n.tugas_id = t.id 
    JOIN mata_pelajaran p ON t.mapel_id = p.id 
    WHERE n.siswa_id = ? AND t.tahun_ajaran_id = ? 
    GROUP BY p.id
");
$stmtNilai->execute([$siswa_id, $tahun_ajaran['id']]);
$nilaiData = $stmtNilai->fetchAll();

// Rekap Absensi
$stmtAbsensi = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) as alfa
    FROM absensi 
    WHERE siswa_id = ? AND tahun_ajaran_id = ?
");
$stmtAbsensi->execute([$siswa_id, $tahun_ajaran['id']]);
$absensi = $stmtAbsensi->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rapor - <?= clean($siswa['nama']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; color: #000; font-family: 'Times New Roman', Times, serif; font-size: 12pt; }
        .rapor-container { width: 100%; max-width: 21cm; margin: 0 auto; padding: 2cm; }
        .rapor-header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .rapor-header h3, .rapor-header h4 { margin: 0; font-weight: bold; }
        .table-rapor { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-rapor th, .table-rapor td { border: 1px solid #000; padding: 8px; }
        .table-rapor th { background-color: #f0f0f0 !important; text-align: center; font-weight: bold; }
        .info-siswa table { width: 100%; margin-bottom: 20px; }
        .info-siswa td { padding: 4px; border: none; }
        .ttd-box { width: 100%; margin-top: 50px; }
        .ttd-box table { width: 100%; border: none; text-align: center; }
        .ttd-box td { border: none; padding-top: 80px; }
        @media print {
            body { background-color: white; }
            .rapor-container { padding: 0; }
            @page { margin: 1cm; size: A4 portrait; }
            button { display: none; }
        }
    </style>
</head>
<body>
    <div class="rapor-container">
        <!-- Tombol Print (Sembunyi saat dicetak) -->
        <div class="text-end mb-3 d-print-none">
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Cetak Rapor</button>
            <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        </div>

        <div class="rapor-header">
            <h3>MADRASAH IBTIDAIYAH (MI) HIDAYATUL HIKMAH</h3>
            <h4>LAPORAN HASIL BELAJAR SISWA</h4>
        </div>

        <div class="info-siswa">
            <table>
                <tr>
                    <td width="20%">Nama Siswa</td>
                    <td width="2%">:</td>
                    <td width="48%"><strong><?= clean($siswa['nama']) ?></strong></td>
                    <td width="15%">Kelas</td>
                    <td width="2%">:</td>
                    <td width="13%"><?= clean($siswa['nama_kelas']) ?></td>
                </tr>
                <tr>
                    <td>Nomor Induk / NIS</td>
                    <td>:</td>
                    <td><?= clean($siswa['nis']) ?></td>
                    <td>Semester</td>
                    <td>:</td>
                    <td><?= ucfirst($tahun_ajaran['semester'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Nama Sekolah</td>
                    <td>:</td>
                    <td>MI Hidayatul Hikmah</td>
                    <td>Tahun Ajaran</td>
                    <td>:</td>
                    <td><?= clean($tahun_ajaran['nama_tahun'] ?? '-') ?></td>
                </tr>
            </table>
        </div>

        <h5 class="fw-bold mb-3">A. NILAI AKADEMIK</h5>
        <table class="table-rapor">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="50%">Mata Pelajaran</th>
                    <th width="15%">Nilai Rata-rata</th>
                    <th width="30%">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($nilaiData)): ?>
                <tr><td colspan="4" class="text-center">Belum ada data nilai pada semester ini.</td></tr>
                <?php else: ?>
                    <?php foreach ($nilaiData as $i => $n): 
                        $rata = round($n['rata_rata']);
                        $predikat = 'Kurang';
                        if ($rata >= 90) $predikat = 'Sangat Baik (A)';
                        elseif ($rata >= 80) $predikat = 'Baik (B)';
                        elseif ($rata >= 75) $predikat = 'Cukup (C)';
                    ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= clean($n['nama_mapel']) ?></td>
                        <td class="text-center"><?= $rata ?></td>
                        <td class="text-center"><?= $predikat ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <h5 class="fw-bold mb-3 mt-4">B. KETIDAKHADIRAN</h5>
        <table class="table-rapor" style="width: 50%;">
            <tbody>
                <tr>
                    <td width="60%">Sakit</td>
                    <td class="text-center"><?= $absensi['sakit'] ?? 0 ?> Hari</td>
                </tr>
                <tr>
                    <td>Izin</td>
                    <td class="text-center"><?= $absensi['izin'] ?? 0 ?> Hari</td>
                </tr>
                <tr>
                    <td>Tanpa Keterangan (Alfa)</td>
                    <td class="text-center"><?= $absensi['alfa'] ?? 0 ?> Hari</td>
                </tr>
            </tbody>
        </table>

        <div class="ttd-box">
            <table>
                <tr>
                    <td width="33%">
                        Mengetahui,<br>
                        Orang Tua / Wali Siswa
                        <br><br><br><br>
                        ( .................................... )
                    </td>
                    <td width="33%"></td>
                    <td width="33%">
                        Jombang, <?= formatTanggal(date('Y-m-d'), 'long') ?><br>
                        Wali Kelas
                        <br><br><br><br>
                        <strong><u><?= clean($siswa['nama_wali_kelas'] ?? '...........................') ?></u></strong><br>
                        NIP: <?= clean($siswa['nip'] ?? '-') ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 40px;">
                        Mengetahui,<br>
                        Kepala Madrasah
                        <br><br><br><br>
                        <strong><u>( Nama Kepala Madrasah )</u></strong><br>
                        NIP: ...........................
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
