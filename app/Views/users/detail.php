<?php
$title = 'Detail User - LBMS';
$current_route = '/users';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';

$detailStatus = strtoupper($userDetail['status'] ?? 'ACTIVE');
$statusColors = [
    'ACTIVE' => 'success',
    'INACTIVE' => 'danger',
    'BLACKLIST' => 'dark'
];
$colorClass = $statusColors[$detailStatus] ?? 'secondary';
?>

<main class="main-content" id="mainContent">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <a href="/users" class="btn btn-outline-secondary btn-sm rounded-pill me-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <div>
                <h1 class="page-title h3 fw-bold mb-0">Detail User</h1>
                <p class="text-muted mb-0">Informasi profil dan hak akses pengguna</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="/users/<?= $userDetail['id'] ?? '' ?>/edit" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-pencil me-2"></i>Edit User
            </a>
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
        <!-- Left: User Identity Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-4">
                    <div class="user-avatar-preview mx-auto" style="width: 120px; height: 120px; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 600; shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);">
                        <?= substr($userDetail['name'] ?? 'U', 0, 1) ?>
                    </div>
                </div>

                <h4 class="fw-bold mb-1"><?= htmlspecialchars($userDetail['name'] ?? 'User Name') ?></h4>
                <p class="text-muted small mb-3"><?= htmlspecialchars($userDetail['email'] ?? 'user@example.com') ?></p>
                
                <div class="mb-4">
                    <span class="badge bg-<?= $colorClass ?>-subtle text-<?= $colorClass ?> px-3 py-2 rounded-pill fw-semibold border border-<?= $colorClass ?>">
                        <i class="bi bi-circle-fill me-1 smaller"></i> <?= $detailStatus ?>
                    </span>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold border border-primary ms-1">
                        <i class="bi bi-shield-lock me-1"></i> <?= htmlspecialchars($userDetail['role'] ?? 'USER') ?>
                    </span>
                </div>

                <?php if (($userDetail["id"] ?? null) !== ($user["id"] ?? null) && $detailStatus !== "BLACKLIST"): ?>
                    <div class="bg-light rounded-4 p-3 mb-2">
                        <label class="form-label small fw-bold text-muted d-block mb-3">Tindakan Cepat</label>
                        <div class="d-grid gap-2">
                            <?php if ($detailStatus === 'INACTIVE'): ?>
                                <button class="btn btn-success btn-sm rounded-pill status-action" data-id="<?= $userDetail['id'] ?>" data-status="ACTIVE">
                                    <i class="bi bi-person-check me-2"></i>Aktifkan User
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-danger btn-sm rounded-pill status-action" data-id="<?= $userDetail['id'] ?>" data-status="INACTIVE">
                                    <i class="bi bi-person-dash me-2"></i>Non-aktifkan
                                </button>
                            <?php endif; ?>
                            
                            <?php if ($detailStatus !== 'BLACKLIST'): ?>
                                <button class="btn btn-outline-dark btn-sm rounded-pill status-action" data-id="<?= $userDetail['id'] ?>" data-status="BLACKLIST">
                                    <i class="bi bi-shield-x me-2"></i>Blacklist User
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: User details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0">Informasi Lengkap</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="icon-box bg-white text-primary rounded-pill p-2 me-3 shadow-sm">
                                    <i class="bi bi-envelope fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Email</div>
                                    <div class="fw-bold"><?= htmlspecialchars($userDetail['email'] ?? '-') ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="icon-box bg-white text-primary rounded-pill p-2 me-3 shadow-sm">
                                    <i class="bi bi-shield-lock fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Role Akses</div>
                                    <div class="fw-bold"><?= htmlspecialchars($userDetail['role'] ?? 'USER') ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                <div class="icon-box bg-white text-primary rounded-pill p-2 me-3 shadow-sm">
                                    <i class="bi bi-calendar-check fs-5"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">Terdaftar Sejak</div>
                                    <div class="fw-bold"><?= date('d M Y', strtotime($userDetail['created_at'] ?? 'now')) ?></div>
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
                                    <div class="fw-bold"><?= date('d M Y, H:i', strtotime($userDetail['updated_at'] ?? 'now')) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm rounded-4 p-4 mt-auto">
                        <h6 class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-2"></i>Catatan Sistem</h6>
                        <p class="small mb-0 text-muted">
                            Status <b>ACTIVE</b> memungkinkan user untuk login dan melakukan transaksi. 
                            Status <b>BLACKLIST</b> akan memblokir seluruh akses peminjaman alat secara permanen kecuali diaktifkan kembali oleh Admin.
                        </p>
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
        $('.status-action').on('click', function() {
            const userId = $(this).data('id');
            const status = $(this).data('status');
            const message = status === 'BLACKLIST'
                ? 'Apakah Anda yakin ingin mem-blacklist user ini? User tidak akan dapat melakukan peminjaman.'
                : (status === 'ACTIVE' ? 'Aktifkan kembali user ini?' : 'Nonaktifkan user ini?');

            if (!confirm(message)) {
                return;
            }

            fetch(`/users/${userId}/update/status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                    return;
                }
                alert(data.message || 'Gagal memperbarui status user');
            })
            .catch(() => {
                alert('Terjadi kesalahan saat memperbarui status user');
            });
        });
    });
</script>
<?php 
$extra_js = ob_get_clean();
require_once __DIR__ . '/../layouts/footer.php'; 
?>
