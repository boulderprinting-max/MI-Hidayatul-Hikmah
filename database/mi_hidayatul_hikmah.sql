-- =====================================================
-- DATABASE: MI Hidayatul Hikmah
-- E-Learning & Manajemen Akademik
-- =====================================================

CREATE DATABASE IF NOT EXISTS mi_hidayatul_hikmah;
USE mi_hidayatul_hikmah;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- TABEL USERS (Login semua pengguna)
-- =====================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','guru_kelas_1','guru_kelas_2','guru_kelas_3','guru_kelas_4','guru_kelas_5','guru_kelas_6','siswa','wali_murid') NOT NULL,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL TAHUN AJARAN
-- =====================================================
DROP TABLE IF EXISTS tahun_ajaran;
CREATE TABLE tahun_ajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_tahun VARCHAR(20) NOT NULL,
    semester ENUM('ganjil','genap') DEFAULT 'ganjil',
    status ENUM('aktif','nonaktif') DEFAULT 'nonaktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL GURU
-- =====================================================
DROP TABLE IF EXISTS guru;
CREATE TABLE guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    nip VARCHAR(30) DEFAULT NULL,
    nama VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L','P') DEFAULT 'L',
    alamat TEXT DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    kelas VARCHAR(10) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL KELAS
-- =====================================================
DROP TABLE IF EXISTS kelas;
CREATE TABLE kelas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelas VARCHAR(20) NOT NULL,
    tingkat INT DEFAULT 1,
    wali_kelas INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wali_kelas) REFERENCES guru(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL SISWA
-- =====================================================
DROP TABLE IF EXISTS siswa;
CREATE TABLE siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    nis VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jenis_kelamin ENUM('L','P') NOT NULL DEFAULT 'L',
    tempat_lahir VARCHAR(50) DEFAULT NULL,
    tanggal_lahir DATE DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    kelas_id INT DEFAULT NULL,
    nama_wali VARCHAR(100) DEFAULT NULL,
    no_hp_wali VARCHAR(20) DEFAULT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    status ENUM('aktif','nonaktif','lulus') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL MATA PELAJARAN
-- =====================================================
DROP TABLE IF EXISTS mata_pelajaran;
CREATE TABLE mata_pelajaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) DEFAULT NULL,
    nama_mapel VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL ABSENSI
-- =====================================================
DROP TABLE IF EXISTS absensi;
CREATE TABLE absensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    kelas_id INT NOT NULL,
    tahun_ajaran_id INT DEFAULT NULL,
    tanggal DATE NOT NULL,
    status ENUM('hadir','sakit','izin','alfa') NOT NULL DEFAULT 'hadir',
    keterangan VARCHAR(255) DEFAULT NULL,
    pencatat_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE SET NULL,
    FOREIGN KEY (pencatat_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL MATERI
-- =====================================================
DROP TABLE IF EXISTS materi;
CREATE TABLE materi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    kelas_id INT NOT NULL,
    mapel_id INT DEFAULT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    file_materi VARCHAR(255) DEFAULT NULL,
    tipe_file ENUM('dokumen','video','link') DEFAULT 'dokumen',
    tanggal_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL TUGAS
-- =====================================================
DROP TABLE IF EXISTS tugas;
CREATE TABLE tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guru_id INT NOT NULL,
    kelas_id INT NOT NULL,
    tahun_ajaran_id INT DEFAULT NULL,
    mapel_id INT DEFAULT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    file_tugas VARCHAR(255) DEFAULT NULL,
    tenggat_waktu DATETIME DEFAULT NULL,
    status ENUM('aktif','selesai') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
    FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE CASCADE,
    FOREIGN KEY (tahun_ajaran_id) REFERENCES tahun_ajaran(id) ON DELETE SET NULL,
    FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL PENGUMPULAN TUGAS (JAWABAN SISWA)
-- =====================================================
DROP TABLE IF EXISTS pengumpulan_tugas;
CREATE TABLE pengumpulan_tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_id INT NOT NULL,
    siswa_id INT NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    status ENUM('tepat_waktu','terlambat') DEFAULT 'tepat_waktu',
    waktu_kumpul DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL NILAI
-- =====================================================
DROP TABLE IF EXISTS nilai;
CREATE TABLE nilai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siswa_id INT NOT NULL,
    tugas_id INT NOT NULL,
    nilai DECIMAL(5,2) DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- TABEL PENGUMUMAN
-- =====================================================
DROP TABLE IF EXISTS pengumuman;
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    target ENUM('semua','guru','siswa','wali_murid') DEFAULT 'semua',
    author_id INT DEFAULT NULL,
    is_published TINYINT(1) DEFAULT 1,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DATA DEFAULT
-- =====================================================

-- Super Admin (password: admin123)
INSERT INTO users (nama, username, password, role, status) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', 'aktif');

-- Tahun Ajaran
INSERT INTO tahun_ajaran (nama_tahun, semester, status) VALUES
('2025/2026', 'genap', 'aktif'),
('2025/2026', 'ganjil', 'nonaktif'),
('2024/2025', 'genap', 'nonaktif');

-- Kelas 1-6
INSERT INTO kelas (nama_kelas, tingkat) VALUES
('Kelas 1', 1),
('Kelas 2', 2),
('Kelas 3', 3),
('Kelas 4', 4),
('Kelas 5', 5),
('Kelas 6', 6);

-- Mata Pelajaran
INSERT INTO mata_pelajaran (kode, nama_mapel) VALUES
('MTK', 'Matematika'),
('BIN', 'Bahasa Indonesia'),
('IPA', 'Ilmu Pengetahuan Alam'),
('IPS', 'Ilmu Pengetahuan Sosial'),
('PAI', 'Pendidikan Agama Islam'),
('BIG', 'Bahasa Inggris'),
('PKN', 'Pendidikan Kewarganegaraan'),
('SBK', 'Seni Budaya dan Keterampilan'),
('PJK', 'Pendidikan Jasmani'),
('AQD', 'Akidah Akhlak'),
('FQH', 'Fiqih'),
('QHD', 'Quran Hadits'),
('SKI', 'Sejarah Kebudayaan Islam'),
('BAR', 'Bahasa Arab');

-- Pengumuman Default
INSERT INTO pengumuman (judul, isi, target, is_published) VALUES
('Selamat Datang di E-Learning MI Hidayatul Hikmah', 'Sistem E-Learning dan Manajemen Akademik MI Hidayatul Hikmah telah aktif. Silakan gunakan sistem ini untuk kegiatan belajar mengajar.', 'semua', 1);

SET FOREIGN_KEY_CHECKS = 1;
