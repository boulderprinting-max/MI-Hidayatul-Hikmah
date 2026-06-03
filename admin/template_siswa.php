<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole('super_admin');
require_once __DIR__ . '/../includes/SimpleXLSXGen.php';

use Shuchkin\SimpleXLSXGen;

// Ambil daftar kelas yang ada
$kelasList = $pdo->query("SELECT nama_kelas FROM kelas ORDER BY tingkat")->fetchAll(PDO::FETCH_COLUMN);
$kelasInfo = implode(', ', $kelasList);

// Buat template Excel
$data = [
    // Header (bold style)
    ['<style bgcolor="#198754" color="#ffffff" font-size="12"><b>NIS</b></style>',
     '<style bgcolor="#198754" color="#ffffff" font-size="12"><b>NAMA SISWA</b></style>',
     '<style bgcolor="#198754" color="#ffffff" font-size="12"><b>L/P</b></style>',
     '<style bgcolor="#198754" color="#ffffff" font-size="12"><b>KELAS</b></style>',
     '<style bgcolor="#198754" color="#ffffff" font-size="12"><b>NAMA WALI</b></style>'],
    // Contoh data
    ['2026001', 'Ahmad Fulan', 'L', 'Kelas 1', 'Bapak Fulan'],
    ['2026002', 'Siti Aisyah', 'P', 'Kelas 1', 'Ibu Aisyah'],
    ['2026003', 'Muhammad Rizki', 'L', 'Kelas 2', 'Bapak Rizki'],
    // Baris kosong untuk diisi
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    ['', '', '', '', ''],
    // Petunjuk pengisian
    [],
    ['<style color="#dc3545"><b>PETUNJUK PENGISIAN:</b></style>'],
    ['1. NIS wajib diisi dan harus unik (tidak boleh sama).'],
    ['2. L/P diisi dengan huruf L (Laki-laki) atau P (Perempuan).'],
    ['3. Kelas diisi dengan nama kelas yang tersedia: ' . $kelasInfo],
    ['4. Hapus contoh data (baris 2-4) sebelum mengisi data asli.'],
    ['5. Jangan mengubah urutan kolom pada baris pertama (header).'],
];

$xlsx = SimpleXLSXGen::fromArray($data);
$xlsx->setDefaultFont('Calibri');
$xlsx->setDefaultFontSize(11);
$xlsx->setColWidth(1, 15);
$xlsx->setColWidth(2, 30);
$xlsx->setColWidth(3, 8);
$xlsx->setColWidth(4, 15);
$xlsx->setColWidth(5, 25);
$xlsx->downloadAs('Template_Import_Siswa_MI_Hidayatul_Hikmah.xlsx');
exit;
