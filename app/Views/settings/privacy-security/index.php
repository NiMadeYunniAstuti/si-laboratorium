<?php
$title = 'Keamanan Akun - LBMS';
$current_route = '/settings/privacy-security';
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
            <h1 class="page-title h3 fw-bold mb-0">Keamanan Akun</h1>
            <p class="text-muted mb-0">Kelola akses dan keamanan akun Anda</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 p-4 pb-0">
                    <h5 class="fw-bold mb-0">Email & Password</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/settings/privacy-security/update" id="privacySecurityForm">
                        <!-- Email Section -->
                        <div class="mb-4 pb-4 border-bottom">
                            <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-text smaller text-muted">Email ini digunakan untuk masuk ke sistem dan menerima notifikasi penting.</div>
                        </div>

                        <!-- Password Section -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3 small text-uppercase text-muted letter-spacing-1">Ubah Password</h6>
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label small fw-semibold">Password Saat Ini</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" id="current_password" name="current_password" 
                                           placeholder="Masukkan password saat ini">
                                </div>
                                <div class="form-text smaller text-muted">Diperlukan untuk memverifikasi identitas Anda saat mengubah password.</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="new_password" class="form-label small fw-semibold">Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                        <input type="password" class="form-control border-start-0 ps-0" id="new_password" name="new_password" 
                                               placeholder="Password minimal 8 karakter">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" class="form-control border-start-0 ps-0" id="confirm_password" name="confirm_password" 
                                               placeholder="Ulangi password baru">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-2 d-flex justify-content-end">
                            <button type="button" onclick="resetForm()" class="btn btn-light rounded-pill px-4 me-2">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-shield-check me-2"></i>Simpan Keamanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-primary text-white mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="bi bi-info-circle fs-4"></i>
                        </div>
                        <h6 class="fw-bold mb-0">Tips Keamanan</h6>
                    </div>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-3 d-flex align-items-start">
                            <i class="bi bi-dot me-1 fs-5"></i>
                            Gunakan kombinasi huruf besar, kecil, angka, dan simbol.
                        </li>
                        <li class="mb-3 d-flex align-items-start">
                            <i class="bi bi-dot me-1 fs-5"></i>
                            Hindari menggunakan informasi personal seperti tanggal lahir.
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="bi bi-dot me-1 fs-5"></i>
                            Ganti password Anda secara berkala setidaknya 3-6 bulan sekali.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="bg-warning-subtle text-warning rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Peringatan Penting</h6>
                    <p class="text-muted small mb-0">
                        Jika Anda mengganti email atau password, Anda akan diminta untuk masuk kembali menggunakan kredensial baru.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php 
// Add extra JS for form validation
ob_start(); 
?>
<script>
    $(document).ready(function() {
        $('#privacySecurityForm').on('submit', function(e) {
            const currentPassword = $('#current_password').val();
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();

            if (newPassword || confirmPassword) {
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Password saat ini harus diisi untuk mengubah password');
                    return false;
                }

                if (newPassword.length < 8) {
                    e.preventDefault();
                    alert('Password baru minimal 8 karakter');
                    return false;
                }

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Password baru dan konfirmasi password tidak cocok');
                    return false;
                }
            }

            return true;
        });

        window.resetForm = function() {
            if (confirm('Batalkan perubahan?')) {
                $('#privacySecurityForm')[0].reset();
            }
        };
    });
</script>
<?php 
$extra_js = ob_get_clean();
require_once __DIR__ . '/../../layouts/footer.php'; 
?>
