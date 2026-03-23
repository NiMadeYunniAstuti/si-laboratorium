<?php
$title = 'Daftar Ruangan - LBMS';
$current_route = '/ruangan';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';

function formatTipeName($tipe) {
    return str_replace('_', ' ', $tipe);
}
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="page-header px-0 mb-4">
        <div>
            <h1 class="page-title">Manajemen Ruangan</h1>
            <p class="page-subtitle">Kelola data ruangan laboratorium</p>
        </div>
        <div class="page-actions">
            <a href="/ruangan/new" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tambah Ruangan
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
            <h5 class="mb-0 fw-bold">Daftar Ruangan</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="ruanganTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">NO</th>
                            <th>KODE</th>
                            <th>NAMA RUANGAN</th>
                            <th>GAMBAR</th>
                            <th>JENIS</th>
                            <th>KAPASITAS</th>
                            <th>LOKASI</th>
                            <th>STATUS</th>
                            <th class="text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($alat)): ?>
                            <?php foreach ($alat as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td class="fw-medium"><?= htmlspecialchars($item['kode_ruangan'] ?? '') ?></td>
                                <td><?= htmlspecialchars($item['nama_ruangan'] ?? '') ?></td>
                                <td>
                                    <?php if (!empty($item['gambar'])): ?>
                                        <button type="button"
                                                class="btn btn-sm btn-light border"
                                                data-bs-toggle="modal"
                                                data-bs-target="#ruanganImageModal"
                                                data-image="<?= htmlspecialchars($item['gambar']) ?>"
                                                data-name="<?= htmlspecialchars($item['nama_ruangan'] ?? 'Ruangan') ?>">
                                            <i class="bi bi-image me-1"></i>Lihat
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small italic">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="small px-2 py-1 bg-light rounded border text-muted">
                                        <?= htmlspecialchars(formatTipeName($item['tipe_name'] ?? 'RUANGAN')) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($item['kapasitas'] ?? '-') ?> <i class="bi bi-people text-muted ms-1"></i></td>
                                <td>
                                    <div class="small">
                                        <span class="d-block text-dark fw-medium"><?= htmlspecialchars($item['gedung'] ?? '-') ?></span>
                                        <span class="text-muted">Lantai <?= htmlspecialchars($item['lantai'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $status = strtoupper($item['status'] ?? 'TERSEDIA');
                                    $badgeClass = match($status) {
                                        'TERSEDIA' => 'bg-success',
                                        'DIPINJAM' => 'bg-primary',
                                        'MAINTENANCE' => 'bg-warning',
                                        'RUSAK' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>-subtle text-<?= str_replace('bg-', '', $badgeClass) ?> border border-<?= str_replace('bg-', '', $badgeClass) ?>-subtle px-2 py-1">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="/ruangan/<?= $item['id'] ?>/detail" class="btn btn-sm btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="/ruangan/<?= $item['id'] ?>/edit" class="btn btn-sm btn-outline-info" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $item['id'] ?>)" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

    <!-- Gambar Ruangan Modal -->
    <div class="modal fade" id="ruanganImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="ruanganImageModalLabel">Gambar Ruangan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" alt="Gambar Ruangan" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#ruanganTable').DataTable({
                responsive: true,
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    },
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox display-4 d-block mb-2"></i>Tidak ada data ruangan</div>',
                    zeroRecords: "Data tidak ditemukan"
                },
                pageLength: 10,
                lengthChange: false,
                info: true
            });
        }

        $('#ruanganImageModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const image = button.data('image') || '';
            const name = button.data('name') || 'Ruangan';
            const modal = $(this);

            modal.find('.modal-title').text(`Gambar Ruangan: ${name}`);
            modal.find('img').attr('src', '/' + image.replace(/^\//, ''));
        });
    });

    function confirmDelete(id) {
        if (confirm('Apakah Anda yakin ingin menghapus ruangan ini?')) {
            window.location.href = `/ruangan/delete/${id}`;
        }
    }
</script>
</body>
</html>
