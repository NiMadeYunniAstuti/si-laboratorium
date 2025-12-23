<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />    <link href="/assets/css/main.css?v=<?php echo date('YmHis'); ?>" rel="stylesheet">
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
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/dashboard" class="sidebar-menu-item">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            <?php endif; ?>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/users" class="sidebar-menu-item active">
                    <i class="bi bi-people"></i>
                    Data User
                </a>
            <?php endif; ?>
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
            <div class="ms-3 flex-grow-1" style="">
                <select id="globalSearch" class="form-select" style="width: 100%;">
                    <option value="">Search...</option>
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
                            <?php echo $unreadNotificationCount; ?>
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    <?php endif; ?>                </a>

                <!-- User Profile -->
                <div class="user-profile">
                    <div class="user-info text-end">
                        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Admin User') ?></div>
                        <div class="user-role"><?= ucfirst(strtolower(htmlspecialchars($user['role'] ?? 'ADMIN'))) ?></div>
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
            <h1 class="mb-0">Edit User</h1>
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

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil me-2"></i>Edit User
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/users/<?= $userDetail['id'] ?? '' ?>/update" id="editUserForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-1"></i>Informasi Pribadi
                            </h6>

                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlspecialchars($userDetail['name'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= htmlspecialchars($userDetail['email'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-shield-lock me-1"></i>Status Akun
                            </h6>

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="USER" <?= ($userDetail['role'] ?? '') === 'USER' ? 'selected' : '' ?>>User</option>
                                    <option value="ADMIN" <?= ($userDetail['role'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <small class="form-text text-muted">Pilih akses level untuk user</small>
                            </div>

                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="/users" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x-circle me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
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

                // Save sidebar state to localStorage
                const isCollapsed = $('#sidebar').hasClass('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }

            // Restore sidebar state from localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                $('#sidebar').addClass('collapsed');
                $('#topNavbar').addClass('sidebar-collapsed');
                $('#mainContent').addClass('sidebar-collapsed');
            }

            // Sidebar toggle click handler
            $('#sidebarToggle').on('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });

            // Mobile sidebar handling

            // Initialize Select2 for global search
            $('#globalSearch').select2({
                theme: 'bootstrap-5',
                placeholder: 'Search...',
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: function() {
                        // Placeholder - will be configured later with actual search endpoint
                        return '/api/search';
                    },
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        // Placeholder - will be configured later with actual data processing
                        return {
                            results: data.items || []
                        };
                    },
                    cache: true
                },
                templateResult: function(item) {
                    if (item.loading) {
                        return 'Searching...';
                    }
                    // Placeholder - will be configured later with actual display template
                    return item.text || item.name || item.title;
                },
                templateSelection: function(item) {
                    // Placeholder - will be configured later with actual selection template
                    return item.text || item.name || item.title || 'Search...';
                }
            });

            // Handle search selection
            $('#globalSearch').on('select2:select', function(e) {
                const data = e.params.data;
                if (data && data.url) {
                    window.location.href = data.url;
                }
            });
            // Mobile sidebar handling
            if ($(window).width() <= 768) {
                $('#sidebar').addClass('collapsed');
                $('#topNavbar').addClass('sidebar-collapsed');
                $('#mainContent').addClass('sidebar-collapsed');
            }

            // Handle window resize
            $(window).on('resize', function() {
                if ($(window).width() > 768) {
                    // Restore desktop state
                    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (!sidebarCollapsed) {
                        $('#sidebar').removeClass('collapsed');
                        $('#topNavbar').removeClass('sidebar-collapsed');
                        $('#mainContent').removeClass('sidebar-collapsed');
                    }
                } else {
                    // Always collapse sidebar on mobile
                    $('#sidebar').addClass('collapsed', 'mobile-active');
                    $('#topNavbar').addClass('sidebar-collapsed');
                    $('#mainContent').addClass('sidebar-collapsed');
                }
            });

            // Form validation
            $('#editUserForm').on('submit', function(e) {
                const name = $('#name').val().trim();
                const email = $('#email').val().trim();
                const role = $('#role').val();

                if (name.length < 3) {
                    e.preventDefault();
                    alert('Nama harus memiliki minimal 3 karakter');
                    return false;
                }

                if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    e.preventDefault();
                    alert('Masukkan alamat email yang valid');
                    return false;
                }

                if (!role) {
                    e.preventDefault();
                    alert('Pilih role untuk user');
                    return false;
                }

                return true;
            });
        });
    </script>
</body>
</html>
