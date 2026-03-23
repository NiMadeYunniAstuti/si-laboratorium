    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="/images/logo.webp" alt="LBMS Logo">
            </div>
        </div>

<?php
// Determine current route from view variable or fallback to REQUEST_URI
$currentUri = $current_route ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>

        <nav class="sidebar-menu">
            <?php if (str_starts_with($currentUri, '/dashboard')): ?>
                <span class="sidebar-menu-item active">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </span>
            <?php else: ?>
                <a href="/dashboard" class="sidebar-menu-item">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            <?php endif; ?>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <?php if (str_starts_with($currentUri, '/users')): ?>
                    <span class="sidebar-menu-item active">
                        <i class="bi bi-people"></i>
                        Data User
                    </span>
                <?php else: ?>
                    <a href="/users" class="sidebar-menu-item">
                        <i class="bi bi-people"></i>
                        Data User
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (($user['role'] ?? 'USER') === 'ADMIN'): ?>
                <?php if (str_starts_with($currentUri, '/alat')): ?>
                    <span class="sidebar-menu-item active">
                        <i class="bi bi-box-seam"></i>
                        Manajemen Alat
                    </span>
                <?php else: ?>
                    <a href="/alat" class="sidebar-menu-item">
                        <i class="bi bi-box-seam"></i>
                        Manajemen Alat
                    </a>
                <?php endif; ?>
                <?php if (str_starts_with($currentUri, '/ruangan')): ?>
                    <span class="sidebar-menu-item active">
                        <i class="bi bi-door-open"></i>
                        Manajemen Ruangan
                    </span>
                <?php else: ?>
                    <a href="/ruangan" class="sidebar-menu-item">
                        <i class="bi bi-door-open"></i>
                        Manajemen Ruangan
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (($user['role'] ?? 'USER') === 'USER'): ?>
                <?php if (str_starts_with($currentUri, '/peminjaman')): ?>
                    <span class="sidebar-menu-item active">
                        <i class="bi bi-hand-index"></i>
                        Peminjaman
                    </span>
                <?php else: ?>
                    <a href="/peminjaman" class="sidebar-menu-item">
                        <i class="bi bi-hand-index"></i>
                        Peminjaman
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (str_starts_with($currentUri, '/settings')): ?>
                <span class="sidebar-menu-item active">
                    <i class="bi bi-gear"></i>
                    Settings
                </span>
            <?php else: ?>
                <a href="/settings" class="sidebar-menu-item">
                    <i class="bi bi-gear"></i>
                    Settings
                </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout" class="sidebar-menu-item logout-item">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </div>
    </aside>
