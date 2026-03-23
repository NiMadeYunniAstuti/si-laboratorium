<?php
$title = 'Peminjaman - LBMS';
$current_route = '/peminjaman';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="page-header px-0 mb-4">
        <div>
            <h1 class="page-title">Peminjaman</h1>
            <p class="page-subtitle">Kelola riwayat peminjaman alat laboratorium</p>
        </div>
        <div class="page-actions">
            <a href="/peminjaman/new" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Ajukan Peminjaman
            </a>
        </div>
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-primary">Data Peminjaman</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="peminjamanTable" class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" width="50">NO</th>
                            <th>PEMINJAM</th>
                            <th>ALAT</th>
                            <th>TANGGAL PINJAM</th>
                            <th>TANGGAL KEMBALI</th>
                            <th>STATUS</th>
                            <th class="text-end">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($peminjaman)): ?>
                            <?php foreach ($peminjaman as $index => $item): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td class="fw-medium text-dark"><?= htmlspecialchars($item['nama_peminjam'] ?? $item['user_name'] ?? $item['name'] ?? 'Unknown') ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($item['item_name'] ?? $item['nama_alat'] ?? 'Unknown') ?></div>
                                    <?php if (!empty($item['item_code'] ?? $item['kode_alat'] ?? '')): ?>
                                        <small class="text-muted"><i class="bi bi-tag me-1"></i><?= htmlspecialchars($item['item_code'] ?? $item['kode_alat'] ?? '') ?></small>
                                    <?php endif; ?>
                                    <?php if (!empty($item['item_type'])): ?>
                                        <span class="badge bg-light text-muted border ms-1" style="font-size:10px"><?= ucfirst($item['item_type']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small text-muted mb-1"><i class="bi bi-calendar-event me-1"></i>Tanggal Pinjam:</div>
                                    <div class="fw-semibold"><?= date('d M Y', strtotime($item['tanggal_pinjam'] ?? 'now')) ?></div>
                                </td>
                                <td>
                                    <div class="small text-muted mb-1"><i class="bi bi-calendar-check me-1"></i>Estimasi Kembali:</div>
                                    <div class="fw-semibold text-primary"><?= date('d M Y', strtotime($item['tanggal_kembali'] ?? 'now')) ?></div>
                                </td>
                                <td>
                                    <?php
                                    $status = strtoupper($item['status'] ?? 'PENDING');
                                    $badgeClass = match($status) {
                                        'SELESAI' => 'bg-success',
                                        'DIPINJAM' => 'bg-info',
                                        'PENDING' => 'bg-warning',
                                        'DITOLAK' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>-subtle text-<?= str_replace('bg-', '', $badgeClass) ?> border border-<?= str_replace('bg-', '', $badgeClass) ?>-subtle px-2 py-1">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/peminjaman/<?= $item['id'] ?>/detail" class="btn btn-outline-primary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($status === 'DIPINJAM'): ?>
                                            <button type="button" class="btn btn-outline-success kembalikan-peminjaman" data-id="<?= $item['id'] ?>" title="Kembalikan">
                                                <i class="bi bi-arrow-return-left"></i>
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
            $('#peminjamanTable').DataTable({
                responsive: true,
                language: {
                    paginate: {
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    },
                    emptyTable: '<div class="text-center py-4 text-muted"><i class="bi bi-inbox display-4 d-block mb-2"></i>Tidak ada data peminjaman</div>',
                    zeroRecords: "Data tidak ditemukan"
                },
                pageLength: 10,
                lengthChange: false,
                info: true,
                ordering: true,
                order: [[0, 'desc']]
            });
        }

        // Date Validation Logic
        const today = new Date().toISOString().split('T')[0];
        $('#tanggal_pinjam').prop('min', today);

        $('#tanggal_pinjam').on('change', function() {
            const pinjamDate = new Date($(this).val());
            pinjamDate.setDate(pinjamDate.getDate() + 1); 
            const minKembali = pinjamDate.toISOString().split('T')[0];
            $('#tanggal_kembali').prop('min', minKembali);

            if ($('#tanggal_kembali').val() && 
                new Date($('#tanggal_kembali').val()) <= new Date($(this).val())) {
                $('#tanggal_kembali').val('');
            }
        });

        $('.view-peminjaman').on('click', function() {
            const peminjamanId = $(this).data('id');
            window.location.href = `/peminjaman/${peminjamanId}/detail`;
        });

        $('.kembalikan-peminjaman').on('click', function() {
            const peminjamanId = $(this).data('id');
            if (confirm('Apakah Anda yakin ingin mengembalikan barang ini?')) {
                window.location.href = `/peminjaman/${peminjamanId}/return`;
            }
        });
    });
</script>
</body>
</html>