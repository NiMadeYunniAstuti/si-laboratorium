<?php
$title = 'Detail Peminjaman - LBMS';
$current_route = '/peminjaman';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';

$status = $peminjamanDetail['status'] ?? 'PENDING';
$statusColors = [
    'PENDING' => 'warning',
    'DISETUJUI' => 'info',
    'DIPINJAM' => 'primary',
    'SELESAI' => 'success',
    'DIBATALKAN' => 'danger',
    'TERLAMBAT' => 'secondary',
    'DITOLAK' => 'danger'
];
$colorClass = $statusColors[$status] ?? 'secondary';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="page-header px-0 mb-4">
        <div class="d-flex align-items-center">
            <a href="/peminjaman" class="btn btn-outline-primary btn-sm me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">Detail Peminjaman</h1>
                <p class="page-subtitle">Informasi lengkap transaksi peminjaman #<?= htmlspecialchars($peminjamanDetail['id'] ?? 'TRX') ?></p>
            </div>
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

    <div class="row g-4">
        <!-- Status & Overview -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="status-indicator mb-4">
                        <div class="loan-icon mx-auto mb-3">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <span class="badge bg-<?= $colorClass ?>-subtle text-<?= $colorClass ?> border border-<?= $colorClass ?>-subtle px-3 py-2 fs-6">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </div>
                    
                    <h4 class="fw-bold mb-1">ID Transaksi</h4>
                    <p class="text-muted mb-4">#<?= htmlspecialchars($peminjamanDetail['id'] ?? 'Unknown') ?></p>

                    <hr class="my-4 opacity-50">

                    <div class="d-grid gap-2">
                        <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                            <?php if ($status === 'PENDING'): ?>
                                <button type="button" class="btn btn-primary status-action" data-status="DIPINJAM">
                                    <i class="bi bi-check-lg me-2"></i>Setujui Peminjaman
                                </button>
                                <button type="button" class="btn btn-outline-danger status-action" data-status="DITOLAK">
                                    <i class="bi bi-x-lg me-2"></i>Tolak Peminjaman
                                </button>
                            <?php elseif ($status === 'DIPINJAM'): ?>
                                <button type="button" class="btn btn-success status-action" data-status="SELESAI">
                                    <i class="bi bi-arrow-return-left me-2"></i>Selesaikan (Kembali)
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i>Cetak Bukti
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-primary">Informasi Peminjaman</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="info-label d-block text-muted small text-uppercase fw-bold mb-1">Peminjam</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <span class="fw-semibold"><?= htmlspecialchars($peminjamanDetail['nama_peminjam'] ?? 'Unknown') ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="info-label d-block text-muted small text-uppercase fw-bold mb-1">Item Dipinjam</label>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    <i class="bi <?= ($peminjamanDetail['item_type'] ?? '') === 'alat' ? 'bi-tools' : 'bi-door-open' ?>"></i>
                                </div>
                                <div>
                                    <?php if (($peminjamanDetail['item_type'] ?? '') === 'alat'): ?>
                                        <div class="fw-semibold"><?= htmlspecialchars($peminjamanDetail['nama_alat'] ?? 'Unknown') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($peminjamanDetail['kode_alat'] ?? '-') ?></small>
                                    <?php elseif (($peminjamanDetail['item_type'] ?? '') === 'ruangan'): ?>
                                        <div class="fw-semibold"><?= htmlspecialchars($peminjamanDetail['nama_ruangan'] ?? 'Unknown') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($peminjamanDetail['kode_ruangan'] ?? '-') ?></small>
                                    <?php else: ?>
                                        <div class="fw-semibold">Unknown</div>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="info-label d-block text-muted small text-uppercase fw-bold mb-1">Tanggal Pinjam</label>
                            <div class="fw-medium">
                                <i class="bi bi-calendar-event me-2 text-primary"></i>
                                <?= date('d F Y', strtotime($peminjamanDetail['tanggal_pinjam'] ?? 'now')) ?>
                                <span class="text-muted ms-1"><?= date('H:i', strtotime($peminjamanDetail['tanggal_pinjam'] ?? 'now')) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="info-label d-block text-muted small text-uppercase fw-bold mb-1">Estimasi Kembali</label>
                            <div class="fw-medium">
                                <i class="bi bi-calendar-check me-2 text-warning"></i>
                                <?= date('d F Y', strtotime($peminjamanDetail['tanggal_kembali'] ?? 'now')) ?>
                                <span class="text-muted ms-1"><?= date('H:i', strtotime($peminjamanDetail['tanggal_kembali'] ?? 'now')) ?></span>
                            </div>
                        </div>

                        <?php if ($status === 'SELESAI'): ?>
                        <div class="col-md-6">
                            <label class="info-label d-block text-muted small text-uppercase fw-bold mb-1">Tanggal Dikembalikan</label>
                            <div class="fw-medium text-success">
                                <i class="bi bi-check-all me-2"></i>
                                <?= date('d F Y H:i', strtotime($peminjamanDetail['updated_at'] ?? 'now')) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-12">
                            <label class="info-label d-block text-muted small text-uppercase fw-bold mb-1">Catatan / Keterangan</label>
                            <div class="p-3 bg-light rounded border-start border-4 border-primary">
                                <?= nl2br(htmlspecialchars($peminjamanDetail['catatan'] ?? $peminjamanDetail['keterangan'] ?? '-')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Trail / History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-primary">Riwayat Status</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3">Waktu</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 py-3 text-muted small"><?= date('d/m/Y H:i', strtotime($peminjamanDetail['created_at'] ?? 'now')) ?></td>
                                    <td><span class="badge bg-warning-subtle text-warning">PENDING</span></td>
                                    <td class="small">Pengajuan dibuat oleh peminjam</td>
                                </tr>
                                <?php if ($status !== 'PENDING'): ?>
                                <tr>
                                    <td class="ps-4 py-3 text-muted small"><?= date('d/m/Y H:i', strtotime($peminjamanDetail['updated_at'] ?? 'now')) ?></td>
                                    <td><span class="badge bg-<?= $colorClass ?>-subtle text-<?= $colorClass ?>"><?= $status ?></span></td>
                                    <td class="small">Status diperbarui oleh sistem/admin</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        $('.status-action').on('click', function() {
            const status = $(this).data('status');
            const id = '<?= $peminjamanDetail['id'] ?? '' ?>';
            let action = '';
            let method = 'POST';
            
            if (status === 'DIPINJAM') action = 'proses';
            else if (status === 'DITOLAK') action = 'tolak';
            else if (status === 'SELESAI') {
                action = 'selesaikan';
            }

            if (!action) return;

            let payload = null;
            if (status === 'DITOLAK') {
                const alasan = prompt('Alasan penolakan (opsional):') || 'Peminjaman ditolak';
                if (!confirm('Apakah Anda yakin ingin menolak peminjaman ini?')) return;
                payload = { alasan };
            } else {
                if (!confirm(`Apakah Anda yakin ingin memproses peminjaman ini ke status ${status}?`)) return;
            }

            fetch(`/peminjaman/${id}/${action}`, {
                method: method,
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
    });
</script>

<style>
    .loan-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-600), var(--primary-800));
        color: white;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
    }
    
    .avatar-sm {
        flex-shrink: 0;
    }
</style>
</body>
</html>