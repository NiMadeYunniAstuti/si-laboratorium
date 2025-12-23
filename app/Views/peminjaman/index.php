<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peminjaman - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
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
        <h1 class="mb-4">Peminjaman</h1>

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
                <h5 class="mb-0">Data Peminjaman</h5>
                <a href="/peminjaman/new" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Ajukan Peminjaman
                </a>
                </div>
                <div class="card-body">
                    <!-- Peminjaman DataTable -->
                <div class="table-responsive">
                    <table id="peminjamanTable" class="table table-striped table-hover">
              <thead>
                  <tr>
                      <th class="text-nowrap">NO</th>
                      <th class="text-nowrap">PEMINJAM</th>
                      <th class="text-nowrap">ALAT</th>
                      <th class="text-nowrap">TANGGAL PINJAM</th>
                      <th class="text-nowrap">TANGGAL KEMBALI</th>
                      <th class="text-nowrap">STATUS</th>
                  </tr>
              </thead>
              <tbody>
                  <?php if (!empty($peminjaman)): ?>
                      <?php foreach ($peminjaman as $index => $item): ?>
                      <tr>
                          <td><?= $index + 1 ?></td>
                          <td><?= htmlspecialchars($item['user_name'] ?? $item['name'] ?? 'Unknown') ?></td>
                          <td>
                              <?= htmlspecialchars($item['nama_alat'] ?? 'Unknown') ?>
                              <?php if (!empty($item['kode_alat'])): ?>
                                  <br><small class="text-muted">(<?= htmlspecialchars($item['kode_alat']) ?>)</small>
                              <?php endif; ?>
                          </td>
                          <td><?= date('d/m/Y', strtotime($item['tanggal_pinjam'] ?? 'now')) ?></td>
                          <td><?= date('d/m/Y', strtotime($item['tanggal_kembali'] ?? 'now')) ?></td>
                          <td>
                              <span class="badge bg-<?= match($item['status'] ?? 'PENDING') {
                                  'SELESAI' => 'success',
                                  'DIPINJAM' => 'secondary',
                                  'PENDING' => 'warning',
                                  'DITOLAK' => 'danger',
                                  default => 'secondary'
                              } ?>">
                                  <?= htmlspecialchars($item['status'] ?? 'PENDING') ?>
                              </span>
                          </td>
                      </tr>
                      <?php endforeach; ?>
                  <?php endif; ?>
              </tbody>
          </table>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
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
                $('#peminjamanTable').DataTable({
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

            // Set minimum date for tanggal_pinjam to today
            const today = new Date().toISOString().split('T')[0];
            $('#tanggal_pinjam').prop('min', today);
            $('#tanggal_pinjam').val(today);

            // Form validation for tambah peminjaman
            $('#tambahPeminjamanForm').on('submit', function(e) {
                const tanggalPinjam = $('#tanggal_pinjam').val();
                const tanggalKembali = $('#tanggal_kembali').val();
                const jumlah = $('#jumlah').val();

                if (new Date(tanggalKembali) <= new Date(tanggalPinjam)) {
                    e.preventDefault();
                    alert('Tanggal kembali harus setelah tanggal pinjam');
                    return false;
                }

                if (jumlah < 1) {
                    e.preventDefault();
                    alert('Jumlah minimal 1');
                    return false;
                }
            });

            // Update minimum tanggal_kembali when tanggal_pinjam changes
            $('#tanggal_pinjam').on('change', function() {
                const pinjamDate = new Date($(this).val());
                pinjamDate.setDate(pinjamDate.getDate() + 1); // Minimum 1 day after pinjam date
                const minKembali = pinjamDate.toISOString().split('T')[0];
                $('#tanggal_kembali').prop('min', minKembali);

                // Clear tanggal_kembali if it's now invalid
                if ($('#tanggal_kembali').val() &&
                    new Date($('#tanggal_kembali').val()) <= new Date($(this).val())) {
                    $('#tanggal_kembali').val('');
                }
            });

            // View peminjaman button click handler
            $('.view-peminjaman').on('click', function() {
                const peminjamanId = $(this).data('id');
                // TODO: Implement view peminjaman functionality
                alert('Lihat detail peminjaman dengan ID: ' + peminjamanId);
            });

            // Kembalikan peminjaman button click handler (keeping for any that might still exist)
            $('.kembalikan-peminjaman').on('click', function() {
                const peminjamanId = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin mengembalikan barang ini?')) {
                    // TODO: Call API to return item
                    alert('Barang berhasil dikembalikan');
                }
            });
        });
    </script>
</body>
</html>
