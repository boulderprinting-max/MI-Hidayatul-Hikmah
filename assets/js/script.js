// =====================================================
// MI Hidayatul Hikmah - E-Learning
// Custom JavaScript
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== Dark Mode Toggle =====
    const darkModeToggle = document.getElementById('darkModeToggle');
    // Tema sudah diterapkan oleh inline script di <head>, jadi tidak perlu set ulang di sini.
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
        });
    }
    
    // ===== Sidebar Toggle (Mobile) =====
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('show');
        });
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    }
    
    // ===== Active Sidebar Link =====
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.sidebar-menu a').forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
        }
    });
    
    // ===== Konfirmasi Hapus dengan SweetAlert2 =====
    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href') || this.dataset.url;
            const nama = this.dataset.nama || 'data ini';
            
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: 'Data "' + nama + '" akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
    
    // ===== Auto-hide Alerts =====
    document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
        setTimeout(function() {
            alert.classList.remove('show');
            setTimeout(function() { alert.remove(); }, 300);
        }, 5000);
    });
    
    // ===== DataTables Init =====
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.data-table').DataTable({
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                emptyTable: 'Tidak ada data tersedia',
                paginate: {
                    first: '«',
                    previous: '‹',
                    next: '›',
                    last: '»'
                }
            },
            responsive: true,
            pageLength: 10
        });
    }
    
    // ===== Tooltip Init =====
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(el) {
        return new bootstrap.Tooltip(el);
    });
    
});
