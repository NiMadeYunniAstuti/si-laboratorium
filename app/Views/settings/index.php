<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="/assets/css/main.css?v=<?php echo date('YmHis'); ?>" rel="stylesheet">
    <style>
        /* Settings-specific styles using Bootstrap */
        .settings-container {
            padding: 2rem 0;
        }

        .settings-header {
            margin-bottom: 2rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .settings-card-link {
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease;
        }

        .settings-card-link:hover {
            text-decoration: none;
            color: inherit;
            transform: translateY(-4px);
        }

        .settings-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .settings-icon.profile {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
        }

        .settings-icon.security {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .settings-features {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .settings-features li {
            padding: 0.25rem 0;
            color: #6b7280;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }

        .settings-features i {
            color: #10b981;
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }

        .settings-arrow {
            color: #3b82f6;
            font-size: 1.125rem;
            transition: transform 0.2s ease;
        }

        .settings-card-link:hover .settings-arrow {
            transform: translateX(4px);
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
    </style>
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
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/users" class="sidebar-menu-item">
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
            <?php if (($user['role'] ?? 'USER') === 'USER'): ?>
                <a href="/peminjaman" class="sidebar-menu-item">
                    <i class="bi bi-hand-index"></i>
                    Peminjaman
                </a>
            <?php endif; ?>
            <a href="/settings" class="sidebar-menu-item active">
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
        <div class="settings-container">
            <div class="settings-header">
                <h1 class="page-title">Pengaturan</h1>
                <p class="page-description">Kelola akun dan preferensi sistem Anda</p>
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

            <div class="settings-grid">
                <!-- Profil Akun Card -->
                <a href="/settings/profile" class="settings-card-link">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="settings-icon profile">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h3 class="card-title h5">Profil Akun</h3>
                            <p class="card-text text-muted">
                                Perbarui data profil dan informasi pengguna Anda
                            </p>
                            <ul class="settings-features">
                                <li><i class="bi bi-check-fill"></i> Nama lengkap</li>
                                <li><i class="bi bi-check-fill"></i> No. telepon</li>
                                <li><i class="bi bi-check-fill"></i> Foto profil</li>
                                <li><i class="bi bi-check-fill"></i> Role Pengguna</li>
                            </ul>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                            <small class="text-muted">Kelola profil</small>
                            <i class="bi bi-arrow-right settings-arrow"></i>
                        </div>
                    </div>
                </a>

                <!-- Privasi & Keamanan Card -->
                <a href="/settings/privacy-security" class="settings-card-link">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="settings-icon security">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <h3 class="card-title h5">Privasi & Keamanan</h3>
                            <p class="card-text text-muted">
                                Kelola keamanan akun dengan email dan password
                            </p>
                            <ul class="settings-features">
                                <li><i class="bi bi-check-fill"></i> Perbarui email</li>
                                <li><i class="bi bi-check-fill"></i> Ubah password</li>
                            </ul>
                        </div>
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                            <small class="text-muted">Amankan akun</small>
                            <i class="bi bi-arrow-right settings-arrow"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

            console.log('Settings index page loaded successfully');
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
