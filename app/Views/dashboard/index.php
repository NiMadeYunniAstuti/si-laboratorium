<?php
$title = 'Dashboard - LBMS';
$current_route = '/dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="welcome-banner p-4 p-lg-5 mb-4 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-800) 100%);">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-6 fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($user['nama'] ?? 'User') ?>! 👋</h1>
                <p class="lead mb-0 opacity-75">Kelola aset dan pantau aktivitas peminjaman laboratorium dengan mudah dalam satu dashboard.</p>
            </div>
            <div class="col-lg-4 d-none d-lg-block text-end">
                <i class="bi bi-rocket-takeoff display-1 opacity-25"></i>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card-hover transition-all">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-warning-subtle text-warning rounded-3 p-3 me-3">
                            <i class="bi bi-hourglass-split fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium text-uppercase ls-1">Pending</div>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['pending'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="small">
                        <span class="text-warning fw-semibold">Butuh Review</span>
                        <span class="text-muted ms-1">peminjaman baru</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card-hover transition-all">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success-subtle text-success rounded-3 p-3 me-3">
                            <i class="bi bi-check-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium text-uppercase ls-1">Selesai</div>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['selesai'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="small">
                        <span class="text-success fw-semibold">Telah Kembali</span>
                        <span class="text-muted ms-1">total transaksi</span>
                    </div>
                </div>
            </div>
        </div>
        <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card-hover transition-all">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary-subtle text-primary rounded-3 p-3 me-3">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium text-uppercase ls-1">User</div>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['users'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="small">
                        <span class="text-primary fw-semibold">Terdaftar</span>
                        <span class="text-muted ms-1">pengguna aktif</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stat-card-hover transition-all">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-info-subtle text-info rounded-3 p-3 me-3">
                            <i class="bi bi-box-seam-fill fs-3"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-medium text-uppercase ls-1">Total Alat</div>
                            <h3 class="fw-bold mb-0 mt-1"><?= $stats['alat'] ?? 0 ?></h3>
                        </div>
                    </div>
                    <div class="small">
                        <span class="text-info fw-semibold">Inventaris</span>
                        <span class="text-muted ms-1">tersedia di lab</span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Terjadi Kesalahan</h6>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
            <div class="d-flex">
                <i class="bi bi-check-circle-fill me-2 mt-1"></i>
                <div>
                    <h6 class="alert-heading fw-bold mb-1">Berhasil</h6>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom-0">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Peminjaman Terbaru</h5>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table id="dashboardTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center py-3">NO</th>
                            <th class="py-3">NAMA PEMINJAM</th>
                            <th class="py-3">ITEM</th>
                            <th class="py-3">TANGGAL PINJAM</th>
                            <th class="py-3">TANGGAL KEMBALI</th>
                            <th class="py-3">STATUS</th>
                            <th class="text-center py-3">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentPeminjaman)): ?>
                            <?php foreach ($recentPeminjaman as $index => $peminjaman): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm rounded-circle bg-light text-primary d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                <?= strtoupper(substr($peminjaman['nama_peminjam'] ?? $peminjaman['user_name'] ?? 'U', 0, 1)); ?>
                                            </div>
                                            <span class="fw-medium text-dark"><?= htmlspecialchars($peminjaman['nama_peminjam'] ?? $peminjaman['user_name'] ?? '-'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-dark fw-semibold"><?= htmlspecialchars($peminjaman['nama_alat'] ?? 'Unknown'); ?></div>
                                        <small class="text-muted">Laboratorium</small>
                                    </td>
                                    <td><?= date('d M Y', strtotime($peminjaman['tanggal_pinjam'] ?? 'now')); ?></td>
                                    <td><?= date('d M Y', strtotime($peminjaman['tanggal_kembali'] ?? 'now')); ?></td>
                                    <td>
                                        <?php
                                        $status = strtoupper($peminjaman['status'] ?? 'UNKNOWN');
                                        $badgeClass = match($status) {
                                            'PENDING' => 'warning',
                                            'DIPINJAM' => 'info',
                                            'SELESAI' => 'success',
                                            'DITOLAK' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeClass ?>-subtle text-<?= $badgeClass ?> border border-<?= $badgeClass ?>-subtle rounded-pill px-3">
                                            <?= $status ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/peminjaman/<?= $peminjaman['id']; ?>/detail"
                                           class="btn btn-icon btn-sm btn-outline-primary rounded-circle" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="text-center text-muted py-5" colspan="7">
                                    <div class="empty-state py-4">
                                        <i class="bi bi-inbox display-4 d-block mb-3 text-light-emphasis"></i>
                                        <h6 class="fw-bold mb-1">Data Kosong</h6>
                                        <p class="small mb-0">Belum ada data peminjaman terbaru untuk saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
