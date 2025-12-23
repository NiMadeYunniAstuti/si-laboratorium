<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/assets/css/main.css?v=<?php echo date('YmHis'); ?>" rel="stylesheet">
  </head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="/images/logo.webp" alt="LBMS Logo">
            </div>
        </div>

        <nav class="sidebar-menu">
            <a href="/dashboard" class="sidebar-menu-item">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
            <a href="/users" class="sidebar-menu-item active">
                <i class="bi bi-people"></i>
                Data User
            </a>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/alat" class="sidebar-menu-item">
                    <i class="bi bi-wrench"></i>
                    Manajemen Alat
                </a>
            <?php endif; ?>
            <a href="/settings" class="sidebar-menu-item">
                <i class="bi bi-gear"></i>
                Settings
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout" class="sidebar-menu-item logout-item">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </div>
    </aside>

    <!-- Top Navbar -->
    <nav class="top-navbar" id="topNavbar">
        <div class="d-flex align-items-center">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <!-- Global Search -->
            <div class="ms-3 flex-grow-1 d-none d-md-block global-search-wrapper" style="">
                <select id="globalSearch" class="form-select" style="width: 100%;">
                    <option value="">Cari</option>
                </select>
            </div>
        </div>

        <div class="navbar-right">
            <div class="d-flex align-items-center">
                <!-- Notification Icon -->
                <a href="/notifications" class="btn btn-outline-secondary me-3 position-relative">
                    <i class="bi bi-bell"></i>
                    <?php if (($unreadNotificationCount ?? 0) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $unreadNotificationCount ?>
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User Profile -->
                <div class="user-profile">
                    <div class="user-info text-end">
                        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Admin User') ?></div>
                        <div class="user-role"><?= htmlspecialchars($user['role'] ?? 'ADMIN') ?></div>
                    </div>
                    <div class="user-avatar ms-2">
                        <?= substr($user['name'] ?? 'Admin User', 0, 1) ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <h1 class="mb-4">Data User</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pengguna</h5>
                <a href="/users/new" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tambah User
                </a>
            </div>
            <div class="card-body">
                <!-- Users DataTable -->
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-hover">
              <thead>
                  <tr>
                      <th>NO</th>
                      <th>NAMA</th>
                      <th>EMAIL</th>
                      <th>STATUS</th>
                      <th>ACTION</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if (!empty($users)): ?>
                      <?php $currentUserId = $user['id'] ?? null; ?>
                      <?php foreach ($users as $index => $userItem): ?>
                      <?php $statusUpper = strtoupper($userItem['status'] ?? 'ACTIVE'); ?>
                      <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= htmlspecialchars($userItem['name'] ?? '') ?></td>
                          <td><?= htmlspecialchars($userItem['email'] ?? '') ?></td>
                          <td>
                              <span class="badge bg-<?= $statusUpper === 'ACTIVE' ? 'success' : ($statusUpper === 'BLACKLIST' ? 'dark' : 'danger') ?>">
                                  <?= $statusUpper === 'BLACKLIST' ? 'Blacklist' : ($statusUpper === 'ACTIVE' ? 'Active' : 'Nonaktif') ?>
                              </span>
                          </td>
                          <td>
                              <div class="d-flex gap-2">
                                  <a href="/users/<?= $userItem['id'] ?>" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                      <i class="bi bi-eye"></i> Detail
                                  </a>
                                  <?php if (($userItem['id'] ?? null) !== $currentUserId && $statusUpper !== 'BLACKLIST'): ?>
                                      <div class="btn-group d-flex flex-nowrap" role="group">
                                          <button class="btn btn-sm btn-outline-<?= $statusUpper === 'ACTIVE' ? 'danger' : 'success' ?> toggle-status flex-shrink-0"
                                                  data-id="<?= $userItem['id'] ?>"
                                                  data-status="<?= $statusUpper === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' ?>">
                                              <i class="bi bi-<?= $statusUpper === 'ACTIVE' ? 'pause' : 'play' ?>"></i>
                                              <?= $statusUpper === 'ACTIVE' ? 'Nonaktif' : 'Aktifkan' ?>
                                          </button>
                                          <button class="btn btn-sm btn-outline-dark blacklist-user flex-shrink-0"
                                                  data-id="<?= $userItem['id'] ?>"
                                                  data-status="BLACKLIST">
                                              <i class="bi bi-shield-x"></i> Blacklist
                                          </button>
                                      </div>
                                  <?php endif; ?>
                              </div>
                          </td>
                      </tr>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
                </div>
            </div>
        </div>

            </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script>
        $(document).ready(function() {
            function toggleSidebar() {
                $('#sidebar').toggleClass('collapsed');
                $('#topNavbar').toggleClass('sidebar-collapsed');
                $('#mainContent').toggleClass('sidebar-collapsed');

                const isCollapsed = $('#sidebar').hasClass('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }

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

            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#usersTable').DataTable({
                    responsive: true,
                    paging: true,
                    searching: false,
                    ordering: false,
                    info: false,
                    lengthChange: false,
                    pageLength: 10,
                    scrollX: true,
                    scrollCollapse: false,
                    autoWidth: false,
                    responsive: false,
                    language: {
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Selanjutnya",
                            previous: "Sebelumnya"
                        },
                        emptyTable: `<div class="text-center py-4">
                            <i class="bi bi-inbox display-6 text-muted"></i>
                            <div class="mt-2 text-muted">Tidak ada data tersedia dalam tabel</div>
                        </div>`,
                        zeroRecords: "Tidak ditemukan data yang cocok"
                    },
                    drawCallback: function() {
                        const api = this.api();
                        const showPagination = api.data().count() > 0;
                        $(api.table().container())
                            .find('.dataTables_paginate')
                            .toggle(showPagination);
                    }
                });
            } else {
                console.warn('jQuery or DataTables is not loaded');
            }

            $('#tambahUserForm').on('submit', function(e) {
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak cocok');
                    return false;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password minimal 8 karakter');
                    return false;
                }
            });

            $('.toggle-status, .blacklist-user').on('click', function() {
                const userId = $(this).data('id');
                const status = $(this).data('status');
                const message = status === 'BLACKLIST'
                    ? 'Apakah Anda yakin ingin mem-blacklist user ini? User tidak akan dapat melakukan peminjaman.'
                    : (status === 'ACTIVE'
                        ? 'Apakah Anda yakin ingin mengaktifkan user ini?'
                        : 'Apakah Anda yakin ingin menonaktifkan user ini?');

                if (!confirm(message)) {
                    return;
                }

                fetch(`/users/${userId}/update/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                            return;
                        }
                        alert(data.message || 'Gagal memperbarui status user');
                    })
                    .catch(() => {
                        alert('Terjadi kesalahan saat memperbarui status user');
                    });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            const $search = $('#globalSearch');
            if (!$search.length || !$.fn.select2) {
                return;
            }

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
                return true;
            });

            $search.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari',
                allowClear: true,
                data: filteredSearchItems
            });

            $search.on('select2:select', function(e) {
                const url = e.params.data && e.params.data.url;
                if (url) {
                    window.location.href = url;
                }
            });
        });
    </script>

</body>
</html>
