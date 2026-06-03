<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

$username = clean($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    setFlash('danger', 'Username dan password wajib diisi!');
    redirect('/index.php');
}

try {
    // Cari user berdasarkan username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'aktif'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Login berhasil - set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect berdasarkan role
        $role = $user['role'];
        if ($role === 'super_admin') {
            redirect('/admin/index.php');
        } elseif (strpos($role, 'guru_kelas_') === 0) {
            redirect('/guru/index.php');
        } elseif ($role === 'siswa') {
            redirect('/siswa/index.php');
        } elseif ($role === 'wali_murid') {
            redirect('/wali/index.php');
        }
    } else {
        setFlash('danger', 'Username atau password salah!');
        redirect('/index.php');
    }
} catch (PDOException $e) {
    setFlash('danger', 'Terjadi kesalahan sistem. Silakan coba lagi.');
    redirect('/index.php');
}
