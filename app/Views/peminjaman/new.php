<?php
$title = 'Ajukan Peminjaman Baru - LBMS';
$current_route = '/peminjaman';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <div class="page-header px-0 mb-4">
        <div class="d-flex align-items-center">
            <a href="/peminjaman" class="btn btn-outline-primary btn-sm me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">Ajukan Peminjaman</h1>
                <p class="page-subtitle">Pilih alat dan tentukan durasi peminjaman</p>
            </div>
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
            <h5 class="mb-0 fw-bold text-primary">
                <i class="bi bi-plus-circle me-2"></i>Form Pengajuan
            </h5>
        </div>
        <div class="card-body p-4">
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
                                <label for="catatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="4" placeholder="Tambahkan catatan atau keterangan tambahan"></textarea>
                                <small class="form-text text-muted">Catatan tambahan jika diperlukan (opsional)</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-box me-1"></i>Detail Peminjaman
                            </h6>

                            <!-- Step 1: Choose Type -->
                            <div class="mb-3">
                                <label class="form-label">Jenis Peminjaman</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_item" id="jenis_alat" value="alat" checked>
                                    <label class="form-check-label" for="jenis_alat"><i class="bi bi-tools me-1"></i> Alat</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="jenis_item" id="jenis_ruangan" value="ruangan">
                                    <label class="form-check-label" for="jenis_ruangan"><i class="bi bi-door-open me-1"></i> Ruangan</label>
                                </div>
                            </div>

                            <!-- Step 2: Select Alat (shown when Alat selected) -->
                            <div id="alat_section" class="mb-3">
                                <label for="alat_id" class="form-label">Pilih Alat</label>
                                <select class="form-control" id="alat_id" name="alat_id" required <?= empty($alatList) ? 'disabled' : '' ?>>
                                    <option value="">Pilih Alat</option>
                                    <?php if (!empty($alatList)): ?>
                                        <?php foreach ($alatList as $alat): ?>
                                            <option value="<?= $alat['id'] ?>">
                                                <?= htmlspecialchars($alat['nama_alat'] ?? 'Alat') ?>
                                                (<?= htmlspecialchars($alat['kode_alat'] ?? '-') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada alat tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <?= empty($alatList) ? 'Belum ada alat tersedia untuk dipinjam.' : 'Pilih alat yang akan dipinjam.' ?>
                                </small>
                            </div>

                            <!-- Step 2: Select Ruangan (hidden by default) -->
                            <div id="ruangan_section" class="mb-3" style="display: none;">
                                <label for="ruangan_id" class="form-label">Pilih Ruangan</label>
                                <select class="form-control select2-enable" id="ruangan_id" name="ruangan_id">
                                    <option value="">Pilih Ruangan</option>
                                    <?php if (!empty($ruanganList)): ?>
                                        <?php foreach ($ruanganList as $ruangan): ?>
                                            <option value="<?= $ruangan['id'] ?>">
                                                <?= htmlspecialchars($ruangan['nama_ruangan'] ?? 'Ruangan') ?>
                                                (<?= htmlspecialchars($ruangan['kode_ruangan'] ?? '-') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <option value="">Tidak ada ruangan tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <?= empty($ruanganList) ? 'Belum ada ruangan tersedia untuk dipinjam.' : 'Pilih ruangan yang akan dipinjam.' ?>
                                </small>
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

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        // Select2 for Alat Selection
        if (!$('#alat_id').prop('disabled')) {
            $('#alat_id').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Pilih alat',
                allowClear: true
            });
        }
        // Toggle between Alat and Ruangan sections
        $('input[name="jenis_item"]').on('change', function() {
            const jenis = $(this).val();
            if (jenis === 'alat') {
                $('#alat_section').show();
                $('#ruangan_section').hide();
                $('#alat_id').prop('required', true);
                $('#ruangan_id').prop('required', false);
                // Initialize Select2 for Alat if not disabled
                if (!$('#alat_id').prop('disabled') && !$('#alat_id').data('select2')) {
                    $('#alat_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Pilih alat',
                        allowClear: true
                    });
                }
            } else {
                $('#alat_section').hide();
                $('#ruangan_section').show();
                $('#alat_id').prop('required', false);
                $('#ruangan_id').prop('required', true);
                // Initialize Select2 for Ruangan
                if (!$('#ruangan_id').data('select2')) {
                    $('#ruangan_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Pilih ruangan',
                        allowClear: true
                    });
                }
            }
        });
        // DateTime Logic
        const now = new Date();
        const pad = (value) => String(value).padStart(2, '0');
        const todayStr = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
        const minDateTime = `${todayStr}T00:00`;
        
        $('#tanggal_pinjam').attr('min', minDateTime);
        $('#tanggal_kembali').attr('min', minDateTime);

        $('#tanggal_pinjam').on('change', function() {
            const pinjamDate = new Date($(this).val());
            if (isNaN(pinjamDate.getTime())) return;

            const minKembali = new Date(pinjamDate.getTime() + (24 * 60 * 60 * 1000)); 
            const minKembaliStr = `${minKembali.getFullYear()}-${pad(minKembali.getMonth() + 1)}-${pad(minKembali.getDate())}T${pad(minKembali.getHours())}:${pad(minKembali.getMinutes())}`;
            $('#tanggal_kembali').attr('min', minKembaliStr);

            // Default: 1 week later
            const defaultKembali = new Date(pinjamDate.getTime() + (7 * 24 * 60 * 60 * 1000));
            const defaultKembaliStr = `${defaultKembali.getFullYear()}-${pad(defaultKembali.getMonth() + 1)}-${pad(defaultKembali.getDate())}T${pad(defaultKembali.getHours())}:${pad(defaultKembali.getMinutes())}`;
            $('#tanggal_kembali').val(defaultKembaliStr);
        });

        // Form Validation
        $('#tambahPeminjamanForm').on('submit', function(e) {
            const tanggalPinjam = new Date($('#tanggal_pinjam').val());
            const tanggalKembali = new Date($('#tanggal_kembali').val());
            const now = new Date();
            now.setSeconds(0, 0);

            if (isNaN(tanggalPinjam.getTime())) {
                e.preventDefault();
                alert('Silakan pilih tanggal pinjam yang valid');
                return false;
            }

            if (tanggalKembali <= tanggalPinjam) {
                e.preventDefault();
                alert('Tanggal kembali harus setelah tanggal pinjam');
                return false;
            }

            return true;
        });
    });
</script>
</body>
</html>
