<?php
$title = 'Data User - LBMS';
$current_route = '/users';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="page-header px-0 mb-4">
        <div>
            <h1 class="page-title">Manajemen User</h1>
            <p class="page-subtitle">Kelola data pengguna sistem</p>
        </div>
        <div class="page-actions">
            <a href="/users/new" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Tambah User
            </a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Daftar Pengguna</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">NO</th>
                            <th>NAMA</th>
                            <th>EMAIL</th>
                            <th>STATUS</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php $currentUserId = $user['id'] ?? null; ?>
                            <?php foreach ($users as $index => $userItem): ?>
                            <?php $statusUpper = strtoupper($userItem['status'] ?? 'ACTIVE'); ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($userItem['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($userItem['email'] ?? '') ?></td>
                                <td>
                                    <?php
                                    $badgeClass = match($statusUpper) {
                                        'ACTIVE' => 'bg-success',
                                        'BLACKLIST' => 'bg-dark',
                                        default => 'bg-danger'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>-subtle text-<?= str_replace('bg-', '', $badgeClass) ?> border border-<?= str_replace('bg-', '', $badgeClass) ?>-subtle px-2 py-1">
                                        <?= $statusUpper === 'BLACKLIST' ? 'Blacklist' : ($statusUpper === 'ACTIVE' ? 'Active' : 'Nonaktif') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="/users/<?= $userItem['id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (($userItem['id'] ?? null) !== $currentUserId && $statusUpper !== 'BLACKLIST'): ?>
                                            <button class="btn btn-sm btn-outline-<?= $statusUpper === 'ACTIVE' ? 'danger' : 'success' ?> toggle-status"
                                                    data-id="<?= $userItem['id'] ?>"
                                                    data-status="<?= $statusUpper === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE' ?>"
                                                    title="<?= $statusUpper === 'ACTIVE' ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                                <i class="bi bi-<?= $statusUpper === 'ACTIVE' ? 'pause-fill' : 'play-fill' ?>"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-dark blacklist-user"
                                                    data-id="<?= $userItem['id'] ?>"
                                                    data-status="BLACKLIST"
                                                    title="Blacklist">
                                                <i class="bi bi-shield-x"></i>
                                            </button>
                                        <?php endif; ?>
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
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#usersTable').DataTable({
                responsive: true,
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    },
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox display-4 d-block mb-2"></i>Tidak ada data user</div>',
                    zeroRecords: "Data tidak ditemukan"
                },
                pageLength: 10,
                lengthChange: false,
                info: true
            });
        }

        $('.toggle-status, .blacklist-user').on('click', function() {
            const userId = $(this).data('id');
            const status = $(this).data('status');
            const message = status === 'BLACKLIST'
                ? 'Apakah Anda yakin ingin mem-blacklist user ini? User tidak akan dapat melakukan peminjaman.'
                : (status === 'ACTIVE'
                    ? 'Apakah Anda yakin ingin mengaktifkan user ini?'
                    : 'Apakah Anda yakin ingin menonaktifkan user ini?');

            if (!confirm(message)) {
                return;
            }

            fetch(`/users/${userId}/update/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal memperbarui status user');
                }
            })
            .catch(() => {
                alert('Terjadi kesalahan saat memperbarui status user');
            });
        });
    });
</script>
</body>
</html>
