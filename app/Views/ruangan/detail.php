<?php
$title = 'Detail Ruangan - LBMS';
$current_route = '/ruangan';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';

$status = strtoupper($ruanganDetail['status'] ?? 'TERSEDIA');
$statusColors = [
    'TERSEDIA' => 'success',
    'DIPINJAM' => 'warning',
    'MAINTENANCE' => 'info',
    'RUSAK' => 'danger'
];
$colorClass = $statusColors[$status] ?? 'secondary';
require_once __DIR__ . '/../../helpers/format_helpers.php';
?>

<main class="main-content" id="mainContent">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="/ruangan" class="btn btn-outline-secondary btn-sm rounded-pill me-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h1 class="page-title h3 fw-bold mb-0">Detail Ruangan</h1>
                <p class="text-muted mb-0">Informasi fasilitas dan ketersediaan ruangan</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="/ruangan/<?= $ruanganDetail['id'] ?? '' ?>/edit" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-pencil me-2"></i>Edit Ruangan
            </a>
            <button class="btn btn-outline-danger rounded-pill px-4" onclick="deleteRuangan()">
                <i class="bi bi-trash me-2"></i>Hapus
            </button>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left: Room Identity Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-4">
                    <?php if (!empty($ruanganDetail['gambar'])): ?>
                        <img src="/<?= ltrim(htmlspecialchars($ruanganDetail['gambar']), '/') ?>" 
                             class="img-fluid rounded-4 shadow-sm border" 
                             style="max-height: 250px; width: 100%; object-fit: cover;" 
                             alt="<?= htmlspecialchars($ruanganDetail['nama_ruangan'] ?? 'Ruangan') ?>">
                    <?php else: ?>
                        <div class="bg-light rounded-4 d-flex align-items-center justify-content-center border border-dashed" style="height: 200px;">
                            <div class="text-muted">
                                <i class="bi bi-door-open fs-1 d-block mb-2"></i>
                                <span class="small">Belum ada foto</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <h4 class="fw-bold mb-1"><?= htmlspecialchars($ruanganDetail['nama_ruangan'] ?? 'Nama Ruangan') ?></h4>
                <p class="text-muted small mb-3">Kode: <span class="fw-semibold"><?= htmlspecialchars($ruanganDetail['kode_ruangan'] ?? '-') ?></span></p>
                
                <div class="mb-4">
                    <span class="badge bg-<?= $colorClass ?>-subtle text-<?= $colorClass ?> px-3 py-2 rounded-pill fw-semibold border border-<?= $colorClass ?>">
                        <i class="bi bi-circle-fill me-1 smaller"></i> <?= $status ?>
                    </span>
                </div>

                <div class="bg-light rounded-4 p-3 mb-2">
                    <label class="form-label small fw-bold text-muted d-block mb-3">Ubah Status Cepat</label>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm rounded-pill" id="ruanganStatusSelect">
                            <option value="TERSEDIA" <?= $status === 'TERSEDIA' ? 'selected' : '' ?>>Tersedia</option>
                            <option value="DIPINJAM" <?= $status === 'DIPINJAM' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="MAINTENANCE" <?= $status === 'MAINTENANCE' ? 'selected' : '' ?>>Maintenance</option>
                            <option value="RUSAK" <?= $status === 'RUSAK' ? 'selected' : '' ?>>Rusak</option>
                        </select>
                        <button class="btn btn-primary btn-sm rounded-pill px-3" id="applyStatusChange">
                            Ubah
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Room Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Informasi Fasilitas</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="icon-box bg-white text-primary rounded-pill p-2 me-3 shadow-sm">
                                    <i class="bi bi-building fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Gedung / Lantai</div>
                                    <div class="fw-bold"><?= htmlspecialchars($ruanganDetail['gedung'] ?? '-') ?> (Lantai <?= htmlspecialchars($ruanganDetail['lantai'] ?? '-') ?>)</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="icon-box bg-white text-primary rounded-pill p-2 me-3 shadow-sm">
                                    <i class="bi bi-people fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Kapasitas</div>
                                    <div class="fw-bold"><?= htmlspecialchars($ruanganDetail['kapasitas'] ?? '0') ?> Orang</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="icon-box bg-white text-primary rounded-pill p-2 me-3 shadow-sm">
                                    <i class="bi bi-collection fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Jenis Ruangan</div>
                                    <div class="fw-bold"><?= htmlspecialchars(formatKategoriName($ruanganDetail['kategori_name'] ?? '-')) ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="icon-box bg-white text-primary rounded-pill p-2 me-3 shadow-sm">
                                    <i class="bi bi-clock-history fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Update Terakhir</div>
                                    <div class="fw-bold"><?= date('d M Y, H:i', strtotime($ruanganDetail['updated_at'] ?? 'now')) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-muted text-uppercase smaller ls-wider mb-3">Deskripsi & Fasilitas</h6>
                        <div class="p-4 bg-light rounded-4 text-muted border border-dashed">
                            <?= nl2br(htmlspecialchars($ruanganDetail['deskripsi'] ?? 'Tidak ada informasi fasilitas tambahan.')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
ob_start(); 
?>
<script>
    $(document).ready(function() {
        $('#applyStatusChange').on('click', function() {
            const newStatus = $('#ruanganStatusSelect').val();
            const currentStatus = '<?= strtoupper($ruanganDetail['status'] ?? 'TERSEDIA') ?>';

            if (!newStatus || newStatus === currentStatus) {
                return;
            }

            if (confirm(`Apakah Anda yakin ingin mengubah status ruangan menjadi ${newStatus}?`)) {
                window.location.href = '/ruangan/change-status/<?= $ruanganDetail['id'] ?? '' ?>/' + newStatus;
            }
        });

        window.deleteRuangan = function() {
            if (confirm('Apakah Anda yakin ingin menghapus ruangan ini? Tindakan ini tidak dapat dibatalkan.')) {
                window.location.href = '/ruangan/delete/<?= $ruanganDetail['id'] ?? '' ?>';
            }
        };
    });
</script>
<?php 
$extra_js = ob_get_clean();
require_once __DIR__ . '/../layouts/footer.php';
echo $extra_js; 
?>
