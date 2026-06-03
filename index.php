<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'super_admin') redirect('/admin/index.php');
    elseif (strpos($role, 'guru_kelas_') === 0) redirect('/guru/index.php');
    elseif ($role === 'siswa') redirect('/siswa/index.php');
    elseif ($role === 'wali_murid') redirect('/wali/index.php');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login E-Learning MI Hidayatul Hikmah">
    <title>Login | MI Hidayatul Hikmah</title>
    
    <!-- Instant Dark Mode -->
    <script>
    (function(){
        var t = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
        document.documentElement.setAttribute('data-bs-theme', t);
    })();
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: time() ?>" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-card fade-in">
        <!-- Logo -->
        <div class="login-logo">🕌</div>
        
        <h1 class="login-title">MI Hidayatul Hikmah</h1>
        <p class="login-subtitle">E-Learning & Manajemen Akademik</p>
        
        <!-- Flash Message -->
        <?php
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show fs-14" role="alert">
                ' . $flash['message'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
        ?>
        
        <form action="login_process.php" method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="bi bi-person me-1"></i> Username
                </label>
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder="Masukkan username" required autofocus>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="bi bi-lock me-1"></i> Password
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Masukkan password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 py-2 mt-2" style="font-size:15px;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
        
        <div class="text-center mt-4">
            <small style="color:var(--text-secondary);">
                &copy; <?= date('Y') ?> MI Hidayatul Hikmah<br>
                <span class="text-green">بِسْمِ اللّٰهِ الرَّحْمٰنِ الرَّحِيْمِ</span>
            </small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const pw = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pw.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});

// Dark mode persistence
const savedTheme = localStorage.getItem('theme') || 'light';
document.documentElement.setAttribute('data-theme', savedTheme);
</script>
</body>
</html>
