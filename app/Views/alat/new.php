<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alat Baru - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
            <a href="/users" class="sidebar-menu-item">
                <i class="bi bi-people"></i>
                Data User
            </a>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/alat" class="sidebar-menu-item active">
                    <i class="bi bi-wrench"></i>
                    Manajemen Alat
                </a>
            <?php endif; ?>
            <?php if (($user['role'] ?? 'USER') === 'USER'): ?>
                <a href="/peminjaman" class="sidebar-menu-item">
                    <i class="bi bi-hand-index"></i>
                    Peminjaman
                </a>
            <?php endif; ?>
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
            <!-- Global Search -->
            <div class="ms-3 flex-grow-1 d-none d-md-block global-search-wrapper" style="">
                <select id="globalSearch" class="form-select" style="width: 100%;">
                    <option value="">Cari</option>
                </select>
            </div>
        </div>

        <div class="navbar-right">
            <div class="d-flex align-items-center">
                <!-- Notification Icon -->
                <a href="/notifications" class="btn btn-outline-secondary me-3 position-relative">
                    <i class="bi bi-bell"></i>
                    <?php if (($unreadNotificationCount ?? 0) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $unreadNotificationCount ?>
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    <?php endif; ?>
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
            <a href="/alat" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <h1 class="mb-0">Tambah Alat Baru</h1>
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

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Form Alat Baru
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/alat/create" id="tambahAlatForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-info-circle me-1"></i>Informasi Dasar
                            </h6>

                            <div class="mb-3">
                                <label for="kode_alat" class="form-label">Kode Alat</label>
                                <input type="text" class="form-control" id="kode_alat" name="kode_alat" placeholder="Contoh: LT001" required>
                                <small class="form-text text-muted">Gunakan format yang konsisten untuk penomoran alat</small>
                            </div>

                            <div class="alert alert-warning d-flex align-items-start" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                                <div>
                                    <strong>Perhatian:</strong> Kode alat tidak dapat diubah setelah disimpan.
                                    Pastikan format dan penulisannya sudah benar sebelum melanjutkan.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="nama_alat" class="form-label">Nama Alat</label>
                                <input type="text" class="form-control" id="nama_alat" name="nama_alat" placeholder="Masukkan nama alat" required>
                                <small class="form-text text-muted">Nama lengkap alat laboratorium</small>
                            </div>

                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Kategori</label>
                                <select class="form-control" id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php if (!empty($kategoriList)): ?>
                                        <?php foreach ($kategoriList as $kategori): ?>
                                            <option value="<?= $kategori['id'] ?>">
                                                <?= htmlspecialchars($kategori['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada kategori tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">Kategori alat laboratorium</small>
                            </div>

                            <div class="mb-3">
                                <label for="tipe_id" class="form-label">Tipe</label>
                                <select class="form-control" id="tipe_id" name="tipe_id" required>
                                    <option value="">Pilih Tipe</option>
                                    <?php if (!empty($tipeList)): ?>
                                        <?php foreach ($tipeList as $tipe): ?>
                                            <option value="<?= $tipe['id'] ?>">
                                                <?= htmlspecialchars($tipe['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada tipe tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">Tipe atau spesifikasi alat</small>
                            </div>

                            <div class="mb-3">
                                <label for="tahun_pembelian" class="form-label">Tahun Pembelian</label>
                                <input type="number" class="form-control" id="tahun_pembelian" name="tahun_pembelian" min="1900" max="<?= date('Y') ?>" value="2025" required>
                                <small class="form-text text-muted">Tahun pembelian alat</small>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status Alat</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="tersedia" selected>Tersedia</option>
                                    <option value="dipinjam">Dipinjam</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="rusak">Rusak</option>
                                </select>
                                <small class="form-text text-muted">Status ketersediaan alat</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-card-text me-1"></i>Keterangan & Media
                            </h6>

                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan" rows="4" placeholder="Masukkan keterangan atau deskripsi alat"></textarea>
                                <small class="form-text text-muted">Deskripsi lengkap, spesifikasi, atau catatan penting</small>
                            </div>

                            <div class="mb-3">
                                <label for="gambar" class="form-label">Foto Alat</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                <small class="form-text text-muted">Upload foto alat (JPG, PNG, maksimal 2MB)</small>
                                <div id="imagePreview" class="mt-2" style="">
                                    <!-- Preview akan ditampilkan di sini -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="/alat" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Simpan Alat
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

            $('#kategori_id, #tipe_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih opsi'
            });

            // Image preview functionality
            $('#gambar').on('change', function(e) {
                const file = e.target.files[0];
                const preview = $('#imagePreview');

                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB');
                        $(this).val('');
                        preview.empty();
                        return;
                    }

                    // Validate file type
                    if (!file.type.match('image.*')) {
                        alert('Hanya file gambar yang diperbolehkan');
                        $(this).val('');
                        preview.empty();
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.html(`
                            <img src="${e.target.result}" class="img-fluid rounded shadow-sm" alt="Preview">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeImage()">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        `);
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.empty();
                }
            });

            // Remove image function
            window.removeImage = function() {
                $('#gambar').val('');
                $('#imagePreview').empty();
            };

            // Form validation
            $('#tambahAlatForm').on('submit', function(e) {
                const tahun = parseInt($('#tahun_pembelian').val());
                const tahunSekarang = new Date().getFullYear();

                if (tahun < 1900 || tahun > tahunSekarang) {
                    e.preventDefault();
                    alert(`Tahun harus antara 1900 dan ${tahunSekarang}`);
                    return false;
                }

                const nama = $('#nama').val().trim();
                if (nama.length < 3) {
                    e.preventDefault();
                    alert('Nama alat minimal 3 karakter');
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

    <script>
        $(document).ready(function() {
            const $search = $('#globalSearch');
            if (!$search.length || !$.fn.select2) {
                return;
            }

            const searchItems = [
                { id: 'dashboard', text: 'Dashboard', url: '/dashboard' },
                { id: 'users', text: 'Users', url: '/users' },
                { id: 'peminjaman', text: 'Peminjaman', url: '/peminjaman' },
                { id: 'alat', text: 'Alat', url: '/alat' },
                { id: 'profile', text: 'Profile', url: '/settings/profile' },
{ id: 'notifications', text: 'Notifications', url: '/notifications' },
            ];

            const userRole = "<?= htmlspecialchars($user['role'] ?? 'USER') ?>";
            const filteredSearchItems = searchItems.filter(item => {
                if (userRole !== 'ADMIN' && (item.id === 'alat' || item.id === 'users')) {
                    return false;
                }
                return true;
            });

            $search.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Cari',
                allowClear: true,
                data: filteredSearchItems
            });

            $search.on('select2:select', function(e) {
                const url = e.params.data && e.params.data.url;
                if (url) {
                    window.location.href = url;
                }
            });
        });
    </script>

</body>
</html>
