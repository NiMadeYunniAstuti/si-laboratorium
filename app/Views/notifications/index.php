<!DOCTYPE html>
<html lang="id">
    <link rel="icon" type="image/x-icon" href="/favicon.ico"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - LBMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/main.css?v=<?php echo date('YmHis'); ?>" rel="stylesheet">
    <style>
        /* Minimal custom styles - using Bootstrap 5.3 components */
        .actions-container {
            margin-bottom: 2rem;
            padding: 0 2rem;
        }
        .notification-item {
            transition: background-color 0.2s ease;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .notification-item.unread {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9ff;
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .notification-icon.info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .notification-icon.success {
            background-color: #d4edda;
            color: #155724;
        }
        .notification-icon.warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .notification-time {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
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
            <a href="/notifications" class="sidebar-menu-item active">
                <i class="bi bi-bell"></i>
                Notifikasi
                <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-danger ms-auto"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <img src="/images/default-avatar.png" alt="User Avatar" class="user-avatar">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'Admin User'); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($user['role'] ?? 'USER'); ?></div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-link text-white p-0" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/settings/profile">Profil</a></li>
                        <li><a class="dropdown-item" href="/settings/privacy-security">Privasi & Keamanan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout">Keluar</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <!-- Flash Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Notifikasi</h1>
                    <p class="page-subtitle">Kelola notifikasi dan pembaruan sistem</p>
                </div>
                <div class="page-actions">
                    <button type="button" class="btn btn-outline-primary" id="markAllReadBtn">
                        <i class="bi bi-check-all me-2"></i>
                        Tandai Semua Dibaca
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="clearAllBtn">
                        <i class="bi bi-trash me-2"></i>
                        Hapus Semua
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications Content -->
        <div class="container-fluid p-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-bell me-2"></i>
                                Daftar Notifikasi
                                <?php if ($unreadCount > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo $unreadCount; ?> baru</span>
                                <?php endif; ?>
                            </h5>
                            <div class="btn-group btn-group-sm" role="group">
                                <input type="radio" class="btn-check" name="filter" id="filter-all" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="filter-all">Semua</label>

                                <input type="radio" class="btn-check" name="filter" id="filter-unread" autocomplete="off">
                                <label class="btn btn-outline-primary" for="filter-unread">Belum Dibaca</label>

                                <input type="radio" class="btn-check" name="filter" id="filter-read" autocomplete="off">
                                <label class="btn btn-outline-primary" for="filter-read">Sudah Dibaca</label>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($notifications)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($notifications as $notification): ?>
                                        <div class="list-group-item notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>
                                             notification-item" data-id="<?php echo $notification['id']; ?>" data-read="<?php echo $notification['is_read']; ?>">
                                            <div class="d-flex align-items-start">
                                                <div class="notification-icon <?php echo getNotificationIconClass($notification['title']); ?>">
                                                    <?php echo getNotificationIcon($notification['title']); ?>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                    <p class="mb-1"><?php echo htmlspecialchars($notification['description']); ?></p>
                                                    <small class="notification-time">
                                                        <i class="bi bi-clock me-1"></i>
                                                        <?php echo formatTimeAgo($notification['created_at']); ?>
                                                    </small>
                                                </div>
                                                <div class="notification-actions">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <button type="button" class="btn btn-sm btn-outline-primary mark-read-btn"
                                                                data-id="<?php echo $notification['id']; ?>" title="Tandai dibaca">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-notification-btn"
                                                            data-id="<?php echo $notification['id']; ?>" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-bell-slash display-1 text-muted"></i>
                                    <h5 class="mt-3 text-muted">Tidak ada notifikasi</h5>
                                    <p class="text-muted">Belum ada notifikasi untuk ditampilkan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/dashboard.js"></script>
    <script>
        // Notification filtering
        document.querySelectorAll('input[name="filter"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const filter = this.id.replace('filter-', '');
                filterNotifications(filter);
            });
        });

        function filterNotifications(filter) {
            const items = document.querySelectorAll('.notification-item');

            items.forEach(item => {
                const isRead = item.dataset.read === '1';

                switch(filter) {
                    case 'unread':
                        item.style.display = isRead ? 'none' : 'block';
                        break;
                    case 'read':
                        item.style.display = isRead ? 'block' : 'none';
                        break;
                    default:
                        item.style.display = 'block';
                }
            });
        }

        // Mark notification as read
        document.querySelectorAll('.mark-read-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                markAsRead(notificationId);
            });
        });

        // Mark all as read
        document.getElementById('markAllReadBtn').addEventListener('click', function() {
            if (confirm('Tandai semua notifikasi sebagai dibaca?')) {
                markAllAsRead();
            }
        });

        // Delete notification
        document.querySelectorAll('.delete-notification-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                if (confirm('Hapus notifikasi ini?')) {
                    deleteNotification(notificationId);
                }
            });
        });

        // Clear all notifications
        document.getElementById('clearAllBtn').addEventListener('click', function() {
            if (confirm('Hapus semua notifikasi? Tindakan ini tidak dapat dibatalkan.')) {
                clearAllNotifications();
            }
        });

        function markAsRead(id) {
            fetch(`/notifications/mark-read/${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`[data-id="${id}"]`);
                    item.classList.remove('unread');
                    item.dataset.read = '1';

                    // Remove mark read button
                    const markBtn = item.querySelector('.mark-read-btn');
                    if (markBtn) {
                        markBtn.remove();
                    }

                    updateUnreadCount();
                }
            });
        }

        function markAllAsRead() {
            fetch('/notifications/mark-all-read', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        item.dataset.read = '1';

                        const markBtn = item.querySelector('.mark-read-btn');
                        if (markBtn) {
                            markBtn.remove();
                        }
                    });
                    updateUnreadCount();
                }
            });
        }

        function deleteNotification(id) {
            fetch(`/notifications/delete/${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const item = document.querySelector(`[data-id="${id}"]`);
                    item.remove();
                    updateUnreadCount();
                }
            });
        }

        function clearAllNotifications() {
            fetch('/notifications/clear-all', {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(item => item.remove());
                    updateUnreadCount();

                    // Show empty state
                    const container = document.querySelector('.card-body');
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash display-1 text-muted"></i>
                            <h5 class="mt-3 text-muted">Tidak ada notifikasi</h5>
                            <p class="text-muted">Belum ada notifikasi untuk ditampilkan</p>
                        </div>
                    `;
                }
            });
        }

        function updateUnreadCount() {
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;
            const badge = document.querySelector('.badge.bg-danger');
            if (badge) {
                if (unreadCount > 0) {
                    badge.textContent = unreadCount;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    </script>

  <?php
    // Helper functions for notification display
    function getNotificationIcon($title) {
        $title = strtolower($title);

        if (strpos($title, 'peminjaman') !== false) {
            return '<i class="bi bi-hand-index"></i>';
        } elseif (strpos($title, 'alat') !== false) {
            return '<i class="bi bi-wrench"></i>';
        } elseif (strpos($title, 'user') !== false || strpos($title, 'pengguna') !== false) {
            return '<i class="bi bi-person"></i>';
        } elseif (strpos($title, 'welcome') !== false || strpos($title, 'selamat datang') !== false) {
            return '<i class="bi bi-house-door"></i>';
        } elseif (strpos($title, 'maintenance') !== false || strpos($title, 'perawatan') !== false) {
            return '<i class="bi bi-tools"></i>';
        } else {
            return '<i class="bi bi-info-circle"></i>';
        }
    }

    function getNotificationIconClass($title) {
        $title = strtolower($title);

        if (strpos($title, 'disetujui') !== false || strpos($title, 'selesai') !== false) {
            return 'success';
        } elseif (strpos($title, 'ditolak') !== false || strpos($title, 'kadaluarsa') !== false) {
            return 'warning';
        } else {
            return 'info';
        }
    }

    function formatTimeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' menit yang lalu';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' jam yang lalu';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' hari yang lalu';
        } else {
            return date('d M Y', $time);
        }
    }
    ?>
</body>
</html>