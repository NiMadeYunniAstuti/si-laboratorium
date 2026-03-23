<?php
$title = 'Edit User - LBMS';
$current_route = '/users';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main class="main-content" id="mainContent">
    <div class="d-flex align-items-center mb-4">
        <a href="/users" class="btn btn-outline-secondary btn-sm rounded-pill me-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <h1 class="page-title h3 fw-bold mb-0">Edit User</h1>
            <p class="text-muted mb-0">Perbarui informasi dan hak akses pengguna</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Data Pengguna</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/users/<?= $userDetail['id'] ?? '' ?>/update" id="editUserForm">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="name" class="form-label small fw-semibold">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" 
                                           value="<?= htmlspecialchars($userDetail['name'] ?? '') ?>" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                           value="<?= htmlspecialchars($userDetail['email'] ?? '') ?>" placeholder="nama@email.com" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label small fw-semibold">Role / Hak Akses</label>
                                <select class="form-select select2-enable" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="USER" <?= ($userDetail['role'] ?? '') === 'USER' ? 'selected' : '' ?>>User</option>
                                    <option value="ADMIN" <?= ($userDetail['role'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <a href="/users" class="btn btn-light rounded-pill px-4 me-2">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">
                                <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 text-center">
                    <div class="user-avatar-preview mx-auto mb-3" style="width: 100px; height: 100px; background: linear-gradient(135deg, #2563eb, #3b82f6); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 600;">
                        <?= substr($userDetail['name'] ?? 'U', 0, 1) ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($userDetail['name'] ?? 'User') ?></h5>
                    <p class="text-muted small mb-0"><?= htmlspecialchars($userDetail['email'] ?? 'user@example.com') ?></p>
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm small p-4">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle-fill me-2"></i>Informasi Role</h6>
                <ul class="mb-0 ps-3">
                    <li class="mb-2"><b>Admin:</b> Memiliki akses penuh ke seluruh modul sistem.</li>
                    <li><b>User:</b> Hanya dapat melakukan peminjaman dan mengelola profil mandiri.</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
