<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - LBMS</title>
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
            <a href="/users" class="sidebar-menu-item active">
                <i class="bi bi-people"></i>
                Data User
            </a>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <a href="/alat" class="sidebar-menu-item">
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
        <h1 class="mb-4">Data User</h1>

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
                <h5 class="mb-0">Daftar Pengguna</h5>
                <a href="/users/new" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Tambah User
                </a>
            </div>
            <div class="card-body">
                <!-- Users DataTable -->
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped table-hover">
              <thead>
                  <tr>
                      <th>NO</th>
                      <!-- <th>NIK</th> -->
                      <th>NAMA</th>
                      <th>EMAIL</th>
                      <th>NO TELEFON</th>
                      <th>STATUS</th>
                      <th>ACTION</th>
                  </tr>
              </thead>
              <tbody>
                  <!-- Sample data rows - these will be replaced with actual data -->
                  <tr>
                      <td>1</td>
                      <!-- <td>1234567890123456</td> -->
                      <td>Admin User</td>
                      <td>admin@lbms.com</td>
                      <td>08123456789</td>
                      <td><span class="badge bg-success">Active</span></td>
                      <td>
                          <div class="d-flex gap-2">
                              <a href="/users/1" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                  <i class="bi bi-eye"></i> Detail
                              </a>
                              <div class="btn-group d-flex flex-nowrap" role="group">
                                  <button class="btn btn-sm btn-outline-danger toggle-status flex-shrink-0" data-id="1">
                                      <i class="bi bi-pause"></i> Nonaktif
                                  </button>
                                  <button class="btn btn-sm btn-outline-dark blacklist-user flex-shrink-0" data-id="1">
                                      <i class="bi bi-shield-x"></i> Blacklist
                                  </button>
                              </div>
                          </div>
                      </td>
                  </tr>
                  <tr>
                      <td>2</td>
                      <!-- <td>2345678901234567</td> -->
                      <td>John Doe</td>
                      <td>john.doe@example.com</td>
                      <td>08234567890</td>
                      <td><span class="badge bg-success">Active</span></td>
                      <td>
                          <div class="d-flex gap-2">
                              <a href="/users/2" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                  <i class="bi bi-eye"></i> Detail
                              </a>
                              <div class="btn-group d-flex flex-nowrap" role="group">
                                  <button class="btn btn-sm btn-outline-danger toggle-status flex-shrink-0" data-id="2">
                                      <i class="bi bi-pause"></i> Nonaktif
                                  </button>
                                  <button class="btn btn-sm btn-outline-dark blacklist-user flex-shrink-0" data-id="2">
                                      <i class="bi bi-shield-x"></i> Blacklist
                                  </button>
                              </div>
                          </div>
                      </td>
                  </tr>
                  <tr>
                      <td>3</td>
                      <!-- <td>3456789012345678</td> -->
                      <td>Jane Smith</td>
                      <td>jane.smith@example.com</td>
                      <td>08345678901</td>
                      <td><span class="badge bg-danger">Nonaktif</span></td>
                      <td>
                          <div class="d-flex gap-2">
                              <a href="/users/3" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                  <i class="bi bi-eye"></i> Detail
                              </a>
                              <div class="btn-group d-flex flex-nowrap" role="group">
                                  <button class="btn btn-sm btn-outline-success toggle-status flex-shrink-0" data-id="3">
                                      <i class="bi bi-play"></i> Aktifkan
                                  </button>
                                  <button class="btn btn-sm btn-outline-dark blacklist-user flex-shrink-0" data-id="3">
                                      <i class="bi bi-shield-x"></i> Blacklist
                                  </button>
                              </div>
                          </div>
                      </td>
                  </tr>
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

            // Initialize DataTable with proper height constraints
            if (typeof $ !== 'undefined' && $.fn.DataTable) {
                $('#usersTable').DataTable({
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
                        emptyTable: "Tidak ada data tersedia dalam tabel",
                        zeroRecords: "Tidak ditemukan data yang cocok"
                    }
                });
            } else {
                console.warn('jQuery or DataTables is not loaded');
            }

            // Form validation for tambah user
            $('#tambahUserForm').on('submit', function(e) {
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();

                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Password dan konfirmasi password tidak cocok');
                    return false;
                }

                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password minimal 8 karakter');
                    return false;
                }
            });

            // Toggle status button click handler
            $('.toggle-status').on('click', function() {
                const userId = $(this).data('id');
                const actionText = $(this).text().trim();

                if (actionText === 'Nonaktif') {
                    // Currently active, will deactivate
                    if (confirm('Apakah Anda yakin ingin menonaktifkan user ini?')) {
                        // TODO: Call API to deactivate user
                        alert('User berhasil dinonaktifkan');
                    }
                } else if (actionText === 'Aktifkan') {
                    // Currently inactive, will activate
                    if (confirm('Apakah Anda yakin ingin mengaktifkan user ini?')) {
                        // TODO: Call API to activate user
                        alert('User berhasil diaktifkan');
                    }
                }
            });

            // Blacklist user button click handler
            $('.blacklist-user').on('click', function() {
                const userId = $(this).data('id');
                if (confirm('Apakah Anda yakin ingin mem-blacklist user ini? User tidak akan dapat melakukan peminjaman.')) {
                    // TODO: Call API to blacklist user
                    alert('User berhasil di-blacklist');
                }
            });
        });
    </script>
</body>
</html>