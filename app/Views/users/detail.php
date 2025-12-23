<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
        <div class="d-flex align-items-center mb-4">
            <a href="/users" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <h1 class="mb-0">Detail User</h1>
        </div>

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

        <div class="row">
            <!-- User Profile Card -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Profil User</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="current-avatar mb-3" style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto;">
                            <?= substr($userDetail['name'] ?? 'User', 0, 1) ?>
                        </div>
                        <h4><?= htmlspecialchars($userDetail['name'] ?? 'User Name') ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($userDetail['role'] ?? 'USER') ?></p>

                        <div class="mb-3">
                            <?php $detailStatus = strtoupper($userDetail['status'] ?? 'ACTIVE'); ?>
                            <?php if ($detailStatus === 'ACTIVE'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php elseif ($detailStatus === 'BLACKLIST'): ?>
                                <span class="badge bg-dark">Blacklist</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Non-aktif</span>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-primary" href="/users/<?= $userDetail['id'] ?? '' ?>/edit">
                                <i class="bi bi-pencil me-2"></i>Edit User
                            </a>
                            <?php if (($userDetail['id'] ?? null) !== ($user['id'] ?? null) && $detailStatus !== 'BLACKLIST'): ?>
                                <div class="btn-group" role="group" aria-label="User status actions">
                                    <?php if ($detailStatus === 'INACTIVE'): ?>
                                        <button class="btn btn-outline-success status-action"
                                                data-id="<?= $userDetail['id'] ?? '' ?>"
                                                data-status="ACTIVE">
                                            <i class="bi bi-person-check me-2"></i>Aktifkan
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-danger status-action"
                                                data-id="<?= $userDetail['id'] ?? '' ?>"
                                                data-status="INACTIVE"
                                                <?= $detailStatus === 'INACTIVE' || $detailStatus === 'BLACKLIST' ? 'disabled' : '' ?>>
                                            <i class="bi bi-person-dash me-2"></i>Non-aktifkan
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-dark status-action"
                                            data-id="<?= $userDetail['id'] ?? '' ?>"
                                            data-status="BLACKLIST"
                                            <?= $detailStatus === 'BLACKLIST' ? 'disabled' : '' ?>>
                                        <i class="bi bi-shield-x me-2"></i>Blacklist
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details Card -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Lengkap</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">NIK</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($userDetail['nik'] ?? '-') ?></p>
                                </div>
                            </div> -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Email</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($userDetail['email'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">No Telepon</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($userDetail['phone'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Role</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-primary"><?= htmlspecialchars($userDetail['role'] ?? 'USER') ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Dibuat</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($userDetail['created_at'] ?? 'now')) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Terakhir Update</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y H:i', strtotime($userDetail['updated_at'] ?? 'now')) ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">
                            <i class="bi bi-pencil me-2"></i>Edit User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="/users/update/<?= $userDetail['id'] ?? '' ?>" id="editUserForm">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editName" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="editName" name="name" value="<?= htmlspecialchars($userDetail['name'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="editEmail" name="email" value="<?= htmlspecialchars($userDetail['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editPhone" class="form-label">No Telepon</label>
                                        <input type="tel" class="form-control" id="editPhone" name="phone" value="<?= htmlspecialchars($userDetail['phone'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editRole" class="form-label">Role</label>
                                        <select class="form-control" id="editRole" name="role" required>
                                            <option value="USER" <?= ($userDetail['role'] ?? '') === 'USER' ? 'selected' : '' ?>>User</option>
                                            <option value="ADMIN" <?= ($userDetail['role'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editStatus" class="form-label">Status</label>
                                        <select class="form-control" id="editStatus" name="status" required>
                                            <option value="ACTIVE" <?= strtoupper($userDetail['status'] ?? '') === 'ACTIVE' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="INACTIVE" <?= strtoupper($userDetail['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Non-aktif</option>
                                            <option value="BLACKLIST" <?= strtoupper($userDetail['status'] ?? '') === 'BLACKLIST' ? 'selected' : '' ?>>Blacklist</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editPassword" class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                                        <input type="password" class="form-control" id="editPassword" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Sidebar toggle functionality
            function toggleSidebar() {
                $('#sidebar').toggleClass('collapsed');
                $('#topNavbar').toggleClass('sidebar-collapsed');
                $('#mainContent').toggleClass('sidebar-collapsed');

                const isCollapsed = $('#sidebar').hasClass('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }

            // Restore sidebar state
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

            // Update user status (inactive/blacklist)
            $('.status-action').on('click', function() {
                const userId = $(this).data('id');
                const status = $(this).data('status');
                const message = status === 'BLACKLIST'
                    ? 'Apakah Anda yakin ingin mem-blacklist user ini? User tidak akan dapat melakukan peminjaman.'
                    : 'Apakah Anda yakin ingin menonaktifkan user ini?';

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

            // Form validation
            $('#editUserForm').on('submit', function(e) {
                const password = $('#editPassword').val();

                if (password && password.length < 8) {
                    e.preventDefault();
                    alert('Password minimal harus 8 karakter!');
                    return false;
                }

                return true;
            });

            // Mobile sidebar handling
            if ($(window).width() <= 768) {
                $('#sidebar').addClass('collapsed');
                $('#topNavbar').addClass('sidebar-collapsed');
                $('#mainContent').addClass('sidebar-collapsed');
            }
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
