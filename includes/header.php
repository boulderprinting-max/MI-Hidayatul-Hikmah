<?php
// Tentukan base path
$basePath = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) $basePath = '..';
elseif (strpos($_SERVER['PHP_SELF'], '/guru/') !== false) $basePath = '..';
elseif (strpos($_SERVER['PHP_SELF'], '/siswa/') !== false) $basePath = '..';
elseif (strpos($_SERVER['PHP_SELF'], '/wali/') !== false) $basePath = '..';
else $basePath = '.';

$user = currentUser();
$pageTitle = $pageTitle ?? 'MI Hidayatul Hikmah';
$cssVersion = @filemtime(__DIR__ . '/../assets/css/style.css') ?: time();
$jsVersion  = @filemtime(__DIR__ . '/../assets/js/script.js') ?: time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="E-Learning &amp; Manajemen Akademik MI Hidayatul Hikmah">
    <title><?= $pageTitle ?> | MI Hidayatul Hikmah</title>
    
    <!-- Instant Dark Mode (sebelum apapun dirender) -->
    <script>
    (function(){
        var t = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', t);
        document.documentElement.setAttribute('data-bs-theme', t);
    })();
    </script>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= $basePath ?>/assets/css/style.css?v=<?= $cssVersion ?>" rel="stylesheet">
</head>
<body>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Navbar -->
<nav class="main-navbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-link d-lg-none p-0 text-dark" id="sidebarToggle" style="font-size:22px;">
            <i class="bi bi-list"></i>
        </button>
        <div>
            <h6 class="mb-0 fw-600" style="color:var(--text-primary);">
                <?= $pageTitle ?>
            </h6>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <button class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode"></button>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown">
                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                     style="width:36px;height:36px;background:var(--primary);color:white;font-size:14px;font-weight:600;">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <div class="d-none d-md-block">
                    <div class="fw-500 fs-14" style="color:var(--text-primary);"><?= clean($user['nama']) ?></div>
                    <div class="fs-13" style="color:var(--text-secondary);"><?= ucwords(str_replace('_', ' ', $user['role'])) ?></div>
                </div>
                <i class="bi bi-chevron-down fs-13" style="color:var(--text-secondary);"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $basePath ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
