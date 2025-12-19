<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS removed - not needed for dashboard table -->
    <link href="/assets/css/main.css?v=<?php echo date('YmHis'); ?>" rel="stylesheet">
    <style>
        /* Minimal custom styles - using Bootstrap 5.3 components */
        .actions-container {
            margin-bottom: 2rem;
            padding: 0 2rem;
        }

        /* Table responsive styles */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #dashboardTable {
            margin-bottom: 0;
        }

        #dashboardTable th {
            white-space: nowrap;
            font-weight: 600;
            font-size: 0.875rem;
            background-color: #f8f9fa;
        }

        #dashboardTable td {
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Action buttons responsive */
        .action-buttons {
            min-width: 120px;
        }

        .btn-group .btn {
            min-width: auto;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .btn-group-sm .btn {
            padding: 0.125rem 0.25rem;
            font-size: 0.7rem;
        }

        /* Mobile optimization */
        @media (max-width: 768px) {
            .table-responsive {
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
            }

            #dashboardTable th,
            #dashboardTable td {
                padding: 0.5rem 0.25rem;
                font-size: 0.75rem;
            }

            .badge {
                font-size: 0.625rem;
                padding: 0.25rem 0.5rem;
            }
        }

        /* Ensure proper scrolling on all devices */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
        <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="/images/logo.webp" alt="LBMS Logo">
            </div>
        </div>

        <nav class="sidebar-menu">
            <a href="/dashboard" class="sidebar-menu-item active">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
            <a href="/users" class="sidebar-menu-item">
                <i class="bi bi-people"></i>
                Data User
            </a>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/alat" class="sidebar-menu-item">
                    <i class="bi bi-wrench"></i>
                    Manajemen Alat
                </a>
            <?php endif; ?>
            <a href="/peminjaman" class="sidebar-menu-item">
                <i class="bi bi-hand-index"></i>
                Peminjaman
            </a>
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

        <nav class="top-navbar" id="topNavbar">
        <div class="d-flex align-items-center">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <div class="navbar-right">
            <div class="d-flex align-items-center">
                <!-- Notification Icon -->
                <a href="/notifications" class="btn btn-outline-secondary me-3 position-relative">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                </a>

                <!-- User Profile -->
                <div class="user-profile">
                    <div class="user-info text-end">
                        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Admin User') ?></div>
                        <div class="user-role"><?= htmlspecialchars($user['role'] ?? 'Admin') ?></div>
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
        <h1 class="display-4 fw-bold mb-4">Dashboard</h1>

        <!-- Statistics -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-box"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Peminjaman Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Peminjaman Selesai</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Total User</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-graph-up"></i>
                </div>
                <div class="stat-number">0</div>
                <div class="stat-label">Total Selesai</div>
            </div>
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
            <h5 class="mb-0">Peminjaman Terbaru</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                    <table id="dashboardTable" class="table table-striped table-hover" style="min-width: 800px;">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>NO PEMINJAMAN</th>
                                <th>TIPE PEMINJAMAN</th>
                                <th>ITEM</th>
                                <th>TANGGAL PINJAM</th>
                                <th>TANGGAL BERAKHIR</th>
                                <th>STATUS</th>
                                <th>ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentPeminjaman)): ?>
                                <?php foreach ($recentPeminjaman as $index => $peminjaman): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>TRX<?php echo str_pad($peminjaman['id'] ?? '000', 3, '0', STR_PAD_LEFT); ?></td>
                                        <td>Alat Laboratorium</td>
                                        <td><?php echo htmlspecialchars($peminjaman['nama_alat'] ?? 'Unknown'); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($peminjaman['tanggal_pinjam'] ?? 'now')); ?></td>
                                        <td><?php echo date('d-m-Y', strtotime($peminjaman['tanggal_kembali'] ?? 'now')); ?></td>
                                        <td>
                                            <?php
                                            $status = strtoupper($peminjaman['status'] ?? 'UNKNOWN');
                                            $badgeClass = 'bg-secondary';
                                            switch ($status) {
                                                case 'PENDING':
                                                    $badgeClass = 'bg-warning';
                                                    break;
                                                case 'DIPINJAM':
                                                    $badgeClass = 'bg-info';
                                                    break;
                                                case 'SELESAI':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                case 'DITOLAK':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                            <?php if ($status === 'PENDING'): ?>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="/peminjaman/<?php echo $peminjaman['id']; ?>/detail"
                                                       class="btn btn-outline-primary" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-success"
                                                            onclick="prosesPeminjaman(<?php echo $peminjaman['id']; ?>)"
                                                            title="Proses">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger"
                                                            onclick="tolakPeminjaman(<?php echo $peminjaman['id']; ?>)"
                                                            title="Tolak">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            <?php elseif ($status === 'DITOLAK' || $status === 'SELESAI'): ?>
                                                <a href="/peminjaman/<?php echo $peminjaman['id']; ?>/detail"
                                                   class="btn btn-sm btn-outline-primary" title="Detail">
                                                    <i class="bi bi-eye me-1"></i>Detail
                                                </a>
                                            <?php else: ?>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="/peminjaman/<?php echo $peminjaman['id']; ?>/detail"
                                                       class="btn btn-outline-primary" title="Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if ($status === 'DIPINJAM'): ?>
                                                        <button type="button" class="btn btn-outline-success"
                                                                onclick="selesaikanPeminjaman(<?php echo $peminjaman['id']; ?>)"
                                                                title="Selesaikan">
                                                            <i class="bi bi-check2-circle"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="empty-state-row">
                                    <td class="text-center text-muted py-4" colspan="8">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        Belum ada data peminjaman
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables removed - not needed for dashboard table -->
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

            // Logout confirmation
            $('form[action="/logout"]').on('submit', function(e) {
                if (!confirm('Apakah Anda yakin ingin keluar?')) {
                    e.preventDefault();
                }
            });

            // Dashboard table - removed DataTables to avoid column count issues
            // Table now uses Bootstrap styling and responsive design only
            console.log('Dashboard loaded successfully - DataTables disabled for better compatibility');

            console.log('Dashboard loaded successfully');
        });

        // Peminjaman action functions
        function prosesPeminjaman(id) {
            if (confirm('Apakah Anda yakin ingin menyetujui peminjaman ini?')) {
                fetch(`/peminjaman/${id}/proses`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal memproses peminjaman: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses peminjaman');
                });
            }
        }

        function tolakPeminjaman(id) {
            const alasan = prompt('Alasan penolakan:');
            if (alasan !== null) {
                fetch(`/peminjaman/${id}/tolak`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        alasan: alasan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menolak peminjaman: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menolak peminjaman');
                });
            }
        }

        function selesaikanPeminjaman(id) {
            if (confirm('Apakah Anda yakin ingin menyelesaikan peminjaman ini?')) {
                fetch(`/peminjaman/${id}/selesaikan`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Gagal menyelesaikan peminjaman: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyelesaikan peminjaman');
                });
            }
        }
    </script>
</body>
</html>