<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - LBMS</title>
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
            <a href="/users" class="sidebar-menu-item active">
                <i class="bi bi-people"></i>
                Data User
            </a>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/manajemen-alat" class="sidebar-menu-item">
                    <i class="bi bi-wrench"></i>
                    Manajemen Alat
                </a>
            <?php endif; ?>
            <a href="/peminjaman" class="sidebar-menu-item">
                <i class="bi bi-hand-index"></i>
                Peminjaman
            </a>
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
            <a href="/users" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <h1 class="mb-0">Detail User</h1>
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
            <!-- User Profile Card -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Profil User</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="current-avatar mb-3" style="width: 120px; height: 120px; font-size: 3rem; margin: 0 auto;">
                            <?= substr($userDetail['name'] ?? 'User', 0, 1) ?>
                        </div>
                        <h4><?= htmlspecialchars($userDetail['name'] ?? 'User Name') ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($userDetail['role'] ?? 'USER') ?></p>

                        <div class="mb-3">
                            <?php if (($userDetail['status'] ?? 'active') === 'active'): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Non-aktif</span>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal">
                                <i class="bi bi-pencil me-2"></i>Edit User
                            </button>
                            <?php if (($userDetail['status'] ?? 'active') === 'active'): ?>
                                <button class="btn btn-outline-danger" onclick="toggleUserStatus('<?= $userDetail['id'] ?? '' ?>')">
                                    <i class="bi bi-person-dash me-2"></i>Non-aktifkan
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-success" onclick="toggleUserStatus('<?= $userDetail['id'] ?? '' ?>')">
                                    <i class="bi bi-person-check me-2"></i>Aktifkan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details Card -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Lengkap</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">NIK</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($userDetail['nik'] ?? '-') ?></p>
                                </div>
                            </div> -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Email</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($userDetail['email'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">No Telepon</label>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($userDetail['phone'] ?? '-') ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Role</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-primary"><?= htmlspecialchars($userDetail['role'] ?? 'USER') ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Tanggal Dibuat</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y', strtotime($userDetail['created_at'] ?? 'now')) ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Terakhir Update</label>
                                    <p class="form-control-plaintext"><?= date('d/m/Y H:i', strtotime($userDetail['updated_at'] ?? 'now')) ?></p>
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
                                        <th>Item</th>
                                        <th>Tanggal Pinjam</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>TRX001</td>
                                        <td>Oscilloscope Digital</td>
                                        <td>15/12/2024</td>
                                        <td><span class="badge bg-success">Selesai</span></td>
                                    </tr>
                                    <tr>
                                        <td>TRX002</td>
                                        <td>Mikroskop</td>
                                        <td>12/12/2024</td>
                                        <td><span class="badge bg-warning">Berjalan</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">
                            <i class="bi bi-pencil me-2"></i>Edit User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="/users/update/<?= $userDetail['id'] ?? '' ?>" id="editUserForm">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editName" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="editName" name="name" value="<?= htmlspecialchars($userDetail['name'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="editEmail" name="email" value="<?= htmlspecialchars($userDetail['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editPhone" class="form-label">No Telepon</label>
                                        <input type="tel" class="form-control" id="editPhone" name="phone" value="<?= htmlspecialchars($userDetail['phone'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="editRole" class="form-label">Role</label>
                                        <select class="form-control" id="editRole" name="role" required>
                                            <option value="USER" <?= ($userDetail['role'] ?? '') === 'USER' ? 'selected' : '' ?>>User</option>
                                            <option value="ADMIN" <?= ($userDetail['role'] ?? '') === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editStatus" class="form-label">Status</label>
                                        <select class="form-control" id="editStatus" name="status" required>
                                            <option value="active" <?= ($userDetail['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                            <option value="inactive" <?= ($userDetail['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Non-aktif</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editPassword" class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                                        <input type="password" class="form-control" id="editPassword" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah">
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

            // Toggle user status
            window.toggleUserStatus = function(userId) {
                if (confirm('Apakah Anda yakin ingin mengubah status user ini?')) {
                    window.location.href = '/users/toggle-status/' + userId;
                }
            };

            // Form validation
            $('#editUserForm').on('submit', function(e) {
                const password = $('#editPassword').val();

                if (password && password.length < 8) {
                    e.preventDefault();
                    alert('Password minimal harus 8 karakter!');
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