<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Alat - LBMS</title>
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
                        <?php if ($alatDetail['gambar'] ?? null): ?>
                            <img src="/images/alat/<?= htmlspecialchars($alatDetail['gambar']) ?>"
                                 class="img-fluid rounded mb-3"
                                 alt="<?= htmlspecialchars($alatDetail['nama'] ?? 'Alat') ?>"
                                 style="max-height: 200px;">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                 style="height: 200px; margin-bottom: 1rem;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>

                        <h4><?= htmlspecialchars($alatDetail['nama'] ?? 'Nama Alat') ?></h4>
                        <p class="text-muted">Kode: <?= htmlspecialchars($alatDetail['kode'] ?? 'ALT001') ?></p>

                        <div class="mb-3">
                            <?php
                            $status = $alatDetail['status'] ?? 'tersedia';
                            $statusColors = [
                                'tersedia' => 'success',
                                'dipinjam' => 'warning',
                                'maintenance' => 'info',
                                'rusak' => 'danger'
                            ];
                            $colorClass = $statusColors[$status] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $colorClass ?>">
                                <?= ucfirst(htmlspecialchars($status)) ?>
                            </span>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAlatModal">
                                <i class="bi bi-pencil me-2"></i>Edit Alat
                            </button>
                            <button class="btn btn-outline-warning" onclick="changeStatus()">
                                <i class="bi bi-arrow-repeat me-2"></i>Ubah Status
                            </button>
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
                                    <p class="form-control-plaintext"><?= htmlspecialchars($alatDetail['nama'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tahun Pembuatan</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($alatDetail['tahun'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-<?= $colorClass ?>">
                                            <?= ucfirst(htmlspecialchars($status)) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Kode Alat</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($alatDetail['kode'] ?? 'ALT001') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted">Keterangan</label>
                            <p class="form-control-plaintext">
                                <?= nl2br(htmlspecialchars($alatDetail['keterangan'] ?? 'Tidak ada keterangan')) ?>
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

                        <hr>

                        <h6 class="text-primary mb-3">
                            <i class="bi bi-clock-history me-1"></i>Riwayat Peminjaman
                        </h6>

                        <!-- Sample loan history -->
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>No Peminjaman</th>
                                        <th>Peminjam</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>TRX001</td>
                                        <td>John Doe</td>
                                        <td>15/12/2024</td>
                                        <td>18/12/2024</td>
                                        <td><span class="badge bg-success">Selesai</span></td>
                                    </tr>
                                    <tr>
                                        <td>TRX002</td>
                                        <td>Jane Smith</td>
                                        <td>12/12/2024</td>
                                        <td>-</td>
                                        <td><span class="badge bg-warning">Berjalan</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Alat Modal -->
        <div class="modal fade" id="editAlatModal" tabindex="-1" aria-labelledby="editAlatModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAlatModalLabel">
                            <i class="bi bi-pencil me-2"></i>Edit Alat
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="/alat/update/<?= $alatDetail['id'] ?? '' ?>" id="editAlatForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editNama" class="form-label">Nama Alat</label>
                                        <input type="text" class="form-control" id="editNama" name="nama" value="<?= htmlspecialchars($alatDetail['nama'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editTahun" class="form-label">Tahun Pembuatan</label>
                                        <input type="number" class="form-control" id="editTahun" name="tahun" value="<?= htmlspecialchars($alatDetail['tahun'] ?? '') ?>" min="1900" max="<?= date('Y') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editStatus" class="form-label">Status</label>
                                        <select class="form-control" id="editStatus" name="status" required>
                                            <option value="tersedia" <?= ($alatDetail['status'] ?? '') === 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                            <option value="dipinjam" <?= ($alatDetail['status'] ?? '') === 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                                            <option value="maintenance" <?= ($alatDetail['status'] ?? '') === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                            <option value="rusak" <?= ($alatDetail['status'] ?? '') === 'rusak' ? 'selected' : '' ?>>Rusak</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editKeterangan" class="form-label">Keterangan</label>
                                        <textarea class="form-control" id="editKeterangan" name="keterangan" rows="4"><?= htmlspecialchars($alatDetail['keterangan'] ?? '') ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editGambar" class="form-label">Ganti Foto Alat (opsional)</label>
                                        <input type="file" class="form-control" id="editGambar" name="gambar" accept="image/*">
                                        <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah foto</small>
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
            window.changeStatus = function() {
                const currentStatus = '<?= $alatDetail['status'] ?? 'tersedia' ?>';
                const newStatus = prompt('Masukkan status baru (tersedia/dipinjam/maintenance/rusak):', currentStatus);

                if (newStatus && ['tersedia', 'dipinjam', 'maintenance', 'rusak'].includes(newStatus.toLowerCase())) {
                    window.location.href = '/alat/change-status/<?= $alatDetail['id'] ?? '' ?>/' + newStatus.toLowerCase();
                } else if (newStatus) {
                    alert('Status tidak valid. Pilih: tersedia, dipinjam, maintenance, atau rusak');
                }
            };

            window.deleteAlat = function() {
                if (confirm('Apakah Anda yakin ingin menghapus alat ini? Tindakan ini tidak dapat dibatalkan.')) {
                    window.location.href = '/alat/delete/<?= $alatDetail['id'] ?? '' ?>';
                }
            };

            // Form validation
            $('#editAlatForm').on('submit', function(e) {
                const tahun = parseInt($('#editTahun').val());
                const tahunSekarang = new Date().getFullYear();

                if (tahun < 1900 || tahun > tahunSekarang) {
                    e.preventDefault();
                    alert(`Tahun harus antara 1900 dan ${tahunSekarang}`);
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
</body>
</html>
