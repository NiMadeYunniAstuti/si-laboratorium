<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
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
                <a href="/alat" class="sidebar-menu-item">
                    <i class="bi bi-wrench"></i>
                    Manajemen Alat
                </a>
            <?php endif; ?>
            <a href="/peminjaman" class="sidebar-menu-item active">
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

    <!-- Top Navbar -->
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

                        <div class="mb-3">
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
                            <?php if ($status === 'PENDING' || $status === 'DIBATALKAN'): ?>
                                <button class="btn btn-outline-primary" onclick="ubahStatus()">
                                    <i class="bi bi-pencil me-2"></i>Ubah Status
                                </button>
                            <?php endif; ?>

                            <?php if ($status === 'DIPINJAM'): ?>
                                <button class="btn btn-outline-success" onclick="kembalikanAlat()">
                                    <i class="bi bi-arrow-return-left me-2"></i>Kembalikan Alat
                                </button>
                            <?php endif; ?>

                            <button class="btn btn-outline-danger" onclick="batalkanPeminjaman()">
                                <i class="bi bi-x-circle me-2"></i>Batalkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Peminjam Card -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">Data Peminjam</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Nama:</strong> <?= htmlspecialchars($peminjamanDetail['nama_peminjam'] ?? '-') ?>
                        </div>
                        <div class="mb-2">
                            <strong>NIM/NIP:</strong> <?= htmlspecialchars($peminjamanDetail['nim_nip'] ?? '-') ?>
                        </div>
                        <div class="mb-2">
                            <strong>Keperluan:</strong><br>
                            <small><?= nl2br(htmlspecialchars($peminjamanDetail['keperluan'] ?? '-')) ?></small>
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
                                    <label class="form-label text-muted">No Peminjaman</label>
                                    <p class="form-control-plaintext fw-bold">#<?= htmlspecialchars($peminjamanDetail['id'] ?? 'TRX001') ?></p>
                                </div>
                            </div>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Estimasi Kembali</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($peminjamanDetail['tanggal_kembali'] ?? 'now')) ?></p>
                                </div>
                            </div>
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Jumlah</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($peminjamanDetail['jumlah'] ?? '1') ?> buah</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Surat Pengantar</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($peminjamanDetail['surat'] ?? 'Tidak ada') ?></p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="text-primary mb-3">
                            <i class="bi bi-box me-1"></i>Alat yang Dipinjam
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama Alat</th>
                                        <th>Kode</th>
                                        <th>Kategori</th>
                                        <th>Kondisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Mikroskop Digital</td>
                                        <td><span class="badge bg-primary">LT001</span></td>
                                        <td>Alat Optik</td>
                                        <td><span class="badge bg-success">Baik</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr>

                        <h6 class="text-primary mb-3">
                            <i class="bi bi-clock-history me-1"></i>Riwayat Perubahan Status
                        </h6>

                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($peminjamanDetail['created_at'] ?? 'now')) ?></small>
                                    <div><strong>Pending</strong> - Pengajuan peminjaman</div>
                                </div>
                            </div>

                            <?php if ($status === 'DIPINJAM' || $status === 'SELESAI' || $status === 'TERLAMBAT'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($peminjamanDetail['tanggal_pinjam'] ?? 'now')) ?></small>
                                    <div><strong>Dipinjam</strong> - Alat diserahkan</div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($status === 'SELESAI'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($peminjamanDetail['tanggal_selesai'] ?? 'now')) ?></small>
                                    <div><strong>Selesai</strong> - Alat dikembalikan</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ubah Status Modal -->
        <div class="modal fade" id="ubahStatusModal" tabindex="-1" aria-labelledby="ubahStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ubahStatusModalLabel">
                            <i class="bi bi-pencil me-2"></i>Ubah Status Peminjaman
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="/peminjaman/update-status/<?= $peminjamanDetail['id'] ?? '' ?>" id="ubahStatusForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="new_status" class="form-label">Status Baru</label>
                                <select class="form-control" id="new_status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="DISETUJUI" <?= ($peminjamanDetail['status'] ?? '') === 'DISETUJUI' ? 'selected' : '' ?>>Disetujui</option>
                                    <option value="DIBATALKAN" <?= ($peminjamanDetail['status'] ?? '') === 'DIBATALKAN' ? 'selected' : '' ?>>Dibatalkan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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

            // Action functions
            window.ubahStatus = function() {
                $('#ubahStatusModal').modal('show');
            };

            window.kembalikanAlat = function() {
                if (confirm('Apakah Anda yakin ingin mengembalikan alat ini?')) {
                    window.location.href = '/peminjaman/kembalikan/<?= $peminjamanDetail['id'] ?? '' ?>';
                }
            };

            window.batalkanPeminjaman = function() {
                if (confirm('Apakah Anda yakin ingin membatalkan peminjaman ini?')) {
                    window.location.href = '/peminjaman/batalkan/<?= $peminjamanDetail['id'] ?? '' ?>';
                }
            };

            // Form validation
            $('#ubahStatusForm').on('submit', function(e) {
                const status = $('#new_status').val();

                if (!status) {
                    e.preventDefault();
                    alert('Silakan pilih status baru');
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
</body>
</html>