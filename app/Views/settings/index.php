<?php
$title = 'Pengaturan - LBMS';
$current_route = '/settings';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main class="main-content" id="mainContent">
    <div class="settings-container">
        <div class="settings-header mb-4">
            <h1 class="page-title h3 fw-bold mb-1">Pengaturan</h1>
            <p class="text-muted">Kelola akun dan preferensi sistem Anda</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mt-2">
            <!-- Profil Akun Card -->
            <div class="col-md-6 col-lg-5">
                <a href="/settings/profile" class="text-decoration-none h-100 d-block">
                    <div class="card h-100 border-0 shadow-sm-hover transition-all">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary-subtle text-primary rounded-3 p-3 me-3">
                                    <i class="bi bi-person-fill fs-3"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-bold mb-0">Profil Akun</h3>
                                    <small class="text-muted">Informasi personal Anda</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-4">
                                Perbarui data profil dan informasi pengguna Anda agar tetap akurat.
                            </p>
                            <div class="bg-light rounded-3 p-3">
                                <ul class="list-unstyled mb-0 small text-muted">
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Nama Lengkap</li>
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Foto Profil</li>
                                    <li class="mb-0"><i class="bi bi-check2 text-success me-2"></i>Role Pengguna</li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 p-4 pt-0 d-flex justify-content-between align-items-center">
                            <span class="text-primary small fw-semibold">Kelola profil</span>
                            <i class="bi bi-arrow-right text-primary"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Privasi & Keamanan Card -->
            <div class="col-md-6 col-lg-5">
                <a href="/settings/privacy-security" class="text-decoration-none h-100 d-block">
                    <div class="card h-100 border-0 shadow-sm-hover transition-all">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success-subtle text-success rounded-3 p-3 me-3">
                                    <i class="bi bi-shield-fill-check fs-3"></i>
                                </div>
                                <div>
                                    <h3 class="h5 fw-bold mb-0">Privasi & Keamanan</h3>
                                    <small class="text-muted">Keamanan akses akun</small>
                                </div>
                            </div>
                            <p class="text-muted small mb-4">
                                Kelola keamanan akun dengan memperbarui email dan mengganti password secara berkala.
                            </p>
                            <div class="bg-light rounded-3 p-3">
                                <ul class="list-unstyled mb-0 small text-muted">
                                    <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>Perbarui Email</li>
                                    <li class="mb-0"><i class="bi bi-check2 text-success me-2"></i>Ubah Password</li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 p-4 pt-0 d-flex justify-content-between align-items-center">
                            <span class="text-primary small fw-semibold">Amankan akun</span>
                            <i class="bi bi-arrow-right text-primary"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
