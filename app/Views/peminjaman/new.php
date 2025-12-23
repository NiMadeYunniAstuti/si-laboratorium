<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Peminjaman Baru - LBMS</title>
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
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/users" class="sidebar-menu-item">
                    <i class="bi bi-people"></i>
                    Data User
                </a>
            <?php endif; ?>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/alat" class="sidebar-menu-item">
                    <i class="bi bi-wrench"></i>
                    Manajemen Alat
                </a>
            <?php endif; ?>
            <a href="/peminjaman" class="sidebar-menu-item active">
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
            <a href="/peminjaman" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
            <h1 class="mb-0">Ajukan Peminjaman Baru</h1>
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
                    <i class="bi bi-plus-circle me-2"></i>Form Peminjaman
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/peminjaman/create" id="tambahPeminjamanForm">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-person me-1"></i>Informasi Peminjam
                            </h6>

                            <div class="mb-3">
                                <label for="nama_peminjam" class="form-label">Nama Peminjam</label>
                                <input type="text" class="form-control" id="nama_peminjam" name="nama_peminjam" placeholder="Masukkan nama lengkap" required>
                                <small class="form-text text-muted">Nama lengkap peminjam</small>
                            </div>

                            <div class="mb-3">
                                <label for="jenis_peminjaman" class="form-label">Jenis Peminjaman</label>
                                <select class="form-control" id="jenis_peminjaman" name="jenis_peminjaman" required>
                                    <option value="">Pilih Jenis Peminjaman</option>
                                    <option value="praktikum">Praktikum</option>
                                    <option value="penelitian">Penelitian</option>
                                    <option value="pengajaran">Pengajaran</option>
                                    <option value="presentasi">Presentasi</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <small class="form-text text-muted">Pilih jenis peminjaman</small>
                            </div>

                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="4" placeholder="Tambahkan catatan atau keterangan tambahan"></textarea>
                                <small class="form-text text-muted">Catatan tambahan jika diperlukan (opsional)</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-box me-1"></i>Detail Peminjaman
                            </h6>

                            <div class="mb-3">
                                <label for="alat_id" class="form-label">Pilih Alat</label>
                                <select class="form-control" id="alat_id" name="alat_id" required>
                                    <option value="">Pilih Alat</option>
                                    <option value="1">Mikroskop Digital (LT001)</option>
                                    <option value="2">Timbangan Analitik (LT002)</option>
                                    <option value="3">Autoklaf (LT003)</option>
                                    <option value="4">Sentrifuge (LT004)</option>
                                    <option value="5">Spektrofotometer (LT005)</option>
                                </select>
                                <small class="form-text text-muted">Pilih alat yang akan dipinjam</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_pinjam" class="form-label">Tanggal & Waktu Pinjam</label>
                                        <input type="datetime-local" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam" required>
                                        <small class="form-text text-muted">Tanggal dan waktu mulai peminjaman</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_kembali" class="form-label">Tanggal & Waktu Kembali</label>
                                        <input type="datetime-local" class="form-control" id="tanggal_kembali" name="tanggal_kembali" required>
                                        <small class="form-text text-muted">Tanggal dan waktu estimasi pengembalian</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="surat" class="form-label">Surat Pengantar</label>
                                <input type="file" class="form-control" id="surat" name="surat" accept="application/pdf,image/*">
                                <small class="form-text text-muted">Upload surat pengantar (PDF, JPG, PNG - maksimal 2MB)</small>
                                <div id="suratPreview" class="mt-2" style="max-width: 200px;">
                                    <!-- Preview akan ditampilkan di sini -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <a href="/peminjaman" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle me-2"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-2"></i>Ajukan Peminjaman
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

            // Set minimum date for tanggal_pinjam (today)
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            const minDateTime = now.toISOString().slice(0, 16);
            $('#tanggal_pinjam').attr('min', minDateTime);
            $('#tanggal_kembali').attr('min', minDateTime);

            // Update minimum tanggal_kembali based on tanggal_pinjam
            $('#tanggal_pinjam').on('change', function() {
                const pinjamDate = new Date($(this).val());
                const minKembali = new Date(pinjamDate.getTime() + (24 * 60 * 60 * 1000)); // +1 day
                const minKembaliStr = minKembali.toISOString().slice(0, 16);
                $('#tanggal_kembali').attr('min', minKembaliStr);

                // Set default tanggal_kembali to 7 days from tanggal_pinjam
                const defaultKembali = new Date(pinjamDate.getTime() + (7 * 24 * 60 * 60 * 1000));
                const defaultKembaliStr = defaultKembali.toISOString().slice(0, 16);
                $('#tanggal_kembali').val(defaultKembaliStr);
            });

            // File upload functionality
            $('#surat').on('change', function(e) {
                const file = e.target.files[0];
                const preview = $('#suratPreview');

                if (file) {
                    // Validate file size (2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran file maksimal 2MB');
                        $(this).val('');
                        preview.empty();
                        return;
                    }

                    // Validate file type
                    if (!file.type.match('application/pdf') && !file.type.match('image.*')) {
                        alert('Hanya file PDF dan gambar yang diperbolehkan');
                        $(this).val('');
                        preview.empty();
                        return;
                    }

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (file.type.match('application/pdf')) {
                            // PDF file
                            preview.html(`
                                <div class="alert alert-info mt-2" role="alert">
                                    <i class="bi bi-file-pdf me-2"></i>
                                    ${file.name}
                                </div>
                            `);
                        } else {
                            // Image file
                            preview.html(`
                                <img src="${e.target.result}" class="img-fluid rounded shadow-sm" alt="Preview">
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeSurat()">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            `);
                        }
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.empty();
                }
            });

            // Remove surat file function
            window.removeSurat = function() {
                $('#surat').val('');
                $('#suratPreview').empty();
            };

            // Form validation
            $('#tambahPeminjamanForm').on('submit', function(e) {
                const tanggalPinjam = new Date($('#tanggal_pinjam').val());
                const tanggalKembali = new Date($('#tanggal_kembali').val());
                const today = new Date();

                // Validate dates
                if (tanggalPinjam < today) {
                    e.preventDefault();
                    alert('Tanggal pinjam tidak boleh kurang dari hari ini');
                    return false;
                }

                if (tanggalKembali <= tanggalPinjam) {
                    e.preventDefault();
                    alert('Tanggal kembali harus lebih dari tanggal pinjam');
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
