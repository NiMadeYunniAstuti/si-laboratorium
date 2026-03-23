<?php
$title = 'Profil Akun - LBMS';
$current_route = '/settings/profile';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/sidebar.php';
require_once __DIR__ . '/../../layouts/navbar.php';
?>

<main class="main-content" id="mainContent">
    <div class="d-flex align-items-center mb-4">
        <a href="/settings" class="btn btn-outline-secondary btn-sm rounded-pill me-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <h1 class="page-title h3 fw-bold mb-0">Profil Akun</h1>
            <p class="text-muted mb-0">Kelola informasi personal Anda</p>
        </div>
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

    <div class="row">
        <div class="col-lg-4">
            <!-- Profile Preview Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <div class="current-avatar mx-auto mb-3" style="width: 100px; height: 100px; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 600;">
                        <?= substr($user['name'] ?? 'U', 0, 1) ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($user['name'] ?? 'User') ?></h5>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($user['email'] ?? 'user@example.com') ?></p>
                    <div class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-3">
                        <?= htmlspecialchars($user['role'] ?? 'USER') ?>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-camera me-2"></i>Ganti Foto Profil
                        </button>
                    </div>
                </div>
            </div>

            <!-- Account Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Status Akun</h6>
                    <div class="d-flex align-items-center">
                        <div class="bg-info-subtle text-info rounded-circle p-2 me-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <div class="small fw-semibold">Terdaftar Sejak</div>
                            <div class="text-muted smaller"><?= date('d M Y') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">Informasi Personal</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/profile" id="profileForm">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label small fw-semibold">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" 
                                           value="<?= htmlspecialchars($user['name'] ?? '') ?>" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="role" class="form-label small fw-semibold">Role</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 bg-light" id="role" 
                                           value="<?= htmlspecialchars($user['role'] ?? 'USER') ?>" disabled>
                                </div>
                                <div class="form-text smaller">Role hanya dapat diubah oleh Administrator Utama.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email_display" class="form-label small fw-semibold">Email Utama</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0 bg-light" id="email_display" 
                                           value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                                </div>
                                <div class="form-text smaller">
                                    Ingin mengubah email? 
                                    <a href="/settings/privacy-security" class="text-primary text-decoration-none fw-semibold">Ke Pengaturan Keamanan</a>
                                </div>
                            </div>

                            <div class="col-12 mt-4 pt-2 border-top">
                                <div class="d-flex justify-content-end">
                                    <button type="reset" class="btn btn-light rounded-pill px-4 me-2">Batal</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
