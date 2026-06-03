    </div><!-- /.main-content -->
    
    <!-- Footer -->
    <footer class="main-footer" style="margin-left:var(--sidebar-width);">
        <p>&copy; <?= date('Y') ?> <strong>MI Hidayatul Hikmah</strong> — E-Learning & Manajemen Akademik</p>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <!-- Custom JS -->
    <script src="<?= $basePath ?>/assets/js/script.js?v=<?= $jsVersion ?? time() ?>"></script>
</body>
</html>
