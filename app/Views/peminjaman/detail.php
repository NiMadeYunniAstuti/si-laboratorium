<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - LBMS</title>
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
                <a href="/peminjaman" class="sidebar-menu-item active">
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
            <a href="/peminjaman" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <h1 class="mb-0">Detail Peminjaman</h1>
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
            <!-- Peminjaman Info Card -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Peminjaman</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="loan-icon mb-3" style="font-size: 3rem; color: #3b82f6;">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                            <h4>Peminjaman #<?= htmlspecialchars($peminjamanDetail['id'] ?? 'TRX001') ?></h4>
                        </div>

                        <div class="mb-3 text-center">
                            <?php
                            $status = $peminjamanDetail['status'] ?? 'PENDING';
                            $statusColors = [
                                'PENDING' => 'warning',
                                'DISETUJUI' => 'info',
                                'DIPINJAM' => 'primary',
                                'SELESAI' => 'success',
                                'DIBATALKAN' => 'danger',
                                'TERLAMBAT' => 'secondary'
                            ];
                            $colorClass = $statusColors[$status] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $colorClass ?> fs-6">
                                <?= htmlspecialchars($status) ?>
                            </span>
                        </div>

                        <div class="d-grid gap-2">
                            <?php if ($status === 'PENDING'): ?>
                                <div class="btn-group" role="group" aria-label="Ubah status peminjaman">
                                    <button type="button" class="btn btn-outline-success status-action" data-status="DIPINJAM">
                                        <i class="bi bi-check-circle me-2"></i>Disetujui
                                    </button>
                                    <button type="button" class="btn btn-outline-danger status-action" data-status="DITOLAK">
                                        <i class="bi bi-x-circle me-2"></i>Ditolak
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Peminjaman Card -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Detail Lengkap</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-<?= $colorClass ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Ajukan</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y H:i', strtotime($peminjamanDetail['created_at'] ?? 'now')) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Pinjam</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($peminjamanDetail['tanggal_pinjam'] ?? 'now')) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Keterangan</label>
                            <p class="form-control-plaintext">
                                <?= nl2br(htmlspecialchars($peminjamanDetail['keterangan'] ?? '-')) ?>
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Selesai</label>
                                    <p class="form-control-plaintext">
                                        <?php
                                        if ($peminjamanDetail['tanggal_selesai'] ?? null) {
                                            echo date('d/m/Y H:i', strtotime($peminjamanDetail['tanggal_selesai']));
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </p>
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

            $('.status-action').on('click', function() {
                const status = $(this).data('status');
                const id = '<?= $peminjamanDetail['id'] ?? '' ?>';
                const action = status === 'DIPINJAM' ? 'proses' : 'tolak';
                let payload = null;

                if (status === 'DIPINJAM') {
                    if (!confirm('Apakah Anda yakin ingin menyetujui peminjaman ini?')) {
                        return;
                    }
                } else {
                    const alasan = prompt('Alasan penolakan (opsional):') || 'Peminjaman ditolak';
                    if (!confirm('Apakah Anda yakin ingin menolak peminjaman ini?')) {
                        return;
                    }
                    payload = { alasan };
                }

                fetch(`/peminjaman/${id}/${action}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: payload ? JSON.stringify(payload) : null
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                            return;
                        }
                        alert(data.message || 'Gagal memperbarui status peminjaman');
                    })
                    .catch(() => {
                        alert('Terjadi kesalahan saat memperbarui status peminjaman');
                    });
            });

            if ($(window).width() <= 768) {
                $('#sidebar').addClass('collapsed');
                $('#topNavbar').addClass('sidebar-collapsed');
                $('#mainContent').addClass('sidebar-collapsed');
            }
        });
    </script>

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -15px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 4px rgba(0,0,0,0.1);
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            border-left: 3px solid #dee2e6;
        }

        .loan-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto;
        }
    </style>

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
