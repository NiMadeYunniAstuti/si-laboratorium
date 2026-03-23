<?php
$title = 'Notifikasi - LBMS';
$current_route = '/notifications';

// Helper functions for notifications
if (!function_exists('getNotificationIcon')) {
    function getNotificationIcon($title) {
        $title = strtolower($title);
        if (strpos($title, 'peminjaman') !== false) return '<i class="bi bi-hand-index"></i>';
        if (strpos($title, 'alat') !== false) return '<i class="bi bi-box-seam"></i>';
        if (strpos($title, 'user') !== false || strpos($title, 'pengguna') !== false) return '<i class="bi bi-person"></i>';
        if (strpos($title, 'welcome') !== false || strpos($title, 'selamat datang') !== false) return '<i class="bi bi-house-door"></i>';
        if (strpos($title, 'maintenance') !== false || strpos($title, 'perawatan') !== false) return '<i class="bi bi-tools"></i>';
        return '<i class="bi bi-info-circle"></i>';
    }
}

if (!function_exists('getNotificationIconClass')) {
    function getNotificationIconClass($title) {
        $title = strtolower($title);
        if (strpos($title, 'disetujui') !== false || strpos($title, 'selesai') !== false) return 'success';
        if (strpos($title, 'ditolak') !== false || strpos($title, 'kadaluarsa') !== false) return 'danger';
        if (strpos($title, 'peringatan') !== false || strpos($title, 'segera') !== false) return 'warning';
        return 'primary';
    }
}

if (!function_exists('formatTimeAgo')) {
    function formatTimeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        if ($diff < 60) return 'Baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', $time);
    }
}

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main class="main-content" id="mainContent">
    <div class="page-header mb-4">
        <h1 class="page-title h3 fw-bold mb-1">Notifikasi</h1>
        <p class="text-muted">Kelola notifikasi dan pembaruan sistem Anda</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">
                Semua Notifikasi
                <?php if (!empty($notifications) && ($unreadCount ?? 0) > 0): ?>
                    <span class="badge bg-danger-subtle text-danger rounded-pill ms-2 fw-semibold px-3" id="unreadBadge">
                        <?= $unreadCount ?> Baru
                    </span>
                <?php endif; ?>
            </h5>
            <?php if (!empty($notifications)): ?>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" id="markAllReadBtn">
                    Tandai Semua Dibaca
                </button>
            <?php endif; ?>
        </div>
        
        <div class="card-body p-0">
            <?php if (!empty($notifications)): ?>
                <div class="list-group list-group-flush" id="notificationList">
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                        $is_unread = empty($notification['is_read']);
                        $icon_class = getNotificationIconClass($notification['title']);
                        ?>
                        <div class="list-group-item p-4 border-bottom notification-item transition-all <?= $is_unread ? 'bg-primary-subtle bg-opacity-10 border-start border-primary border-4' : '' ?>" 
                             data-id="<?= $notification['id'] ?>" style="border-left-width: <?= $is_unread ? '4px' : '0' ?> !important;">
                            <div class="d-flex align-items-start">
                                <div class="bg-<?= $icon_class ?>-subtle text-<?= $icon_class ?> rounded-circle p-3 me-3 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <?= getNotificationIcon($notification['title']) ?>
                                </div>
                                <div class="flex-grow-1 me-3">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($notification['title']) ?></h6>
                                        <small class="text-muted smaller">
                                            <i class="bi bi-clock me-1"></i>
                                            <?= formatTimeAgo($notification['created_at']) ?>
                                        </small>
                                    </div>
                                    <p class="text-muted small mb-2 lh-base"><?= htmlspecialchars($notification['description']) ?></p>
                                    
                                    <div class="d-flex align-items-center">
                                        <?php if ($is_unread): ?>
                                            <button type="button" class="btn btn-link text-primary p-0 me-3 small text-decoration-none fw-semibold mark-read-btn" data-id="<?= $notification['id'] ?>">
                                                Tandai dibaca
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($notification['peminjaman_id'])): ?>
                                            <a href="/peminjaman/<?= $notification['peminjaman_id'] ?>/detail" class="btn btn-link text-info p-0 me-3 small text-decoration-none fw-semibold">
                                                Detail Peminjaman <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        <?php endif; ?>

                                        <button type="button" class="btn btn-link text-danger p-0 ms-auto small text-decoration-none fw-semibold delete-notification-btn" data-id="<?= $notification['id'] ?>">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-inline-flex p-4 mb-4">
                        <i class="bi bi-bell-slash display-4 text-muted"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Tidak ada notifikasi</h5>
                    <p class="text-muted mb-0">Belum ada pembaruan sistem untuk saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php 
ob_start(); 
?>
<script>
    $(document).ready(function() {
        // Handle Mark as Read
        $('.mark-read-btn').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');
            markAsRead(id, btn);
        });

        // Handle Delete
        $('.delete-notification-btn').on('click', function() {
            const id = $(this).data('id');
            if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
                deleteNotification(id);
            }
        });

        // Mark All Read
        $('#markAllReadBtn').on('click', function() {
            if (confirm('Tandai semua notifikasi sebagai telah dibaca?')) {
                fetch('/notifications/mark-all-read', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(() => location.reload());
            }
        });

        function markAsRead(id, element) {
            fetch(`/notifications/mark-read/${id}`, { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = element.closest('.notification-item');
                    item.removeClass('bg-primary-subtle bg-opacity-10 border-start border-primary border-4');
                    item.css('border-left-width', '0');
                    element.fadeOut();
                    updateBadgeCount();
                }
            })
            .catch(err => console.error('Error:', err));
        }

        function deleteNotification(id) {
            fetch(`/notifications/delete/${id}`, { method: 'DELETE' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $(`.notification-item[data-id="${id}"]`).fadeOut(function() {
                        $(this).remove();
                        if ($('#notificationList .notification-item').length === 0) {
                            location.reload(); // Show empty state
                        }
                        updateBadgeCount();
                    });
                }
            })
            .catch(err => console.error('Error:', err));
        }

        function updateBadgeCount() {
            const unreadCount = $('#notificationList .notification-item.bg-primary-subtle').length;
            const badge = $('#unreadBadge');
            const navBadge = $('.top-navbar .badge.bg-danger');

            if (unreadCount > 0) {
                badge.text(`${unreadCount} Baru`);
                navBadge.text(unreadCount).show();
            } else {
                badge.fadeOut();
                navBadge.fadeOut();
            }
        }
    });
</script>
<?php 
$extra_js = ob_get_clean();
require_once __DIR__ . '/../layouts/footer.php'; 
?>
