    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Sidebar Toggle
            function toggleSidebar() {
                $('#sidebar').toggleClass('collapsed');
                $('#topNavbar').toggleClass('sidebar-collapsed');
                $('#mainContent').toggleClass('sidebar-collapsed');

                const isCollapsed = $('#sidebar').hasClass('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }

            // Restore Sidebar State
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                $('#sidebar').addClass('collapsed');
                $('#topNavbar').addClass('sidebar-collapsed');
                $('#mainContent').addClass('sidebar-collapsed');
            }

            $('#sidebarToggle').on('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });

            // Mobile Auto-collapse
            if ($(window).width() <= 768) {
                $('#sidebar').addClass('collapsed');
                $('#topNavbar').addClass('sidebar-collapsed');
                $('#mainContent').addClass('sidebar-collapsed');
            }

            // Global Search Initialization
            const $search = $('#globalSearch');
            if ($search.length && $.fn.select2) {
                const searchItems = [
                    { id: 'dashboard', text: 'Dashboard', url: '/dashboard' },
                    { id: 'users', text: 'Users', url: '/users' },
                    { id: 'peminjaman', text: 'Peminjaman', url: '/peminjaman' },
                    { id: 'alat', text: 'Alat', url: '/alat' },
                    { id: 'profile', text: 'Profile', url: '/settings/profile' },
                    { id: 'notifications', text: 'Notifications', url: '/notifications' },
                ];

                const userRole = "<?= htmlspecialchars($user['role'] ?? 'USER') ?>";
                const filteredSearchItems = searchItems.filter(item => {
                    if (userRole !== 'ADMIN' && (item.id === 'alat' || item.id === 'users')) {
                        return false;
                    }
                    if (userRole === 'ADMIN' && item.id === 'peminjaman') {
                        return false;
                    }
                    return true;
                });

                $search.select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Cari halaman...',
                    allowClear: true,
                    data: filteredSearchItems
                });

                $search.on('select2:select', function(e) {
                    const url = e.params.data && e.params.data.url;
                    if (url) {
                        window.location.href = url;
                    }
                });
            }
        });
    </script>


    <?php if (isset($extra_js) && !empty($extra_js)): ?>
    <?= $extra_js ?>
    <?php endif; ?>
</body>
</html>