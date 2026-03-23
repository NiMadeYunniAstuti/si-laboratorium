<?php
$title = 'Tambah User Baru - LBMS';
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
            <h1 class="page-title h3 fw-bold mb-0">Tambah User Baru</h1>
            <p class="text-muted mb-0">Daftarkan pengguna baru ke dalam sistem</p>
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
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Data Pengguna Baru</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="/users/create" id="tambahUserForm">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="name" class="form-label small fw-semibold">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="name" name="name" 
                                           placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" 
                                           placeholder="nama@email.com" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label small fw-semibold">Role / Hak Akses</label>
                                <select class="form-select select2-enable" id="role" name="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="USER">User</option>
                                    <option value="ADMIN">Admin</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="password" name="password" 
                                           placeholder="Masukkan password" required>
                                    <button class="btn btn-light border border-start-0" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="confirmPassword" class="form-label small fw-semibold">Konfirmasi Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" class="form-control border-start-0 border-end-0 ps-0" id="confirmPassword" name="confirmPassword" 
                                           placeholder="Ulangi password" required>
                                    <button class="btn btn-light border border-start-0" type="button" id="toggleConfirmPassword">
                                        <i class="bi bi-eye" id="confirmPasswordIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                            <a href="/users" class="btn btn-light rounded-pill px-4 me-2">Batal</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">
                                <i class="bi bi-check-circle me-2"></i>Simpan User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Panduan Pengisian</h6>
                    <ul class="small text-muted ps-3 mb-0">
                        <li class="mb-2">Gunakan alamat email aktif sebagai identitas login unik.</li>
                        <li class="mb-2">Password sebaiknya terdiri dari minimal 8 karakter dengan kombinasi huruf dan angka.</li>
                        <li class="mb-2"><b>Admin:</b> Memiliki akses ke manajemen inventaris dan pengguna.</li>
                        <li><b>User:</b> Memiliki akses terbatas untuk peminjaman alat.</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-warning border-0 shadow-sm small p-4">
                <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
                Pastikan data yang dimasukkan sudah benar. Password tidak akan ditampilkan kembali setelah disimpan demi keamanan.
            </div>
        </div>
    </div>
</main>

<?php 
ob_start(); 
?>
<script>
    $(document).ready(function() {
        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const passwordIcon = $('#passwordIcon');

            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                passwordIcon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                passwordIcon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        $('#toggleConfirmPassword').on('click', function() {
            const confirmPasswordField = $('#confirmPassword');
            const confirmPasswordIcon = $('#confirmPasswordIcon');

            if (confirmPasswordField.attr('type') === 'password') {
                confirmPasswordField.attr('type', 'text');
                confirmPasswordIcon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                confirmPasswordField.attr('type', 'password');
                confirmPasswordIcon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        $('#tambahUserForm').on('submit', function(e) {
            const password = $('#password').val();
            const confirmPassword = $('#confirmPassword').val();

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
                return false;
            }

            return true;
        });
    });
</script>
<?php 
$extra_js = ob_get_clean();
require_once __DIR__ . '/../layouts/footer.php'; 
?>
