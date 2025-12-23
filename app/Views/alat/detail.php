<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Alat - LBMS</title>
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
            <a href="/users" class="sidebar-menu-item">
                <i class="bi bi-people"></i>
                Data User
            </a>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/alat" class="sidebar-menu-item active">
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
            <a href="/alat" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <h1 class="mb-0">Detail Alat</h1>
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
            <!-- Tool Image Card -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Foto Alat</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($alatDetail['gambar'])): ?>
                            <img src="/<?= ltrim(htmlspecialchars($alatDetail['gambar']), '/') ?>"
                                 class="img-fluid rounded mb-3"
                                 alt="<?= htmlspecialchars($alatDetail['nama_alat'] ?? 'Alat') ?>"
                                 style="max-height: 200px;">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                 style="height: 200px; margin-bottom: 1rem;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>

                        <h4><?= htmlspecialchars($alatDetail['nama_alat'] ?? 'Nama Alat') ?></h4>
                        <p class="text-muted">Kode: <?= htmlspecialchars($alatDetail['kode_alat'] ?? '-') ?></p>

                        <div class="mb-3">
                            <?php
                            $status = strtoupper($alatDetail['status'] ?? 'TERSEDIA');
                            $statusColors = [
                                'TERSEDIA' => 'success',
                                'DIPINJAM' => 'warning',
                                'MAINTENANCE' => 'info',
                                'RUSAK' => 'danger'
                            ];
                            $colorClass = $statusColors[$status] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $colorClass ?>">
                                <?= ucfirst(strtolower(htmlspecialchars($status))) ?>
                            </span>
                        </div>

                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-primary" href="/alat/<?= $alatDetail['id'] ?? '' ?>/edit">
                                <i class="bi bi-pencil me-2"></i>Edit Alat
                            </a>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <select class="form-select" id="alatStatusSelect" style="">
                                    <option value="TERSEDIA" <?= $status === 'TERSEDIA' ? 'selected' : '' ?>>Tersedia</option>
                                    <option value="DIPINJAM" <?= $status === 'DIPINJAM' ? 'selected' : '' ?>>Dipinjam</option>
                                    <option value="MAINTENANCE" <?= $status === 'MAINTENANCE' ? 'selected' : '' ?>>Maintenance</option>
                                    <option value="RUSAK" <?= $status === 'RUSAK' ? 'selected' : '' ?>>Rusak</option>
                                </select>
                                <button class="btn btn-outline-primary" id="applyStatusChange">
                                    <i class="bi bi-check2-circle me-2"></i>Ubah Status
                                </button>
                            </div>
                            <button class="btn btn-outline-danger" onclick="deleteAlat()">
                                <i class="bi bi-trash me-2"></i>Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tool Details Card -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Lengkap</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Nama Alat</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($alatDetail['nama_alat'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tahun Pembelian</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($alatDetail['tahun_pembelian'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-<?= $colorClass ?>">
                                            <?= ucfirst(strtolower(htmlspecialchars($status))) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Kode Alat</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($alatDetail['kode_alat'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Keterangan</label>
                            <p class="form-control-plaintext">
                                <?= nl2br(htmlspecialchars($alatDetail['deskripsi'] ?? 'Tidak ada keterangan')) ?>
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Ditambahkan</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($alatDetail['created_at'] ?? 'now')) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Terakhir Update</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y H:i', strtotime($alatDetail['updated_at'] ?? 'now')) ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
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

            $('#applyStatusChange').on('click', function() {
                const newStatus = $('#alatStatusSelect').val();
                const currentStatus = '<?= strtoupper($alatDetail['status'] ?? 'TERSEDIA') ?>';

                if (!newStatus || newStatus === currentStatus) {
                    return;
                }

                if (confirm(`Ubah status alat menjadi ${newStatus}?`)) {
                    window.location.href = '/alat/change-status/<?= $alatDetail['id'] ?? '' ?>/' + newStatus;
                }
            });

            window.deleteAlat = function() {
                if (confirm('Apakah Anda yakin ingin menghapus alat ini? Tindakan ini tidak dapat dibatalkan.')) {
                    window.location.href = '/alat/delete/<?= $alatDetail['id'] ?? '' ?>';
                }
            };

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
