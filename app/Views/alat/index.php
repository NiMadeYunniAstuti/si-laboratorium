<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Alat - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        <h1 class="mb-4">Daftar Alat</h1>

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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Alat</h5>
                <a href="/alat/new" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tambah Alat
                </a>
            </div>
            <div class="card-body">
                <!-- Alat DataTable -->
                <div class="table-responsive">
                    <table id="alatTable" class="table table-striped table-hover">
              <thead>
                  <tr>
                      <th>NO</th>
                      <th>KODE ALAT</th>
                      <th>NAMA ALAT</th>
                      <th>GAMBAR</th>
                      <th>TAHUN PEMBELIAN</th>
                      <th>STATUS</th>
                      <th>ACTION</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if (!empty($alat)): ?>
                      <?php foreach ($alat as $index => $item): ?>
                      <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= htmlspecialchars($item['kode_alat'] ?? '') ?></td>
                          <td><?= htmlspecialchars($item['nama_alat'] ?? '') ?></td>
                          <td>
                              <?php if (!empty($item['gambar'])): ?>
                                  <button type="button"
                                          class="btn btn-sm btn-outline-primary"
                                          data-bs-toggle="modal"
                                          data-bs-target="#alatImageModal"
                                          data-image="/<?= ltrim(htmlspecialchars($item['gambar']), '/') ?>"
                                          data-name="<?= htmlspecialchars($item['nama_alat'] ?? 'Alat') ?>">
                                      <i class="bi bi-image me-1"></i>Lihat
                                  </button>
                              <?php else: ?>
                                  <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
                                      <i class="bi bi-image me-1"></i>Tidak ada
                                  </button>
                              <?php endif; ?>
                          </td>
                          <td><?= htmlspecialchars($item['tahun_pembelian'] ?? '-') ?></td>
                          <td>
                              <span class="badge bg-<?= match($item['status'] ?? 'TERSEDIA') {
                                  'TERSEDIA' => 'success',
                                  'DIPINJAM' => 'warning',
                                  'MAINTENANCE' => 'warning',
                                  'RUSAK' => 'danger',
                                  default => 'secondary'
                              } ?>">
                                  <?= htmlspecialchars($item['status'] ?? 'TERSEDIA') ?>
                              </span>
                          </td>
                          <td>
                              <div class="alat-actions">
                                  <a href="/alat/<?= $item['id'] ?>/detail" class="btn btn-sm btn-outline-info">
                                      <i class="bi bi-eye"></i> Detail
                                  </a>
                              </div>
                          </td>
                      </tr>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
                </div>
            </div>
        </div>

        <!-- Tambah Alat Modal -->
        <div class="modal fade" id="tambahAlatModal" tabindex="-1" aria-labelledby="tambahAlatModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahAlatModalLabel">
                            <i class="bi bi-plus-circle me-2"></i>Tambah Alat Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="/alat/create" id="tambahAlatForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="kode" class="form-label">Kode Alat</label>
                                <input type="text" class="form-control" id="kode" name="kode" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama_alat" class="form-label">Nama Alat</label>
                                <input type="text" class="form-control" id="nama_alat" name="nama_alat" required>
                            </div>
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-control" id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Alat Optik">Alat Optik</option>
                                    <option value="Alat Ukur">Alat Ukur</option>
                                    <option value="Alat Sterilisasi">Alat Sterilisasi</option>
                                    <option value="Alat Pemisah">Alat Pemisah</option>
                                    <option value="Alat Analisis">Alat Analisis</option>
                                    <option value="Alat Pemanas">Alat Pemanas</option>
                                    <option value="Alat Pendingin">Alat Pendingin</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="stok" class="form-label">Jumlah Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="kondisi" class="form-label">Kondisi</label>
                                <select class="form-control" id="kondisi" name="kondisi" required>
                                    <option value="">Pilih Kondisi</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Rusak Ringan">Rusak Ringan</option>
                                    <option value="Rusak Berat">Rusak Berat</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Gambar Alat Modal -->
        <div class="modal fade" id="alatImageModal" tabindex="-1" aria-labelledby="alatImageModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="alatImageModalLabel">Gambar Alat</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="" alt="Gambar Alat" class="img-fluid rounded">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
        <script>
        $(document).ready(function() {
            // Sidebar toggle functionality
            function toggleSidebar() {
                $('#sidebar').toggleClass('collapsed');
                $('#topNavbar').toggleClass('sidebar-collapsed');
                $('#mainContent').toggleClass('sidebar-collapsed');

                // Save sidebar state to localStorage
                const isCollapsed = $('#sidebar').hasClass('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }

            // Restore sidebar state from localStorage
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed) {
                $('#sidebar').addClass('collapsed');
                $('#topNavbar').addClass('sidebar-collapsed');
                $('#mainContent').addClass('sidebar-collapsed');
            }

            // Sidebar toggle click handler
            $('#sidebarToggle').on('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });

            // Initialize DataTable with same properties as dashboard
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#alatTable').DataTable({
                    responsive: true,
                    paging: true,
                    searching: false,
                    ordering: false,
                    info: false,
                    lengthChange: false,
                    pageLength: 10,
                    scrollX: true,
                    scrollCollapse: false,
                    autoWidth: false,
                    responsive: false,
                    // Remove fixedHeader to use CSS-only solution
                    language: {
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Selanjutnya",
                            previous: "Sebelumnya"
                        },
                        emptyTable: `<div class="text-center py-4">
                            <i class="bi bi-inbox display-6 text-muted"></i>
                            <div class="mt-2 text-muted">Tidak ada data tersedia dalam tabel</div>
                        </div>`,
                        zeroRecords: "Tidak ditemukan data yang cocok"
                    },
                    drawCallback: function() {
                        const api = this.api();
                        const showPagination = api.data().count() > 0;
                        $(api.table().container())
                            .find('.dataTables_paginate')
                            .toggle(showPagination);
                    }
                });
            } else {
                console.warn('jQuery or DataTables is not loaded');
            }

            // Form validation for tambah alat
            $('#tambahAlatForm').on('submit', function(e) {
                const stok = $('#stok').val();
                const kode = $('#kode').val();

                if (stok < 1) {
                    e.preventDefault();
                    alert('Stok minimal 1');
                    return false;
                }

                if (!kode.match(/^LT\d{3,}$/)) {
                    e.preventDefault();
                    alert('Kode alat harus diawali dengan LT diikuti angka minimal 3 digit');
                    return false;
                }
            });

            // Edit alat button click handler
            $('.edit-alat').on('click', function() {
                const alatId = $(this).data('id');
                // TODO: Implement edit alat functionality
                alert('Edit alat dengan ID: ' + alatId);
            });

            // Delete alat button click handler (keeping for any that might still exist)
            $('.delete-alat').on('click', function() {
                const alatId = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin menghapus alat ini?')) {
                    // TODO: Call API to delete alat
                    alert('Alat berhasil dihapus');
                }
            });

            // Maintenance alat button click handler (keeping for any that might still exist)
            $('.maintenance-alat').on('click', function() {
                const alatId = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin mengajukan maintenance untuk alat ini?')) {
                    // TODO: Call API to submit maintenance request
                    alert('Permintaan maintenance berhasil diajukan');
                }
            });

            // View alat button click handler (for both .view-alat and .lihat-alat)
            $('.view-alat, .lihat-alat').on('click', function() {
                const alatId = $(this).data('id');
                // TODO: Implement view alat functionality
                alert('Lihat detail alat dengan ID: ' + alatId);
            });

            $('#alatImageModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const image = button.data('image') || '';
                const name = button.data('name') || 'Alat';
                const modal = $(this);

                modal.find('.modal-title').text(`Gambar Alat - ${name}`);
                modal.find('img').attr('src', image).attr('alt', name);
            });
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
